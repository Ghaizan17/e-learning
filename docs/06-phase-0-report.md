# 06 — Phase 0 Report (Foundation)

Status: **done** · Date: 2026-11-18

## 1. What changed

### Dependencies (approved: D2, D10)
| Package | Version | Why |
|---|---|---|
| `spatie/laravel-permission` | v8.5 | roles + permissions per requirement |
| `alpinejs` | ^3.17.5 | interactions required by the brief |

### Environment (approved: D1, D12)
- DB moved from SQLite to **MariaDB 11.8.8**, database `e_learning` (utf8mb4).
- `config/app.php` timezone → `Asia/Jakarta`; `.env`/`.env.example` → `APP_LOCALE=id`,
  `APP_FAKER_LOCALE=id_ID`, `APP_NAME="E-Learning Sekolah"`.
- `tests` keep SQLite `:memory:` (fast, isolated).
- Removed stale `public/hot` so Blade uses the Vite manifest.

### Database (approved: D4, D5)
- Deleted duplicate `create_materials_table` (pending, would crash) and dead `add_deleted_at_to_posts_table`.
- `materials.image-cover` → `materials.cover_image_path`; model/form/table/infolist updated.
- New `2026_11_18_175234_add_profile_fields_to_users_table`: `phone`, `avatar_path`, `is_active` (+ index).
- Spatie tables published (`create_permission_tables`).

### Application code
New:
- `app/Enums/RoleName.php` — the five roles, labels, portal/admin helpers.
- `app/Http/Middleware/EnsureRole.php` — `role:` alias, blocks inactive accounts.
- `app/Http/Controllers/Auth/LoginController.php`, `DashboardController.php`, `ProfileController.php`,
  `Student/DashboardController.php`, `Teacher/DashboardController.php`.
- `app/Http/Requests/Auth/LoginRequest.php` (per email+IP throttle, 5/min),
  `ProfileUpdateRequest.php`, `PasswordUpdateRequest.php`.
- `app/Providers/AppServiceProvider.php` — `Gate::before` for Super Admin, strict Eloquent in tests.
- Seeders: `RolePermissionSeeder` (40 permissions, idempotent bulk insert), `AdminUserSeeder`
  (5 demo accounts, skipped in production), rewritten `DatabaseSeeder`.
- Factories: role states `superAdmin/schoolAdmin/teacher/homeroomTeacher/student/inactive`.
- Blade: `x-layouts.guest`, `x-layouts.portal`, `x-ui.{card,stat,badge,icon,input,alert,empty-state,progress,page-header}`,
  `auth/login`, `profile/edit`, `portal/student/dashboard`, `portal/teacher/dashboard`.
- Routes: root → login, shared login/logout, `/dashboard` role redirect, profile, `/student/*`, `/teacher/*`.
- Filament: `canAccessPanel()` (admin roles only, active only), `HasAvatar`, real `StatsOverview`
  (fake chart arrays removed), user form/table/infolist with roles + status, new nav groups.

Changed:
- `routes/web.php`, `bootstrap/app.php` (middleware alias + guest/user redirects),
  `app/Models/User.php`, `app/Models/Material.php`, `app/Providers/Filament/AdminPanelProvider.php`,
  `app/Filament/Resources/Users/*`, `app/Filament/Resources/Materials/*`,
  `resources/css/app.css` (Tailwind v4 tokens + `@source` for views), `resources/js/app.js` (Alpine).

### Tests
| File | Covers |
|---|---|
| `tests/Pest.php` | RefreshDatabase + role seeding per feature test |
| `tests/Feature/Auth/LoginTest.php` | login per role, invalid creds, inactive account, throttle, logout |
| `tests/Feature/Authorization/PortalAccessTest.php` | guest redirects, cross-portal 403, panel access matrix, inactive session, role redirect |
| `tests/Feature/Profile/ProfileTest.php` | profile update, email uniqueness, avatar upload, password change rules |
| `tests/Feature/Database/RoleSeederTest.php` | seeder idempotency, role permission sets |
| `tests/Feature/Filament/AdminResourcesTest.php` | admin user/material pages render with relations |

## 2. Verification run

```
php artisan migrate:fresh --seed      OK (6 migrations, 2 seeders)
php artisan test --compact             39 passed (105 assertions)
vendor/bin/pint --format agent         clean
npm run build                          OK (css 65 KB, js 54 KB)
curl /  → 302 /login
curl /login → 200, assets 200
curl /admin/login → 200
```

## 3. Authorization facts now enforced server-side

- Only `super_admin` / `school_admin` reach `/admin`; teacher/student get 403.
- Deactivated accounts cannot log in and cannot use a live session (`EnsureRole` + `canAccessPanel`).
- Login is throttled 5 attempts/minute per email+IP with lockout messaging.
- Portal routes are role-gated; policies per record arrive in Phase 1+.

## 4. Demo accounts (local only, password `SEED_PASSWORD` default `password`)

| Role | Email |
|---|---|
| Super Admin | superadmin@school.test |
| Admin Sekolah | admin@school.test |
| Guru | guru@school.test |
| Wali Kelas | walikelas@school.test |
| Siswa | siswa@school.test |

## 5. Known limitations (by design, next phases)

- Dashboards show zeros/empty states until academic + learning modules exist (no dummy data).
- Profile pages are shared; role-specific profile fields (NISN/NIP) arrive with `student_profiles`
  / `teacher_profiles` in Phase 1.
- No password reset/email verification routes yet (mail is still `log`).
- `public/build` is gitignored; run `npm run build` after pulling.

## 6. Gate result

Phase 0 gate **passed**: fresh MySQL migrate + seed, five roles routed to the correct portal,
non-admins blocked from Filament, tests/pint/build green. Ready for **Phase 1 — Academic Management**.
