# 03 — Roles, Permissions, Policies

## 1. Roles (Spatie)

`super_admin`, `school_admin`, `teacher`, `homeroom_teacher`, `student`.
A user may hold `teacher` + `homeroom_teacher`; homeroom capability is additive, never a replacement.
`student` is mutually exclusive with staff roles (enforced in `UserService`).

## 2. Permission matrix (portal-level)

Legend: **F** full, **O** own-scope (policy checks ownership), **R** read-only scoped, — none.

| Capability | Super Admin | School Admin | Teacher | Homeroom | Student |
|---|---|---|---|---|---|
| Filament `/admin` access | F | F | — | — | — |
| User / role management | F | R teachers+students | — | — | — |
| Academic years, semesters | F | F | — | — | — |
| Departments, classrooms, subjects | F | F | R | R own class | — |
| Student / teacher CRUD | F | F | — | R own class | — |
| Classroom members, transfers | F | F | — | R own class | — |
| Teacher assignments | F | F | R own | R own class | — |
| Courses (create/edit/publish) | F | F | O | O | — |
| Chapters / lessons | F | F | O | O | R enrolled |
| Lesson progress | R | R | R own course | R own class | O |
| Assignments + attachments | F | F | O | O | R enrolled |
| Submission review + grade | F | R | O | R own class | O own submit |
| Quizzes + questions | F | F | O | O | R enrolled |
| Quiz attempt / submit | — | — | — | — | O |
| Grading scheme (weights) | F | F | O | R | — |
| Grade edit (+history) | F | F | O | — | R own |
| Class sessions / attendance | F | F | O | R own class | R own |
| Announcements | F | F | O course/class | O class | R targeted |
| Discussions / replies | F | F | O course | R | O enrolled |
| Reports / analytics | F all | F school | O own course | O own class | R own |
| App settings | F | — | — | — | — |
| Gamification config | F | F | — | — | R own |

Permission naming: `{resource}.{action}` — e.g. `courses.create`, `grades.update`,
`attendance.record`, `announcements.publish`, `reports.view`, `settings.manage`.
Roles are seeded; permissions are only ever checked in `authorize()` / Policies — never in Blade alone.

## 3. Gate bootstrap

```php
// AppServiceProvider::boot()
Gate::before(fn (User $user) => $user->hasRole('super_admin') ? true : null);
```

Policies registered explicitly (not guessed) for:
`AcademicYear, Semester, Department, Classroom, Subject, TeacherAssignment, Course, Chapter, Lesson,
Enrollment, Assignment, Submission, Quiz, Question, Grade, ClassSession, Attendance,
Announcement, Discussion, DiscussionReply, LessonAttachment, User`.

## 4. Policy contracts (the checks that matter)

| Policy@method | Rule |
|---|---|
| `CoursePolicy@view` | enrolled student **or** assigned teacher **or** homeroom of the course classroom **or** admin |
| `CoursePolicy@update` | assigned teacher (`course.teacherAssignment.teacher_id === user.id`) or admin |
| `CoursePolicy@publish` | update + at least one published lesson + valid grade scheme |
| `LessonPolicy@view` | course view + `lesson.is_published` + owning chapter published (students) |
| `LessonProgressPolicy@update` | `progress.user_id === user.id` **and** course view |
| `AssignmentPolicy@submit` | enrollment active + published + (now ≤ due_at **or** teacher allows late) |
| `SubmissionPolicy@view` | owner, course teacher, homeroom (read-only), admin |
| `SubmissionPolicy@grade` | course teacher; result stored in `submission_grades` + grade sync |
| `QuizPolicy@attempt` | enrollment active + published + inside `starts_at`..`ends_at` + attempts left |
| `QuizAttemptPolicy@update` | attempt owner + `status === in_progress` + server `now < expires_at` |
| `GradePolicy@update` | course teacher + scheme valid; writes `grade_histories` |
| `GradePolicy@view` | own grade (published) / course teacher / homeroom (aggregate only) |
| `AttendancePolicy@record` | course teacher or admin; attendance date belongs to a session of that course |
| `ClassSessionPolicy@manage` | course teacher |
| `AnnouncementPolicy@view` | audience matches viewer role/classroom/enrollment |
| `FilePolicy@download` | mirrors the owning resource policy (lesson → course view; submission → submission view) |
| `ClassroomPolicy@viewStudents` | homeroom teacher of that classroom, or admin |

## 5. Middleware

`EnsureRole` (`role:teacher|homeroom_teacher`) — portal gate only; it never replaces policies.
Registered alias in `bootstrap/app.php`. Filament panel access uses `FilamentUser::canAccessPanel()`.

## 6. Data-scoping rules (no client-supplied ownership)

- Never accept `user_id` / `teacher_id` / `course_id` from a student request body; derive from `auth()->user()`
  and the route-bound model.
- Student queries always join `enrollments` (or `classroom_members` for homeroom) — enforced in a
  `CourseAccessScope` query scope, not in Blade.
- Teacher lists filter by `teacher_assignments.teacher_id` through a global-ish scope applied in services.
- `grade_histories.changed_by` is always `auth()->id()`, never request data.
- File downloads stream from a private disk; no `Storage::url()` for lesson/submission files.

## 7. Seed accounts (dev only)

`Super Admin <superadmin@school.test>`, `Admin Sekolah`, `Guru`, `Wali Kelas`, `Siswa` — password from
`SEED_PASSWORD` env or `password` in local/testing only. Seeder refuses to run when `app()->isProduction()`.
