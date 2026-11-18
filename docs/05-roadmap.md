# 05 — Implementation Roadmap

Each phase ends with: migrations run clean, `vendor/bin/pint --dirty --format agent`, targeted Pest tests,
`npm run build`, and a short written handoff (files added/changed). No phase starts before the previous gate passes.

## Phase 0 — Foundation & repair (no product feature)

Depends on: approval of D1–D12.
- `git init` + `.gitignore` check + initial commit.
- Fix migrations: delete duplicate `create_materials` and dead `add_deleted_at_to_posts`; rename
  `materials.image-cover` → `cover_image_path`; update `Material` model/form/table.
- Install + configure `spatie/laravel-permission`; seed 5 roles + permission list.
- Add `Enums` for roles/statuses; `User` gains `HasRoles`, `is_active`, avatar, phone.
- Auth: login/logout/password reset pages, `/dashboard` role redirect, `EnsureRole` middleware.
- Filament: `canAccessPanel()` restricted to `super_admin` / `school_admin`; admin seeder; remove fake widget charts.
- Blade foundation: `x-layouts.portal`, sidebar/topbar, `x-ui.*` components, Alpine registration, Tailwind tokens.
- App config: `Asia/Jakarta`, `id` locale, `id_ID` faker, private disk `local` for materials.
- Tests: login, role redirect, panel access denied for teacher/student, migration smoke.

**Gate:** fresh DB migrates, all 5 roles log in to the correct place, `php artisan test` green.

## Phase 1 — Academic management

Depends on: Phase 0.
- Migrations: academic_years, semesters, departments, classrooms, classroom_members, subjects, teacher_assignments.
- Models + relations + enums + factories + seeders (1 year, 2 semesters, 3 departments, 6 classrooms, 10 subjects).
- Services: `AcademicYearService` (single active), `ClassroomService` (membership + transfer),
  `TeacherAssignmentService` (no double booking of a teacher in one slot).
- Policies + Form Requests; Filament resources (years, semesters, departments, classrooms+members RM,
  subjects, teacher assignments) and a `Homeroom` assignment action.
- Features: promote/transfer student preserving history; rollover to a new academic year (copy classrooms,
  not members).
- Tests: single active year, transfer keeps history, teacher assignment uniqueness, authorization.

**Gate:** admin can build the full 2026/2027 structure from the UI; history survives a transfer.

## Phase 2 — Learning (courses, chapters, lessons, progress)

