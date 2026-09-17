## What to build
Membuat migration, model, factory, dan seeder untuk seluruh entitas database utama (Project, ProjectUser, Checklist, Guest, Vendor, Rundown, SeserahanItem).

## Acceptance criteria
- [x] Model dan migration `Project` (name, slug, wedding_date, total_budget) terbuat.
- [x] Model dan migration pivot `ProjectUser` (project_id, user_id, role) terbuat dengan explicit ENUM/string.
- [x] Model dan migration `Checklist` (project_id, title, status: pending/done, assigned_to) terbuat.
- [x] Model dan migration `Guest` (project_id, name, side: pria/wanita/bersama, rsvp: pending/hadir/absen, pax, guest_book_message) terbuat.
- [x] Model dan migration `Vendor` (project_id, name, category, contact, package_price, paid_amount, status: pending/dp/paid, mou_path) terbuat.
- [x] Model dan migration `Rundown` (project_id, time, activity, description, assigned_to) terbuat.
- [x] Model dan migration `SeserahanItem` (project_id, item_name, status: planning/shipping/purchased/ready, tracking_number, price) terbuat.
- [x] Seluruh relasi model terdefinisi dengan benar (Eloquent relationships).
- [x] Migrasi berhasil dijalankan (`php artisan migrate`).

## Blocked by
- [Issue 1: Setup Framework & Breeze](file:///c:/laragon/www/wedding_planner/docs/issue_1_setup.md)

## Robustness & Future-Proof Decisions
1. **Cascading Delete (Integritas Data)**: Menambahkan `cascadeOnDelete()` di semua tabel anak (`checklists`, `guests`, `vendors`, `rundowns`, `seserahan_items`, `project_user`). Menghapus project otomatis membersihkan data terkait.
2. **Indexing (Performa)**: Menambahkan index unik pada `projects.slug` agar mempercepat loading halaman undangan publik.
3. **Decimal Precision**: Menggunakan tipe data `decimal(12, 2)` untuk uang (`total_budget`, `package_price`, `paid_amount`, `price`) menghindari floating-point errors.

