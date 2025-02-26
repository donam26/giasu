<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    /**
     * Hiển thị danh sách môn học
     */
    public function index()
    {
        $subjects = Subject::query()
            ->where('is_active', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->groupBy('category');

        return view('pages.subjects.index', [
            'subjects' => $subjects
        ]);
    }

    /**
     * Hiển thị chi tiết môn học
     */
    public function show(Subject $subject)
    {
        return view('pages.subjects.show', [
            'subject' => $subject->load(['tutors' => function ($query) {
                $query->where('status', 'active')
                    ->where('is_verified', true)
                    ->orderBy('rating', 'desc');
            }])
        ]);
    }
}
