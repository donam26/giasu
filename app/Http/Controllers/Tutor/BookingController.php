<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Tutor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class BookingController extends Controller
{
    public function index()
    {
        $tutor = Auth::user()->tutor;
        $upcomingBookings = Booking::with(['student', 'subject'])
            ->where('tutor_id', $tutor->id)
            ->whereIn('status', [Booking::STATUS_CONFIRMED, Booking::STATUS_PENDING])
            ->where('start_time', '>', now())
            ->orderBy('start_time')
            ->paginate(5, ['*'], 'upcoming');

        $pastBookings = Booking::with(['student', 'subject'])
            ->where('tutor_id', $tutor->id)
            ->whereIn('status', [Booking::STATUS_COMPLETED, Booking::STATUS_CANCELLED])
            ->orderBy('start_time', 'desc')
            ->paginate(5, ['*'], 'past');

        $pendingCompletionBookings = Booking::with(['student', 'subject'])
            ->where('tutor_id', $tutor->id)
            ->whereIn('status', [Booking::STATUS_PENDING_COMPLETION, Booking::STATUS_CONFIRMED])
            ->where('end_time', '<', now())
            ->orderBy('end_time', 'desc')
            ->paginate(5, ['*'], 'pending_completion');

        return view('tutor.bookings.index', compact('upcomingBookings', 'pastBookings', 'pendingCompletionBookings'));
    }

    public function show(Booking $booking)
    {
        $tutor = Auth::user()->tutor;
        
        if ($booking->tutor_id !== $tutor->id) {
            abort(403, 'Bạn không có quyền xem buổi học này');
        }
        
        $booking->load(['student', 'subject', 'payments', 'tutorEarning']);

        return view('tutor.bookings.show', compact('booking'));
    }

    public function updateStatus(Request $request, Booking $booking)
    {
        $tutor = Auth::user()->tutor;
        
        if ($booking->tutor_id !== $tutor->id) {
            abort(403, 'Bạn không có quyền cập nhật buổi học này');
        }

        $validated = $request->validate([
            'status' => 'required|in:confirmed,pending',
            'notes' => 'nullable|string',
        ], [
            'status.required' => 'Trạng thái không được bỏ trống',
            'status.in' => 'Trạng thái không hợp lệ',
        ]);

        $booking->update([
            'status' => $validated['status'],
            'notes' => $validated['notes'],
        ]);

        return redirect()->route('tutor.bookings.show', $booking)
            ->with('success', 'Đã cập nhật trạng thái buổi học thành công');
    }
    
    /**
     * Bắt đầu buổi học
     */
    public function startClass(Booking $booking)
    {
        $tutor = Auth::user()->tutor;
        
        if ($booking->tutor_id !== $tutor->id) {
            abort(403, 'Bạn không có quyền bắt đầu buổi học này');
        }
        
        // Kiểm tra xem buổi học đã thanh toán chưa
        if (!$booking->isPaid()) {
            return back()->with('error', 'Buổi học chưa được thanh toán, không thể bắt đầu');
        }
        
        // Kiểm tra xem buổi học có ở trạng thái confirmed không
        if ($booking->status !== Booking::STATUS_CONFIRMED) {
            return back()->with('error', 'Buổi học không ở trạng thái có thể bắt đầu');
        }
        
        // Kiểm tra thời gian (nên trong vòng 15 phút trước giờ bắt đầu)
        $minutesUntilStart = now()->diffInMinutes($booking->start_time, false);
        if ($minutesUntilStart > 15 || $minutesUntilStart < -60) { // Cho phép bắt đầu muộn tối đa 60 phút
            return back()->with('error', 'Chỉ có thể bắt đầu buổi học trong vòng 15 phút trước hoặc 60 phút sau giờ bắt đầu');
        }
        
        $booking->update([
            'status' => Booking::STATUS_IN_PROGRESS
        ]);
        
        return redirect()->route('tutor.bookings.show', $booking)
            ->with('success', 'Buổi học đã bắt đầu');
    }
    
    /**
     * Xác nhận hoàn thành buổi học (từ phía gia sư)
     */
    public function confirmCompletion(Request $request, Booking $booking)
    {
        $tutor = Auth::user()->tutor;
        
        if ($booking->tutor_id !== $tutor->id) {
            abort(403, 'Bạn không có quyền xác nhận buổi học này');
        }
        
        // Kiểm tra nếu buổi học đã kết thúc
        if (!$booking->hasEnded()) {
            return back()->with('error', 'Buổi học chưa kết thúc, không thể xác nhận hoàn thành');
        }
        
        // Chỉ cho phép xác nhận nếu trạng thái là confirmed, in_progress hoặc pending_completion
        if (!in_array($booking->status, [
            Booking::STATUS_CONFIRMED, 
            Booking::STATUS_IN_PROGRESS, 
            Booking::STATUS_PENDING_COMPLETION
        ])) {
            return back()->with('error', 'Không thể xác nhận buổi học ở trạng thái hiện tại');
        }
        
        $validated = $request->validate([
            'completion_notes' => 'nullable|string|max:500',
        ], [
            'completion_notes.string' => 'Ghi chú phải là chuỗi ký tự',
            'completion_notes.max' => 'Ghi chú không được vượt quá 500 ký tự',
        ]);
        
        $booking->update([
            'tutor_confirmed' => true,
            'status' => Booking::STATUS_PENDING_COMPLETION,
            'completion_notes' => $validated['completion_notes'] ?? null
        ]);
            
        // Kiểm tra nếu cả gia sư và học sinh đều đã xác nhận
        if ($booking->tutor_confirmed && $booking->student_confirmed) {
            $booking->update([
                'status' => Booking::STATUS_COMPLETED,
                'completed_at' => now()
            ]);
            
            // Tạo bản ghi thu nhập cho gia sư
            app(\App\Services\TutorEarningService::class)->createEarningRecord($booking);
        } else {
            // Gửi thông báo cho học sinh để xác nhận hoàn thành
            // TODO: Thêm notification sau
        }
        
        return redirect()->route('tutor.bookings.show', $booking)
            ->with('success', 'Cảm ơn bạn đã xác nhận hoàn thành buổi học');
    }
    
    /**
     * Báo cáo vấn đề với buổi học
     */
    public function reportIssue(Request $request, Booking $booking)
    {
        $tutor = Auth::user()->tutor;
        
        if ($booking->tutor_id !== $tutor->id) {
            abort(403, 'Bạn không có quyền báo cáo về buổi học này');
        }
        
        $validated = $request->validate([
            'issue_type' => 'required|in:student_absent,technical_issue,other',
            'issue_description' => 'required|string|max:1000',
        ], [
            'issue_type.required' => 'Loại vấn đề không được bỏ trống',
            'issue_type.in' => 'Loại vấn đề không hợp lệ',
            'issue_description.required' => 'Mô tả vấn đề không được bỏ trống',
            'issue_description.string' => 'Mô tả vấn đề phải là chuỗi ký tự',
            'issue_description.max' => 'Mô tả vấn đề không được vượt quá 1000 ký tự',
        ]);
        
        // Lưu vấn đề vào booking notes
        $issueNote = 'Vấn đề: ' . $validated['issue_type'] . "\n" . $validated['issue_description'];
        
        $booking->update([
            'notes' => $booking->notes . "\n\n" . $issueNote
        ]);
        
        // Gửi thông báo cho admin về vấn đề
        // TODO: Thêm notification sau
        
        return redirect()->route('tutor.bookings.show', $booking)
            ->with('success', 'Báo cáo vấn đề đã được ghi nhận. Quản trị viên sẽ liên hệ với bạn sớm');
    }
} 