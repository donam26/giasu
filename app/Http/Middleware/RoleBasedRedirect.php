<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class RoleBasedRedirect
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Kiểm tra đăng nhập
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $route = $request->route()->getName();
        $path = $request->path();

        // Lấy danh sách routes công cộng từ config
        $publicRoutes = config('roles.public_routes', []);

        // Nếu là route công cộng, cho phép truy cập
        if (in_array($route, $publicRoutes) || $path === '/') {
            return $next($request);
        }

        // Xử lý theo từng role
        return $this->handleRoleBasedAccess($user, $route, $path, $request, $next);
    }

    /**
     * Xử lý truy cập dựa trên role
     */
    private function handleRoleBasedAccess($user, $route, $path, $request, $next)
    {
        // ADMIN - Chỉ được truy cập admin routes
        if ($user->is_admin) {
            return $this->handleAdminAccess($route, $path, $request, $next);
        }

        // TUTOR - Chỉ được truy cập tutor routes  
        if ($user->isTutor() && $user->tutor && $user->tutor->status === 'active') {
            return $this->handleTutorAccess($route, $path, $request, $next);
        }

        // PENDING TUTOR - Chỉ được truy cập trang pending
        if ($user->isTutor() && $user->tutor && $user->tutor->status !== 'active') {
            return $this->handlePendingTutorAccess($route, $user, $request, $next);
        }

        // STUDENT - Chỉ được truy cập student routes và general routes
        return $this->handleStudentAccess($route, $path, $request, $next);
    }

    /**
     * Xử lý truy cập cho Admin
     */
    private function handleAdminAccess($route, $path, $request, $next)
    {
        // Admin chỉ được truy cập admin routes
        if (str_starts_with($route, 'admin.') || str_starts_with($path, 'admin')) {
            return $next($request);
        }

        // Redirect admin về dashboard nếu truy cập route khác
        $adminDashboard = config('roles.default_dashboards.admin.route', 'admin.dashboard');
        return redirect()->route($adminDashboard)
            ->with('info', 'Bạn đã được chuyển về trang quản trị.');
    }

    /**
     * Xử lý truy cập cho Tutor
     */
    private function handleTutorAccess($route, $path, $request, $next)
    {
        // Tutor được truy cập tutor routes và một số general routes
        if (str_starts_with($route, 'tutor.') || str_starts_with($path, 'tutor')) {
            return $next($request);
        }

        // Cho phép tutor truy cập một số routes được cấu hình
        $tutorCrossRoutes = config('roles.cross_role_routes.tutor', []);
        if (in_array($route, $tutorCrossRoutes)) {
            return $next($request);
        }

        // Redirect tutor về dashboard nếu truy cập route khác
        $tutorDashboard = config('roles.default_dashboards.tutor.route', 'tutor.dashboard');
        return redirect()->route($tutorDashboard)
            ->with('info', 'Bạn đã được chuyển về trang gia sư.');
    }

    /**
     * Xử lý truy cập cho Tutor chưa được kích hoạt
     */
    private function handlePendingTutorAccess($route, $user, $request, $next)
    {
        // Chỉ được truy cập trang pending và một số routes cơ bản
        if (in_array($route, ['tutors.pending', 'tutors.edit', 'tutors.update'])) {
            return $next($request);
        }

        return redirect()->route('tutors.pending', $user->tutor)
            ->with('warning', 'Tài khoản gia sư của bạn chưa được kích hoạt. Vui lòng chờ quản trị viên xác nhận.');
    }

    /**
     * Xử lý truy cập cho Student
     */
    private function handleStudentAccess($route, $path, $request, $next)
    {
        // Student được truy cập student routes
        if (str_starts_with($route, 'student.') || str_starts_with($path, 'student')) {
            return $next($request);
        }

        // Cho phép student truy cập các routes được cấu hình
        $studentCrossRoutes = config('roles.cross_role_routes.student', []);
        if (in_array($route, $studentCrossRoutes)) {
            return $next($request);
        }

        // Nếu student truy cập admin hoặc tutor routes, redirect về student dashboard
        if (str_starts_with($route, 'admin.') || str_starts_with($route, 'tutor.')) {
            $studentDashboard = config('roles.default_dashboards.student.route', 'student.bookings.index');
            return redirect()->route($studentDashboard)
                ->with('error', 'Bạn không có quyền truy cập trang này.');
        }

        // Cho phép truy cập các route khác (general routes)
        return $next($request);
    }

    /**
     * Xác định role của user
     */
    private function getUserRole($user)
    {
        if ($user->is_admin) {
            return 'admin';
        }
        
        if ($user->isTutor() && $user->tutor && $user->tutor->status === 'active') {
            return 'tutor';
        }
        
        if ($user->isTutor() && $user->tutor && $user->tutor->status !== 'active') {
            return 'pending_tutor';
        }
        
        return 'student';
    }

    /**
     * Log middleware activity for debugging
     */
    private function logAccess($user, $route, $action)
    {
        Log::info('Role-based access control', [
            'user_id' => $user->id,
            'user_role' => $this->getUserRole($user),
            'route' => $route,
            'action' => $action
        ]);
    }
} 