<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(): Response
    {
        $currentUser = auth()->user();

        abort_unless($currentUser?->isSuperAdmin(), 403);

        $users = User::query()
            ->with('company')
            ->orderByRaw("CASE WHEN role = 'superadmin' THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->get()
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'company' => [
                    'id' => $user->company?->id,
                    'name' => $user->company?->name,
                ],
                'created_at' => optional($user->created_at)->toISOString(),
                'updated_at' => optional($user->updated_at)->toISOString(),
                'is_current' => $currentUser?->id === $user->id,
            ])
            ->values();

        return Inertia::render('Users/Index', [
            'users' => $users,
            'stats' => [
                'total' => $users->count(),
                'superadmin' => $users->where('role', 'superadmin')->count(),
                'admin' => $users->where('role', 'admin')->count(),
            ],
            'current_user_id' => $currentUser?->id,
        ]);
    }
}
