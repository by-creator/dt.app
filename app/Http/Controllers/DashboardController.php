<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $today = CarbonImmutable::today();
        $recentUsers = User::query()
            ->with('role')
            ->latest()
            ->take(8)
            ->get();

        $adminRoleId = Role::query()
            ->where('name', 'ADMIN')
            ->value('id');

        return view('dashboard', [
            'todayLabel' => now()->locale('fr')->translatedFormat('l d F Y'),
            'stats' => [
                'total_users' => User::query()->count(),
                'total_roles' => Role::query()->count(),
                'admin_users' => $adminRoleId
                    ? User::query()->where('role_id', $adminRoleId)->count()
                    : 0,
                'users_today' => User::query()->whereDate('created_at', $today)->count(),
            ],
            'recentUsers' => $recentUsers,
        ]);
    }
}
