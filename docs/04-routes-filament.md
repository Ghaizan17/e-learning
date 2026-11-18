# 04 — Route & Filament Structure

## 1. Route structure (`routes/web.php`, split by portal)

```php
// auth
GET  /login                       Auth\LoginController@create
POST /login                       Auth\LoginController@store        throttle:5,1
POST /logout                      Auth\LoginController@destroy
GET  /forgot-password             Auth\PasswordResetLinkController@create
POST /forgot-password             Auth\PasswordResetLinkController@store   throttle:6,1
GET  /reset-password/{token}      Auth\NewPasswordController@create
POST /reset-password              Auth\NewPasswordController@store

// post-login router
GET  /dashboard  ->  redirect by primary role (admin → /admin, teacher → /guru, homeroom → /walikelas, student → /student)
GET  /profile    ->  shared profile edit (name, phone, avatar, password)

// student portal  (auth, verified, role:student, throttle:60,1)
/student/dashboard
/student/courses                        /student/courses/{course}
/student/courses/{course}/lessons/{lesson}
/student/courses/{course}/lessons/{lesson}/complete        POST
/student/assignments                    /student/assignments/{assignment}
/student/assignments/{assignment}/submission               GET|POST
/student/quizzes                        /student/quizzes/{quiz}
/student/quizzes/{quiz}/start                              POST
/student/attempts/{attempt}                                GET   (exam page)
/student/attempts/{attempt}/answer                         POST  (JSON, autosave)
/student/attempts/{attempt}/submit                         POST
/student/results/{attempt}
/student/grades                         /student/grades/{course}
/student/attendance
/student/schedule
/student/announcements                  /student/announcements/{announcement}
/student/courses/{course}/discussions   .../{discussion}
/student/notifications                                         (index + mark read)

// teacher portal  (auth, role:teacher => /guru, role:homeroom_teacher => /walikelas)
/{guru|walikelas}/dashboard
/guru/classes                        /guru/classes/{classroom}        (read-only roster)
/guru/courses                        /guru/courses/create
/guru/courses/{course}               /guru/courses/{course}/edit
/guru/courses/{course}/chapters      + create/edit/reorder/publish
/guru/courses/{course}/lessons       + create/edit/reorder/publish
/guru/courses/{course}/assignments   + submissions list
/guru/submissions/{submission}       + grade POST
/guru/courses/{course}/quizzes       + builder
/guru/quizzes/{quiz}/questions       + options
/guru/courses/{course}/grades        + update POST
/guru/courses/{course}/attendance    + sessions CRUD
/guru/courses/{course}/announcements
/guru/students/{student}             (only own courses / own homeroom)
/guru/homeroom                       (wali kelas: monitor, grades, attendance recap)

// shared authenticated file streaming (policy enforced)
GET /files/lesson/{lessonAttachment}      File\LessonAttachmentController@show
GET /files/assignment/{assignmentAttachment}
GET /files/submission/{submissionFile}
GET /files/avatar/{user}

// Filament
/admin/...  (auth + canAccessPanel)
```

Rules: every mutating route is POST/PATCH/DELETE with CSRF, portal groups are `throttle:60,1`,
auth routes `throttle:5,1`, quiz answer autosave `throttle:120,1` (a single attempt can autosave often).

## 2. JSON endpoints (Blade + Alpine only, no SPA)

| Method | Path | Purpose |
|---|---|---|
| POST | `/student/attempts/{attempt}/answer` | autosave one answer `{question_id, option_ids[], answer_text}` |
| POST | `/student/attempts/{attempt}/submit` | final submit (idempotent, transactional) |
| POST | `/student/lessons/{lesson}/progress` | mark in-progress/completed |
| POST | `/guru/submissions/{submission}/grade` | score + feedback |
| GET | `/student/notifications/unread-count` | topbar badge polling (30 s) |

All JSON responses use API Resources, never raw models (prevents `is_correct` leakage).

## 3. Filament v5 structure (split-file layout already in the repo)

```
app/Filament/
├── Resources/
│   ├── Users/{UserResource,Pages,Schemas,Tables}                     (extend existing)
│   ├── AcademicYears/{...}           Data Master
│   ├── Semesters/{...}               Data Master
│   ├── Departments/{...}             Data Master
│   ├── Classrooms/{...}              Data Master  (+ RelationManager Members, TeacherAssignments)
│   ├── Subjects/{...}                Data Master
│   ├── TeacherAssignments/{...}      Data Master
│   ├── Courses/{...}                 Learning
│   │    └── RelationManagers/ ChaptersRelationManager, EnrollmentsRelationManager, AssignmentsRelationManager,
│   │                           QuizzesRelationManager, GradeComponentsRelationManager
│   ├── Chapters/{...}                Learning  (nested under Course via RelationManager only if simpler)
│   ├── Lessons/{...}                 Learning  (+ AttachmentsRelationManager)
│   ├── Enrollments/{...}             Learning
│   ├── Assignments/{...}             Assessment (+ AttachmentsRelationManager, SubmissionsRelationManager)
│   ├── Submissions/{...}             Assessment (view + grade action)
│   ├── Quizzes/{...}                 Assessment (+ QuestionsRelationManager)
│   ├── Questions/{...}               Assessment (options via RelationManager/Repeater)
│   ├── GradingCategories/{...}       Assessment
│   ├── Grades/{...}                  Assessment (audit history relation)
│   ├── ClassSessions/{...}           Attendance (+ AttendancesRelationManager)
│   ├── Announcements/{...}           Communication
│   └── Discussions/{...}             Communication
├── Pages/
│   ├── Auth/Login.php               (keep Filament login)
│   ├── Reports/LearningAnalytics.php
│   └── Reports/StudentProgress.php
└── Widgets/
    ├── StatsOverview.php             (rewrite: real counts, no fake charts)
    ├── LearningActivityChart.php
    ├── GradeDistributionChart.php
    ├── AssignmentStatsWidget.php
    ├── AttendanceTodayWidget.php
    └── RecentActivitiesWidget.php
```

Resource rules:
- Nested collection editing (chapters, questions, attachments) uses **Relation Managers**, not separate
  top-level resources, whenever the child has no independent lifecycle.
- Resources carry `getEloquentQuery()` scoping so a teacher never sees another teacher's course in admin.
- `recordTitleAttribute`, navigation groups, and icons follow the existing pattern
  (`Heroicon::Outlined*` + `activeNavigationIcon`).
- Every destructive action requires confirmation; bulk deletes exclude users with `super_admin` role.
- Access: `canViewAny()` runs `auth()->user()->can('...')`; Super Admin bypass is handled by `Gate::before`.

## 4. Reports / analytics surfaces

| Surface | Source |
|---|---|
| Admin dashboard | counts + monthly series over `enrollments`, `lesson_progress`, `submissions`, `quiz_attempts`, `attendances` |
| Learning analytics page | per-classroom progress %, assignment submission rate, average quiz score, attendance rate |
| Student progress report | course progress, grade breakdown by category, attendance recap |
| Grade export | CSV/Excel via a queued job (`GradesExportJob`) writing to `storage/app/exports`, download via signed route |

## 5. Navigation groups (extend the panel provider)

`Data Master` (users, years, semesters, departments, classrooms, subjects, teacher assignments)
→ `Learning` (courses, chapters, lessons, enrollments)
→ `Assessment` (assignments, submissions, quizzes, questions, grading scheme, grades)
→ `Attendance` (sessions, attendance)
→ `Communication` (announcements, discussions)
→ `Reports` (analytics, progress, exports)
→ `Settings` (roles, app config).
