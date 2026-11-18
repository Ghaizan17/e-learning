# Phase 2 — Learning (Laporan)

Status: **selesai** · Tanggal: 18 November 2026

## Ruang lingkup

Kursus, bab, materi, lampiran privat, pendaftaran siswa, progres belajar, panel guru, dan portal siswa. Semua data berasal dari database; tidak ada konten hardcoded.

## Skema database

| Tabel | Catatan |
| --- | --- |
| `courses` | `teacher_assignment_id` (restrict), slug unik, status draft/published/archived, `published_at`, soft delete |
| `chapters` | unik `(course_id, position)`, `is_published`, soft delete |
| `lessons` | tipe enum (text/video/pdf/document/link/code), konten HTML, URL video/eksternal, estimasi menit, unik `(chapter_id, position)` |
| `lesson_attachments` | file privat di disk `local`, metadata mime & ukuran; file dihapus saat record dihapus |
| `enrollments` | unik `(course_id, user_id)`, status active/completed/dropped, `final_score`/`letter_grade` disiapkan untuk Phase 5 |
| `lesson_progress` | unik `(user_id, lesson_id)`, status not_started/in_progress/completed, timestamp mulai/selesai/terakhir dilihat |

## Enum

`CourseStatus`, `LessonType`, `EnrollmentStatus`, `LessonProgressStatus` (label & warna, dipakai Blade dan Filament).

## Service

- `CourseService` — slug unik (termasuk data terhapus), validasi syarat terbit (minimal satu bab + materi terbit), publish/archive/unpublish, sinkron pendaftaran saat terbit.
- `ChapterService` / `LessonService` — posisi otomatis unik, slug materi unik per bab, materi hanya bisa terbit jika babnya terbit.
- `EnrollmentService` — pendaftaran idempotent, sinkron anggota kelas aktif, reaktivasi siswa yang keluar, penyelesaian pendaftaran.
- `LessonProgressService` — mulai/selesai materi, persentase hanya dari materi terbit, otomatis menutup enrollment saat 100%.
- `CourseAccessService` — admin, guru pemilik, dan siswa terdaftar; bab/materi draf tetap tersembunyi.

## Otorisasi

Policy baru: `Course`, `Chapter`, `Lesson`, `LessonAttachment`, `Enrollment`, `LessonProgress`. Izin `courses.*`, `chapters.manage`, `lessons.manage`, `enrollments.view/manage` sudah ada di seeder; guru mendapat `enrollments.manage` dan hanya berlaku untuk kursus miliknya.

## Panel guru (`/teacher`)

- Kelas Saya, daftar kursus, buat/edit/hapus kursus + sampul.
- Halaman kelola kursus: tambah/edit/hapus bab, tambah/edit/hapus materi (semua tipe), terbitkan/sembunyikan bab & materi, unggah/hapus lampiran privat, terbitkan/arsipkan kursus.
- Halaman siswa: daftar pendaftaran, tombol sinkronkan anggota kelas.

## Portal siswa (`/student`)

- Kursus Saya dengan progres per kursus.
- Detail kursus: bab, materi, progres, tombol lanjut belajar.
- Halaman belajar: konten HTML, embed YouTube, tautan eksternal, unduh lampiran (policy), navigasi sebelumnya/berikutnya, tandai selesai.
- Dashboard siswa dan guru kini memakai angka nyata (kursus, materi selesai, progres rata-rata, siswa terdaftar).

## Panel admin (Filament)

- `CourseResource` (aksi terbitkan/arsipkan dengan validasi service) + RelationManager Bab.
- `LessonResource` dengan pembuatan via `LessonService` (posisi & slug otomatis) dan toggle publikasi tervalidasi.
- Widget ringkasan: pengguna, kelas, kursus terbit, pendaftaran.

## Seeder

`LearningSeeder` (non-produksi, idempotent): 3 kursus realistis — 2 terbit dengan 8 materi, 1 draf — dan 12 pendaftaran siswa dari kelas XI PPLG A.

## Verifikasi

- `migrate:fresh --seed` → 3 kursus (2 terbit), 4 bab, 9 materi, 12 enrollment.
- `php artisan test --compact` → **121 tes lulus (327 assertion)**, termasuk:
  - syarat publikasi kursus/materi,
  - sinkronisasi enrollment idempotent & hanya anggota aktif,
  - perhitungan progres dan penyelesaian enrollment,
  - isolasi progres antar siswa,
  - policy guru/siswa/admin,
  - unggah + unduh lampiran privat (ditolak tanpa enrollment),
  - alur portal guru → siswa → selesai materi,
  - smoke test resource Filament dan pembuatan bab dengan posisi otomatis.
- `vendor/bin/pint --dirty` bersih, `npm run build` sukses.

## Ditunda / catatan

- Resource `Material` lama (scaffold awal) dibiarkan apa adanya, tidak dipakai oleh modul kursus.
- EnrollmentResource mandiri tidak dibuat; pengelolaan pendaftaran dilakukan dari halaman siswa kursus (sesuai alur guru).
- Pratinjau materi oleh guru memakai portal siswa (belum ada mode pratinjau khusus).
- `final_score`/`letter_grade` pada `enrollments` disiapkan untuk Phase 5 (penilaian).
