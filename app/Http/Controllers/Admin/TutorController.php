<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tutor;
use App\Models\Subject;
use App\Models\ClassLevel;
use Illuminate\Http\Request;

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
        $request->validate([
            'education_level' => ['required', 'string', 'max:255'],
            'university' => ['required', 'string', 'max:255'],
            'major' => ['required', 'string', 'max:255'],
            'teaching_experience' => ['required', 'integer', 'min:0'],
            'bio' => ['required', 'string'],
            'hourly_rate' => ['required', 'numeric', 'min:0'],
            'subjects' => ['required', 'array', 'min:1'],
            'subjects.*' => ['exists:subjects,id'],
            'class_levels' => ['required', 'array', 'min:1'],
            'class_levels.*' => ['exists:class_levels,id'],
            'status' => ['required', 'in:pending,active,inactive'],
        ]);

        $tutor->update([
            'education_level' => $request->education_level,
            'university' => $request->university,
            'major' => $request->major,
            'teaching_experience' => $request->teaching_experience,
            'bio' => $request->bio,
            'hourly_rate' => $request->hourly_rate,
            'status' => $request->status,
        ]);

        // Cập nhật môn học
        $tutor->subjects()->sync($request->subjects);

        // Cập nhật cấp học
        $tutor->classLevels()->sync($request->class_levels);

        return redirect()->route('admin.tutors.index')
            ->with('success', 'Thông tin gia sư đã được cập nhật.');
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
} 