<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\DocumentType;
use App\Models\User;
use Illuminate\Database\Seeder;

class DocumentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get first registrar for auto-assignment
        $registrar = User::where('usertype', 'registrar')->first();
        $registrarDept = Department::where('type', 'department')->first();

        $documentTypes = [
            [
                'name' => 'Memorandum',
                'code' => 'MEMO',
                'description' => 'Internal communication and announcements',
                'is_active' => true,
                'allowed_roles' => ['admin', 'registrar', 'dean', 'department_head', 'faculty', 'staff'],
                'allowed_receive' => ['admin', 'registrar', 'dean', 'department_head', 'faculty', 'staff'],
                'auto_assign_enabled' => true,
                'routing_logic' => 'role',
                'default_receiver_role' => 'registrar',
            ],
            [
                'name' => 'Letter',
                'code' => 'LTR',
                'description' => 'Official correspondence',
                'is_active' => true,
                'allowed_roles' => ['admin', 'registrar', 'dean', 'department_head', 'faculty', 'staff'],
                'allowed_receive' => ['admin', 'registrar', 'dean', 'department_head'],
                'auto_assign_enabled' => true,
                'routing_logic' => 'role',
                'default_receiver_role' => 'registrar',
            ],
            [
                'name' => 'Request',
                'code' => 'REQ',
                'description' => 'Formal requests and applications',
                'is_active' => true,
                'allowed_roles' => ['admin', 'registrar', 'dean', 'department_head', 'faculty', 'staff', 'student'],
                'allowed_receive' => ['admin', 'registrar', 'dean', 'department_head'],
                'auto_assign_enabled' => true,
                'routing_logic' => 'role',
                'default_receiver_role' => 'registrar',
            ],
            [
                'name' => 'Report',
                'code' => 'RPT',
                'description' => 'Reports and documentation',
                'is_active' => true,
                'allowed_roles' => ['admin', 'registrar', 'dean', 'department_head', 'faculty', 'staff'],
                'allowed_receive' => ['admin', 'registrar', 'dean', 'department_head'],
                'auto_assign_enabled' => false,
            ],
            [
                'name' => 'Circular',
                'code' => 'CIR',
                'description' => 'Circulars and announcements',
                'is_active' => true,
                'allowed_roles' => ['admin', 'registrar'],
                'allowed_receive' => ['admin', 'registrar', 'dean', 'department_head', 'faculty', 'staff'],
                'auto_assign_enabled' => false,
            ],
            [
                'name' => 'Resolution',
                'code' => 'RES',
                'description' => 'Official resolutions',
                'is_active' => true,
                'allowed_roles' => ['admin', 'registrar', 'dean'],
                'allowed_receive' => ['admin', 'registrar', 'dean', 'department_head'],
                'auto_assign_enabled' => false,
            ],
            [
                'name' => 'Certificate',
                'code' => 'CERT',
                'description' => 'Certificates and certifications',
                'is_active' => true,
                'allowed_roles' => ['admin', 'registrar', 'dean', 'department_head'],
                'allowed_receive' => ['admin', 'registrar', 'dean', 'department_head'],
                'auto_assign_enabled' => true,
                'routing_logic' => 'role',
                'default_receiver_role' => 'registrar',
            ],
            [
                'name' => 'Endorsement',
                'code' => 'END',
                'description' => 'Endorsements and recommendations',
                'is_active' => true,
                'allowed_roles' => ['admin', 'registrar', 'dean', 'department_head', 'faculty'],
                'allowed_receive' => ['admin', 'registrar', 'dean', 'department_head'],
                'auto_assign_enabled' => false,
            ],
            [
                'name' => 'Notice',
                'code' => 'NOT',
                'description' => 'Notices and advisories',
                'is_active' => true,
                'allowed_roles' => ['admin', 'registrar', 'dean', 'department_head'],
                'allowed_receive' => ['admin', 'registrar', 'dean', 'department_head', 'faculty', 'staff'],
                'auto_assign_enabled' => false,
            ],
            [
                'name' => 'Student Request',
                'code' => 'SREQ',
                'description' => 'Student requests and applications',
                'is_active' => true,
                'allowed_roles' => ['admin', 'student'],
                'allowed_receive' => ['admin', 'registrar'],
                'auto_assign_enabled' => true,
                'routing_logic' => 'role',
                'default_receiver_role' => 'registrar',
            ],
            [
                'name' => 'Department Memo',
                'code' => 'DMEMO',
                'description' => 'Department-specific memorandum',
                'is_active' => true,
                'allowed_roles' => ['admin', 'dean', 'department_head'],
                'allowed_receive' => ['admin', 'registrar', 'dean', 'department_head'],
                'auto_assign_enabled' => $registrarDept ? true : false,
                'routing_logic' => $registrarDept ? 'department' : null,
                'default_receiver_department_id' => $registrarDept?->id,
            ],
            [
                'name' => 'Specific Assignment Test',
                'code' => 'SATEST',
                'description' => 'Test document type with specific user assignment',
                'is_active' => true,
                'allowed_roles' => ['admin', 'registrar', 'dean', 'department_head', 'faculty', 'staff'],
                'allowed_receive' => ['admin', 'registrar', 'dean', 'department_head'],
                'auto_assign_enabled' => $registrar ? true : false,
                'routing_logic' => $registrar ? 'specific_user' : null,
                'default_receiver_user_id' => $registrar?->id,
            ],
            [
                'name' => 'Other',
                'code' => 'OTH',
                'description' => 'Other document types',
                'is_active' => true,
                'allowed_roles' => ['admin', 'registrar', 'dean', 'department_head', 'faculty', 'staff'],
                'allowed_receive' => ['admin', 'registrar', 'dean', 'department_head'],
                'auto_assign_enabled' => false,
            ],
        ];

        foreach ($documentTypes as $type) {
            DocumentType::updateOrCreate(
                ['code' => $type['code']],
                $type
            );
        }

        $this->command->info('Document types seeded with allowed_roles and auto-assignment configurations!');
    }
}
