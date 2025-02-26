<?php

namespace App\Http\Controllers;

use App\Models\Tutor;
use App\Models\Subject;
use App\Models\ClassLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TutorController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $tutors = Tutor::query()
            ->when($request->subject_id, function ($query, $subject_id) {
                return $query->whereHas('subjects', function ($q) use ($subject_id) {
                    $q->where('subjects.id', $subject_id);
                });
            })
            ->when($request->class_level_id, function ($query, $class_level_id) {
                return $query->whereHas('classLevels', function ($q) use ($class_level_id) {
                    $q->where('class_levels.id', $class_level_id);
                });
            })
            ->when($request->min_price, function ($query, $min_price) {
                return $query->where('hourly_rate', '>=', $min_price);
            })
            ->when($request->max_price, function ($query, $max_price) {
                return $query->where('hourly_rate', '<=', $max_price);
            })
            ->when($request->rating, function ($query, $rating) {
                return $query->where('rating', '>=', $rating);
            })
            ->when($request->online_only, function ($query) {
                return $query->where('can_teach_online', true);
            })
            ->where('status', '=', 'active')
            ->where('is_verified', true)
            ->orderBy($request->sort_by ?? 'rating', $request->sort_order ?? 'desc')
            ->paginate(12);

        return view('pages.tutors.index', [
            'tutors' => $tutors,
            'subjects' => Subject::where('is_active', true)->get(),
            'classLevels' => ClassLevel::where('is_active', true)->get()
        ]);
    }

    public function show(Tutor $tutor)
    {
        return view('pages.tutors.show', [
            'tutor' => $tutor->load(['subjects', 'classLevels'])
        ]);
    }

    public function create()
    {
        return view('pages.tutors.create', [
            'subjects' => Subject::where('is_active', true)->get(),
            'classLevels' => ClassLevel::where('is_active', true)->get()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'education_level' => 'required|string|max:255',
            'university' => 'nullable|string|max:255',
            'major' => 'nullable|string|max:255',
            'teaching_experience' => 'required|string',
            'bio' => 'required|string',
            'avatar' => 'nullable|image|max:1024',
            'certification_files' => 'nullable|array',
            'certification_files.*' => 'file|mimes:pdf,jpg,jpeg,png|max:2048',
            'hourly_rate' => 'required|numeric|min:0',
            'teaching_locations' => 'nullable|array',
            'can_teach_online' => 'boolean',
            'subjects' => 'required|array|min:1',
            'subjects.*' => 'exists:subjects,id',
            'class_levels' => 'required|array|min:1',
            'class_levels.*' => 'exists:class_levels,id'
        ]);

        $tutor = new Tutor($validated);
        $tutor->user_id = Auth::id();
        $tutor->status = 'pending';

        if ($request->hasFile('avatar')) {
            $tutor->avatar = $request->file('avatar')->store('avatars', 'public');
        }

        if ($request->hasFile('certification_files')) {
            $paths = [];
            foreach ($request->file('certification_files') as $file) {
                $paths[] = $file->store('certifications', 'public');
            }
            $tutor->certification_files = $paths;
        }

        $tutor->save();

        $tutor->subjects()->attach($request->subjects);
        $tutor->classLevels()->attach($request->class_levels);

        return redirect()->route('tutors.show', $tutor)
            ->with('success', 'Hồ sơ gia sư của bạn đã được tạo và đang chờ xét duyệt.');
    }

    public function edit(Tutor $tutor)
    {
        $this->authorize('update', $tutor);

        return view('pages.tutors.edit', [
            'tutor' => $tutor->load(['subjects', 'classLevels']),
            'subjects' => Subject::where('is_active', true)->get(),
            'classLevels' => ClassLevel::where('is_active', true)->get()
        ]);
    }

    public function update(Request $request, Tutor $tutor)
    {
        $this->authorize('update', $tutor);

        $validated = $request->validate([
            'education_level' => 'required|string|max:255',
            'university' => 'nullable|string|max:255',
            'major' => 'nullable|string|max:255',
            'teaching_experience' => 'required|string',
            'bio' => 'required|string',
            'avatar' => 'nullable|image|max:1024',
            'certification_files' => 'nullable|array',
            'certification_files.*' => 'file|mimes:pdf,jpg,jpeg,png|max:2048',
            'hourly_rate' => 'required|numeric|min:0',
            'teaching_locations' => 'nullable|array',
            'can_teach_online' => 'boolean',
            'subjects' => 'required|array|min:1',
            'subjects.*' => 'exists:subjects,id',
            'class_levels' => 'required|array|min:1',
            'class_levels.*' => 'exists:class_levels,id'
        ]);

        if ($request->hasFile('avatar')) {
            if ($tutor->avatar) {
                Storage::disk('public')->delete($tutor->avatar);
            }
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        if ($request->hasFile('certification_files')) {
            if ($tutor->certification_files) {
                foreach ($tutor->certification_files as $file) {
                    Storage::disk('public')->delete($file);
                }
            }
            $paths = [];
            foreach ($request->file('certification_files') as $file) {
                $paths[] = $file->store('certifications', 'public');
            }
            $validated['certification_files'] = $paths;
        }

        $tutor->update($validated);

        $tutor->subjects()->sync($request->subjects);
        $tutor->classLevels()->sync($request->class_levels);

        return redirect()->route('tutors.show', $tutor)
            ->with('success', 'Hồ sơ gia sư đã được cập nhật thành công.');
    }

    public function book(Request $request, Tutor $tutor)
    {
        // TODO: Implement booking logic
        return back()->with('success', 'Yêu cầu đặt lịch của bạn đã được gửi đến gia sư.');
    }

    public function bookings()
    {
        // TODO: Implement bookings list
        return view('pages.tutors.bookings');
    }
}
