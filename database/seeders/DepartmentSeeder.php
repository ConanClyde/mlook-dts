<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Colleges (Led by Deans)
        $colleges = [
            [
                'name' => 'College of Graduate Studies',
                'code' => 'CGS',
                'description' => 'College of Graduate Studies - Led by Dean',
                'type' => 'college',
            ],
            [
                'name' => 'College of Law',
                'code' => 'CLAW',
                'description' => 'College of Law - Led by Dean',
                'type' => 'college',
            ],
            [
                'name' => 'College of Engineering',
                'code' => 'COE',
                'description' => 'College of Engineering - Led by Dean',
                'type' => 'college',
            ],
            [
                'name' => 'College of Information Technology',
                'code' => 'CIT',
                'description' => 'College of Information Technology - Led by Dean',
                'type' => 'college',
            ],
            [
                'name' => 'College of Arts and Sciences',
                'code' => 'CAS',
                'description' => 'College of Arts and Sciences - Led by Dean',
                'type' => 'college',
            ],
            [
                'name' => 'College of Management',
                'code' => 'COM',
                'description' => 'College of Management - Led by Dean',
                'type' => 'college',
            ],
            [
                'name' => 'Institute of Criminal Justice Education',
                'code' => 'ICJE',
                'description' => 'Institute of Criminal Justice Education - Led by Dean',
                'type' => 'college',
            ],
            [
                'name' => 'College of Technology',
                'code' => 'COT',
                'description' => 'College of Technology - Led by Dean',
                'type' => 'college',
            ],
            [
                'name' => 'College of Education',
                'code' => 'CE',
                'description' => 'College of Education - Led by Dean',
                'type' => 'college',
            ],
        ];

        // Administrative Departments (Led by Department Heads or Special Roles)
        $adminDepartments = [
            [
                'name' => 'Registrar Office',
                'code' => 'REG',
                'description' => 'Handles student records, enrollment, and academic documents - Led by Registrar',
                'type' => 'department',
            ],
            [
                'name' => 'Office of the Campus Director',
                'code' => 'OCD',
                'description' => 'Campus Director\'s Office - Led by Campus Director',
                'type' => 'department',
            ],
            [
                'name' => 'Human Resource Office',
                'code' => 'HRO',
                'description' => 'Human Resource Management Office - Led by Department Head',
                'type' => 'department',
            ],
            [
                'name' => 'Accounting Office',
                'code' => 'ACCT',
                'description' => 'Accounting and Finance Office - Led by Department Head',
                'type' => 'department',
            ],
            [
                'name' => 'Student Affairs Office',
                'code' => 'SAO',
                'description' => 'Student Affairs and Services - Led by Department Head',
                'type' => 'department',
            ],
            [
                'name' => 'Library',
                'code' => 'LIB',
                'description' => 'University Library - Led by Department Head',
                'type' => 'department',
            ],
            [
                'name' => 'Information Technology Services',
                'code' => 'ITS',
                'description' => 'IT Support and Services Office - Led by Department Head',
                'type' => 'department',
            ],
            [
                'name' => 'Physical Plant and Facilities',
                'code' => 'PPF',
                'description' => 'Facilities and Maintenance Office - Led by Department Head',
                'type' => 'department',
            ],
            [
                'name' => 'Security Office',
                'code' => 'SEC',
                'description' => 'Campus Security Office - Led by Department Head',
                'type' => 'department',
            ],
            [
                'name' => 'Guidance and Counseling',
                'code' => 'GC',
                'description' => 'Guidance and Counseling Services - Led by Department Head',
                'type' => 'department',
            ],
            [
                'name' => 'Supply and Property Office',
                'code' => 'SPO',
                'description' => 'Supply and Property Management - Led by Department Head',
                'type' => 'department',
            ],
            [
                'name' => 'Research and Development Office',
                'code' => 'RDO',
                'description' => 'Research and Development Office - Led by Department Head',
                'type' => 'department',
            ],
        ];

        $departments = array_merge($colleges, $adminDepartments);

        foreach ($departments as $department) {
            Department::updateOrCreate(
                ['code' => $department['code']], // Find by code
                $department // Update or create with this data
            );
        }
    }
}
