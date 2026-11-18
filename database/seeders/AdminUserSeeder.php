<?php

namespace Database\Seeders;

use App\Enums\RoleName;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Create the mandatory administrator accounts and one account per portal role.
     *
     * Runs outside production only; production accounts are created by a Super Admin.
     */
    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command?->warn('AdminUserSeeder skipped: production environment.');

            return;
        }

        $password = (string) env('SEED_PASSWORD', 'password');

        $accounts = [
            ['name' => 'Super Admin', 'email' => 'superadmin@school.test', 'role' => RoleName::SuperAdmin],
            ['name' => 'Admin Sekolah', 'email' => 'admin@school.test', 'role' => RoleName::SchoolAdmin],
            ['name' => 'Guru Contoh', 'email' => 'guru@school.test', 'role' => RoleName::Teacher],
            ['name' => 'Wali Kelas Contoh', 'email' => 'walikelas@school.test', 'role' => RoleName::HomeroomTeacher],
            ['name' => 'Siswa Contoh', 'email' => 'siswa@school.test', 'role' => RoleName::Student],
        ];

        foreach ($accounts as $account) {
            $user = User::query()->firstOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'password' => $password,
                    'email_verified_at' => now(),
                    'is_active' => true,
                ],
            );

            if (! $user->hasRole($account['role']->value)) {
                $user->assignRole($account['role']->value);
            }
        }
    }
}
