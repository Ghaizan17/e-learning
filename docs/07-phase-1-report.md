# Phase 1 — Academic Management (Laporan)

Status: **selesai** · Tanggal: 18 November 2026

## Ruang lingkup

Fondasi domain akademik: tahun ajaran, semester, program keahlian, mata pelajaran, kelas, keanggotaan kelas (riwayat), dan penugasan mengajar. Seluruh data dibuat dari UI admin dan tersimpan di MariaDB; tidak ada data dummy di kode.

## Skema database

| Tabel | Catatan penting |
| --- | --- |
| `academic_years` | `name` unik, rentang tanggal, `is_active` (satu aktif) |
| `semesters` | FK tahun ajaran (cascade), unik `(academic_year_id, name)` dan `(academic_year_id, sequence)` |
| `departments` | `code` unik, soft delete |
| `subjects` | `code` unik, `department_id` nullable, soft delete |
| `classrooms` | FK tahun ajaran (restrict), wali kelas, tingkat, kapasitas, unik `(academic_year_id, name)`, soft delete |
| `classroom_members` | `joined_at`/`left_at`/`status`; append-only (tidak dihapus), indeks `(user,status)` dan `(classroom,status)` |
| `teacher_assignments` | FK guru/kelas/mapel/tahun/semester; unik `(teacher,classroom,subject,semester)` |
| `student_profiles`, `teacher_profiles` | Ekstensi 1:1 dari `users` (NISN/NIS, NIP/NUPTK, dll.) |

## Enum

`SemesterName` (Ganjil/Genap + urutan), `GradeLevel` (X/XI/XII), `ClassroomMemberStatus` (aktif/pindah/lulus/keluar + label & warna), `Gender`, `EmploymentStatus`.

## Service (logika bisnis)

- `AcademicYearService` — aktivasi tahun, penonaktifan semester terkait, validasi semester milik tahun.
- `ClassroomService` — pendaftaran siswa (idempotent), validasi peran siswa, satu kelas aktif per tahun ajaran, kapasitas, pindah kelas, penutupan keanggotaan tanpa menghapus riwayat.
- `TeacherAssignmentService` — validasi peran guru, konsistensi tahun/kelas/semester, anti duplikasi saat create & update.
- `AcademicRolloverService` — menduplikasi semester + kelas ke tahun ajaran baru tanpa menyalin anggota dan wali kelas.

## Otorisasi

Policy untuk 8 model (`AcademicYear`, `Semester`, `Department`, `Subject`, `Classroom`, `TeacherAssignment`, `StudentProfile`, `TeacherProfile`). Guru hanya dapat melihat kelas tempat ia mengajar atau menjadi wali kelas; siswa tidak memiliki akses akademik admin. Super admin tetap memiliki semua izin lewat `Gate::before`.

## Panel admin (Filament v5)

- Resource: Tahun Ajaran (+ RelationManager Semester), Semester, Program Keahlian, Mata Pelajaran, Kelas (+ RelationManager Siswa), Penugasan Mengajar.
- Aksi khusus:
  - Aktifkan tahun ajaran (menonaktifkan yang lain).
  - Duplikasi struktur tahun ajaran dari header daftar tahun ajaran.
  - Tambah/pindah/keluarkan siswa pada RelationManager kelas (memakai service, riwayat tetap utuh).
  - Form penugasan mengajar memfilter kelas & semester sesuai tahun ajaran dan menolak duplikasi.
- Semua resource berada pada grup navigasi **Akademik**.

## Seeder

`AcademicSeeder` (hanya non-produksi, idempotent) membuat struktur 2026/2027: 3 program keahlian, 8 mata pelajaran, 6 kelas, 4 akun guru, 13 siswa, 10 penugasan mengajar, dan penempatan siswa aktif. `RolePermissionSeeder` ditambah izin `teacher_assignments.view` untuk guru.

## Verifikasi

- `php artisan migrate:fresh --seed` → 15 migrasi + 3 seeder berhasil; 2026/2027 aktif dengan 2 semester, 6 kelas, 13 keanggotaan aktif, 10 penugasan.
- `php artisan test --compact` → **82 tes lulus (213 assertion)**, termasuk:
  - satu tahun ajaran & satu semester aktif,
  - rollover tanpa menyalin anggota,
  - kapasitas, peran, dan satu kelas aktif per tahun,
  - pindah kelas menyimpan dua baris riwayat,
  - anti duplikasi penugasan (service + form),
  - smoke test 6 halaman index, form create, aksi aktifkan, rollover, dan create kelas di panel admin.
- `vendor/bin/pint --dirty --format agent` → bersih.
- `php artisan route:list --path=admin` → seluruh route resource akademik terdaftar.

## Ditunda ke fase berikutnya

- Manajemen profil siswa/guru yang lengkap di panel (saat ini data profil dibuat seeder; pengelolaan penuh menyusul bersama modul siswa/guru).
- Penghapusan penugasan mengajar sudah dijaga, tetapi penjagaan tambahan akan diperlukan saat `courses` (Phase 2) mereferensikannya.
