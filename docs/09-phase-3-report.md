# Phase 3 — Assignments (Laporan)

Status: **selesai** · Tanggal: 18 November 2026

## Ruang lingkup

Siklus tugas penuh: guru membuat dan menerbitkan tugas, siswa mengumpulkan (teks + berkas privat), guru menilai dengan umpan balik, siswa melihat nilai. Tenggat divalidasi di server dan pengumpulan lewat tenggat ditandai terlambat. Notifikasi tersimpan di database dan tampil di portal.

## Skema database

| Tabel | Catatan |
| --- | --- |
| `assignments` | `course_id` cascade, instruksi, `due_at`, `max_score`, `allow_resubmission`, `is_published`, soft delete, indeks `(course_id, due_at)` |
| `assignment_attachments` | berkas privat pada disk `local`, metadata mime & ukuran, file dihapus saat record dihapus |
| `submissions` | unik `(assignment_id, user_id, attempt_no)`, `is_latest`, konten, status, `submitted_at` |
| `submission_files` | berkas jawaban privat, nama asli disimpan untuk unduhan |
| `submission_grades` | unik per submission, penilai, skor, umpan balik, `graded_at` |
| `notifications` | tabel notifikasi database standar Laravel |

Enum baru: `SubmissionStatus` (draft/submitted/late/graded) dengan label & warna.

## Service

- `AssignmentService` — buat draf, ubah, terbitkan (menolak tenggat yang sudah lewat, mengirim notifikasi ke siswa terdaftar), sembunyikan, hapus.
- `AssignmentSubmissionService` — validasi server: tugas terbit + siswa terdaftar; menolak percobaan kedua bila pengumpulan ulang mati; menandai `late` otomatis; menyimpan berkas privat; menutup percobaan lama dalam transaksi; menilai dengan batas `max_score` dan mengirim notifikasi nilai.
- `SendAssignmentDeadlineReminders` (queued job) — mengingatkan siswa yang belum mengumpulkan untuk tugas yang jatuh tempo dalam 24 jam, maksimal sekali per 12 jam. Dijadwalkan tiap jam di `routes/console.php`.

## Otorisasi

Policy baru: `Assignment`, `Submission`, `AssignmentAttachment`, `SubmissionFile`. Guru hanya untuk kursus yang diampu; siswa hanya untuk kursus yang diikuti dan tugas yang sudah terbit. Skor di luar 0..max ditolak service. Guru lain mendapat 403 saat menilai.

## Portal guru

- Daftar tugas lintas kursus, buat/edit/hapus tugas, terbitkan/sembunyikan.
- Halaman tugas: instruksi, unggah/hapus lampiran, statistik pengumpulan, tabel pengumpulan dengan unduhan berkas dan form nilai + umpan balik inline.

## Portal siswa

- Daftar tugas dengan status (belum/dikumpulkan/terlambat/dinilai) dan nilai.
- Halaman tugas: instruksi, lampiran, status percobaan, nilai & umpan balik, form pengumpulan teks/berkas, peringatan tenggat lewat, dan opsi kumpulkan ulang hanya jika diizinkan guru.

## Notifikasi

- `AssignmentPublishedNotification`, `AssignmentGradedNotification`, `AssignmentDeadlineReminderNotification` (kanal database).
- Dropdown notifikasi di header portal: jumlah belum dibaca, 5 terbaru, tautan ke halaman terkait, tandai satu/semua dibaca (`NotificationController`).

## Panel admin (Filament)

- `AssignmentResource`: form (kursus, judul, instruksi, tenggat, skor maksimal, pengumpulan ulang), tabel dengan aksi terbitkan/sembunyikan, halaman lihat dengan RelationManager `SubmissionsRelationManager` (nilai + umpan balik lewat service).

## Seeder

`AssignmentSeeder` (non-produksi, idempotent): 3 tugas realistis untuk kursus Pemrograman Web Dasar dan Matematika Wajib XI, semuanya diterbitkan sehingga siswa demo menerima notifikasi.

## Verifikasi

- `migrate:fresh --seed` → 3 tugas terbit, 18 notifikasi `assignment_published`.
- `php artisan test --compact` → **145 tes lulus (403 assertion)**, termasuk:
  - pengumpulan sebelum/sesudah tenggat (status `submitted`/`late`),
  - penolakan pengumpulan ulang dan percobaan baru saat diizinkan,
  - siswa luar kursus & tugas draf ditolak,
  - penilaian + batas skor + notifikasi nilai,
  - pengingat tenggat hanya untuk yang belum mengumpulkan dan tidak berulang,
  - alur portal guru → siswa → penilaian → siswa melihat nilai,
  - guru lain tidak bisa menilai,
  - resource Filament, pembuatan draf dari admin, aksi terbit, dan penilaian dari RelationManager,
  - notifikasi ditandai dibaca dan mengarah ke halaman tugas.
- `vendor/bin/pint --dirty` bersih, `npm run build` sukses.

## Catatan / ditunda

- Tidak ada penyimpanan draf jawaban siswa; pengumpulan langsung berstatus `submitted`/`late`.
- SubmissionResource mandiri tidak dibuat; penilaian dilakukan dari RelationManager pada tugas (satu permukaan pengelolaan).
- Pengingat tenggat memerlukan `php artisan queue:work` (queue `database`) dan scheduler aktif di produksi.
- Berkas tugas disimpan privat; unduhan selalu lewat policy (pemilik atau guru pengampu).
