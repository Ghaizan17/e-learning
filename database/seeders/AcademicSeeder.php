<?php

namespace Database\Seeders;

use App\Enums\ClassroomMemberStatus;
use App\Enums\Gender;
use App\Enums\GradeLevel;
use App\Enums\RoleName;
use App\Enums\SemesterName;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\ClassroomMember;
use App\Models\Department;
use App\Models\Semester;
use App\Models\StudentProfile;
use App\Models\Subject;
use App\Models\TeacherAssignment;
use App\Models\TeacherProfile;
use App\Models\User;
use App\Services\Academic\ClassroomService;
use App\Services\Academic\TeacherAssignmentService;
use Illuminate\Database\Seeder;

class AcademicSeeder extends Seeder
{
    /**
     * Seed a realistic 2026/2027 school structure for local development.
     */
    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command?->warn('AcademicSeeder skipped: production environment.');

            return;
        }

        $year = AcademicYear::query()->firstOrCreate(
            ['name' => '2026/2027'],
            ['start_date' => '2026-07-01', 'end_date' => '2027-06-30', 'is_active' => false],
        );
        $year->update(['is_active' => true]);

        $odd = Semester::query()->firstOrCreate(
            ['academic_year_id' => $year->getKey(), 'name' => SemesterName::Odd->value],
            [
                'sequence' => SemesterName::Odd->sequence(),
                'start_date' => '2026-07-01',
                'end_date' => '2026-12-31',
                'is_active' => false,
            ],
        );
        $odd->update(['is_active' => true]);

        Semester::query()->firstOrCreate(
            ['academic_year_id' => $year->getKey(), 'name' => SemesterName::Even->value],
            [
                'sequence' => SemesterName::Even->sequence(),
                'start_date' => '2027-01-01',
                'end_date' => '2027-06-30',
                'is_active' => false,
            ],
        );

        $departments = [
            'PPLG' => 'Pengembangan Perangkat Lunak dan Gim',
            'AKL' => 'Akuntansi dan Keuangan Lembaga',
            'DKV' => 'Desain Komunikasi Visual',
        ];

        $departmentModels = [];

        foreach ($departments as $code => $name) {
            $departmentModels[$code] = Department::query()->firstOrCreate(
                ['code' => $code],
                ['name' => $name, 'description' => 'Program keahlian '.$name.'.'],
            );
        }

        $subjects = [
            ['code' => 'MTK', 'name' => 'Matematika', 'department' => null],
            ['code' => 'BIND', 'name' => 'Bahasa Indonesia', 'department' => null],
            ['code' => 'BING', 'name' => 'Bahasa Inggris', 'department' => null],
            ['code' => 'PJOK', 'name' => 'Pendidikan Jasmani, Olahraga, dan Kesehatan', 'department' => null],
            ['code' => 'PWEB', 'name' => 'Pemrograman Web', 'department' => 'PPLG'],
            ['code' => 'BDATA', 'name' => 'Basis Data', 'department' => 'PPLG'],
            ['code' => 'PKK', 'name' => 'Produk Kreatif dan Kewirausahaan', 'department' => 'AKL'],
            ['code' => 'DDG', 'name' => 'Dasar Desain Grafis', 'department' => 'DKV'],
        ];

        $subjectModels = [];

        foreach ($subjects as $subject) {
            $subjectModels[$subject['code']] = Subject::query()->firstOrCreate(
                ['code' => $subject['code']],
                [
                    'name' => $subject['name'],
                    'department_id' => $subject['department'] !== null
                        ? $departmentModels[$subject['department']]->getKey()
                        : null,
                ],
            );
        }

        $teachers = [
            'guru@school.test' => ['name' => 'Guru Contoh', 'gender' => Gender::Male, 'nip' => '198501012010011001'],
            'walikelas@school.test' => ['name' => 'Wali Kelas Contoh', 'gender' => Gender::Female, 'nip' => '198703152011012002'],
            'rudi@school.test' => ['name' => 'Rudi Hartono', 'gender' => Gender::Male, 'nip' => '199002202015011003'],
            'sari@school.test' => ['name' => 'Sari Dewi', 'gender' => Gender::Female, 'nip' => '199204112016012004'],
        ];

        $teacherModels = [];

        foreach ($teachers as $email => $data) {
            $teacher = User::query()->firstOrCreate(
                ['email' => $email],
                [
                    'name' => $data['name'],
                    'password' => (string) env('SEED_PASSWORD', 'password'),
                    'email_verified_at' => now(),
                    'is_active' => true,
                ],
            );

            if (! $teacher->hasAnyRole(RoleName::teacherValues())) {
                $teacher->assignRole(RoleName::Teacher->value);
            }

            TeacherProfile::query()->firstOrCreate(
                ['user_id' => $teacher->getKey()],
                [
                    'nip' => $data['nip'],
                    'gender' => $data['gender'],
                    'employment_status' => 'permanent',
                    'joined_at' => '2020-07-01',
                ],
            );

            $teacherModels[$email] = $teacher;
        }

        $classrooms = [
            ['name' => 'XI PPLG A', 'grade_level' => GradeLevel::Eleven, 'department' => 'PPLG', 'homeroom' => 'walikelas@school.test'],
            ['name' => 'XI PPLG B', 'grade_level' => GradeLevel::Eleven, 'department' => 'PPLG', 'homeroom' => 'sari@school.test'],
            ['name' => 'X PPLG A', 'grade_level' => GradeLevel::Ten, 'department' => 'PPLG', 'homeroom' => 'rudi@school.test'],
            ['name' => 'XII PPLG A', 'grade_level' => GradeLevel::Twelve, 'department' => 'PPLG', 'homeroom' => null],
            ['name' => 'XI AKL A', 'grade_level' => GradeLevel::Eleven, 'department' => 'AKL', 'homeroom' => null],
            ['name' => 'X DKV A', 'grade_level' => GradeLevel::Ten, 'department' => 'DKV', 'homeroom' => null],
        ];

        $classroomModels = [];

        foreach ($classrooms as $classroom) {
            $classroomModels[$classroom['name']] = Classroom::query()->firstOrCreate(
                ['academic_year_id' => $year->getKey(), 'name' => $classroom['name']],
                [
                    'department_id' => $departmentModels[$classroom['department']]->getKey(),
                    'homeroom_teacher_id' => $classroom['homeroom'] !== null
                        ? $teacherModels[$classroom['homeroom']]->getKey()
                        : null,
                    'grade_level' => $classroom['grade_level'],
                    'capacity' => 32,
                ],
            );
        }

        $assignments = [
            ['teacher' => 'guru@school.test', 'classroom' => 'XI PPLG A', 'subject' => 'PWEB'],
            ['teacher' => 'guru@school.test', 'classroom' => 'XI PPLG B', 'subject' => 'PWEB'],
            ['teacher' => 'rudi@school.test', 'classroom' => 'XI PPLG A', 'subject' => 'BDATA'],
            ['teacher' => 'rudi@school.test', 'classroom' => 'XII PPLG A', 'subject' => 'BDATA'],
            ['teacher' => 'sari@school.test', 'classroom' => 'XI PPLG A', 'subject' => 'MTK'],
            ['teacher' => 'sari@school.test', 'classroom' => 'XI PPLG B', 'subject' => 'MTK'],
            ['teacher' => 'sari@school.test', 'classroom' => 'X PPLG A', 'subject' => 'MTK'],
            ['teacher' => 'walikelas@school.test', 'classroom' => 'X PPLG A', 'subject' => 'BIND'],
            ['teacher' => 'walikelas@school.test', 'classroom' => 'XI PPLG B', 'subject' => 'BIND'],
            ['teacher' => 'walikelas@school.test', 'classroom' => 'X DKV A', 'subject' => 'BIND'],
        ];

        $assignmentService = app(TeacherAssignmentService::class);
        $classroomService = app(ClassroomService::class);

        foreach ($assignments as $assignment) {
            $teacher = $teacherModels[$assignment['teacher']];
            $classroom = $classroomModels[$assignment['classroom']];
            $subject = $subjectModels[$assignment['subject']];

            $exists = TeacherAssignment::query()
                ->where('teacher_id', $teacher->getKey())
                ->where('classroom_id', $classroom->getKey())
                ->where('subject_id', $subject->getKey())
                ->where('semester_id', $odd->getKey())
                ->exists();

            if ($exists) {
                continue;
            }

            $assignmentService->assign($teacher, $classroom, $subject, $year, $odd);
        }

        $studentNames = [
            'siswa@school.test' => 'Siswa Contoh',
            'rina@school.test' => 'Rina Anggraini',
            'dimas@school.test' => 'Dimas Prakoso',
            'ayu@school.test' => 'Ayu Lestari',
            'fajar@school.test' => 'Fajar Nugroho',
            'nadia@school.test' => 'Nadia Putri',
            'bima@school.test' => 'Bima Saputra',
            'citra@school.test' => 'Citra Maharani',
            'eko@school.test' => 'Eko Prasetyo',
            'fitri@school.test' => 'Fitri Handayani',
            'galih@school.test' => 'Galih Ramadhan',
            'hana@school.test' => 'Hana Safitri',
            'ivan@school.test' => 'Ivan Kurniawan',
        ];

        $studentModels = [];

        $studentCounter = 0;

        foreach ($studentNames as $email => $name) {
            $studentCounter++;

            $student = User::query()->firstOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => (string) env('SEED_PASSWORD', 'password'),
                    'email_verified_at' => now(),
                    'is_active' => true,
                ],
            );

            if (! $student->hasRole(RoleName::Student->value)) {
                $student->assignRole(RoleName::Student->value);
            }

            StudentProfile::query()->firstOrCreate(
                ['user_id' => $student->getKey()],
                [
                    'nisn' => '007'.str_pad((string) $studentCounter, 7, '0', STR_PAD_LEFT),
                    'nis' => '26'.str_pad((string) $studentCounter, 3, '0', STR_PAD_LEFT),
                    'gender' => $studentCounter % 2 === 0 ? Gender::Female : Gender::Male,
                ],
            );

            $studentModels[$email] = $student;
        }

        $placements = [
            'XI PPLG A' => ['siswa@school.test', 'rina@school.test', 'dimas@school.test', 'ayu@school.test', 'fajar@school.test', 'nadia@school.test'],
            'XI PPLG B' => ['bima@school.test', 'citra@school.test', 'eko@school.test', 'fitri@school.test', 'galih@school.test', 'hana@school.test'],
            'X PPLG A' => ['ivan@school.test'],
        ];

        foreach ($placements as $classroomName => $emails) {
            $classroom = $classroomModels[$classroomName];

            foreach ($emails as $email) {
                $student = $studentModels[$email];

                $alreadyActive = ClassroomMember::query()
                    ->where('classroom_id', $classroom->getKey())
                    ->where('user_id', $student->getKey())
                    ->where('status', ClassroomMemberStatus::Active->value)
                    ->exists();

                if ($alreadyActive) {
                    continue;
                }

                $classroomService->enroll($student, $classroom);
            }
        }

        $this->command?->info('Academic structure seeded for '.$year->name.'.');
    }
}
