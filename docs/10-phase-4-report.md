# Phase 4 — Kuis & Ujian (Report)

Status: **selesai** · Test suite: **180 passed / 497 assertions** · Pint & `npm run build` bersih.

## 1. Cakupan

| Area | Hasil |
| --- | --- |
| Bank soal | Tipe `multiple_choice`, `multiple_select`, `true_false`, `short_answer`, `essay` |
| Pengerjaan | Attempt server-side dengan `expires_at`, resume attempt aktif, batas percobaan |
| Penilaian | Auto-scoring objektif, esai dinilai manual guru, skor attempt dihitung ulang |
| Integritas | `is_correct` tidak pernah dikirim ke siswa; deadline & limit dicek di service |
| Publikasi | Kuis wajib punya soal dan kunci jawaban valid sebelum bisa diterbitkan |
| Notifikasi | `quiz_graded` saat seluruh jawaban esai selesai dinilai |
| Scheduler | `ExpireQuizAttempts` setiap 5 menit menutup attempt kedaluwarsa |
| Admin | Filament `QuizResource` + relation manager soal |
| Portal guru | CRUD kuis, builder soal, publikasi, review attempt, penilaian esai |
| Portal siswa | Daftar kuis, instruksi, halaman ujian + timer + autosave, hasil |

## 2. Skema Database

6 migration baru:

- `quizzes` — `course_id`, judul, jenis (`quiz`/`exam`), durasi, window `starts_at`/`ends_at`, `max_attempts`, `passing_score`, `shuffle_questions`, `shuffle_options`, `show_result_immediately`, `is_published`, soft deletes.
- `questions` — `quiz_id`, tipe, teks, poin, penjelasan, `position` unik per kuis.
- `question_options` — `question_id`, teks, `is_correct`, `position` unik per soal.
- `quiz_attempts` — `quiz_id`, `user_id`, `attempt_no`, `started_at`, `expires_at`, `submitted_at`, status, skor; unik (`quiz_id`,`user_id`,`attempt_no`).
- `quiz_answers` — per attempt+soal, opsi tunggal (`question_option_id`), teks, `is_correct`, `score_awarded`, penilai, waktu nilai.
- `quiz_answer_options` — jawaban multi-select.

## 3. Enum & Model

- Enum: `QuizType`, `QuestionType` (`isAutoGraded()`, `requiresOptions()`, `allowsMultipleSelection()`), `QuizAttemptStatus`.
- Model: `Quiz`, `Question`, `QuestionOption`, `QuizAttempt`, `QuizAnswer`, `QuizAnswerOption`; relasi `Course::quizzes()`.
- Helper penting: `Quiz::totalPoints()`, `Quiz::activeAttemptFor()`, `QuizAttempt::remainingSeconds()`, `QuizAttempt::canRevealResult()`.

## 4. Service (business logic)

| Service | Tanggung jawab |
| --- | --- |
| `QuizService` | create/update/publish/unpublish/delete + `assertPublishable()` |
| `QuestionService` | sinkronisasi opsi per id, posisi otomatis, validasi jumlah & kunci jawaban |
| `QuizAttemptService` | mulai attempt (lock transaksi), simpan jawaban, submit, expire, urutan acak deterministik |
| `QuizAutoGradingService` | skor objektif; multi-select set-equality; short answer case-insensitive |
| `QuizGradingService` | nilai esai dengan batas poin, hitung ulang total, set `graded`, kirim notifikasi |

Semua aturan waktu dan kepemilikan diperiksa di service, bukan hanya di controller.

## 5. Otorisasi

- `QuizPolicy` — view/manage/publish via `CourseAccessService`, `take` untuk siswa terdaftar.
- `QuestionPolicy` — delegasi ke kuis.
- `QuizAttemptPolicy` — siswa hanya attempt sendiri; guru hanya kelas yang diajar.
- `QuizAnswerPolicy` — penilaian esai oleh guru pengampu.

## 6. Endpoint Portal

Siswa:
`GET /student/quizzes`, `GET /student/quizzes/{quiz}`, `POST /student/quizzes/{quiz}/start`,
`GET /student/quiz-attempts/{attempt}`, `POST /student/quiz-attempts/{attempt}/answers` (JSON autosave),
`POST /student/quiz-attempts/{attempt}/submit`, `GET /student/quiz-attempts/{attempt}/result`.

Guru:
CRUD `/teacher/quizzes`, `POST /teacher/quizzes/{quiz}/publish|unpublish`,
CRUD `/teacher/quizzes/{quiz}/questions`, `GET /teacher/quiz-attempts/{attempt}`,
`POST /teacher/quiz-answers/{answer}/grade`.

## 7. UI

- `portal/teacher/quizzes/*` — form kuis, builder soal Alpine (tipe soal dinamis), daftar soal, review attempt.
- `portal/student/quizzes/*` — daftar, instruksi, halaman ujian dengan timer + autosave, halaman hasil.
- `resources/js/app.js` — komponen Alpine `quizAttempt`: countdown, auto-submit saat waktu habis, autosave `fetch` dengan CSRF, indikator tersimpan.
- Filament `QuizResource` + `QuestionsRelationManager` (create via service, publish action dengan pesan validasi).

## 8. Seeder

`QuizSeeder` (non-produksi, idempotent) membuat **Kuis HTML & CSS Dasar** pada kursus Pemrograman Web Dasar:
4 soal (MC, benar/salah, multi-select, esai), total 100 poin, 15 menit, 2 percobaan, langsung terbit.

## 9. Pengujian

- `tests/Feature/Learning/QuizAttemptTest.php` — 16 tes: window, resume, limit, expiry, duplikasi submit, auto-scoring, multi-select, short answer, esai manual, batas skor, job expiry, kebocoran `is_correct`, opsi lintas soal.
- `tests/Feature/Learning/PortalQuizTest.php` — 13 tes: daftar, mulai, render tanpa metadata kunci, autosave, otorisasi lintas siswa, submit + hasil, sembunyikan hasil, deadline, review guru, builder guru, penilaian esai, urutan acak stabil, siswa luar kursus.
- `tests/Feature/Filament/QuizResourcesTest.php` — 6 tes: render halaman, create kuis, tambah soal via relation manager, publish action, halaman view.
- `tests/Support/QuizFixture.php` — fixture kuis + soal objektif & esai.

## 10. Catatan Operasional

- Job `ExpireQuizAttempts` memerlukan `php artisan queue:work` dan scheduler aktif.
- Mengubah tipe soal setelah dibuat tidak menghapus opsi lama; gunakan hapus soal lalu buat ulang bila strukturnya berubah.
- Hasil tidak ditampilkan sebelum `ends_at` jika `show_result_immediately = false`.

## 11. Verifikasi

```
php artisan test --compact        # 180 passed / 497 assertions
vendor/bin/pint --dirty --format agent
npm run build
php artisan migrate:fresh --seed  # 1 kuis terbit, 4 soal, 10 opsi
```
