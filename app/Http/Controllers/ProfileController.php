<?php

namespace App\Http\Controllers;

use App\Services\ProfileService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function __construct(private ProfileService $profiles)
    {
    }

    public function show(Request $request): Response
    {
        return Inertia::render('Profile/Show', $this->profiles->show($request->user()));
    }
}