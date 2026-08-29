<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function __construct(private UserService $users)
    {
    }

    public function index(): Response
    {
        return Inertia::render('Users/Index', $this->users->list());
    }

    public function updateRole(Request $request, User $user)
    {
        return $this->handle(
            fn () => $this->users->updateRole($request, $user),
            fn () => back()->with('success', 'User role updated successfully.'),
            'UserController::updateRole'
        );
    }
}