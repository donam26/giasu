<?php

namespace App\Traits;

trait HasRoleDashboard
{
    /**
     * Lấy route dashboard mặc định cho user
     */
    public function getDefaultDashboardRoute()
    {
        $role = $this->getUserRole();
        $dashboardConfig = config("roles.default_dashboards.{$role}");
        
        return $dashboardConfig['route'] ?? 'home';
    }

    /**
     * Lấy URL dashboard mặc định cho user
     */
    public function getDefaultDashboardUrl()
    {
        $role = $this->getUserRole();
        $dashboardConfig = config("roles.default_dashboards.{$role}");
        
        return $dashboardConfig['url'] ?? '/';
    }

    /**
     * Lấy tên dashboard mặc định cho user
     */
    public function getDefaultDashboardName()
    {
        $role = $this->getUserRole();
        $dashboardConfig = config("roles.default_dashboards.{$role}");
        
        return $dashboardConfig['name'] ?? 'Dashboard';
    }

    /**
     * Xác định role của user
     */
    public function getUserRole()
    {
        // Admin có quyền cao nhất
        if ($this->is_admin) {
            return 'admin';
        }
        
        // Kiểm tra xem có phải gia sư không
        if ($this->isTutor() && $this->tutor) {
            // Gia sư đã được kích hoạt
            if ($this->tutor->status === 'active') {
                return 'tutor';
            }
            // Gia sư chưa được kích hoạt
            return 'pending_tutor';
        }
        
        // Mặc định là student
        return 'student';
    }

    /**
     * Kiểm tra xem user có quyền truy cập route không
     */
    public function canAccessRoute($routeName)
    {
        $role = $this->getUserRole();
        $publicRoutes = config('roles.public_routes', []);
        
        // Nếu là route công cộng
        if (in_array($routeName, $publicRoutes)) {
            return true;
        }
        
        // Kiểm tra prefix route theo role
        $rolePrefixes = config('roles.route_prefixes', []);
        if (isset($rolePrefixes[$role])) {
            foreach ($rolePrefixes[$role] as $prefix) {
                if (str_starts_with($routeName, $prefix)) {
                    return true;
                }
            }
        }
        
        // Kiểm tra cross-role routes
        $crossRoleRoutes = config("roles.cross_role_routes.{$role}", []);
        if (in_array($routeName, $crossRoleRoutes)) {
            return true;
        }
        
        return false;
    }

    /**
     * Redirect đến dashboard phù hợp
     */
    public function redirectToDashboard($message = null, $messageType = 'info')
    {
        $route = $this->getDefaultDashboardRoute();
        $redirect = redirect()->route($route);
        
        if ($message) {
            $redirect->with($messageType, $message);
        }
        
        return $redirect;
    }
} 