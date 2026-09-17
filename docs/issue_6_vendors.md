## What to build
Modul direktori Vendor untuk manajemen kontak vendor, penyimpanan file MoU kontrak (dalam local storage server), dan kalkulasi komparasi harga paket vendor.

## Acceptance criteria
- [x] Fitur CRUD Vendor (nama, kategori, kontak, harga paket, status pembayaran).
- [x] Fitur upload file MoU (kontrak) ke storage local Laravel (`storage/app/public/contracts`).
- [x] Link download MoU aman menggunakan symbolic link public.
- [x] UI visual untuk membandingkan harga paket vendor sejenis (komparasi harga).

## Blocked by
- [Issue 3: Project Management & Invitation Slug Router](file:///c:/laragon/www/wedding_planner/docs/issue_3_projects.md)

## Upload Security & Robustness Decisions
1. **Validasi Mime-Type Ketat (Anti-RCE)**: Membatasi upload MoU berkas hanya untuk jenis file aman (PDF, DOC/DOCX, dan format gambar JPG/PNG) dengan ukuran maksimal 10MB. Ini menutup celah penyerangan remote code execution (RCE) via upload script PHP/JS.
2. **Dynamic Hash-Naming (Anti-Overwrite)**: Nama file MoU diubah menggunakan cryptographically secure hashName generator Laravel secara dinamis sebelum disimpan ke disk storage, mencegah tabrakan nama file (collision) dan overwrite data antar project.
3. **Model Event Cleanup (Anti-Orphaned Files)**: Mendaftarkan listener static `deleted` di level model `Vendor` agar ketika baris data vendor dihapus, berkas MoU terkait juga otomatis terhapus bersih dari disk storage.
4. **Symbolic Storage Connection**: Tautan publik diaktifkan via `php artisan storage:link` agar file contract dapat diunduh langsung lewat browser via public link.