Depends on: Phase 1.
- Migrations: courses, chapters, lessons, lesson_attachments, enrollments, lesson_progress.
- Course lifecycle: draft → published → archived; publish requires ≥1 published lesson.
- `EnrollmentService`: sync from classroom members per teacher assignment; idempotent.
- `CourseAccessService` + query scopes; `LessonProgressService` with `completed_at` and course progress %.
- Private file storage + `LessonAttachmentController` with policy.
- Filament: CourseResource + Chapter/Lesson relation managers, LessonResource, EnrollmentResource.
- Teacher portal: My Classes, My Courses, course/chapter/lesson CRUD, publish toggles.
- Student portal: My Courses, course detail, learning page (sidebar chapters, prev/next, mark complete, downloads).
- Tests: enrollment sync, progress ownership (student cannot touch another's), unpublished lesson hidden,
  file download denied without enrollment, progress percentage math.

**Gate:** teacher publishes a course, student sees it, completes lessons, progress matches DB.

## Phase 3 — Assignments

Depends on: Phase 2.
- Migrations: assignments, assignment_attachments, submissions, submission_files, submission_grades.
- `AssignmentSubmissionService`: server deadline validation, late flag, revisions when allowed,
  single latest attempt, transaction on submit.
- Teacher: assignment CRUD, submission list, grading + feedback.
- Student: list/detail, text + file submission, status, score, feedback, revision UI when enabled.
- Notifications: assignment published, deadline reminder job (queued), graded.
- Filament: AssignmentResource (+ submissions RM), SubmissionResource with grade action.
- Tests: submit before/after deadline, late status, revision off/on, cross-student denial, upload validation.

**Gate:** full assignment cycle works end-to-end; deadline enforced server-side.

## Phase 4 — Quiz & examination

Depends on: Phase 2 (independent from Phase 3 in schema).
- Migrations: quizzes, questions, question_options, quiz_attempts, quiz_answers, quiz_answer_options.
- `QuizAttemptService`: start attempt (window + attempts left), `expires_at`, autosave answer,
  final submit inside transaction with `lockForUpdate`, duplicate-submit protection, auto-expire job.
- `QuizAutoGradingService`: MC/true-false/multi-select/short-answer scoring; essay pending manual grade.
- `QuizGradingService`: manual essay grading recomputes attempt score.
- Student UI: timer driven by `expires_at` (server truth), autosave every 20 s + on blur, review page
  gated by `show_result_immediately` and `ends_at`.
- Teacher UI: quiz builder (questions/options ordering, randomization flags), attempt monitoring, manual grading.
- Tests: before start / after end / expired timer / attempts limit / duplicate submit / answer leakage /
  shuffle integrity / essay manual grading / attempt history.

**Gate:** a 60-minute exam cannot be submitted after expiry even if the client clock is manipulated.

## Phase 5 — Grading

Depends on: Phases 3 + 4 (sources exist).
- Migrations: grading_categories, grade_components, grades, grade_histories.
- `GradeCalculationService`: weights total 100, per-category average, weighted final score, letter grade;
  writes `enrollments.final_score`/`letter_grade`.
- `GradeAuditService`: every manual change writes old/new/changed_by/reason in one transaction.
- Sync from assignment grading + quiz results into `grades`.
- Teacher: scheme editor, gradebook matrix (students × components), inline edit with reason, export.
- Student: grades page, per-course breakdown, published-only visibility.
- Filament: GradingCategoryResource, GradeResource (+ history infolist), read-only teacher scoping.
- Tests: weight validation, weighted math, audit trail, unpublished grade hidden, export authorization.

**Gate:** reported final grade reproduces from source scores in a test assertion.

## Phase 6 — Attendance

Depends on: Phase 2.
- Migrations: class_sessions, attendances.
- `ClassSessionService` (meeting numbering, unique per course), `AttendanceService` (bulk upsert, authorization).
- Teacher: session list, take attendance (bulk form), edit window, recap.
- Homeroom: class attendance recap (read-only).
- Student: own attendance history + percentage.
- Filament: ClassSessionResource (+ attendances RM).
- Tests: duplicate session blocked, bulk save idempotent, student cannot see others' attendance.
- Deferred hook: `qr_token`/`qr_expires_at` ready for a QR check-in action (Phase 9).

## Phase 7 — Communication & notifications

Depends on: Phases 1–2.
- Migrations: announcements, discussions, discussion_replies, notifications.
- Audience targeting (all/teachers/students/classroom/course) enforced in policy + query scope.
- Discussion threads per course/lesson with replies and lock/pin moderation.
- Notification classes + queued deadline/grade/announcement notifications, in-app bell with unread count.
- Tests: audience isolation (class A cannot see class B announcement), reply authorization, notification dispatch.

## Phase 8 — Dashboards, analytics, reports

Depends on: Phases 0–7.
- Student dashboard: greeting, enrolled count, real progress, upcoming assignments/exams, recent activity,
  announcements, learning stats.
- Teacher dashboard: classes, pending submissions, upcoming sessions, class progress, grade distribution.
- Filament widgets rebuilt with real aggregates; analytics + student progress pages; CSV exports via queue.
- Tests: each widget query returns expected numbers for a fixture dataset; empty states render.

## Phase 9 — Hardening, gamification, optional real-time

- Badges/points with award jobs on completion events (if approved).
- QR attendance check-in.
- Security pass: rate limits, file MIME/size validation, mass-assignment audit, N+1 check
  (`Model::preventLazyLoading()` in tests), policy coverage report.
- QA pass: `migrate:fresh --seed`, route list review, Pint, full suite, `npm run build`,
  responsive checks at 375/768/1280 px, `look_at_image` review of key screens.
- Optional: Reverb broadcasting, ApexCharts upgrade.

## Dependency graph

```
Phase 0 ──▶ Phase 1 ──▶ Phase 2 ──┬─▶ Phase 3 ──┐
                                  ├─▶ Phase 4 ──┼─▶ Phase 5 ──▶ Phase 8 ──▶ Phase 9
                                  └─▶ Phase 6 ──┘
Phase 2 ──▶ Phase 7 ──▶ Phase 8
```

## Definition of done (per feature)

1. Migration + model + factory + seeder sample data (non-dummy, realistic).
2. Service owns the business rule; controller is thin; request validated; policy enforced.
3. Filament/Blade both use the same service — no duplicated calculation.
4. Feature test proves the rule and the negative case (403/422).
5. `php artisan test`, `pint`, `npm run build` pass; docs updated with files touched.

## Estimated effort

| Phase | Scope | Est. sessions |
|---|---|---|
| 0 | repair + auth + roles + layout | 1–2 |
| 1 | academic | 2 |
| 2 | learning | 2–3 |
| 3 | assignments | 2 |
| 4 | quiz/exam | 3 |
| 5 | grading | 2 |
| 6 | attendance | 1–2 |
| 7 | communication | 2 |
| 8 | dashboards/reports | 2 |
| 9 | hardening | 2 |
