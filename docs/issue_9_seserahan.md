## What to build
Modul pelacakan logistik Seserahan (Logistics Tracker) dengan 4 tahapan status barang, input resi pengiriman kurir, dan pembuatan tautan pelacakan resi eksternal otomatis.

## Acceptance criteria
- [x] CRUD Barang Seserahan (Nama barang, Harga, Status, Nomor Resi).
- [x] Pilihan status barang seserahan dibatasi oleh explicit ENUM: `pending`, `purchased`, `delivered`, `returned`.
- [x] Kolom input `tracking_url` hanya tampil jika status dipilih `purchased`/`delivered` (pengiriman kurir).
- [x] Generator link pelacakan resi eksternal otomatis mengarah ke web tracker eksternal jika kolom `tracking_url` terisi.
- [x] UI berupa board/list status yang bersih.

## Blocked by
- [Issue 3: Project Management & Invitation Slug Router](file:///c:/laragon/www/wedding_planner/docs/issue_3_projects.md)

## Security & Robustness Decisions
1. **Sanitasi Protokol URL Eksternal (Anti-XSS)**: Membatasi input kolom `tracking_url` dengan aturan validasi `url` Laravel yang mewajibkan skema `http://` atau `https://`. Ini memblokir eksekusi javascript code injection (`javascript:`) atau bahaya cross-site scripting (XSS) di browser saat link pelacakan diklik.
2. **Strict Status Enum Whitelisting**: Membatasi transisi status barang seserahan hanya ke opsi yang valid (`pending`, `purchased`, `delivered`, `returned`). Setiap percobaan manipulasi request dengan status di luar opsi tersebut akan digagalkan oleh middleware validasi Laravel.
3. **Interactive Kanban Board Flow**: UI diimplementasikan menggunakan papan kanban interaktif lengkap dengan tombol perpindahan status barang instan sehingga meminimalisir kesalahan input manual user.

