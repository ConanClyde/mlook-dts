<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Program;
use Illuminate\Database\Seeder;

class ProgramSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get colleges
        $cgs = Department::where('code', 'CGS')->first();
        $claw = Department::where('code', 'CLAW')->first();
        $coe = Department::where('code', 'COE')->first();
        $cit = Department::where('code', 'CIT')->first();
        $cas = Department::where('code', 'CAS')->first();
        $com = Department::where('code', 'COM')->first();
        $icje = Department::where('code', 'ICJE')->first();
        $cot = Department::where('code', 'COT')->first();
        $ce = Department::where('code', 'CE')->first();

        $programs = [];

        // College of Graduate Studies Programs
        if ($cgs) {
            $programs = array_merge($programs, [
                [
                    'name' => 'Doctor of Philosophy in Technology Education Management',
                    'code' => 'CGS-PHD-TEM',
                    'description' => 'PhD in Technology Education Management',
                    'college_id' => $cgs->id,
                ],
                [
                    'name' => 'Doctor of Philosophy in Development Administration',
                    'code' => 'CGS-PHD-DA',
                    'description' => 'PhD in Development Administration',
                    'college_id' => $cgs->id,
                ],
                [
                    'name' => 'Doctor of Philosophy in Science Education',
                    'code' => 'CGS-PHD-SE',
                    'description' => 'PhD in Science Education',
                    'college_id' => $cgs->id,
                ],
                [
                    'name' => 'Doctor of Philosophy in Mathematics Education',
                    'code' => 'CGS-PHD-ME',
                    'description' => 'PhD in Mathematics Education',
                    'college_id' => $cgs->id,
                ],
                [
                    'name' => 'Master of Arts in Technology Education',
                    'code' => 'CGS-MA-TE',
                    'description' => 'MA in Technology Education',
                    'college_id' => $cgs->id,
                ],
                [
                    'name' => 'Master of Arts in Development Administration',
                    'code' => 'CGS-MA-DA',
                    'description' => 'MA in Development Administration',
                    'college_id' => $cgs->id,
                ],
                [
                    'name' => 'Master of Arts in Science Education',
                    'code' => 'CGS-MA-SE',
                    'description' => 'MA in Science Education',
                    'college_id' => $cgs->id,
                ],
                [
                    'name' => 'Master of Arts in Mathematics Education',
                    'code' => 'CGS-MA-ME',
                    'description' => 'MA in Mathematics Education',
                    'college_id' => $cgs->id,
                ],
                [
                    'name' => 'Master in Management Engineering',
                    'code' => 'CGS-ME',
                    'description' => 'Master in Management Engineering',
                    'college_id' => $cgs->id,
                ],
            ]);
        }

        // College of Law Programs
        if ($claw) {
            $programs = array_merge($programs, [
                [
                    'name' => 'Juris Doctor (JD)',
                    'code' => 'CLAW-JD',
                    'description' => 'Juris Doctor Program',
                    'college_id' => $claw->id,
                ],
            ]);
        }

        // College of Engineering Programs
        if ($coe) {
            $programs = array_merge($programs, [
                [
                    'name' => 'Bachelor of Science in Electrical Engineering (BSEE)',
                    'code' => 'COE-BSEE',
                    'description' => 'Electrical Engineering Program',
                    'college_id' => $coe->id,
                ],
                [
                    'name' => 'Bachelor of Science in Mechanical Engineering (BSME)',
                    'code' => 'COE-BSME',
                    'description' => 'Mechanical Engineering Program',
                    'college_id' => $coe->id,
                ],
            ]);
        }

        // College of Information Technology Programs
        if ($cit) {
            $programs = array_merge($programs, [
                [
                    'name' => 'Master in Information Technology (MIT)',
                    'code' => 'CIT-MIT',
                    'description' => 'Master in Information Technology',
                    'college_id' => $cit->id,
                ],
                [
                    'name' => 'Bachelor of Science in Information Technology (BSInfo-Tech)',
                    'code' => 'CIT-BSInfoTech',
                    'description' => 'Bachelor of Science in Information Technology',
                    'college_id' => $cit->id,
                ],
            ]);
        }

        // College of Arts and Sciences Programs
        if ($cas) {
            $programs = array_merge($programs, [
                [
                    'name' => 'Bachelor of Science in Psychology (BSP)',
                    'code' => 'CAS-BSP',
                    'description' => 'Psychology Program',
                    'college_id' => $cas->id,
                ],
                [
                    'name' => 'Bachelor of Arts in Political Science (BAPoS)',
                    'code' => 'CAS-BAPoS',
                    'description' => 'Political Science Program',
                    'college_id' => $cas->id,
                ],
                [
                    'name' => 'Bachelor of Arts in English Language (BAEL)',
                    'code' => 'CAS-BAEL',
                    'description' => 'English Language Program',
                    'college_id' => $cas->id,
                ],
                [
                    'name' => 'Batsilyer ng Sining sa Filipino (BSF)',
                    'code' => 'CAS-BSF',
                    'description' => 'Filipino Program',
                    'college_id' => $cas->id,
                ],
            ]);
        }

        // College of Management Programs
        if ($com) {
            $programs = array_merge($programs, [
                [
                    'name' => 'Bachelor of Science in Hospitality Management (BSHM)',
                    'code' => 'COM-BSHM',
                    'description' => 'Hospitality Management Program',
                    'college_id' => $com->id,
                ],
                [
                    'name' => 'Bachelor of Science in Business Administration (BSBA)',
                    'code' => 'COM-BSBA',
                    'description' => 'Business Administration Program',
                    'college_id' => $com->id,
                ],
                [
                    'name' => 'Bachelor of Science in Office Administration (BSOA)',
                    'code' => 'COM-BSOA',
                    'description' => 'Office Administration Program',
                    'college_id' => $com->id,
                ],
                [
                    'name' => 'Bachelor of Public Administration (BPA)',
                    'code' => 'COM-BPA',
                    'description' => 'Public Administration Program',
                    'college_id' => $com->id,
                ],
            ]);
        }

        // Institute of Criminal Justice Education Programs
        if ($icje) {
            $programs = array_merge($programs, [
                [
                    'name' => 'Bachelor of Science in Criminology (BSCrim)',
                    'code' => 'ICJE-BSCrim',
                    'description' => 'Criminology Program',
                    'college_id' => $icje->id,
                ],
            ]);
        }

        // College of Technology Programs
        if ($cot) {
            $programs = array_merge($programs, [
                [
                    'name' => 'Bachelor of Science in Industrial Technology (BSIT)',
                    'code' => 'COT-BSIT',
                    'description' => 'Industrial Technology Program',
                    'college_id' => $cot->id,
                ],
                [
                    'name' => 'Bachelor of Science in Electro-mechanical Technology (BSEMT)',
                    'code' => 'COT-BSEMT',
                    'description' => 'Electro-mechanical Technology Program',
                    'college_id' => $cot->id,
                ],
                [
                    'name' => 'Bachelor of Science in Food Technology (BSFT)',
                    'code' => 'COT-BSFT',
                    'description' => 'Food Technology Program',
                    'college_id' => $cot->id,
                ],
                [
                    'name' => 'Bachelor of Science in Textile and Fashion Technology (BSTFT)',
                    'code' => 'COT-BSTFT',
                    'description' => 'Textile and Fashion Technology Program',
                    'college_id' => $cot->id,
                ],
            ]);
        }

        // College of Education Programs
        if ($ce) {
            $programs = array_merge($programs, [
                [
                    'name' => 'Bachelor of Technical-Vocational Teacher Education (BTVTEd)',
                    'code' => 'CE-BTVTEd',
                    'description' => 'Technical-Vocational Teacher Education Program',
                    'college_id' => $ce->id,
                ],
                [
                    'name' => 'Bachelor of Technology and Livelihood Education (BTLEd)',
                    'code' => 'CE-BTLEd',
                    'description' => 'Technology and Livelihood Education Program',
                    'college_id' => $ce->id,
                ],
                [
                    'name' => 'Bachelor of Secondary Education (BSEd)',
                    'code' => 'CE-BSEd',
                    'description' => 'Secondary Education Program',
                    'college_id' => $ce->id,
                ],
                [
                    'name' => 'Bachelor of Elementary Education (BEEd)',
                    'code' => 'CE-BEEd',
                    'description' => 'Elementary Education Program',
                    'college_id' => $ce->id,
                ],
                [
                    'name' => 'Bachelor of Early Childhood Education (BECEd)',
                    'code' => 'CE-BECEd',
                    'description' => 'Early Childhood Education Program',
                    'college_id' => $ce->id,
                ],
                [
                    'name' => 'Bachelor of Physical Education (BPEd)',
                    'code' => 'CE-BPEd',
                    'description' => 'Physical Education Program',
                    'college_id' => $ce->id,
                ],
            ]);
        }

        foreach ($programs as $program) {
            Program::updateOrCreate(
                ['code' => $program['code']],
                $program
            );
        }
    }
}
