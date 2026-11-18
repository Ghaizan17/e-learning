# Phase 5 — Penilaian (Report)

Status: **selesai** · Test suite: **219 passed / 605 assertions** · Pint bersih.

## 1. Cakupan

| Area | Hasil |
| --- | --- |
| Skema nilai | 4 kategori (Tugas, Kuis, Ujian, Partisipasi), bobot wajib total 100% |
| Komponen | Dibuat otomatis saat tugas/kuis diterbitkan, terhubung ke sumber via morph |
| Buku nilai | Matriks siswa × komponen, edit skor + alasan, publikasi ke siswa |
| Perhitungan | Rata-rata per kategori → nilai akhir berbobot → huruf A–E |
| Audit | Setiap perubahan skor menulis `grade_histories` dalam transaksi |
| Sinkronisasi | Nilai tugas dan kuis otomatis masuk buku nilai |
| Ekspor | CSV buku nilai dengan skor komponen + nilai akhir |
| Portal guru | Daftar buku nilai, skema, matriks, ekspor |
| Portal siswa | Daftar nilai, rincian per kategori, hanya nilai terbit |
| Filament | `GradingCategoryResource`, `GradeResource` + riwayat nilai |

## 2. Skema Database

- `grading_categories` — kursus, nama enum, bobot, unik (kursus, nama).
- `grade_components` — kursus, kategori, morph `gradeable` (Assignment/Quiz), judul, skor maksimum.
- `grades` — kursus, siswa, komponen, skor, maksimum, persentase, penilai, `is_published`, unik (komponen, siswa).
- `grade_histories` — nilai, skor lama/baru, pengubah, alasan.

## 3. Service

| Service | Tanggung jawab |
| --- | --- |
| `GradingSchemeService` | Skema default, validasi total 100, syarat publikasi kursus |
| `GradeComponentService` | Sinkronisasi komponen dari tugas/kuis |
| `GradeCalculationService` | Nilai akhir berbobot, huruf, breakdown siswa |
| `GradeSyncService` | Salin nilai submission/attempt ke gradebook |
| `GradeAuditService` | Edit nilai + riwayat + hitung ulang, publikasi |

Semua perhitungan hanya ada di service; controller dan Filament memanggil service yang sama.

## 4. Integrasi

- `CourseService` membuat skema default saat kursus dibuat dan memblokir publikasi bila bobot ≠ 100.
- `AssignmentService::publish` dan `QuizService::publish` membuat `grade_components`.
- `AssignmentSubmissionService::grade` menyinkronkan nilai tugas.
- `QuizAttemptService` (submit/expire) dan `QuizGradingService` menyinkronkan nilai kuis ketika sudah dinilai penuh.
- `GradeSyncService` menghitung ulang `enrollments.final_score` dan `letter_grade`.

## 5. Otorisasi

- `GradePolicy` — guru hanya kursus yang diampu; siswa hanya nilai miliknya yang `is_published`.
- `GradingCategoryPolicy` — `grading_scheme.manage` + akses kursus.
- `GradeComponentPolicy` — view berdasarkan akses kursus.

## 6. Endpoint

Guru:
`GET /guru/grades`, `GET /guru/courses/{course}/grades`, `GET .../grades/export`,
`PUT /guru/grades/{grade}`, `POST /guru/grades/{grade}/publish`,
`GET|PUT /guru/courses/{course}/grading`.

Siswa:
`GET /student/grades`, `GET /student/grades/{course}`.

## 7. Seeder

`GradingSeeder` memastikan skema tiap kursus, menyinkronkan komponen dari tugas/kuis yang sudah ada, dan menghitung ulang seluruh enrollment. Tidak membuat nilai palsu.

## 8. Pengujian

- `GradingSchemeTest` — skema default, validasi 100%, blokir publikasi, pembuatan komponen.
- `GradeCalculationTest` — matematika berbobot, pengecualian nilai belum terbit, audit, batas skor, sinkronisasi tugas/kuis, ambang huruf.
- `PortalGradeTest` — halaman guru/siswa, edit + riwayat, otorisasi lintas guru/siswa, ekspor CSV.
- `Filament/GradingResourcesTest` — halaman admin, view + riwayat, edit via aksi tabel.
- `AcademicYearFactory` dibuat deterministik untuk menghilangkan flake unique constraint.

## 9. Catatan

- Kategori Partisipasi berbobot 0 secara default karena belum ada komponen sumber manual; bobot dapat diubah guru.
- Nilai hanya masuk `final_score` jika `is_published = true`; nilai tersembunyi tidak bocor ke siswa.
- Menghapus tugas/kuis tidak menghapus riwayat nilai; komponen tetap menyimpan judul dan skor.

## 10. Verifikasi

```
php artisan test --compact        # 219 passed / 605 assertions
vendor/bin/pint --dirty --format agent
npm run build
php artisan migrate:fresh --seed  # skema 12 kategori, 4 komponen nilai
```
