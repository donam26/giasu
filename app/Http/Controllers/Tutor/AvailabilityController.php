<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use App\Models\TutorAvailability;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Models\Booking;

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
        // Ghi log thông tin input 
        Log::info('Thêm lịch rảnh mới cho gia sư: ', [
            'tutor_id' => Auth::user()->tutor->id,
            'input' => $request->all()
        ]);

        // Xác thực dữ liệu đầu vào
        $request->validate([
            'day_of_week' => 'required|integer|between:0,6',
            'date' => 'nullable|date',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'is_recurring' => 'nullable|boolean',
            'status' => 'nullable|in:active,inactive',
        ], [
            'day_of_week.required' => 'Ngày trong tuần không được bỏ trống',
            'day_of_week.integer' => 'Ngày trong tuần phải là số nguyên',
            'day_of_week.between' => 'Ngày trong tuần phải có giá trị từ 0 đến 6',
            'date.date' => 'Ngày không hợp lệ',
            'start_time.required' => 'Thời gian bắt đầu không được bỏ trống',
            'end_time.required' => 'Thời gian kết thúc không được bỏ trống',
            'end_time.after' => 'Thời gian kết thúc phải sau thời gian bắt đầu',
            'status.in' => 'Trạng thái không hợp lệ',
        ]);

        try {
            $tutorId = Auth::user()->tutor->id;
            
            // Định dạng thời gian
            $startTime = Carbon::parse($request->start_time);
            $endTime = Carbon::parse($request->end_time);
            
            // Kiểm tra xem có lịch trùng không
            $existingSlots = TutorAvailability::where('tutor_id', $tutorId)
                ->where('day_of_week', $request->day_of_week)
                ->where(function($query) use ($request) {
                    // Nếu có ngày cụ thể, chỉ kiểm tra trùng với ngày đó
                    if ($request->filled('date')) {
                        $query->whereDate('date', $request->date);
                    } elseif ($request->boolean('is_recurring')) {
                        // Nếu là lịch lặp lại, kiểm tra các lịch lặp lại khác
                        $query->whereNull('date')->where('is_recurring', true);
                    }
                })
                ->where(function($query) use ($startTime, $endTime) {
                    $query->whereBetween('start_time', [$startTime, $endTime])
                        ->orWhereBetween('end_time', [$startTime, $endTime])
                        ->orWhere(function($q) use ($startTime, $endTime) {
                            $q->where('start_time', '<=', $startTime)
                              ->where('end_time', '>=', $endTime);
                        });
                })
                ->get();

            if ($existingSlots->count() > 0) {
                Log::warning('Phát hiện lịch rảnh trùng lặp:', [
                    'tutor_id' => $tutorId,
                    'day' => $request->day_of_week,
                    'date' => $request->date,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'existing_slots' => $existingSlots
                ]);
                
                return redirect()->back()->with('error', 'Bạn đã có lịch rảnh trong khoảng thời gian này!');
            }

            // Tạo bản ghi mới
            $availability = new TutorAvailability();
            $availability->tutor_id = $tutorId;
            $availability->day_of_week = $request->day_of_week;
            $availability->date = $request->filled('date') ? $request->date : null;
            $availability->start_time = $startTime;
            $availability->end_time = $endTime;
            $availability->is_recurring = 1;
            $availability->status = $request->input('status', 'active');
            
            if ($availability->save()) {
                Log::info('Đã thêm lịch rảnh thành công', [
                    'availability_id' => $availability->id, 
                    'tutor_id' => $tutorId
                ]);
                return redirect()->route('tutor.availability.index')->with('success', 'Đã thêm lịch rảnh thành công!');
            } else {
                Log::error('Không thể lưu lịch rảnh', [
                    'tutor_id' => $tutorId, 
                    'availability_data' => $availability->toArray()
                ]);
                return redirect()->back()->with('error', 'Có lỗi xảy ra khi lưu lịch rảnh!');
            }
        } catch (\Exception $e) {
            Log::error('Lỗi khi thêm lịch rảnh:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    /**
     * Hiển thị form chỉnh sửa lịch rảnh
     */
    public function edit(TutorAvailability $availability)
    {
        // Kiểm tra quyền truy cập
        $tutor = Auth::user()->tutor;
        
        return view('tutor.availability.edit', [
            'availability' => $availability,
            'daysOfWeek' => $this->getDaysOfWeek(),
        ]);
    }

    /**
     * Cập nhật lịch rảnh
     */
    public function update(Request $request, $id)
    {
        // Ghi log thông tin input
        Log::info('Cập nhật lịch rảnh:', [
            'availability_id' => $id,
            'input' => $request->all()
        ]);

        // Xác thực dữ liệu đầu vào
        $request->validate([
            'day_of_week' => 'required|integer|between:0,6',
            'date' => 'nullable|date',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'is_recurring' => 'nullable|boolean',
            'status' => 'nullable|in:active,inactive',
        ], [
            'day_of_week.required' => 'Ngày trong tuần không được bỏ trống',
            'day_of_week.integer' => 'Ngày trong tuần phải là số nguyên',
            'day_of_week.between' => 'Ngày trong tuần phải có giá trị từ 0 đến 6',
            'date.date' => 'Ngày không hợp lệ',
            'start_time.required' => 'Thời gian bắt đầu không được bỏ trống',
            'end_time.required' => 'Thời gian kết thúc không được bỏ trống',
            'end_time.after' => 'Thời gian kết thúc phải sau thời gian bắt đầu',
            'status.in' => 'Trạng thái không hợp lệ',
        ]);

        try {
            $tutorId = Auth::user()->tutor->id;
            
            // Tìm lịch rảnh cần cập nhật
            $availability = TutorAvailability::where('id', $id)
                ->where('tutor_id', $tutorId)
                ->first();
            
            if (!$availability) {
                Log::warning('Không tìm thấy lịch rảnh để cập nhật', [
                    'availability_id' => $id,
                    'tutor_id' => $tutorId
                ]);
                return redirect()->back()->with('error', 'Không tìm thấy lịch rảnh');
            }
            
            // Định dạng thời gian
            $startTime = Carbon::parse($request->start_time);
            $endTime = Carbon::parse($request->end_time);
            
            // Kiểm tra xem có lịch trùng không (ngoại trừ chính lịch hiện tại)
            $existingSlots = TutorAvailability::where('tutor_id', $tutorId)
                ->where('id', '!=', $id)
                ->where('day_of_week', $request->day_of_week)
                ->where(function($query) use ($request) {
                    // Nếu có ngày cụ thể, chỉ kiểm tra trùng với ngày đó
                    if ($request->filled('date')) {
                        $query->whereDate('date', $request->date);
                    } elseif ($request->boolean('is_recurring')) {
                        // Nếu là lịch lặp lại, kiểm tra các lịch lặp lại khác
                        $query->whereNull('date')->where('is_recurring', true);
                    }
                })
                ->where(function($query) use ($startTime, $endTime) {
                    $query->whereBetween('start_time', [$startTime, $endTime])
                        ->orWhereBetween('end_time', [$startTime, $endTime])
                        ->orWhere(function($q) use ($startTime, $endTime) {
                            $q->where('start_time', '<=', $startTime)
                              ->where('end_time', '>=', $endTime);
                        });
                })
                ->get();

            if ($existingSlots->count() > 0) {
                Log::warning('Phát hiện lịch rảnh trùng lặp khi cập nhật:', [
                    'availability_id' => $id,
                    'tutor_id' => $tutorId,
                    'day' => $request->day_of_week,
                    'date' => $request->date,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'existing_slots' => $existingSlots
                ]);
                
                return redirect()->back()->with('error', 'Bạn đã có lịch rảnh trong khoảng thời gian này!');
            }

            // Cập nhật thông tin
            $availability->day_of_week = $request->day_of_week;
            $availability->date = $request->filled('date') ? $request->date : null;
            $availability->start_time = $startTime;
            $availability->end_time = $endTime;
            $availability->is_recurring = $request->boolean('is_recurring');
            $availability->status = $request->input('status', 'active');
            
            if ($availability->save()) {
                Log::info('Đã cập nhật lịch rảnh thành công', [
                    'availability_id' => $availability->id
                ]);
                return redirect()->route('tutor.availability.index')->with('success', 'Đã cập nhật lịch rảnh thành công!');
            } else {
                Log::error('Không thể cập nhật lịch rảnh', [
                    'availability_id' => $id,
                    'availability_data' => $availability->toArray()
                ]);
                return redirect()->back()->with('error', 'Có lỗi xảy ra khi cập nhật lịch rảnh!');
            }
        } catch (\Exception $e) {
            Log::error('Lỗi khi cập nhật lịch rảnh:', [
                'availability_id' => $id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    /**
     * Xóa lịch rảnh
     */
    public function destroy($id)
    {
        // Ghi log thông tin
        Log::info('Đang thực hiện xóa lịch rảnh', [
            'availability_id' => $id,
            'tutor_id' => Auth::user()->tutor->id
        ]);

        try {
            $tutorId = Auth::user()->tutor->id;
            
            // Tìm lịch rảnh cần xóa
            $availability = TutorAvailability::where('id', $id)
                ->where('tutor_id', $tutorId)
                ->first();
            
            if (!$availability) {
                Log::warning('Không tìm thấy lịch rảnh để xóa', [
                    'availability_id' => $id,
                    'tutor_id' => $tutorId
                ]);
                return redirect()->back()->with('error', 'Không tìm thấy lịch rảnh');
            }
            
            // Kiểm tra xem lịch rảnh có đang được sử dụng không
            $hasBookings = Booking::where('tutor_id', $tutorId)
                ->where('status', '!=', 'cancelled');
                
            // Nếu có ngày cụ thể, chỉ kiểm tra lịch học cho ngày đó
            if ($availability->date) {
                $hasBookings->whereDate('start_time', $availability->date);
            }
            
            $hasBookings = $hasBookings->where(function($query) use ($availability) {
                $query->whereBetween('start_time', [$availability->start_time, $availability->end_time])
                    ->orWhereBetween('end_time', [$availability->start_time, $availability->end_time])
                    ->orWhere(function($q) use ($availability) {
                        $q->where('start_time', '<=', $availability->start_time)
                          ->where('end_time', '>=', $availability->end_time);
                    });
            });
            
            if ($hasBookings->exists()) {
                Log::warning('Không thể xóa lịch rảnh vì đang có lịch học', [
                    'availability_id' => $id,
                    'related_bookings' => $hasBookings->get()->pluck('id')
                ]);
                
                return redirect()->back()->with('error', 'Không thể xóa lịch rảnh đang được sử dụng cho buổi học');
            }
            
            // Thực hiện xóa
            if ($availability->delete()) {
                Log::info('Đã xóa lịch rảnh thành công', [
                    'availability_id' => $id
                ]);
                return redirect()->route('tutor.availability.index')->with('success', 'Đã xóa lịch rảnh thành công!');
            } else {
                Log::error('Không thể xóa lịch rảnh', [
                    'availability_id' => $id
                ]);
                return redirect()->back()->with('error', 'Có lỗi xảy ra khi xóa lịch rảnh!');
            }
        } catch (\Exception $e) {
            Log::error('Lỗi khi xóa lịch rảnh:', [
                'availability_id' => $id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
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
        // Ghi log thông tin
        Log::info('Thêm nhanh lịch rảnh mới:', [
            'tutor_id' => Auth::user()->tutor->id,
            'input' => $request->all()
        ]);

        // Validate input
        $request->validate([
            'days' => 'required|array',
            'days.*' => 'required|integer|between:0,6',
            'timeSlots' => 'required|array',
            'timeSlots.*' => 'required|string',
            'date' => 'nullable|date',
            'status' => 'nullable|in:active,inactive',
            'is_recurring' => 'nullable|boolean',
        ]);

        try {
            $tutorId = Auth::user()->tutor->id;
            $days = $request->days;
            $timeSlots = $request->timeSlots;
            $date = $request->filled('date') ? $request->date : null;
            $status = $request->input('status', 'active');
            $isRecurring = $request->boolean('is_recurring');
            
            // Xóa lịch trùng nếu có (tùy chọn)
            if ($request->boolean('replace_existing') && $request->filled('date')) {
                TutorAvailability::where('tutor_id', $tutorId)
                    ->whereDate('date', $date)
                    ->delete();
                
                Log::info('Đã xóa lịch rảnh trùng lặp', [
                    'tutor_id' => $tutorId,
                    'date' => $date
                ]);
            }

            $count = 0;
            $errors = [];

            foreach ($days as $day) {
                foreach ($timeSlots as $timeSlot) {
                    $times = explode('-', $timeSlot);
                    if (count($times) != 2) {
                        $errors[] = "Định dạng thời gian không hợp lệ: $timeSlot";
                        continue;
                    }

                    $startTime = trim($times[0]);
                    $endTime = trim($times[1]);

                    // Kiểm tra trùng lặp
                    $existingSlots = TutorAvailability::where('tutor_id', $tutorId)
                        ->where('day_of_week', $day)
                        ->where(function($query) use ($date, $isRecurring) {
                            if ($date) {
                                $query->whereDate('date', $date);
                            } elseif ($isRecurring) {
                                $query->whereNull('date')->where('is_recurring', true);
                            }
                        })
                        ->where(function($query) use ($startTime, $endTime) {
                            $startDateTime = Carbon::parse($startTime);
                            $endDateTime = Carbon::parse($endTime);
                            
                            $query->whereBetween('start_time', [$startDateTime, $endDateTime])
                                ->orWhereBetween('end_time', [$startDateTime, $endDateTime])
                                ->orWhere(function($q) use ($startDateTime, $endDateTime) {
                                    $q->where('start_time', '<=', $startDateTime)
                                      ->where('end_time', '>=', $endDateTime);
                                });
                        })
                        ->get();

                    if ($existingSlots->count() > 0) {
                        Log::warning('Phát hiện lịch trùng lặp khi thêm nhanh:', [
                            'tutor_id' => $tutorId,
                            'day' => $day,
                            'date' => $date,
                            'time_slot' => $timeSlot
                        ]);
                        continue; // Bỏ qua slot trùng lặp
                    }

                    // Tạo mới
                    $availability = new TutorAvailability();
                    $availability->tutor_id = $tutorId;
                    $availability->day_of_week = $day;
                    $availability->date = $date;
                    $availability->start_time = Carbon::parse($startTime);
                    $availability->end_time = Carbon::parse($endTime);
                    $availability->is_recurring = $isRecurring;
                    $availability->status = $status;

                    if ($availability->save()) {
                        $count++;
                        Log::info('Đã thêm lịch rảnh thành công', [
                            'availability_id' => $availability->id, 
                            'day' => $day,
                            'time_slot' => $timeSlot
                        ]);
                    } else {
                        $errors[] = "Không thể lưu lịch cho {$day}, {$timeSlot}";
                        Log::error('Không thể lưu lịch rảnh', [
                            'day' => $day,
                            'time_slot' => $timeSlot
                        ]);
                    }
                }
            }

            if ($count > 0) {
                return redirect()->route('tutor.availability.index')
                    ->with('success', "Đã thêm $count lịch rảnh thành công!" . 
                        (count($errors) > 0 ? " Có " . count($errors) . " lỗi xảy ra." : ""));
            } else {
                return redirect()->back()
                    ->with('error', 'Không thể thêm lịch rảnh. ' . implode(', ', $errors));
            }
        } catch (\Exception $e) {
            Log::error('Lỗi khi thêm nhanh lịch rảnh:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
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
