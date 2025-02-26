<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function index()
    {
        $subjects = Subject::withCount(['tutors', 'bookings'])
            ->latest()
            ->paginate(10);
        return view('admin.subjects.index', compact('subjects'));
    }

    public function create()
    {
        return view('admin.subjects.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:subjects'],
            'description' => ['required', 'string'],
            'category' => ['required', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ]);

        Subject::create([
            'name' => $request->name,
            'description' => $request->description,
            'category' => $request->category,
            'is_active' => $request->is_active ?? true,
        ]);

        return redirect()->route('admin.subjects.index')
            ->with('success', 'Môn học đã được tạo thành công.');
    }

    public function show(Subject $subject)
    {
        $subject->load(['tutors.user', 'bookings.student']);
        return view('admin.subjects.show', compact('subject'));
    }

    public function edit(Subject $subject)
    {
        return view('admin.subjects.edit', compact('subject'));
    }

    public function update(Request $request, Subject $subject)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:subjects,name,'.$subject->id],
            'description' => ['required', 'string'],
            'category' => ['required', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ]);

        $subject->update([
            'name' => $request->name,
            'description' => $request->description,
            'category' => $request->category,
            'is_active' => $request->is_active ?? true,
        ]);

        return redirect()->route('admin.subjects.index')
            ->with('success', 'Môn học đã được cập nhật thành công.');
    }

    public function destroy(Subject $subject)
    {
        // Kiểm tra xem môn học có đang được sử dụng không
        if ($subject->tutors()->exists() || $subject->bookings()->exists()) {
            return back()->with('error', 'Không thể xóa môn học đang được sử dụng.');
        }

        $subject->delete();

        return redirect()->route('admin.subjects.index')
            ->with('success', 'Môn học đã được xóa thành công.');
    }
} 