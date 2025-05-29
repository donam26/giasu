<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('login')
                ->with('error', 'Vui lòng đăng nhập để tiếp tục.');
        }

        $student = Auth::user()->student;
        
        if (!$student) {
            return redirect()->route('home')
                ->with('error', 'Bạn không có quyền truy cập trang này. Trang này chỉ dành cho học sinh.');
        }

        if ($student->status !== 'active') {
            return redirect()->route('home')
                ->with('error', 'Tài khoản học sinh của bạn chưa được kích hoạt.');
        }

        return $next($request);
    }
} 