<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Tutor;
use App\Models\Booking;
use App\Models\Subject;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class StudentBookingController extends Controller
{
    public function index()
    {
        $bookings = Auth::user()->bookings()
            ->with(['tutor.user', 'subject'])
            ->latest()
            ->paginate(10);

        return view('student.bookings.index', compact('bookings'));
    }

    public function create(Tutor $tutor)
    {
        return view('student.bookings.create', [
            'tutor' => $tutor->load(['subjects', 'classLevels']),
        ]);
    }

    public function store(Request $request, Tutor $tutor)
    {
        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'start_time' => 'required|date|after:now',
            'duration' => 'required|integer|min:1|max:4',
            'notes' => 'nullable|string|max:500',
        ]);

        // Tính thời gian kết thúc
        $startTime = Carbon::parse($validated['start_time']);
        $duration = intval($validated['duration']);
        $endTime = Carbon::parse($validated['start_time'])->addHours($duration);

        // Lấy giá theo môn học
        $subject = $tutor->subjects()->findOrFail($validated['subject_id']);
        $pricePerHour = $subject->pivot->price_per_hour ?? $tutor->hourly_rate;
        $totalAmount = $pricePerHour * $duration;

        // Tạo booking
        $booking = new Booking([
            'student_id' => Auth::id(),
            'tutor_id' => $tutor->id,
            'subject_id' => $validated['subject_id'],
            'start_time' => $startTime,
            'end_time' => $endTime,
            'status' => 'pending',
            'notes' => $validated['notes'],
            'price_per_hour' => $pricePerHour,
            'total_amount' => $totalAmount,
        ]);

        $booking->save();

        // Chuyển hướng đến trang thanh toán
        return redirect()->route('payment.create', ['booking' => $booking])
            ->with('success', 'Yêu cầu đặt lịch đã được tạo. Vui lòng hoàn tất thanh toán.');
    }

    public function show(Booking $booking)
    {
        if ($booking->student_id !== Auth::id()) {
            abort(403);
        }

        return view('student.bookings.show', [
            'booking' => $booking->load(['tutor.user', 'subject'])
        ]);
    }

    /**
     * Hủy buổi học
     */
    public function cancel(Request $request, Booking $booking)
    {
        if ($booking->student_id !== Auth::id()) {
            abort(403);
        }

        if (!in_array($booking->status, ['pending', 'confirmed', 'scheduled'])) {
            return back()->with('error', 'Không thể hủy buổi học này.');
        }

        // Kiểm tra nếu booking đã được thanh toán thành công
        $hasSuccessfulPayment = $booking->payments()->where('status', 'completed')->exists();
        
        if ($hasSuccessfulPayment) {
            // Kiểm tra thời gian hủy so với thời gian bắt đầu buổi học
            $hoursUntilBooking = now()->diffInHours($booking->start_time, false);
            
            // Nếu thời gian hủy quá gần buổi học và không được hoàn tiền
            if ($hoursUntilBooking < 12) {
                return back()->with('error', 'Không thể hủy buổi học này vì bạn đã thanh toán và thời gian hủy quá gần so với thời gian học. Vui lòng liên hệ hỗ trợ nếu cần giúp đỡ.');
            }
        }

        $validated = $request->validate([
            'cancelled_reason' => 'required|string|max:500',
        ]);

        // Kiểm tra thời gian hủy so với thời gian bắt đầu buổi học
        $hoursUntilBooking = now()->diffInHours($booking->start_time, false);
        $refundPercentage = 0;
        
        if ($hoursUntilBooking >= 24) {
            // Hủy trước 24h: hoàn 100%
            $refundPercentage = 100; 
        } elseif ($hoursUntilBooking >= 12) {
            // Hủy trước 12h: hoàn 50%
            $refundPercentage = 50;
        }
        // Hủy dưới 12h: không hoàn tiền

        $booking->update([
            'status' => 'cancelled',
            'cancelled_reason' => $validated['cancelled_reason'],
            'cancelled_by' => 'student',
            'refund_percentage' => $refundPercentage
        ]);

        // Xử lý hoàn tiền nếu đã thanh toán
        $message = "";
        if ($hasSuccessfulPayment) {
            if ($refundPercentage > 0) {
                // Tạo bản ghi hoàn tiền mới trong bảng payments
                $originalPayment = $booking->payments()->where('status', 'completed')->first();
                
                if ($originalPayment) {
                    $refundAmount = ($booking->total_amount * $refundPercentage / 100);
                    
                    // Tạo một payment mới cho việc hoàn tiền
                    $refundPayment = $booking->payments()->create([
                        'vnp_txn_ref' => 'REFUND-' . Str::random(10),
                        'amount' => -$refundAmount, // Giá trị âm để thể hiện hoàn tiền
                        'status' => 'completed',
                        'payment_method' => $originalPayment->payment_method,
                        'bank_code' => $originalPayment->bank_code,
                        'paid_at' => now(),
                        'response_data' => [
                            'refund_for' => $originalPayment->id,
                            'refund_percentage' => $refundPercentage,
                            'original_amount' => $booking->total_amount,
                            'refund_type' => 'cancellation'
                        ]
                    ]);
                    
                    // Trong môi trường thực tế, gọi service xử lý hoàn tiền:
                    // $paymentService->processRefund($booking, $refundPercentage, $refundPayment);
                }
                
                // Ghi chú về hoàn tiền
                $refundMessage = "Buổi học đã được hủy. ";
                if ($refundPercentage == 100) {
                    $refundMessage .= "Bạn sẽ được hoàn lại 100% học phí (" . number_format($booking->total_amount, 0, ',', '.') . "đ).";
                } elseif ($refundPercentage == 50) {
                    $refundAmount = $booking->total_amount * 0.5;
                    $refundMessage .= "Bạn sẽ được hoàn lại 50% học phí (" . number_format($refundAmount, 0, ',', '.') . "đ) do hủy trước 12 giờ.";
                }
                
                $message = $refundMessage;
            } else {
                $message = "Buổi học đã được hủy. Bạn sẽ không được hoàn tiền do hủy quá gần thời gian học.";
            }
        } else {
            $message = "Buổi học đã được hủy thành công.";
        }

        // Thông báo cho gia sư về việc hủy buổi học
        if ($booking->tutor && $booking->tutor->user) {
            // $booking->tutor->user->notify(new BookingCancelled($booking));
            
            // Tạo thông báo trong hệ thống
            $booking->tutor->user->notifications()->create([
                'type' => 'App\Notifications\BookingCancelled',
                'data' => [
                    'booking_id' => $booking->id,
                    'student_name' => Auth::user()->name,
                    'subject' => $booking->subject->name,
                    'time' => $booking->start_time->format('d/m/Y H:i'),
                    'reason' => $validated['cancelled_reason']
                ],
            ]);
        }

        // Lưu lịch sử hủy buổi học
        Log::info('Học sinh hủy buổi học', [
            'booking_id' => $booking->id,
            'student_id' => Auth::id(),
            'reason' => $validated['cancelled_reason'],
            'refund_percentage' => $refundPercentage,
            'cancelled_at' => now()->toDateTimeString()
        ]);

        return back()->with('success', $message);
    }

    /**
     * Hiển thị danh sách gia sư đang thuê và đã thuê
     */
    public function tutors()
    {
        $user = Auth::user();
        
        // Lấy danh sách gia sư hiện tại (có booking với trạng thái confirmed hoặc scheduled)
        $currentTutors = Tutor::whereHas('bookings', function($query) use ($user) {
            $query->where('student_id', $user->id)
                  ->whereIn('status', ['confirmed', 'scheduled'])
                  ->where('end_time', '>=', now());
        })
        ->with(['user', 'subjects', 'bookings' => function($query) use ($user) {
            $query->where('student_id', $user->id)
                  ->whereIn('status', ['confirmed', 'scheduled'])
                  ->where('end_time', '>=', now())
                  ->latest();
        }])
        ->get()
        ->map(function($tutor) {
            $tutor->latest_booking = $tutor->bookings->first();
            return $tutor;
        });
        
        // Lấy danh sách gia sư đã thuê trong quá khứ (booking đã hoàn thành hoặc đã hết hạn)
        $pastTutors = Tutor::whereHas('bookings', function($query) use ($user) {
            $query->where('student_id', $user->id)
                  ->where(function($q) {
                      $q->where('status', 'completed')
                        ->orWhere(function($q2) {
                            $q2->whereIn('status', ['confirmed', 'scheduled'])
                               ->where('end_time', '<', now());
                        });
                  });
        })
        ->with(['user', 'subjects', 'reviews' => function($query) use ($user) {
            $query->where('student_id', $user->id);
        }])
        ->get();
        
        return view('student.bookings.tutors', compact('currentTutors', 'pastTutors'));
    }
} 