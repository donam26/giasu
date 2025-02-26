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
        $booking->load(['student', 'tutor.user', 'subject']);
        return view('admin.bookings.show', compact('booking'));
    }

    public function edit(Booking $booking)
    {
        return view('admin.bookings.edit', compact('booking'));
    }

    public function update(Request $request, Booking $booking)
    {
        $request->validate([
            'status' => ['required', 'in:pending,confirmed,completed,cancelled'],
            'admin_notes' => ['nullable', 'string'],
        ]);

        $booking->update([
            'status' => $request->status,
            'admin_notes' => $request->admin_notes,
        ]);

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