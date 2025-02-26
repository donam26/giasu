<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Tutor;
use App\Models\Subject;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Thống kê cơ bản
        $totalUsers = User::count();
        $totalTutors = Tutor::count();
        $totalSubjects = Subject::count();
        $totalBookings = Booking::count();

        // Lấy danh sách đặt lịch hôm nay
        $todayBookings = Booking::with(['student', 'tutor.user', 'subject'])
            ->whereDate('start_time', Carbon::today())
            ->get();

        // Thống kê đăng ký gia sư theo thời gian (7 ngày gần nhất)
        $tutorRegistrationData = Tutor::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as total'))
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $tutorRegistrationChart = [
            'labels' => $tutorRegistrationData->pluck('date')->map(function($date) {
                return Carbon::parse($date)->format('d/m');
            }),
            'data' => $tutorRegistrationData->pluck('total'),
        ];

        // Thống kê đặt lịch theo môn học
        $bookingsBySubjectData = Booking::select('subjects.name', DB::raw('count(*) as total'))
            ->join('subjects', 'bookings.subject_id', '=', 'subjects.id')
            ->groupBy('subjects.id', 'subjects.name')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $bookingsBySubjectChart = [
            'labels' => $bookingsBySubjectData->pluck('name'),
            'data' => $bookingsBySubjectData->pluck('total'),
        ];

        return view('admin.dashboard', compact(
            'totalUsers',
            'totalTutors',
            'totalSubjects',
            'totalBookings',
            'todayBookings',
            'tutorRegistrationChart',
            'bookingsBySubjectChart'
        ));
    }
} 