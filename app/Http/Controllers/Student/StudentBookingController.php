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
        // Lấy lịch rảnh của gia sư cho 14 ngày tới
        $startDate = now()->startOfDay();
        $endDate = now()->addDays(14)->endOfDay();
        
        // Thêm log để theo dõi
        \Illuminate\Support\Facades\Log::info('Lấy lịch rảnh của gia sư', [
            'tutor_id' => $tutor->id,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d')
        ]);
        
        // Lấy lịch rảnh cụ thể theo ngày
        $dateSpecificAvailabilities = $tutor->availabilities()
            ->where('status', 'active')
            ->whereNotNull('date')
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();
        
        \Illuminate\Support\Facades\Log::info('Lịch rảnh cụ thể theo ngày', [
            'count' => $dateSpecificAvailabilities->count()
        ]);
        
        // Lấy lịch rảnh theo ngày trong tuần (lặp lại hàng tuần)
        $weeklyAvailabilities = $tutor->availabilities()
            ->where('status', 'active')
            ->where('is_recurring', true)
            ->whereNull('date')
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();
        
        \Illuminate\Support\Facades\Log::info('Lịch rảnh lặp lại hàng tuần', [
            'count' => $weeklyAvailabilities->count(),
            'sample' => $weeklyAvailabilities->take(2)->toArray()
        ]);
        
        // Tạo mảng chứa lịch rảnh theo ngày
        $availabilitiesByDate = [];
        
        // Thêm lịch rảnh cụ thể theo ngày
        foreach ($dateSpecificAvailabilities as $availability) {
            $date = $availability->date->format('Y-m-d');
            if (!isset($availabilitiesByDate[$date])) {
                $availabilitiesByDate[$date] = [];
            }
            $availabilitiesByDate[$date][] = $availability;
        }
        
        // Thêm lịch rảnh hàng tuần vào các ngày tương ứng
        $currentDate = clone $startDate;
        while ($currentDate <= $endDate) {
            $dayOfWeek = $currentDate->dayOfWeek; // 0 (Chủ nhật) đến 6 (Thứ bảy)
            $currentDateStr = $currentDate->format('Y-m-d');
            
            // Nếu chưa có lịch rảnh cụ thể cho ngày này, thêm lịch rảnh hàng tuần
            if (!isset($availabilitiesByDate[$currentDateStr])) {
                $availabilitiesByDate[$currentDateStr] = [];
            }
            
            // Lọc và thêm các lịch rảnh hàng tuần cho ngày này
            foreach ($weeklyAvailabilities as $availability) {
                if ((int)$availability->day_of_week === $dayOfWeek) {
                    $availabilitiesByDate[$currentDateStr][] = $availability;
                }
            }
            
            // Nếu không có lịch rảnh nào cho ngày này, xóa ngày đó khỏi mảng
            if (empty($availabilitiesByDate[$currentDateStr])) {
                unset($availabilitiesByDate[$currentDateStr]);
            }
            
            $currentDate->addDay();
        }
        
        \Illuminate\Support\Facades\Log::info('Tổng số ngày có lịch rảnh trước khi lọc', [
            'count' => count($availabilitiesByDate),
            'dates' => array_keys($availabilitiesByDate)
        ]);
        
        // Lọc ra các khung giờ rảnh thực sự (không trùng với booking đã có và chưa qua)
        foreach ($availabilitiesByDate as $date => &$availabilities) {
            $dateObj = \Carbon\Carbon::parse($date);
            
            // Lọc các khung giờ phù hợp
            $availabilities = collect($availabilities)->filter(function($availability) use ($dateObj, $tutor) {
                // Tạo đối tượng datetime đầy đủ từ date và time
                $startDateTime = clone $dateObj;
                $endDateTime = clone $dateObj;
                
                // Thêm giờ và phút
                $startDateTime->setTime(
                    $availability->start_time->format('H'), 
                    $availability->start_time->format('i')
                );
                $endDateTime->setTime(
                    $availability->end_time->format('H'), 
                    $availability->end_time->format('i')
                );
                
                // Nếu thời gian đã qua, bỏ qua
                if ($startDateTime < now()) {
                    return false;
                }
                
                // Kiểm tra xem đã có booking nào vào khung giờ này chưa
                $hasConflict = Booking::where('tutor_id', $tutor->id)
                    ->whereIn('status', ['confirmed', 'scheduled', 'pending'])
                    ->where(function($query) use ($startDateTime, $endDateTime) {
                        $query->whereBetween('start_time', [$startDateTime, $endDateTime])
                            ->orWhereBetween('end_time', [$startDateTime, $endDateTime])
                            ->orWhere(function($q) use ($startDateTime, $endDateTime) {
                                $q->where('start_time', '<=', $startDateTime)
                                  ->where('end_time', '>=', $endDateTime);
                            });
                    })->exists();
                
                return !$hasConflict;
            })->values()->toArray();
            
            // Nếu không còn khung giờ nào sau khi lọc, xóa ngày đó
            if (count($availabilities) === 0) {
                unset($availabilitiesByDate[$date]);
            }
        }
        
        \Illuminate\Support\Facades\Log::info('Số ngày có lịch rảnh sau khi lọc', [
            'count' => count($availabilitiesByDate),
            'dates' => array_keys($availabilitiesByDate)
        ]);
        
        // Sắp xếp mảng theo thứ tự ngày
        ksort($availabilitiesByDate);
        
        return view('student.bookings.create', [
            'tutor' => $tutor->load(['subjects', 'classLevels']),
            'availabilitiesByDate' => $availabilitiesByDate
        ]);
    }

    public function store(Request $request, Tutor $tutor)
    {
        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'time_slot' => 'required|string',
            'selected_date' => 'required|date',
            'notes' => 'nullable|string|max:500',
        ]);

        // Phân tích time_slot để lấy giờ bắt đầu và kết thúc
        $timeSlotParts = explode('_', $validated['time_slot']);
        if (count($timeSlotParts) !== 2) {
            return back()->withInput()->with('error', 'Định dạng khung giờ không hợp lệ.');
        }

        $startTimeStr = $timeSlotParts[0];
        $endTimeStr = $timeSlotParts[1];

        // Tạo đối tượng datetime từ ngày và giờ
        $startTime = Carbon::parse($validated['selected_date'] . ' ' . $startTimeStr);
        $endTime = Carbon::parse($validated['selected_date'] . ' ' . $endTimeStr);

        // Kiểm tra xem thời gian có nằm trong quá khứ không
        if ($startTime->isPast()) {
            return back()->withInput()->with('error', 'Không thể đặt lịch cho thời gian đã qua.');
        }

        // Kiểm tra xem thời gian đặt lịch có nằm trong lịch rảnh của gia sư không
        if (!$this->isWithinTutorAvailability($tutor, $startTime, $endTime)) {
            return back()->withInput()->with('error', 'Thời gian bạn chọn không nằm trong lịch rảnh của gia sư. Vui lòng chọn thời gian khác.');
        }

        // Kiểm tra xem thời gian đặt lịch có trùng với lịch đã đặt của gia sư không
        if ($this->hasBookingConflict($tutor, $startTime, $endTime)) {
            return back()->withInput()->with('error', 'Gia sư đã có lịch dạy vào thời gian này. Vui lòng chọn thời gian khác.');
        }

        // Lấy giá theo môn học
        $subject = $tutor->subjects()->findOrFail($validated['subject_id']);
        $pricePerHour = $subject->pivot->price_per_hour ?? $tutor->hourly_rate;
        
        // Tính thời lượng và tổng tiền
        $durationHours = $startTime->diffInMinutes($endTime) / 60;
        $totalAmount = $pricePerHour * $durationHours;

        // Tạo booking
        try {
            DB::beginTransaction();
            
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
            
            DB::commit();
            
            // Chuyển hướng đến trang thanh toán
            return redirect()->route('payment.create', ['booking' => $booking])
                ->with('success', 'Yêu cầu đặt lịch đã được tạo. Vui lòng hoàn tất thanh toán.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Lỗi khi tạo booking', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withInput()->with('error', 'Có lỗi xảy ra khi đặt lịch. Vui lòng thử lại sau.');
        }
    }

    /**
     * Kiểm tra xem thời gian có nằm trong lịch rảnh của gia sư hay không
     */
    private function isWithinTutorAvailability($tutor, $startTime, $endTime)
    {
        $date = $startTime->toDateString();
        $dayOfWeek = $startTime->dayOfWeek;
        
        // Lấy giờ và phút để so sánh
        $startTimeFormatted = $startTime->format('H:i');
        $endTimeFormatted = $endTime->format('H:i');
        
        // Kiểm tra lịch rảnh cụ thể theo ngày trước
        $dateSpecificAvailability = $tutor->availabilities()
            ->where('date', $date)
            ->where('status', 'active')
            ->get();
            
        // Kiểm tra từng khung giờ rảnh cụ thể theo ngày
        foreach ($dateSpecificAvailability as $availability) {
            $availStartTime = $availability->start_time->format('H:i');
            $availEndTime = $availability->end_time->format('H:i');
            
            // Nếu khung giờ đặt lịch nằm hoàn toàn trong khung giờ rảnh của gia sư
            if ($startTimeFormatted >= $availStartTime && $endTimeFormatted <= $availEndTime) {
                // Kiểm tra xem lịch rảnh này có trùng với booking nào khác không
                if (!$this->hasBookingConflict($tutor, $startTime, $endTime)) {
                    return true;
                }
            }
        }
        
        // Nếu không có lịch rảnh cụ thể theo ngày, kiểm tra lịch rảnh lặp lại hàng tuần
        if ($dateSpecificAvailability->isEmpty()) {
            $weeklyAvailabilities = $tutor->availabilities()
                ->where('day_of_week', $dayOfWeek)
                ->where('is_recurring', true)
                ->where('status', 'active')
                ->whereNull('date')
                ->get();
                
            foreach ($weeklyAvailabilities as $availability) {
                $availStartTime = $availability->start_time->format('H:i');
                $availEndTime = $availability->end_time->format('H:i');
                
                // Nếu khung giờ đặt lịch nằm hoàn toàn trong khung giờ rảnh của gia sư
                if ($startTimeFormatted >= $availStartTime && $endTimeFormatted <= $availEndTime) {
                    // Kiểm tra xem lịch rảnh này có trùng với booking nào khác không
                    if (!$this->hasBookingConflict($tutor, $startTime, $endTime)) {
                        return true;
                    }
                }
            }
        }
        
        return false;
    }

    /**
     * Kiểm tra xem có bị trùng với lịch dạy khác không
     */
    private function hasBookingConflict($tutor, $startTime, $endTime)
    {
        return Booking::where('tutor_id', $tutor->id)
            ->whereIn('status', ['confirmed', 'scheduled', 'pending'])
            ->where(function($query) use ($startTime, $endTime) {
                $query->whereBetween('start_time', [$startTime, $endTime])
                    ->orWhereBetween('end_time', [$startTime, $endTime])
                    ->orWhere(function($q) use ($startTime, $endTime) {
                        $q->where('start_time', '<=', $startTime)
                          ->where('end_time', '>=', $endTime);
                    });
            })
            ->exists();
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