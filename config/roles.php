<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Role-based Dashboard Configuration
    |--------------------------------------------------------------------------
    |
    | Cấu hình trang dashboard mặc định cho từng role trong hệ thống
    |
    */

    'default_dashboards' => [
        'admin' => [
            'route' => 'admin.dashboard',
            'url' => '/admin',
            'name' => 'Trang quản trị'
        ],
        'tutor' => [
            'route' => 'tutor.dashboard',
            'url' => '/tutor/dashboard',
            'name' => 'Trang gia sư'
        ],
        'student' => [
            'route' => 'student.bookings.index',
            'url' => '/student/bookings',
            'name' => 'Trang học sinh'
        ],
        'guest' => [
            'route' => 'home',
            'url' => '/',
            'name' => 'Trang chủ'
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Public Routes (Accessible by all authenticated users)
    |--------------------------------------------------------------------------
    |
    | Danh sách các routes mà tất cả người dùng đã đăng nhập đều có thể truy cập
    |
    */
    'public_routes' => [
        'logout', 'profile.edit', 'profile.update', 'profile.destroy', 
        'profile.password.update', 'profile.update-avatar',
        'payment.callback', 'tutors.index', 'tutors.show', 
        'subjects.index', 'subjects.show', 
        'ai-advisor', 'ai-advisor.chat', 'ai-advisor.reset',
        'privacy-policy', 'about-us', 'contact', 'faq', 'guide', 'terms'
    ],

    /*
    |--------------------------------------------------------------------------
    | Role-specific Route Prefixes
    |--------------------------------------------------------------------------
    |
    | Định nghĩa các prefix route cho từng role
    |
    */
    'route_prefixes' => [
        'admin' => ['admin.'],
        'tutor' => ['tutor.'],
        'student' => ['student.'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cross-role Accessible Routes
    |--------------------------------------------------------------------------
    |
    | Các routes mà một số role khác nhau có thể truy cập
    |
    */
    'cross_role_routes' => [
        'student' => [
            // Student có thể truy cập các routes liên quan đến tutor
            'tutors.book', 'tutors.bookings', 'payment.create', 'payment.history',
            'tutors.store', 'tutors.create', 'tutors.register'
        ],
        'tutor' => [
            // Tutor có thể truy cập một số routes liên quan đến profile tutor
            'tutors.edit', 'tutors.update', 'tutors.pending'
        ]
    ]
]; 