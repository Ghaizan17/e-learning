# 00 — Project Audit Report

Audit date: 2026-11-18 · Audited by: implementation agent
Scope: `/home/ghaizan/Work/e-learning` (working tree, no VCS)

## 1. Verified stack (from composer.lock / package.json / CLI, not assumptions)

| Component | Installed | Evidence |
|---|---|---|
| PHP | 8.4.26 (NTS, Xdebug on) | `php -v` |
| Laravel | 13.42.0 | `php artisan --version` |
| Filament | v5.11.4 | `composer show`, `artisan about` |
| Livewire | v4.4.8 | `artisan about` |
| Pest | 5.3.6 + pest-plugin-laravel 5.0.1 | `composer show --direct` |
| Tailwind CSS | ^4.0 (CSS-first, no `tailwind.config.js`) | `package.json`, `resources/css/app.css` |
| Vite | ^8.0 + laravel-vite-plugin ^3.1 | `package.json` |
| Alpine.js | **not installed** (Blade/Alpine requested) | `package.json` has no alpinejs |
| Charts | **not installed** (no ApexCharts, no chart lib) | `package.json` |
| Authz | **spatie/laravel-permission NOT installed** | `composer show spatie/laravel-permission` fails |
| DB runtime | **sqlite** (`database/database.sqlite`, 236 KB) | `.env` `DB_CONNECTION=sqlite`, `artisan about` |
| DB requested | MySQL / MariaDB | MariaDB 11.8.8 client present; server/credentials unverified |
| Queue | database | `.env` |
| Cache / Session | database | `.env` |
| Broadcasting | log (no Reverb installed) | `.env` |
| Filesystem disk | `public` (repo default overridden) | `.env` |
| PHP extensions | pdo_mysql, pdo_sqlite, mbstring, gd, zip, intl, fileinfo | `php -m` |
| VCS | **none** (`fatal: not a git repository`) | `git status` |

## 2. Existing implementation (complete inventory)

Application code (only 2 domains exist):

- `app/Models/User.php` — minimal, `#[Fillable]`/`#[Hidden]` attributes (Laravel 13 style), hashed password cast.
- `app/Models/Material.php` — implements Filament `HasRichContent`, `SoftDeletes`, casts `content` to array, fillable includes `image-cover`.
- `app/Filament/Resources/Users/*` — Resource + Form/Infolist/Table + 4 pages (Filament v5 split-file structure).
- `app/Filament/Resources/Materials/*` — same structure, soft-delete aware, `withoutGlobalScopes(SoftDeletingScope)` on route binding.
- `app/Filament/Widgets/StatsOverview.php` — 2 stat cards.
- `app/Providers/Filament/AdminPanelProvider.php` — panel `admin`, path `/admin`, login enabled, indigo, 3 nav groups (`Data Master`, `Courses`, `Settings`).
- `routes/web.php` — only `GET /` → `welcome` view.
- `resources/views/welcome.blade.php` — Laravel skeleton page (inline fallback CSS).
- `resources/css/app.css` — Tailwind v4 `@import 'tailwindcss'` + `@theme` font (no Alpine/JS imports).
- `resources/js/app.js` — empty.
- Tests: `ExampleTest` (feature) + `ExampleTest` (unit) only, both pass.

## 3. Database state

Migration status (verified with `migrate:status`):

```
0001_01_01_000000_create_users_table ............... Ran
0001_01_01_000001_create_cache_table ............... Ran
0001_01_01_000002_create_jobs_table ................ Ran
2026_11_16_033303_create_materials_table ........... PENDING  <-- duplicate create
2026_11_16_033709_create_materials_table ........... Ran
2026_11_16_034921_add_deleted_at_to_posts_table .... PENDING  <-- targets non-existent table
```

Tables: `users, cache, cache_locks, failed_jobs, job_batches, jobs, materials, migrations, password_reset_tokens, sessions`.

Data: 1 user, 3 materials.

**Blocker:** `php artisan migrate` on this database currently fails — `2026_11_16_033303_create_materials_table` tries to `CREATE TABLE materials` a second time. A fresh `migrate:fresh` also fails once it reaches the later duplicate. This must be cleaned before any new migration is added.

## 4. Gaps between the requirement and the current code

| Area | State |
|---|---|
| Roles / permissions | none |
| Student & teacher auth | none (no `/login`, no `/dashboard`, no guards/policies) |
| Academic module | none |
| Course / chapter / lesson | only the demo `Material` model |
| Assignment, quiz, grading, attendance, communication | none |
| Filament panel access control | none — any authenticated user can open `/admin` |
| Services / Policies / Form Requests / Enums | directories do not exist |
| Factories / seeders | only `UserFactory`, generic `DatabaseSeeder` |
| Frontend build | `public/build` missing → `@vite` throws until `npm run build` |
| Docs folder | did not exist (created by this audit) |

## 5. Defects found in existing code

