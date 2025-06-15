<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Tutor;
use App\Models\Booking;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * Hiển thị form đánh giá gia sư
     */
    public function create(Request $request, Tutor $tutor)
    {
        // Nếu có booking_id được truyền, ưu tiên đánh giá cho buổi học cụ thể đó
        if ($request->has('booking')) {
            $specificBooking = Booking::where('id', $request->booking)
                ->where('student_id', Auth::id())
                ->where('tutor_id', $tutor->id)
                ->where('status', 'completed')
                ->with('subject')
                ->first();
                
            if (!$specificBooking) {
                return redirect()->back()->with('error', 'Buổi học không hợp lệ hoặc chưa hoàn thành');
            }
            
            // Kiểm tra xem đã đánh giá buổi học này chưa
            $hasReviewedBooking = Review::where('booking_id', $specificBooking->id)->exists();
            if ($hasReviewedBooking) {
                return redirect()->back()->with('error', 'Bạn đã đánh giá buổi học này rồi');
            }
            
            // Truyền booking cụ thể để pre-select trong form
            return view('student.reviews.create', [
                'tutor' => $tutor, 
                'completedBookings' => collect([$specificBooking]),
                'selectedBooking' => $specificBooking
            ]);
        }
        
        // Logic cũ cho trường hợp không có booking cụ thể
        $completedBookings = Booking::where('student_id', Auth::id())
            ->where('tutor_id', $tutor->id)
            ->where('status', 'completed')
            ->with('subject')
            ->get();
            
        if ($completedBookings->isEmpty()) {
            return redirect()->back()->with('error', 'Bạn chưa hoàn thành buổi học nào với gia sư này');
        }
        
        // Lọc ra những buổi học chưa được đánh giá
        $unreviewedBookings = $completedBookings->filter(function($booking) {
            return !Review::where('booking_id', $booking->id)->exists();
        });
        
        if ($unreviewedBookings->isEmpty()) {
            return redirect()->back()->with('error', 'Bạn đã đánh giá tất cả buổi học với gia sư này rồi');
        }
        
        return view('student.reviews.create', [
            'tutor' => $tutor, 
            'completedBookings' => $unreviewedBookings
        ]);
    }
    
    /**
     * Lưu đánh giá gia sư
     */
    public function store(Request $request, Tutor $tutor)
    {
        $validated = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:500',
            'is_anonymous' => 'nullable|in:0,1'
        ], [
            'booking_id.required' => 'Vui lòng chọn buổi học để đánh giá',
            'booking_id.exists' => 'Buổi học đã chọn không hợp lệ',
            'rating.required' => 'Vui lòng chọn số sao đánh giá',
            'rating.integer' => 'Số sao đánh giá phải là số nguyên',
            'rating.min' => 'Số sao đánh giá phải từ 1 đến 5',
            'rating.max' => 'Số sao đánh giá phải từ 1 đến 5',
            'comment.required' => 'Vui lòng nhập nội dung đánh giá',
            'comment.max' => 'Nội dung đánh giá không được vượt quá 500 ký tự',
        ]);
        
        // Kiểm tra booking thuộc về học sinh này và gia sư này
        $booking = Booking::where('id', $validated['booking_id'])
            ->where('student_id', Auth::id())
            ->where('tutor_id', $tutor->id)
            ->where('status', 'completed')
            ->firstOrFail();
        
        // Kiểm tra xem đã đánh giá booking này chưa
        $existingReview = Review::where('booking_id', $booking->id)->exists();
        if ($existingReview) {
            return redirect()->back()->with('error', 'Bạn đã đánh giá buổi học này rồi');
        }
        
        // Tạo đánh giá
        $review = Review::create([
            'student_id' => Auth::id(),
            'tutor_id' => $tutor->id,
            'booking_id' => $validated['booking_id'],
            'rating' => $validated['rating'],
            'comment' => $validated['comment'],
            'is_anonymous' => (bool)($validated['is_anonymous'] ?? false),
        ]);
        
        // Cập nhật rating trung bình của gia sư
        $avgRating = $tutor->reviews()->avg('rating');
        $tutor->update(['rating' => $avgRating]);
        
        return redirect()->route('student.bookings.tutors')
            ->with('success', 'Cảm ơn bạn đã đánh giá gia sư!');
    }
} 