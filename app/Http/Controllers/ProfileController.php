<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function show(Request $request): Response
    {
        $user = $request->user()->load([
            'memberProfile.purok',
            'memberProfile.household',
        ]);

        return Inertia::render('Profile/Show', [
            'user' => $user,
            'roles' => $user->getRoleNames()->values(),
        ]);
    }
}