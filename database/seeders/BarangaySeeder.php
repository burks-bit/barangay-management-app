<?php

namespace Database\Seeders;

use App\Models\BarangayOfficial;
use App\Models\BarangayProfile;
use Illuminate\Database\Seeder;

class BarangaySeeder extends Seeder
{
    public function run(): void
    {
        // Create Barangay Profile
        $barangayProfile = BarangayProfile::create([
            'name' => 'Santisimo Rosario',
            'description' => 'A peaceful and progressive barangay committed to the welfare of its residents.',
            'mission' => 'To provide efficient and transparent governance, ensuring the safety, health, and well-being of every resident through responsive programs and services.',
            'vision' => 'A peaceful, progressive, and resilient barangay where every resident enjoys a high quality of life, equal opportunities, and strong community spirit.',
            'address' => 'Barangay Hall, Santisimo Rosario, San Pablo City, Laguna',
            'about' => 'Barangay Santisimo Rosario in San Pablo City, Laguna is a vibrant community composed of five puroks with active community participation. We are committed to delivering basic services, maintaining peace and order, and fostering community development.',
            'is_active' => true,
        ]);

        // Barangay Captain and Vice Captain
        $officials = [
            [
                'position' => 'captain',
                'first_name' => 'Ramon',
                'middle_name' => 'Santos',
                'last_name' => 'Dela Cruz',
                'sex' => 'male',
                'contact_number' => '09171234567',
                'term_start' => 2023,
                'term_end' => 2025,
                'is_active' => true,
                'notes' => 'Punong Barangay',
            ],
            [
                'position' => 'vice_captain',
                'first_name' => 'Maria',
                'middle_name' => 'Cruz',
                'last_name' => 'Santos',
                'sex' => 'female',
                'contact_number' => '09179876543',
                'term_start' => 2023,
                'term_end' => 2025,
                'is_active' => true,
                'notes' => 'Punong Barangay Vice Captain',
            ],
        ];

        // Add Kagawads (Barangay Councilors)
        $kagawads = [
            ['Juan', 'Reyes', 'Dela Cruz', 'male', 'Public Safety'],
            ['Ana', 'Lopez', 'Reyes', 'female', 'Health'],
            ['Carlos', 'Villanueva', 'Ramos', 'male', 'Education'],
            ['Elena', 'Torres', 'Castro', 'female', 'Livelihood'],
            ['Miguel', 'Ramos', 'Santos', 'male', 'Infrastructure'],
            ['Rosa', 'Cruz', 'Mendoza', 'female', 'Environment'],
            ['Antonio', 'Castro', 'Garcia', 'male', 'Youth and Sports'],
        ];

        foreach ($kagawads as $kagawad) {
            $officials[] = [
                'position' => 'kagawad',
                'first_name' => $kagawad[0],
                'middle_name' => $kagawad[1],
                'last_name' => $kagawad[2],
                'sex' => $kagawad[3],
                'contact_number' => '0917' . str_pad(rand(1000000, 9999999), 7, '0', STR_PAD_LEFT),
                'committee' => $kagawad[4],
                'term_start' => 2023,
                'term_end' => 2025,
                'is_active' => true,
            ];
        }

        // Secretary, Treasurer, SK Chairperson
        $officials[] = [
            'position' => 'secretary',
            'first_name' => 'Liza',
            'middle_name' => 'Fernandez',
            'last_name' => 'Aquino',
            'sex' => 'female',
            'contact_number' => '09178881234',
            'term_start' => 2023,
            'term_end' => 2025,
            'is_active' => true,
        ];

        $officials[] = [
            'position' => 'treasurer',
            'first_name' => 'Carlos',
            'middle_name' => 'Villanueva',
            'last_name' => 'Ramos',
            'sex' => 'male',
            'contact_number' => '09175556789',
            'term_start' => 2023,
            'term_end' => 2025,
            'is_active' => true,
        ];

        $officials[] = [
            'position' => 'sangguniang_kabataan_chairperson',
            'first_name' => 'Ana',
            'middle_name' => 'Lopez',
            'last_name' => 'Reyes',
            'sex' => 'female',
            'contact_number' => '09172223344',
            'term_start' => 2023,
            'term_end' => 2025,
            'is_active' => true,
        ];

        foreach ($officials as $official) {
            $official['barangay_profile_id'] = $barangayProfile->id;
            BarangayOfficial::create($official);
        }
    }
}