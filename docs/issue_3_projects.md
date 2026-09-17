## What to build
Fitur manajemen Wedding Project (membuat project baru, mengundang user lain ke project via pivot role) dan routing dynamic slug publik untuk generator undangan online.

## Acceptance criteria
- [x] Controller dan route CRUD `Project` terintegrasi dengan middleware `auth`.
- [x] User bisa membuat project baru (otomatis menjadi owner / role pengantin/WO di pivot table).
- [x] Halaman kelola anggota project (menambahkan user lain ke project dengan role 'wo', 'pengantin', atau 'keluarga').
- [x] Setup route publik `/undangan/{slug}` untuk diakses tanpa autentikasi (mengarah ke halaman undangan spesifik project).
- [x] Verifikasi bahwa slug project unik.

## Blocked by
- [Issue 2: Database Schema & Relations](file:///c:/laragon/www/wedding_planner/docs/issue_2_database.md)

## Robustness & Security Decisions
1. **Otorisasi Multi-Tenant (Anti-Data Leak)**: Membatasi hak akses CRUD Project agar hanya bisa diakses oleh User yang tercatat sebagai member di pivot `project_user`. User lain akan menerima respon `403 Forbidden`.
2. **Auto-Suffix Unique Slug (Anti-Crash)**: Implementasi algoritma dynamic slug checker yang mendeteksi collision nama project dan otomatis menambahkan nomor suffix (misal: `-1`, `-2`) jika slug sudah terpakai di database.
3. **Data Serialization Aman (Anti-Leaking)**: Halaman publik `/undangan/{slug}` dibatasi hanya menserialisasi data publik tamu (Guest Book) dan susunan rundown. Data budget, MoU kontrak vendor, dan data internal WO/Pengantin lainnya tidak dikirim ke client-side.

