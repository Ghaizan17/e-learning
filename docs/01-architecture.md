# 01 — Architecture

## 1. Style

Modular monolith inside Laravel's standard structure. No `app/Modules/*` restructure
(that would be a base-folder change needing approval); domains are separated by
namespace, service classes, form requests, policies and routes.

```
app/
├── Enums/                       CourseStatus, LessonType, LessonProgressStatus,
│                                SubmissionStatus, QuestionType, AttemptStatus,
│                                AttendanceStatus, GradeCategoryType, AudienceType, ...
├── Models/                      Eloquent only (relationships, casts, scopes)
├── Policies/                    one per aggregate root (Course, Assignment, Quiz, ...)
├── Services/<Domain>/           business logic + transactions
│   ├── Academic/                AcademicYearService, ClassroomService, EnrollmentService
│   ├── Learning/                CourseService, LessonProgressService, CourseAccessService
│   ├── Assessment/              AssignmentSubmissionService, QuizAttemptService, QuizGradingService
│   ├── Grading/                 GradeService, GradeCalculationService, GradeAuditService
│   ├── Attendance/              ClassSessionService, AttendanceService
│   └── Communication/           AnnouncementService, NotificationService
├── Actions/                     single-purpose invokables used by services/jobs
├── Http/
│   ├── Controllers/Admin|Teacher|Student|Auth|Files
│   ├── Requests/<Domain>/       Form Requests (authorize() + rules())
│   ├── Middleware/EnsureRole.php
│   └── Resources/               JSON resources for fetch endpoints (quiz autosave)
├── Notifications/               AssignmentPublished, DeadlineReminder, GradePublished
├── Jobs/                        deadline + reminder jobs (queue: database)
├── Filament/                    Resources/Pages/Widgets (v5 split-file layout)
├── Support/                     HtmlSanitizer, Duration, QueryFilters
└── Providers/                   AppServiceProvider (Gate::before, policies, morph map)
```

Rules:
- Controllers: authorize → validate (FormRequest) → call service → redirect/render. No queries beyond eager loading.
- Blade: read-only, no business logic. Reusable components in `resources/views/components`.
- Services accept models/DTOs, return models/values, own `DB::transaction`.
- Policies are the single source of authorization; middleware only gates the portal.

## 2. Portals & guards

| Portal | Prefix | Middleware | UI |
|---|---|---|---|
| Auth | `/login`, `/logout`, `/password/*` | guest / web | Blade |
| Filament | `/admin` | `auth`, `FilamentUser::canAccessPanel` | Filament v5 |
| Teacher | `/teacher` | `auth`, `role:teacher\|homeroom_teacher` | Blade + Alpine |
| Student | `/student` | `auth`, `role:student` | Blade + Alpine |
| Shared files | `/files/{type}/{id}` | `auth`, policy | Controller stream from private disk |

- One auth guard (`web`), one `users` table, roles via Spatie.
- Super Admin / Admin Sekolah do not use the teacher/student portals; they are redirected to `/admin`.
- Homeroom teachers ("Wali Kelas") use the teacher portal, with a "Kelas Binaan" area gated by `HomeroomPolicy`.
- Filament panel: `canAccessPanel()` returns true only for `super_admin` and `school_admin`.

## 3. Authorization model

Two layers, both server-side:

1. **Role gate** — `role:` middleware + Spatie permissions for coarse actions
   (`courses.create`, `grades.update`, `attendance.record`, ...).
2. **Ownership policy** — per-record checks:
   - Teacher owns a course through `teacher_assignment.teacher_id`.
   - Teacher can only touch chapters/lessons/assignments/quizzes of owned courses.
   - Student can only read courses where an active `enrollment` exists.
   - Student can only write `lesson_progress`, `submissions`, `quiz_attempts` rows whose `user_id` is theirs.
   - Homeroom can read (never write) data of students in their `classrooms.homeroom_teacher_id`.
   - Grade edits require `grades.update` **and** course ownership; every change writes `grade_histories`.

`AppServiceProvider::boot()` registers `Gate::before(fn ($user) => $user->hasRole('super_admin') ? true : null)`
and enables `Model::preventLazyLoading()` outside production.

## 4. Key modeling decisions

- **People FKs point to `users.id`.** `student_profiles` and `teacher_profiles` are 1:1 attribute
  extensions (NISN, NIP, address). Avoids dual FK joins and keeps policies/notifications simple.
- **`courses.teacher_assignment_id` is the scope.** Subject, classroom, semester, academic year,
  and teacher come from the assignment; no duplicated columns on `courses`.
- **History is append-only** for academic placement: `classroom_members` keeps `joined_at`/`left_at`/`status`;
  promotion or transfer closes the old row and inserts a new one. Nothing is deleted on rollover.
- **Grading is traceable**: `grades.grade_component_id` → `grade_components.gradeable` (morph to
  `Assignment`/`Quiz`) → source score. `grade_histories` records every manual change.
- **Quiz integrity**: correct options never serialized to the student payload; `is_correct` is only
  read inside `QuizGradingService`. Attempt rows are created server-side with `expires_at`;
  submission compares `now()` to `expires_at`/`ends_at` in the service, inside a transaction with
  `lockForUpdate()`.
- **Private files**: `storage/app/private/` disk for lesson attachments and submissions.
  Downloads only through `FileDownloadController` after a policy check; never a public URL.

## 5. Notifications & queues

- Laravel database notifications (`notifications` table), dispatched by services.
- Queue connection `database`; `ShouldQueue` notifications/jobs for deadline reminders.
- Optional Reverb is **not** installed — real-time is deferred (D9/D10 gate). Polling/refresh is enough
  for Phase 1–7.

## 6. Frontend conventions

- Tailwind v4 CSS-first: tokens in `resources/css/app.css` `@theme` block (brand palette,
  semantic `--color-surface`, `--color-ink`, spacing scale). No `tailwind.config.js`.
- Alpine.js registered in `resources/js/app.js` (single `Alpine.start()`; plugin-free).
- Blade components (planned): `x-layouts.app`, `x-layouts.portal`, `x-ui.card`, `x-ui.stat`,
  `x-ui.badge`, `x-ui.button`, `x-ui.empty-state`, `x-ui.alert`, `x-ui.progress-bar`,
  `x-ui.modal` (Alpine), `x-ui.tabs` (Alpine), `x-ui.toast` (session flash),
  `x-portal.sidebar`, `x-portal.topbar`, `x-course.card`, `x-lesson.nav`.
- One decorative color (indigo primary) + semantic success/warning/danger; no gradients on data surfaces.
- Every list view has an empty state and a pagination state; every form has inline error display.

## 7. Analytics

- Filament widgets query aggregates (counts, averages, per-month series) directly — no cached mocks.
- Student/teacher dashboard stats come from services (`CourseProgressService`, `GradeSummaryService`)
  so the same numbers drive Filament and Blade.
