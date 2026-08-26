<?php

namespace App\Services;

use App\Models\BarangayProfile;

class AuthService
{
    /**
     * Data for the login page (active barangay profile and officials).
     */
    public function loginPageData(): array
    {
        return [
            'canResetPassword' => false,
            'barangay' => BarangayProfile::with(['activeOfficials' => function ($query) {
                $query->select([
                    'id',
                    'barangay_profile_id',
                    'position',
                    'first_name',
                    'middle_name',
                    'last_name',
                    'suffix',
                ])->orderBy('position');
            }])->where('is_active', true)->latest()->first(),
        ];
    }
}