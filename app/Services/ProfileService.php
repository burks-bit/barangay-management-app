<?php

namespace App\Services;

use App\Models\User;

class ProfileService
{
    public function show(User $user): array
    {
        $user->load([
            'memberProfile.purok',
            'memberProfile.household',
        ]);

        return [
            'user' => $user,
            'roles' => $user->getRoleNames()->values(),
        ];
    }
}