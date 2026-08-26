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
        $this->users->updateRole($request, $user);

        return back()->with('success', 'User role updated successfully.');
    }
}