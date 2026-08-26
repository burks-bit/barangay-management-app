<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;

class UserService
{
    public function list(): array
    {
        return [
            'users' => User::with('roles')->orderBy('name')->get(['id', 'name', 'email', 'is_active']),
            'roles' => ['admin', 'moderator', 'member'],
        ];
    }

    public function updateRole(Request $request, User $user): void
    {
        $validated = $request->validate([
            'role' => 'required|in:admin,moderator,member',
        ]);

        abort_if($user->is(auth()->user()) && $validated['role'] !== 'admin', 422, 'You cannot remove your own administrator role.');

        $user->syncRoles([$validated['role']]);
    }
}