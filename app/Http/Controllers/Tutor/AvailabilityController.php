<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use App\Models\TutorAvailability;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AvailabilityController extends Controller
{
    /**
     * Hiển thị danh sách lịch rảnh của gia sư
     */
    public function index()
    {
        $tutor = Auth::user()->tutor;
        $availabilities = $tutor->availabilities()->orderBy('day_of_week')->orderBy('start_time')->get();
        
        return view('tutor.availability.index', [
            'availabilities' => $availabilities,
            'daysOfWeek' => $this->getDaysOfWeek(),
        ]);
    }

    /**
     * Hiển thị form tạo lịch rảnh mới
     */
    public function create()
    {
        return view('tutor.availability.create', [
            'daysOfWeek' => $this->getDaysOfWeek(),
        ]);
    }

    /**
     * Lưu lịch rảnh mới
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'day_of_week' => 'required|integer|between:0,6',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'date' => 'nullable|date',
        ]);

        $tutor = Auth::user()->tutor;
        
        // Kiểm tra xem lịch rảnh có bị trùng không
        $overlapping = $tutor->availabilities()
            ->where('day_of_week', $validated['day_of_week'])
            ->where(function($query) use ($validated) {
                $query->whereBetween('start_time', [$validated['start_time'], $validated['end_time']])
                    ->orWhereBetween('end_time', [$validated['start_time'], $validated['end_time']])
                    ->orWhere(function($q) use ($validated) {
                        $q->where('start_time', '<=', $validated['start_time'])
                          ->where('end_time', '>=', $validated['end_time']);
                    });
            })->exists();
            
        if ($overlapping) {
            return redirect()->back()->withInput()->withErrors([
                'general' => 'Lịch rảnh này trùng với lịch rảnh đã có.',
            ]);
        }

        // Thêm is_recurring và status vào dữ liệu nếu không được cung cấp
        $availabilityData = $validated;
        if (!isset($availabilityData['is_recurring'])) {
            $availabilityData['is_recurring'] = false;
        }
        if (!isset($availabilityData['status'])) {
            $availabilityData['status'] = 'available';
        }

        $tutor->availabilities()->create($availabilityData);

        return redirect()->route('tutor.schedule.index')
            ->with('success', 'Thêm lịch rảnh thành công.');
    }

    /**
     * Hiển thị form chỉnh sửa lịch rảnh
     */
    public function edit(TutorAvailability $availability)
    {
        // Kiểm tra quyền truy cập
        $tutor = Auth::user()->tutor;
        if ($availability->tutor_id !== $tutor->id) {
            abort(403, 'Bạn không có quyền chỉnh sửa lịch rảnh này.');
        }
        
        return view('tutor.availability.edit', [
            'availability' => $availability,
            'daysOfWeek' => $this->getDaysOfWeek(),
        ]);
    }

    /**
     * Cập nhật lịch rảnh
     */
    public function update(Request $request, TutorAvailability $availability)
    {
        // Kiểm tra quyền truy cập
        $tutor = Auth::user()->tutor;
        if ($availability->tutor_id !== $tutor->id) {
            abort(403, 'Bạn không có quyền chỉnh sửa lịch rảnh này.');
        }
        
        $validated = $request->validate([
            'day_of_week' => 'required|integer|between:0,6',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        // Kiểm tra xem lịch rảnh có bị trùng không (trừ chính nó)
        $overlapping = $tutor->availabilities()
            ->where('id', '!=', $availability->id)
            ->where('day_of_week', $validated['day_of_week'])
            ->where(function($query) use ($validated) {
                $query->whereBetween('start_time', [$validated['start_time'], $validated['end_time']])
                    ->orWhereBetween('end_time', [$validated['start_time'], $validated['end_time']])
                    ->orWhere(function($q) use ($validated) {
                        $q->where('start_time', '<=', $validated['start_time'])
                          ->where('end_time', '>=', $validated['end_time']);
                    });
            })->exists();
            
        if ($overlapping) {
            return redirect()->back()->withInput()->withErrors([
                'general' => 'Lịch rảnh này trùng với lịch rảnh đã có.',
            ]);
        }

        $availability->update($validated);

        return redirect()->route('tutor.schedule.index')
            ->with('success', 'Cập nhật lịch rảnh thành công.');
    }

    /**
     * Xóa lịch rảnh
     */
    public function destroy(TutorAvailability $availability)
    {
        // Kiểm tra quyền truy cập
        $tutor = Auth::user()->tutor;
        if ($availability->tutor_id !== $tutor->id) {
            abort(403, 'Bạn không có quyền xóa lịch rảnh này.');
        }
        
        $availability->delete();

        return redirect()->route('tutor.schedule.index')
            ->with('success', 'Đã xóa lịch rảnh.');
    }

    /**
     * Tạo nhanh lịch rảnh theo mẫu
     */
    public function quickCreate()
    {
        return view('tutor.availability.quick-create', [
            'daysOfWeek' => $this->getDaysOfWeek(),
        ]);
    }

    /**
     * Lưu nhiều lịch rảnh cùng lúc
     */
    public function quickStore(Request $request)
    {
        $validated = $request->validate([
            'days' => 'required|array',
            'days.*' => 'integer|between:0,6',
            'timeSlots' => 'required|array',
            'timeSlots.*.start' => 'required|date_format:H:i',
            'timeSlots.*.end' => 'required|date_format:H:i|after:timeSlots.*.start',
            'date' => 'nullable|date',
        ]);

        $tutor = Auth::user()->tutor;
        $added = 0;

        foreach ($validated['days'] as $day) {
            foreach ($validated['timeSlots'] as $timeSlot) {
                // Kiểm tra trùng lặp
                $overlapping = $tutor->availabilities()
                    ->where('day_of_week', $day)
                    ->where(function($query) use ($timeSlot) {
                        $query->whereBetween('start_time', [$timeSlot['start'], $timeSlot['end']])
                            ->orWhereBetween('end_time', [$timeSlot['start'], $timeSlot['end']])
                            ->orWhere(function($q) use ($timeSlot) {
                                $q->where('start_time', '<=', $timeSlot['start'])
                                  ->where('end_time', '>=', $timeSlot['end']);
                            });
                    })->exists();
                
                if (!$overlapping) {
                    $tutor->availabilities()->create([
                        'day_of_week' => $day,
                        'start_time' => $timeSlot['start'],
                        'end_time' => $timeSlot['end'],
                        'date' => $validated['date'] ?? null,
                        'is_recurring' => $validated['is_recurring'] ?? false,
                        'status' => $validated['status'] ?? 'available',
                    ]);
                    $added++;
                }
            }
        }

        return redirect()->route('tutor.schedule.index')
            ->with('success', "Đã thêm {$added} lịch rảnh mới.");
    }

    /**
     * Danh sách các ngày trong tuần
     */
    private function getDaysOfWeek()
    {
        return [
            0 => 'Chủ Nhật',
            1 => 'Thứ Hai',
            2 => 'Thứ Ba',
            3 => 'Thứ Tư',
            4 => 'Thứ Năm',
            5 => 'Thứ Sáu',
            6 => 'Thứ Bảy',
        ];
    }
}
