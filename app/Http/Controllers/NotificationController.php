<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(private NotificationService $notifications)
    {
    }

    public function read(Request $request, string $notification): RedirectResponse
    {
        $this->notifications->markAsRead($request->user(), $notification);

        return back();
    }
}