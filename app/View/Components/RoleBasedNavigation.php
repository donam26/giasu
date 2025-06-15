<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\Support\Facades\Auth;

class RoleBasedNavigation extends Component
{
    public $user;
    public $currentRole;
    public $dashboardInfo;
    public $availableRoutes;

    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        $this->user = Auth::user();
        
        if ($this->user) {
            $this->currentRole = $this->user->getUserRole();
            $this->dashboardInfo = config("roles.default_dashboards.{$this->currentRole}");
            $this->availableRoutes = $this->getAvailableRoutes();
        }
    }

    /**
     * Lấy danh sách routes có thể truy cập
     */
    private function getAvailableRoutes()
    {
        if (!$this->user) {
            return [];
        }

        $routes = [];
        $role = $this->currentRole;

        // Thêm routes công cộng
        $publicRoutes = config('roles.public_routes', []);
        foreach ($publicRoutes as $route) {
            $routes[] = [
                'name' => $route,
                'type' => 'public'
            ];
        }

        // Thêm routes theo role
        $rolePrefixes = config('roles.route_prefixes', []);
        if (isset($rolePrefixes[$role])) {
            foreach ($rolePrefixes[$role] as $prefix) {
                $routes[] = [
                    'prefix' => $prefix,
                    'type' => 'role_specific'
                ];
            }
        }

        // Thêm cross-role routes
        $crossRoleRoutes = config("roles.cross_role_routes.{$role}", []);
        foreach ($crossRoleRoutes as $route) {
            $routes[] = [
                'name' => $route,
                'type' => 'cross_role'
            ];
        }

        return $routes;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
        return view('components.role-based-navigation');
    }
} 