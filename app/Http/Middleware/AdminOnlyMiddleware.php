<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminOnlyMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Nếu chưa đăng nhập
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $route = $request->route()->getName();

        // Nếu là admin
        if ($user->is_admin) {
            // Cho phép truy cập admin routes và một số routes cơ bản
            $allowedRoutes = [
                'logout', 'profile.edit', 'profile.update', 'profile.destroy', 
                'profile.password.update', 'profile.update-avatar'
            ];
            
            if (str_starts_with($route, 'admin.') || in_array($route, $allowedRoutes)) {
                return $next($request);
            }
            
            // Redirect về admin dashboard cho tất cả route khác
            return redirect()->route('admin.dashboard')
                ->with('info', 'Bạn đã được chuyển về trang quản trị.');
        }

        // Nếu không phải admin, cho phép truy cập
        return $next($request);
    }
} 