1. **Duplicate `materials` migration** (pending) — blocks all migrations.
2. **`posts` migration** with empty body for a table that does not exist — dead file.
3. **Hyphenated DB column** `materials.image-cover` — invalid identifier convention (needs quoting everywhere; breaks joins/GD tools). Should be `cover_image_path`.
4. `Material::$fillable` mixes real columns and a UI-only key; `SoftDeletes` used without an archival policy.
5. `User` fillable/attributes duplicated between `#[Fillable]` attribute and `protected $fillable` — pick one convention.
6. `UsersTable` has no role/status column and `DeleteBulkAction` on the only auth table (no guard against deleting self / last admin).
7. `UserForm` password field `required()` also on edit → forces resets; no confirmation/max-length.
8. `StatsOverview` hardcodes `->chart([7, 2, 10, ...])` — fake data (violates the "no dummy data" rule) and imports an unrelated `Tiptap\Nodes\OrderedList`.
9. No `canAccessPanel()` → authorization hole on the admin panel.
10. No soft-delete restore policy for materials vs `ViewMaterial` route binding already bypassing the scope.
11. Filament panel has no `->brandLogo`, favicon, or `->darkMode()` decision; dark mode currently follows system.
12. No `.gitignore` enforcement of `.env` in practice — repo isn't initialized.

## 6. Risk register

| Risk | Impact | Mitigation |
|---|---|---|
| No version control | Lost work, no review/rollback | `git init` + initial commit before Phase 1 |
| SQLite dev DB vs MySQL requirement | SQL differences (enum, index length, strict mode) | Move to MariaDB before schema work; tests stay on SQLite `:memory:` |
| Adding ~50 tables at once | Long red build, hard review | Phased migrations, `migrate:fresh` gate per phase |
| Filament v5 API drift | Broken resources | Follow existing v5 split-file pattern + `search-docs` before each resource |
| Livewire 4 + Filament 5 + Tailwind 4 | Frontend asset mismatch | Keep `npm run build` in every phase gate |
| Quiz/logic bugs leaking answers | Academic integrity | Server-side validation, policies, feature tests per rule |
| New dependencies | Portfolio risk | Explicit approval list (section 8) |

## 7. Reusable foundations that should be kept

- Filament v5 split-resource layout (`Resources/X/{XResource, Pages/, Schemas/, Tables/}`) — extend this pattern.
- Tailwind v4 CSS-first (`@theme` in `app.css`, no JS config) — keep.
- `#[\Illuminate\Database\Eloquent\Attributes\Fillable]` / `#[Hidden]` — Laravel 13 idiom, standardize on attributes instead of `$fillable` arrays.
- Admin panel nav groups (`Data Master`, `Courses`, `Settings`) — extend to `Academic`, `Learning`, `Assessment`, `Attendance`, `Communication`, `Reports`.
- Pest 5 test harness, `phpunit.xml` already forces `DB_CONNECTION=sqlite`, `:memory:`, array cache/session — good for fast feature tests.

## 8. Decisions requiring your approval (blocking Phase 1)

| # | Decision | Recommendation |
|---|---|---|
| D1 | **Database**: switch dev `.env` to MariaDB 11.8 or stay SQLite until production? | Switch to MariaDB now (matches requirement, avoids migration surprises); keep SQLite in-memory for tests. Needs DB name/user/password from you. |
| D2 | **Install `spatie/laravel-permission`** (v8.x current, v7.2+ added Laravel 13 support) | Approve `composer require spatie/laravel-permission` + publish migration/config. No other package needs installing. |
| D3 | **`git init` + initial commit** | Approve. Non-destructive, enables per-phase review. |
| D4 | **Repair the broken migrations**: delete the two duplicate/dead files (`2026_11_16_033303`, `2026_11_16_034921`), then `migrate:fresh` | Approve. Only demo data (1 user, 3 materials) is lost; production has no data. |
| D5 | **Rename `materials.image-cover` → `cover_image_path`** (migration + model + form + table) | Approve; do it in the same cleanup commit. |
| D6 | **Students/teachers are `users` rows; `student_profiles`/`teacher_profiles` hold attributes only** | Approve. All domain FKs point to `users.id`. |
| D7 | **Course scoped by `teacher_assignment_id`** (teacher + classroom + subject + semester in one FK) instead of duplicating 4 columns on `courses` | Approve. Prevents contradictory data; Filament shows the assignment picker. |
| D8 | **Single login page** at `/login` with role-based redirect; Filament `/admin` restricted via `canAccessPanel()` to Super Admin/Admin Sekolah | Approve. Replaces "any user can enter admin". |
| D9 | **Charts**: use Filament's built-in ChartWidget for admin; for Blade dashboards use CSS/SVG progress bars first. Add ApexCharts only if you want richer student/teacher charts (new npm dependency). | Keep zero new JS deps in Phase 1–2. |
| D10 | **Alpine.js**: required by the brief, not installed. Add `alpinejs` npm dep (plus `@tailwindcss/vite` already present). | Approve one npm install: `alpinejs` only. |
| D11 | **Rich content format**: lessons store sanitized HTML (`content_html`), not Filament JSON, so Blade can render it without Filament JS. | Approve. Sanitized through a small allow-list helper (no new dependency). |
| D12 | **Timezone/locale**: app currently `UTC`/`en`. Set `Asia/Jakarta`, `id` locale, `id_ID` faker for a school product. | Approve; touches `config/app.php`, `.env`. |

Nothing below is implemented yet. Waiting for approval on D1–D12.
