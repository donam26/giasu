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
        $user = Auth::user();
        
        // Sắp xếp ưu tiên buổi học sắp tới trước, sau đó đến buổi học đã qua
        $bookings = Booking::with(['tutor.user', 'subject'])
            ->where('student_id', $user->id)
            ->orderBy(DB::raw('start_time >= NOW()'), 'desc') // Buổi học sắp tới lên đầu
            ->orderBy('start_time', 'asc') // Trong cùng nhóm, sắp xếp theo thời gian gần nhất
            ->paginate(10);
        
        return view('student.bookings.index', compact('bookings'));
    }

    public function create(Tutor $tutor)
    {
        $subjects = $tutor->subjects;
        
        // Lấy lịch rảnh của gia sư (cả lịch cụ thể và lịch lặp lại)
        $availabilities = $tutor->availabilities()
            ->where(function ($query) {
                $query->where('start_time', '>', now()) // Lịch rảnh cụ thể trong tương lai
                    ->orWhere('is_recurring', true); // Hoặc lịch lặp lại
            })
            ->where('status', 'active')
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();
        
        // Lấy danh sách booking đã có của gia sư (không bao gồm cancelled)
        $existingBookings = Booking::where('tutor_id', $tutor->id)
            ->where('status', '!=', Booking::STATUS_CANCELLED)
            ->get();
        
        // Nhóm lịch theo ngày để hiển thị trong tabs
        $availabilitiesByDate = [];
        
        // Sử dụng cùng logic xử lý như trong component tutors.availability.blade.php
        $dayNamesFull = ['Chủ Nhật', 'Thứ Hai', 'Thứ Ba', 'Thứ Tư', 'Thứ Năm', 'Thứ Sáu', 'Thứ Bảy'];
        
        foreach ($availabilities as $availability) {
            if ($availability->date) {
                // Sử dụng ngày cụ thể
                $dateKey = $availability->date->format('Y-m-d');
                
                if (!isset($availabilitiesByDate[$dateKey])) {
                    $availabilitiesByDate[$dateKey] = [];
                }
                
                // Kiểm tra xung đột với booking đã có
                $hasConflict = $this->checkTimeSlotConflict(
                    $availability->start_time, 
                    $availability->end_time, 
                    $existingBookings
                );
                
                if (!$hasConflict) {
                    $availabilitiesByDate[$dateKey][] = [
                        'id' => $availability->id,
                        'start_time' => $availability->start_time,
                        'end_time' => $availability->end_time
                    ];
                }
            } elseif ($availability->is_recurring) {
                // Ngày hiện tại + lặp qua 7 ngày tới để tìm ngày thích hợp
                $today = Carbon::now();
                $dayOfWeek = $availability->day_of_week;
                
                // Tìm ngày tiếp theo có thứ tương ứng
                $daysToAdd = ($dayOfWeek - $today->dayOfWeek + 7) % 7;
                if ($daysToAdd == 0) $daysToAdd = 7; // Nếu là thứ của hôm nay, lấy ngày của tuần sau
                
                $nextDate = $today->copy()->addDays($daysToAdd);
                $dateKey = $nextDate->format('Y-m-d');
                
                // Tạo khung giờ cho ngày này
                $startDateTime = Carbon::parse($dateKey . ' ' . $availability->start_time->format('H:i:s'));
                $endDateTime = Carbon::parse($dateKey . ' ' . $availability->end_time->format('H:i:s'));
                
                // Bỏ qua nếu thời gian đã qua
                if ($startDateTime->isPast()) continue;
                
                if (!isset($availabilitiesByDate[$dateKey])) {
                    $availabilitiesByDate[$dateKey] = [];
                }
                
                // Kiểm tra xung đột với booking đã có
                $hasConflict = $this->checkTimeSlotConflict(
                    $startDateTime, 
                    $endDateTime, 
                    $existingBookings
                );
                
                if (!$hasConflict) {
                    $availabilitiesByDate[$dateKey][] = [
                        'id' => $availability->id,
                        'start_time' => $startDateTime,
                        'end_time' => $endDateTime,
                    ];
                }
            }
        }
        
        // Sắp xếp lịch theo ngày
        ksort($availabilitiesByDate);
        
        return view('student.bookings.create', compact('tutor', 'subjects', 'availabilitiesByDate'));
    }

    public function store(Request $request, Tutor $tutor)
    {
        // Validate dữ liệu đầu vào
        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'selected_date' => 'required|date',
            'time_slot' => 'required',
            'notes' => 'nullable|string',
        ], [
            'subject_id.required' => 'Vui lòng chọn môn học',
            'subject_id.exists' => 'Môn học đã chọn không hợp lệ',
            'selected_date.required' => 'Vui lòng chọn ngày học',
            'selected_date.date' => 'Ngày học không hợp lệ',
            'time_slot.required' => 'Vui lòng chọn khung giờ học',
        ]);
        
        // Tách thời gian bắt đầu và kết thúc từ time_slot (format: 'HH:MM_HH:MM')
        $timeSlotParts = explode('_', $validated['time_slot']);
        if (count($timeSlotParts) !== 2) {
            return back()->with('error', 'Định dạng thời gian không hợp lệ');
        }
        
        $startTime = Carbon::parse($validated['selected_date'] . ' ' . $timeSlotParts[0]);
        $endTime = Carbon::parse($validated['selected_date'] . ' ' . $timeSlotParts[1]);
        
        // Đảm bảo endTime > startTime
        if ($endTime->lessThanOrEqualTo($startTime)) {
            return back()->with('error', 'Thời gian kết thúc phải sau thời gian bắt đầu');
        }
        
        // Kiểm tra xung đột lịch học với các booking đã có
        $conflictingBooking = Booking::where('tutor_id', $tutor->id)
            ->where('status', '!=', Booking::STATUS_CANCELLED)
            ->where(function ($query) use ($startTime, $endTime) {
                // Kiểm tra overlap: booking mới bắt đầu trước khi booking cũ kết thúc
                // và booking mới kết thúc sau khi booking cũ bắt đầu
                $query->where(function ($q) use ($startTime, $endTime) {
                    $q->where('start_time', '<', $endTime)
                      ->where('end_time', '>', $startTime);
                });
            })
            ->first();
            
        if ($conflictingBooking) {
            $conflictStudent = $conflictingBooking->student;
            $conflictTime = $conflictingBooking->start_time->format('H:i') . ' - ' . $conflictingBooking->end_time->format('H:i');
            
            return back()
                ->withInput()
                ->with('error', "Khung giờ {$conflictTime} đã có học viên khác đặt lịch. Vui lòng chọn khung giờ khác.");
        }
        
        // SỬA LỖI: Tính số giờ từ thời gian bắt đầu đến kết thúc
        // Trước đây: $minutes = $endTime->diffInMinutes($startTime);
        $minutes = $startTime->diffInMinutes($endTime);
        $hours = floor($minutes / 60);
        
        // Nếu có phút lẻ (từ 30 phút trở lên), thêm 0.5 giờ
        if ($minutes % 60 >= 30) {
            $hours += 0.5;
        }
      
        // Lấy giá của môn học cụ thể nếu có, nếu không thì dùng giá mặc định của gia sư
        $subject = $tutor->subjects()->findOrFail($validated['subject_id']);
        $pricePerHour = $subject->pivot->price_per_hour ?? $tutor->hourly_rate;
        
        $totalAmount = $pricePerHour * $hours;
        
        // Log để kiểm tra
        \Illuminate\Support\Facades\Log::info('Tính toán đặt lịch', [
            'start_time' => $startTime->format('H:i'),
            'end_time' => $endTime->format('H:i'),
            'minutes' => $minutes,
            'hours' => $hours,
            'price_per_hour' => $pricePerHour,
            'total_amount' => $totalAmount
        ]);
        
        $booking = Booking::create([
            'student_id' => Auth::id(),
            'tutor_id' => $tutor->id,
            'subject_id' => $validated['subject_id'],
            'start_time' => $startTime,
            'end_time' => $endTime,
            'day_of_week' => $startTime->dayOfWeek,
            'status' => Booking::STATUS_PENDING,
            'notes' => $validated['notes'],
            'price_per_hour' => $pricePerHour,
            'total_amount' => $totalAmount,
        ]);
        
        // Chuyển hướng đến trang thanh toán
        return redirect()->route('payment.create', $booking)
            ->with('success', 'Đã tạo đặt lịch thành công, vui lòng hoàn tất thanh toán');
    }

    public function show(Booking $booking)
    {
        if ($booking->student_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền xem buổi học này');
        }
        
        $booking->load(['tutor.user', 'subject', 'payments', 'tutorEarning']);
        
        return view('student.bookings.show', compact('booking'));
    }

    public function cancel(Request $request, Booking $booking)
    {
        if ($booking->student_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền hủy buổi học này');
        }
        
        if (!in_array($booking->status, [Booking::STATUS_PENDING, Booking::STATUS_CONFIRMED])) {
            return back()->with('error', 'Không thể hủy buổi học ở trạng thái hiện tại');
        }
        
        // Logic tính % hoàn tiền dựa vào thời gian hủy
        $refundPercentage = 0;
        $hoursUntilStart = now()->diffInHours($booking->start_time, false);
        
        if ($hoursUntilStart > 24) {
            $refundPercentage = 100; // Hoàn tiền 100% nếu hủy trước 24h
        } elseif ($hoursUntilStart > 12) {
            $refundPercentage = 50; // Hoàn tiền 50% nếu hủy trước 12h
        } // Không hoàn tiền nếu hủy trước ít hơn 12h
        
        $booking->update([
            'status' => Booking::STATUS_CANCELLED,
            'cancelled_reason' => $request->reason,
            'cancelled_by' => 'student',
            'refund_percentage' => $refundPercentage
        ]);
        
        // Xử lý hoàn tiền nếu có
        if ($refundPercentage > 0 && $booking->status === Booking::STATUS_CONFIRMED) {
            $refundAmount = $booking->calculateRefundAmount();
            
            // Cập nhật trạng thái thanh toán
            $newPaymentStatus = $refundPercentage == 100 
                ? Booking::PAYMENT_STATUS_REFUNDED 
                : Booking::PAYMENT_STATUS_PARTIAL_REFUNDED;
                
            $booking->update(['status' => $newPaymentStatus]);
            
            // TODO: Xử lý hoàn tiền qua cổng thanh toán
        }
        
        return redirect()->route('student.bookings.index')
            ->with('success', 'Đã hủy lịch học thành công' . 
                ($refundPercentage > 0 ? '. Bạn sẽ được hoàn lại ' . $refundPercentage . '% học phí' : ''));
    }

    /**
     * Đặt lại buổi học (hủy booking cũ và chuyển đến trang đặt lại)
     */
    public function rebook(Request $request, Booking $booking)
    {
        // Kiểm tra quyền truy cập
        if ($booking->student_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền đặt lại buổi học này');
        }
        
        // Chỉ cho phép đặt lại các booking đang ở trạng thái "pending" (chờ xác nhận)
        if ($booking->status !== Booking::STATUS_PENDING) {
            return back()->with('error', 'Chỉ có thể đặt lại những buổi học đang chờ xác nhận');
        }
        
        // Lưu thông tin cần thiết trước khi hủy
        $tutorId = $booking->tutor_id;
        $subjectId = $booking->subject_id;
        $notes = $booking->notes;
        
        try {
            DB::beginTransaction();
            
            // Hủy booking cũ
            $booking->update([
                'status' => Booking::STATUS_CANCELLED,
                'cancelled_reason' => 'Đặt lại bởi học sinh',
                'cancelled_by' => 'student',
                'refund_percentage' => 100 // Hoàn tiền 100% vì chưa được xác nhận
            ]);
            
            // Xử lý hoàn tiền nếu đã thanh toán
            if ($booking->payment_status === 'paid') {
                $booking->update(['payment_status' => 'refunded']);
                // TODO: Xử lý hoàn tiền qua cổng thanh toán nếu cần
            }
            
            DB::commit();
            
            // Chuyển hướng đến trang đặt lại với thông tin cũ
            return redirect()->route('student.bookings.create', ['tutor' => $tutorId])
                ->with('rebook_info', [
                    'subject_id' => $subjectId,
                    'notes' => $notes,
                    'original_booking_id' => $booking->id
                ])
                ->with('success', 'Đã hủy buổi học cũ. Vui lòng chọn lịch mới để đặt lại.');
                
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Lỗi khi đặt lại booking: ' . $e->getMessage());
            
            return back()->with('error', 'Có lỗi xảy ra khi đặt lại. Vui lòng thử lại sau.');
        }
    }
    
    /**
     * Xác nhận hoàn thành buổi học (từ phía học sinh)
     */
    public function confirmCompletion(Booking $booking)
    {
        if ($booking->student_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền xác nhận buổi học này');
        }
        
        // Kiểm tra xem buổi học đã kết thúc chưa
        if (!$booking->hasEnded()) {
            return back()->with('error', 'Buổi học chưa kết thúc, không thể xác nhận hoàn thành');
        }
        
        // Kiểm tra trạng thái booking có hợp lệ không
        if ($booking->status !== Booking::STATUS_CONFIRMED) {
            return back()->with('error', 'Không thể xác nhận buổi học ở trạng thái hiện tại');
        }
        
        // Đơn giản hóa: Đánh dấu buổi học hoàn thành ngay lập tức
            $booking->update([
                'status' => Booking::STATUS_COMPLETED,
                'completed_at' => now()
            ]);
            
            // Tạo bản ghi thu nhập cho gia sư
            app(\App\Services\TutorEarningService::class)->createEarningRecord($booking);
        
            // Gửi thông báo nhắc học viên đánh giá gia sư
            $bookingData = [
                'booking_id' => $booking->id,
                'tutor_id' => $booking->tutor_id,
                'tutor_name' => $booking->tutor->user->name,
                'subject_name' => $booking->subject->name
            ];
            
            $student = \App\Models\User::find($booking->student_id);
            $student->notify(new \App\Notifications\BookingCompleted($bookingData));
        
        // Thông báo cho học sinh và chuyển hướng đến trang đánh giá
        return redirect()->route('student.bookings.show', $booking)
            ->with('success', 'Cảm ơn bạn đã xác nhận hoàn thành buổi học. Hãy để lại đánh giá về gia sư.');
    }
    
    /**
     * Đánh giá buổi học sau khi đã hoàn thành
     */
    public function rateBooking(Request $request, Booking $booking)
    {
        if ($booking->student_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền đánh giá buổi học này');
        }
        
        if ($booking->status !== Booking::STATUS_COMPLETED) {
            return back()->with('error', 'Chỉ có thể đánh giá buổi học đã hoàn thành');
        }
        
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:500'
        ], [
            'rating.required' => 'Vui lòng chọn số sao đánh giá',
            'rating.integer' => 'Số sao đánh giá phải là số nguyên',
            'rating.min' => 'Số sao đánh giá phải từ 1 đến 5',
            'rating.max' => 'Số sao đánh giá phải từ 1 đến 5',
            'review.max' => 'Nội dung đánh giá không được vượt quá 500 ký tự',
        ]);
        
        // Lưu đánh giá vào bảng reviews
        $booking->tutor->reviews()->create([
            'student_id' => Auth::id(),
            'booking_id' => $booking->id,
            'rating' => $validated['rating'],
            'comment' => $validated['review'],
        ]);
        
        // Cập nhật rating trung bình của gia sư
        $tutor = $booking->tutor;
        $avgRating = $tutor->reviews()->avg('rating');
        $tutor->update(['rating' => $avgRating]);
        
        return redirect()->route('student.bookings.show', $booking)
            ->with('success', 'Cảm ơn bạn đã đánh giá buổi học');
    }

    /**
     * Hiển thị danh sách gia sư đang thuê và đã thuê
     */
    public function tutors()
    {
        $user = Auth::user();
        
        // Lấy danh sách gia sư đang thuê (có buổi học đã xác nhận & chưa hoàn thành)
        $currentTutors = Tutor::with(['user', 'subjects', 'reviews' => function($query) use ($user) {
                $query->where('student_id', $user->id);
            }])
            ->whereHas('bookings', function($query) use ($user) {
                $query->where('student_id', $user->id)
                    ->where('status', Booking::STATUS_CONFIRMED)
                    ->where('end_time', '>', now());
            })
            ->where('status', 'active')
            ->where('is_verified', true)
            ->get();
        
        // Lấy danh sách gia sư đã từng thuê (có buổi học đã hoàn thành)
        $pastTutors = Tutor::with(['user', 'subjects', 'reviews' => function($query) use ($user) {
                $query->where('student_id', $user->id);
            }])
            ->whereHas('bookings', function($query) use ($user) {
                $query->where('student_id', $user->id)
                    ->where('status', Booking::STATUS_COMPLETED);
            })
            ->where('status', 'active')
            ->where('is_verified', true)
            ->whereNotIn('id', $currentTutors->pluck('id'))
            ->get();
            
        // Thêm thông tin buổi học tiếp theo cho từng gia sư (gần nhất trước)
        $currentTutors->each(function($tutor) use ($user) {
            $tutor->latest_booking = Booking::where('student_id', $user->id)
                ->where('tutor_id', $tutor->id)
                ->where('status', Booking::STATUS_CONFIRMED)
                ->where('start_time', '>', now())
                ->orderBy('start_time', 'asc') // Buổi học gần nhất trước
                ->first();
        });
            
        return view('student.bookings.tutors', compact('currentTutors', 'pastTutors'));
    }
    
    /**
     * Kiểm tra xung đột thời gian giữa khung giờ mới và các booking đã có
     */
    private function checkTimeSlotConflict($startTime, $endTime, $existingBookings)
    {
        foreach ($existingBookings as $booking) {
            // Kiểm tra overlap: khung giờ mới bắt đầu trước khi booking cũ kết thúc
            // và khung giờ mới kết thúc sau khi booking cũ bắt đầu
            if ($startTime < $booking->end_time && $endTime > $booking->start_time) {
                return true; // Có xung đột
            }
        }
        return false; // Không có xung đột
    }
} 