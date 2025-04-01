<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    public function index()
    {
        $bookings = Auth::user()->tutor->bookings()
            ->with(['student', 'subject'])
            ->latest()
            ->paginate(10);

        return view('tutor.bookings.index', compact('bookings'));
    }

    public function show(Booking $booking)
    {
        if ($booking->tutor_id !== Auth::user()->tutor->id) {
            abort(403);
        }

        return view('tutor.bookings.show', [
            'booking' => $booking->load(['student', 'subject'])
        ]);
    }

    /**
     * Cập nhật trạng thái buổi học
     */
    public function updateStatus(Request $request, Booking $booking)
    {
        if ($booking->tutor_id !== Auth::user()->tutor->id) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => 'required|in:confirmed,scheduled,completed,cancelled',
            'completion_notes' => 'nullable|string'
        ]);

        // Kiểm tra logic chuyển đổi trạng thái
        $currentStatus = $booking->status;
        $newStatus = $validated['status'];
        
        $validTransitions = [
            'pending' => ['confirmed', 'cancelled'],
            'confirmed' => ['scheduled', 'completed', 'cancelled'],
            'scheduled' => ['completed', 'cancelled'],
        ];
        
        if (!isset($validTransitions[$currentStatus]) || 
            !in_array($newStatus, $validTransitions[$currentStatus])) {
            return back()->with('error', 'Không thể chuyển trạng thái từ ' . 
                $currentStatus . ' sang ' . $newStatus);
        }
        
        // Thêm các trường cập nhật
        $updateData = ['status' => $newStatus];
        
        // Nếu hoàn thành, cập nhật thêm thông tin
        if ($newStatus === 'completed') {
            // Tạm thời bỏ qua việc lưu completion_notes vì cột chưa tồn tại
            // $updateData['completion_notes'] = $validated['completion_notes'];
            $updateData['completed_at'] = now();
            
            // Cập nhật tổng số giờ dạy của gia sư
            $duration = $booking->end_time->diffInHours($booking->start_time);
            Auth::user()->tutor->increment('total_teaching_hours', $duration);
        }
        
        $booking->update($updateData);

        // Gửi thông báo cho học sinh
        $studentMessage = match($newStatus) {
            'confirmed' => 'Gia sư đã xác nhận buổi học của bạn.',
            'scheduled' => 'Gia sư đã lên lịch cho buổi học của bạn.',
            'completed' => 'Gia sư đã đánh dấu buổi học của bạn là đã hoàn thành.',
            'cancelled' => 'Rất tiếc, gia sư đã hủy buổi học của bạn.',
            default => ''
        };
        
        if ($studentMessage) {
            // Tạo thông báo - nếu có hệ thống thông báo
            // $booking->student->notify(new BookingStatusChanged($booking, $studentMessage));
        }

        return back()->with('success', 'Trạng thái buổi học đã được cập nhật.');
    }
} 