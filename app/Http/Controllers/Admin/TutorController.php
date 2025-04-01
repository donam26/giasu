<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tutor;
use App\Models\Subject;
use App\Models\ClassLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TutorController extends Controller
{
    public function index()
    {
        $tutors = Tutor::with(['user', 'subjects', 'classLevels'])
            ->latest()
            ->paginate(10);
        return view('admin.tutors.index', compact('tutors'));
    }

    public function show(Tutor $tutor)
    {
        $tutor->load(['user', 'subjects', 'classLevels', 'bookings']);
        return view('admin.tutors.show', compact('tutor'));
    }

    public function edit(Tutor $tutor)
    {
        $subjects = Subject::all();
        $classLevels = ClassLevel::all();
        return view('admin.tutors.edit', compact('tutor', 'subjects', 'classLevels'));
    }

    public function update(Request $request, Tutor $tutor)
    {
        // Debug dữ liệu gửi đến
        Log::info('Admin tutor update request data:', [
            'tutor_id' => $tutor->id,
            'all_data' => $request->all(),
            'subjects' => $request->subjects,
            'class_levels' => $request->class_levels
        ]);

        $request->validate([
            'education_level' => ['required', 'string', 'max:255'],
            'university' => ['required', 'string', 'max:255'],
            'major' => ['required', 'string', 'max:255'],
            'teaching_experience' => ['required', 'string'],
            'bio' => ['required', 'string'],
            'hourly_rate' => ['required', 'numeric', 'min:0'],
            'subjects' => ['required', 'array', 'min:1'],
            'subjects.*' => ['exists:subjects,id'],
            'class_levels' => ['required', 'array', 'min:1'],
            'class_levels.*' => ['exists:class_levels,id'],
            'status' => ['required', 'in:pending,active,inactive'],
            'is_verified' => ['boolean'],
        ]);

        try {
            $tutor->update([
                'education_level' => $request->education_level,
                'university' => $request->university,
                'major' => $request->major,
                'teaching_experience' => $request->teaching_experience,
                'bio' => $request->bio,
                'hourly_rate' => $request->hourly_rate,
                'status' => $request->status,
                'is_verified' => $request->is_verified ?? $tutor->is_verified,
            ]);

            // Cập nhật môn học
            $tutor->subjects()->sync($request->subjects);

            // Cập nhật cấp học
            $tutor->classLevels()->sync($request->class_levels);

            return redirect()->route('admin.tutors.index')
                ->with('success', 'Thông tin gia sư đã được cập nhật.');
        } catch (\Exception $e) {
            Log::error('Lỗi khi cập nhật gia sư:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    public function destroy(Tutor $tutor)
    {
        // Kiểm tra xem gia sư có đang có lịch dạy không
        if ($tutor->bookings()->whereIn('status', ['pending', 'confirmed'])->exists()) {
            return back()->with('error', 'Không thể xóa gia sư đang có lịch dạy.');
        }

        // Xóa các liên kết với môn học và cấp học
        $tutor->subjects()->detach();
        $tutor->classLevels()->detach();

        // Xóa gia sư
        $tutor->delete();

        return redirect()->route('admin.tutors.index')
            ->with('success', 'Gia sư đã được xóa thành công.');
    }

    /**
     * Phê duyệt gia sư
     */
    public function approve(Tutor $tutor)
    {
        try {
            if ($tutor->status !== 'pending') {
                return back()->with('error', 'Gia sư này không ở trạng thái chờ duyệt.');
            }

            // Cập nhật trạng thái thành active và xác minh
            $tutor->update([
                'status' => 'active',
                'is_verified' => true
            ]);

            // Gửi thông báo cho gia sư (có thể thêm logic gửi email ở đây)

            return redirect()->route('admin.tutors.index')
                ->with('success', 'Gia sư đã được phê duyệt thành công.');
        } catch (\Exception $e) {
            Log::error('Lỗi khi phê duyệt gia sư:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'Có lỗi xảy ra khi phê duyệt: ' . $e->getMessage());
        }
    }
} 