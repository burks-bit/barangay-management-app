<?php

namespace App\Http\Middleware;

use App\Models\BarangayProfile;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Defines the props that are shared with all Inertia responses.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $user ? $user->load('memberProfile') : null,
                'roles' => $user ? $user->getRoleNames()->values() : [],
                'permissions' => $user ? $user->getAllPermissions()->pluck('name')->values() : [],
            ],
            'barangay' => BarangayProfile::where('is_active', true)
                ->with(['officials' => fn ($q) => $q->where('is_active', true)])
                ->first(),
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'info' => fn () => $request->session()->get('info'),
                'warning' => fn () => $request->session()->get('warning'),
            ],
        ]);
    }
}