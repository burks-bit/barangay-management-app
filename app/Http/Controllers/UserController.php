<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Users/Index', [
            'users' => User::with('roles')->orderBy('name')->get(['id', 'name', 'email', 'is_active']),
            'roles' => ['admin', 'moderator', 'member'],
        ]);
    }

    public function updateRole(Request $request, User $user)
    {
        $validated = $request->validate([
            'role' => 'required|in:admin,moderator,member',
        ]);

        abort_if($user->is(auth()->user()) && $validated['role'] !== 'admin', 422, 'You cannot remove your own administrator role.');

        $user->syncRoles([$validated['role']]);

        return back()->with('success', 'User role updated successfully.');
    }
}
