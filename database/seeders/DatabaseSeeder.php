<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\AssistanceRequest;
use App\Models\AssistanceType;
use App\Models\Calamity;
use App\Models\Complaint;
use App\Models\ComplaintCategory;
use App\Models\EvacuationCenter;
use App\Models\Household;
use App\Models\Incident;
use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use App\Models\MemberProfile;
use App\Models\Purok;
use App\Models\RequestType;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RoleAndPermissionSeeder::class);

        // Create Puroks
        $puroks = [
            ['name' => 'Purok 1', 'code' => 'P1', 'description' => 'Near the barangay hall'],
            ['name' => 'Purok 2', 'code' => 'P2', 'description' => 'Along the main road'],
            ['name' => 'Purok 3', 'code' => 'P3', 'description' => 'Riverside area'],
            ['name' => 'Purok 4', 'code' => 'P4', 'description' => 'Hillside area'],
            ['name' => 'Purok 5', 'code' => 'P5', 'description' => 'Near the school'],
        ];
        foreach ($puroks as $purok) {
            Purok::create($purok);
        }

        // Create Admin
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@barangay.test',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $admin->assignRole('admin');

        // Create Moderators (with member profiles)
        $moderators = [
            [
                'name' => 'Maria Santos',
                'email' => 'maria@barangay.test',
                'profile' => [
                    'first_name' => 'Maria',
                    'middle_name' => 'Cruz',
                    'last_name' => 'Santos',
                    'date_of_birth' => '1985-03-15',
                    'sex' => 'female',
                    'civil_status' => 'married',
                    'contact_number' => '09171234567',
                    'address' => '123 Purok 1 St.',
                    'occupation' => 'Barangay Secretary',
                    'verification_status' => 'verified',
                ],
            ],
            [
                'name' => 'Juan Dela Cruz',
                'email' => 'juan@barangay.test',
                'profile' => [
                    'first_name' => 'Juan',
                    'middle_name' => 'Reyes',
                    'last_name' => 'Dela Cruz',
                    'date_of_birth' => '1990-07-22',
                    'sex' => 'male',
                    'civil_status' => 'single',
                    'contact_number' => '09179876543',
                    'address' => '456 Purok 2 St.',
                    'occupation' => 'Barangay Kagawad',
                    'verification_status' => 'verified',
                ],
            ],
            [
                'name' => 'Ana Reyes',
                'email' => 'ana@barangay.test',
                'profile' => [
                    'first_name' => 'Ana',
                    'middle_name' => 'Lopez',
                    'last_name' => 'Reyes',
                    'date_of_birth' => '1992-11-08',
                    'sex' => 'female',
                    'civil_status' => 'single',
                    'contact_number' => '09175551234',
                    'address' => '789 Purok 3 St.',
                    'occupation' => 'Barangay Health Worker',
                    'verification_status' => 'verified',
                ],
            ],
        ];

        foreach ($moderators as $moderatorData) {
            $user = User::create([
                'name' => $moderatorData['name'],
                'email' => $moderatorData['email'],
                'password' => Hash::make('password'),
                'is_active' => true,
            ]);
            $user->assignRole('moderator');

            $profileData = $moderatorData['profile'];
            $profileData['user_id'] = $user->id;
            $profileData['purok_id'] = Purok::inRandomOrder()->first()->id;
            $profileData['resident_id'] = 'RES-' . str_pad($user->id, 6, '0', STR_PAD_LEFT);
            $profileData['verified_by'] = $admin->id;
            $profileData['verified_at'] = now();
            MemberProfile::create($profileData);
        }

        // Create Households
        $households = [];
        for ($i = 1; $i <= 10; $i++) {
            $households[] = Household::create([
                'household_code' => 'HH-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'address' => $i * 100 . ' Sample St., Purok ' . (($i % 5) + 1),
                'purok_id' => (($i % 5) + 1),
                'contact_number' => '0917' . str_pad($i * 1111, 7, '0', STR_PAD_LEFT),
                'vulnerability_indicators' => $i % 3 === 0 ? ['senior_citizen', 'pwd'] : ($i % 2 === 0 ? ['child'] : []),
            ]);
        }

        // Create Members
        $memberNames = [
            ['Pedro', 'Garcia', 'Mendoza'], ['Liza', 'Fernandez', 'Aquino'],
            ['Carlos', 'Villanueva', 'Ramos'], ['Elena', 'Torres', 'Castro'],
            ['Miguel', 'Ramos', 'Santos'], ['Rosa', 'Mendoza', 'Lopez'],
            ['Antonio', 'Castro', 'Garcia'], ['Carmen', 'Aquino', 'Reyes'],
            ['Jose', 'Lopez', 'Villanueva'], ['Teresa', 'Reyes', 'Fernandez'],
            ['Ramon', 'Santos', 'Torres'], ['Gloria', 'Cruz', 'Mendoza'],
            ['Manuel', 'Diaz', 'Castro'], ['Fe', 'Ramos', 'Aquino'],
            ['Ricardo', 'Garcia', 'Lopez'], ['Nena', 'Villanueva', 'Santos'],
            ['Domingo', 'Torres', 'Reyes'], ['Lourdes', 'Mendoza', 'Cruz'],
            ['Felipe', 'Aquino', 'Diaz'], ['Corazon', 'Fernandez', 'Garcia'],
        ];

        foreach ($memberNames as $index => $member) {
            $user = User::create([
                'name' => $member[0] . ' ' . $member[2],
                'email' => strtolower($member[0] . '.' . $member[2]) . '@barangay.test',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]);
            $user->assignRole('member');

            $household = $households[$index % 10];
            $profile = MemberProfile::create([
                'user_id' => $user->id,
                'resident_id' => 'RES-' . str_pad($user->id, 6, '0', STR_PAD_LEFT),
                'first_name' => $member[0],
                'middle_name' => $member[1],
                'last_name' => $member[2],
                'date_of_birth' => now()->subYears(25 + ($index % 40))->subDays($index * 17),
                'sex' => $index % 2 === 0 ? 'male' : 'female',
                'civil_status' => ['single', 'married', 'married', 'widowed'][$index % 4],
                'contact_number' => '0918' . str_pad($index * 2222, 7, '0', STR_PAD_LEFT),
                'address' => $household->address,
                'purok_id' => $household->purok_id,
                'household_id' => $household->id,
                'occupation' => ['Farmer', 'Fisherman', 'Teacher', 'Vendor', 'Driver', 'Housewife', 'Laborer'][$index % 7],
                'verification_status' => $index % 4 === 0 ? 'pending' : 'verified',
                'verified_by' => $index % 4 === 0 ? null : $admin->id,
                'verified_at' => $index % 4 === 0 ? null : now(),
            ]);

            // Add to household members
            $household->members()->create([
                'member_profile_id' => $profile->id,
                'relationship' => $index === 0 ? 'head' : ($index % 3 === 0 ? 'spouse' : 'child'),
                'is_head' => $index === 0,
            ]);
        }

        // Set household heads
        foreach ($households as $index => $household) {
            $head = MemberProfile::where('household_id', $household->id)->first();
            if ($head) {
                $household->update(['head_of_family_id' => $head->id]);
            }
        }

        // Create Complaint Categories
        $complaintCategories = [
            ['name' => 'Noise', 'slug' => 'noise'],
            ['name' => 'Illegal Dumping', 'slug' => 'illegal-dumping'],
            ['name' => 'Stray Animals', 'slug' => 'stray-animals'],
            ['name' => 'Parking', 'slug' => 'parking'],
            ['name' => 'Neighborhood Dispute', 'slug' => 'neighborhood-dispute'],
            ['name' => 'Public Disturbance', 'slug' => 'public-disturbance'],
            ['name' => 'Property Concern', 'slug' => 'property-concern'],
            ['name' => 'Illegal Construction', 'slug' => 'illegal-construction'],
            ['name' => 'Environmental Concern', 'slug' => 'environmental-concern'],
            ['name' => 'Other', 'slug' => 'other'],
        ];
        foreach ($complaintCategories as $category) {
            ComplaintCategory::create($category);
        }

        // Create Request Types
        $requestTypes = [
            ['name' => 'Barangay Clearance', 'slug' => 'barangay-clearance', 'fee' => 50],
            ['name' => 'Certificate of Residency', 'slug' => 'certificate-of-residency', 'fee' => 30],
            ['name' => 'Certificate of Indigency', 'slug' => 'certificate-of-indigency', 'fee' => 0],
            ['name' => 'Certificate of Good Moral', 'slug' => 'certificate-of-good-moral', 'fee' => 30],
            ['name' => 'Business Clearance', 'slug' => 'business-clearance', 'fee' => 100],
            ['name' => 'Other Barangay Document', 'slug' => 'other-document', 'fee' => 0],
        ];
        foreach ($requestTypes as $type) {
            RequestType::create($type);
        }

        // Create Assistance Types
        $assistanceTypes = [
            ['name' => 'Medical', 'slug' => 'medical'],
            ['name' => 'Financial', 'slug' => 'financial'],
            ['name' => 'Food', 'slug' => 'food'],
            ['name' => 'Emergency', 'slug' => 'emergency'],
            ['name' => 'Transportation', 'slug' => 'transportation'],
            ['name' => 'Other', 'slug' => 'other'],
        ];
        foreach ($assistanceTypes as $type) {
            AssistanceType::create($type);
        }

        // Create Sample Complaints
        $members = User::role('member')->get();
        $complaintSubjects = [
            'Loud music at night', 'Illegal garbage dumping', 'Stray dogs barking',
            'Neighbor dispute over property line', 'Unattended construction noise',
            'Public disturbance at karaoke bar', 'Abandoned vehicle blocking road',
        ];
        $complaintStatuses = ['submitted', 'under_review', 'verified', 'assigned', 'under_investigation', 'resolved', 'closed'];

        for ($i = 0; $i < 10; $i++) {
            $member = $members[$i % $members->count()];
            $status = $complaintStatuses[$i % count($complaintStatuses)];
            $complaint = Complaint::create([
                'complaint_code' => 'CMP-' . date('Y') . '-' . str_pad($i + 1, 6, '0', STR_PAD_LEFT),
                'complainant_id' => $member->id,
                'category_id' => ComplaintCategory::inRandomOrder()->first()->id,
                'subject' => $complaintSubjects[$i % count($complaintSubjects)],
                'description' => 'Detailed description of the complaint issue number ' . ($i + 1) . '. This includes the full context of what happened.',
                'location' => 'Purok ' . (($i % 5) + 1),
                'incident_datetime' => now()->subDays($i + 1),
                'priority' => ['low', 'medium', 'high', 'urgent'][$i % 4],
                'assigned_to' => $i % 3 === 0 ? null : User::role('moderator')->inRandomOrder()->first()->id,
                'status' => $status,
                'resolution' => in_array($status, ['resolved', 'closed']) ? 'Complaint has been resolved through mediation.' : null,
                'resolution_date' => in_array($status, ['resolved', 'closed']) ? now()->subDays($i) : null,
            ]);

            // Add status history
            $complaint->statusHistories()->create([
                'user_id' => $member->id,
                'from_status' => null,
                'to_status' => 'submitted',
                'remarks' => 'Complaint submitted',
            ]);
        }

        // Create Sample Service Requests
        $requestPurposes = [
            'Employment application', 'School enrollment', 'Government benefit application',
            'Business permit processing', 'Loan application', 'Medical assistance application',
        ];
        $requestStatuses = ['submitted', 'for_verification', 'approved', 'processing', 'ready_for_release', 'released', 'rejected'];

        for ($i = 0; $i < 10; $i++) {
            $member = $members[$i % $members->count()];
            $status = $requestStatuses[$i % count($requestStatuses)];
            $request = ServiceRequest::create([
                'tracking_number' => 'REQ-' . date('Y') . '-' . str_pad($i + 1, 6, '0', STR_PAD_LEFT),
                'requester_id' => $member->id,
                'request_type_id' => RequestType::inRandomOrder()->first()->id,
                'purpose' => $requestPurposes[$i % count($requestPurposes)],
                'description' => 'Supporting details for request number ' . ($i + 1),
                'status' => $status,
                'assigned_to' => $i % 3 === 0 ? null : User::role('moderator')->inRandomOrder()->first()->id,
                'submitted_at' => now()->subDays($i + 1),
                'processed_at' => in_array($status, ['processing', 'ready_for_release', 'released']) ? now()->subDays($i) : null,
                'released_at' => $status === 'released' ? now()->subDays($i - 1) : null,
            ]);

            $request->statusHistories()->create([
                'user_id' => $member->id,
                'from_status' => null,
                'to_status' => 'submitted',
                'remarks' => 'Request submitted',
            ]);
        }

        // Create Sample Assistance Requests
        $assistanceReasons = [
            'Medical emergency - hospital bills', 'Financial assistance for family',
            'Food assistance for the week', 'Emergency assistance after flood',
            'Transportation assistance for medical appointment',
        ];

        for ($i = 0; $i < 3; $i++) {
            $member = $members[$i % $members->count()];
            $status = ['submitted', 'for_verification', 'approved'][$i];
            AssistanceRequest::create([
                'assistance_code' => 'AST-' . date('Y') . '-' . str_pad($i + 1, 6, '0', STR_PAD_LEFT),
                'applicant_id' => $member->id,
                'assistance_type_id' => AssistanceType::inRandomOrder()->first()->id,
                'reason' => $assistanceReasons[$i],
                'amount' => $i === 0 ? 5000 : null,
                'status' => $status,
            ]);
        }

        // Create Calamities
        $calamity1 = Calamity::create([
            'event_code' => 'CAL-' . date('Y') . '-000001',
            'name' => 'Typhoon Rolly',
            'type' => 'typhoon',
            'description' => 'Strong typhoon with heavy rainfall and strong winds affecting the barangay.',
            'started_at' => now()->subDays(10),
            'severity' => 'high',
            'status' => 'under_response',
            'affected_households' => 25,
            'affected_residents' => 100,
        ]);
        $calamity1->puroks()->attach([1, 2, 3]);

        $calamity2 = Calamity::create([
            'event_code' => 'CAL-' . date('Y') . '-000002',
            'name' => 'Flash Flood',
            'type' => 'flood',
            'description' => 'Flash flood caused by continuous heavy rain.',
            'started_at' => now()->subDays(3),
            'severity' => 'moderate',
            'status' => 'active',
            'affected_households' => 10,
            'affected_residents' => 40,
        ]);
        $calamity2->puroks()->attach([3, 4]);

        // Create Incidents
        $incidentTypes = ['flood', 'fire', 'landslide', 'accident'];
        for ($i = 0; $i < 3; $i++) {
            Incident::create([
                'incident_code' => 'INC-' . date('Y') . '-' . str_pad($i + 1, 6, '0', STR_PAD_LEFT),
                'calamity_id' => $i < 2 ? $calamity1->id : null,
                'type' => $incidentTypes[$i],
                'location' => 'Purok ' . (($i % 5) + 1),
                'purok_id' => ($i % 5) + 1,
                'description' => 'Incident description for incident number ' . ($i + 1),
                'severity' => ['high', 'moderate', 'low'][$i],
                'reported_by' => User::role('moderator')->first()->id,
                'status' => ['under_response', 'verified', 'reported'][$i],
                'affected_households' => ($i + 1) * 5,
                'affected_residents' => ($i + 1) * 20,
            ]);
        }

        // Create Evacuation Centers
        $centers = [
            ['name' => 'Barangay Covered Court', 'location' => 'Barangay Hall Compound', 'capacity' => 300, 'facilities' => ['restrooms', 'kitchen', 'water_source']],
            ['name' => 'Barangay Elementary School', 'location' => 'Purok 5', 'capacity' => 500, 'facilities' => ['classrooms', 'restrooms', 'kitchen', 'water_source']],
            ['name' => 'Barangay Health Center', 'location' => 'Purok 1', 'capacity' => 100, 'facilities' => ['medical_room', 'restrooms', 'water_source']],
        ];
        foreach ($centers as $center) {
            EvacuationCenter::create($center);
        }

        // Create Inventory Items
        $inventoryItems = [
            ['name' => 'Food Pack', 'sku' => 'FP-001', 'unit' => 'pack', 'current_stock' => 500, 'reorder_level' => 100, 'category' => 'food'],
            ['name' => 'Bottled Water', 'sku' => 'BW-001', 'unit' => 'bottle', 'current_stock' => 1000, 'reorder_level' => 200, 'category' => 'water'],
            ['name' => 'Hygiene Kit', 'sku' => 'HK-001', 'unit' => 'kit', 'current_stock' => 200, 'reorder_level' => 50, 'category' => 'hygiene'],
            ['name' => 'Blanket', 'sku' => 'BL-001', 'unit' => 'piece', 'current_stock' => 150, 'reorder_level' => 30, 'category' => 'clothing'],
            ['name' => 'Medicine Kit', 'sku' => 'MK-001', 'unit' => 'kit', 'current_stock' => 50, 'reorder_level' => 10, 'category' => 'medical'],
        ];
        foreach ($inventoryItems as $item) {
            $inventoryItem = InventoryItem::create($item);
            InventoryTransaction::create([
                'inventory_item_id' => $inventoryItem->id,
                'type' => 'in',
                'quantity' => $item['current_stock'],
                'stock_before' => 0,
                'stock_after' => $item['current_stock'],
                'source' => 'Initial stock',
                'user_id' => $admin->id,
            ]);
        }

        // Create Announcements
        $announcements = [
            [
                'title' => 'FLOOD WARNING',
                'content' => 'Residents of Purok 3 are advised to prepare for possible flooding. Please monitor updates and be ready to evacuate.',
                'type' => 'calamity_warning',
                'priority' => 'emergency',
                'status' => 'published',
            ],
            [
                'title' => 'Barangay Cleanup Drive',
                'content' => 'Join us this Saturday for our monthly cleanup drive. Meet at the barangay hall at 7 AM.',
                'type' => 'community_event',
                'priority' => 'normal',
                'status' => 'published',
            ],
            [
                'title' => 'Free Medical Mission',
                'content' => 'Free medical checkup and medicines will be available at the Barangay Health Center on Friday.',
                'type' => 'barangay_announcement',
                'priority' => 'important',
                'status' => 'published',
            ],
        ];
        foreach ($announcements as $announcement) {
            Announcement::create(array_merge($announcement, [
                'created_by' => User::role('moderator')->first()->id,
                'published_at' => now(),
            ]));
        }
    }
}