<?php

namespace App\Services\Academic;

use App\Enums\ClassroomMemberStatus;
use App\Enums\RoleName;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\ClassroomMember;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ClassroomService
{
    /**
     * Place a student in a classroom, keeping previous placements untouched.
     */
    public function enroll(User $student, Classroom $classroom, ?CarbonInterface $joinedAt = null): ClassroomMember
    {
        $this->assertIsStudent($student);

        return DB::transaction(function () use ($student, $classroom, $joinedAt): ClassroomMember {
            $existing = $this->activeMembershipInClassroom($student, $classroom);

            if ($existing !== null) {
                return $existing;
            }

            $current = $this->activeMembership($student, $classroom->academicYear);

            if ($current !== null) {
                throw ValidationException::withMessages([
                    'user_id' => 'Siswa sudah terdaftar aktif di '.$current->classroom->name
                        .' pada tahun ajaran ini. Gunakan pindah kelas.',
                ]);
            }

            $this->assertCapacityAvailable($classroom);

            return $classroom->members()->create([
                'user_id' => $student->getKey(),
                'joined_at' => $joinedAt ?? now(),
                'status' => ClassroomMemberStatus::Active,
            ]);
        });
    }

    /**
     * Move a student from one classroom to another, preserving both records.
     */
    public function transfer(
        User $student,
        Classroom $from,
        Classroom $to,
        ?CarbonInterface $date = null,
    ): ClassroomMember {
        $this->assertIsStudent($student);

        return DB::transaction(function () use ($student, $from, $to, $date): ClassroomMember {
            $membership = $this->activeMembershipInClassroom($student, $from);

            if ($membership === null) {
                throw ValidationException::withMessages([
                    'user_id' => 'Siswa tidak memiliki keanggotaan aktif di kelas asal.',
                ]);
            }

            $this->closeMembership($membership, ClassroomMemberStatus::Transferred, $date);

            $target = $this->activeMembershipInClassroom($student, $to);

            if ($target !== null) {
                return $target;
            }

            $this->assertCapacityAvailable($to);

            return $to->members()->create([
                'user_id' => $student->getKey(),
                'joined_at' => $date ?? now(),
                'status' => ClassroomMemberStatus::Active,
            ]);
        });
    }

    /**
     * Close a membership without deleting the academic history.
     */
    public function remove(
        ClassroomMember $member,
        ClassroomMemberStatus $status = ClassroomMemberStatus::Dropped,
        ?CarbonInterface $date = null,
    ): ClassroomMember {
        if (in_array($status, [ClassroomMemberStatus::Active], true)) {
            throw ValidationException::withMessages([
                'status' => 'Status akhir keanggotaan tidak boleh aktif.',
            ]);
        }

        return DB::transaction(function () use ($member, $status, $date): ClassroomMember {
            $this->closeMembership($member, $status, $date);

            return $member->refresh();
        });
    }

    /**
     * Active membership of a student inside one classroom.
     */
    public function activeMembershipInClassroom(User $student, Classroom $classroom): ?ClassroomMember
    {
        return $classroom->members()
            ->where('user_id', $student->getKey())
            ->where('status', ClassroomMemberStatus::Active->value)
            ->first();
    }

    /**
     * Active membership of a student inside an academic year.
     */
    public function activeMembership(User $student, AcademicYear $academicYear): ?ClassroomMember
    {
        return ClassroomMember::query()
            ->where('user_id', $student->getKey())
            ->where('status', ClassroomMemberStatus::Active->value)
            ->whereHas('classroom', fn ($query) => $query->where('academic_year_id', $academicYear->getKey()))
            ->with('classroom')
            ->first();
    }

    /**
     * Prevent a closed membership from being closed twice.
     */
    private function closeMembership(
        ClassroomMember $member,
        ClassroomMemberStatus $status,
        ?CarbonInterface $date,
    ): void {
        if (! $member->isActive()) {
            throw ValidationException::withMessages([
                'user_id' => 'Keanggotaan siswa ini sudah tidak aktif.',
            ]);
        }

        $member->update([
            'status' => $status,
            'left_at' => $date ?? now(),
        ]);
    }

    /**
     * Enforce the classroom capacity when it is configured.
     */
    private function assertCapacityAvailable(Classroom $classroom): void
    {
        if ($classroom->capacity === null) {
            return;
        }

        $active = $classroom->activeMembers()->count();

        if ($active >= $classroom->capacity) {
            throw ValidationException::withMessages([
                'user_id' => 'Kapasitas kelas '.$classroom->name.' sudah penuh ('.$classroom->capacity.' siswa).',
            ]);
        }
    }

    /**
     * Only accounts with the student role may hold a classroom membership.
     */
    private function assertIsStudent(User $student): void
    {
        if (! $student->hasRole(RoleName::Student->value)) {
            throw ValidationException::withMessages([
                'user_id' => 'Pengguna yang dipilih bukan siswa.',
            ]);
        }
    }
}
