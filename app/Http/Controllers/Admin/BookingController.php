<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index()
    {
        $bookings = Booking::with(['student', 'tutor.user', 'subject'])
            ->latest()
            ->paginate(10);
        return view('admin.bookings.index', compact('bookings'));
    }

    public function show(Booking $booking)
    {
        $booking->load(['student', 'tutor.user', 'subject', 'payments']);
        return view('admin.bookings.show', compact('booking'));
    }

    public function edit(Booking $booking)
    {
        $booking->load(['student', 'tutor.user', 'subject']);
        return view('admin.bookings.edit', compact('booking'));
    }

    public function update(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,confirmed,completed,cancelled'],
            'admin_notes' => ['nullable', 'string'],
        ]);

        // Nếu status thay đổi, lưu thêm thông tin liên quan
        if ($booking->status != $validated['status']) {
            if ($validated['status'] == 'completed') {
                $validated['completed_at'] = now();
            } elseif ($validated['status'] == 'cancelled') {
                if (empty($booking->cancelled_reason)) {
                    $validated['cancelled_reason'] = 'Hủy bởi quản trị viên';
                    $validated['cancelled_by'] = 'admin';
                }
            }
        }

        $booking->update($validated);

        return redirect()->route('admin.bookings.index')
            ->with('success', 'Trạng thái đặt lịch đã được cập nhật.');
    }

    public function destroy(Booking $booking)
    {
        // Chỉ cho phép xóa các đặt lịch đã hoàn thành hoặc đã hủy
        if (!in_array($booking->status, ['completed', 'cancelled'])) {
            return back()->with('error', 'Chỉ có thể xóa các đặt lịch đã hoàn thành hoặc đã hủy.');
        }

        $booking->delete();

        return redirect()->route('admin.bookings.index')
            ->with('success', 'Đặt lịch đã được xóa thành công.');
    }
} 