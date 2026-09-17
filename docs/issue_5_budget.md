## What to build
Modul pelacak keuangan (Budget Tracker) dengan pembagian alokasi otomatis berdasarkan persentase default, pencatatan DP/pelunasan transaksi, dan status pembayaran vendor.

## Acceptance criteria
- [x] Mesin kalkulasi alokasi otomatis dari total target budget project ke kategori utama:
  - Vendor: 50%
  - Katering: 30%
  - Seserahan: 10%
  - Lain-lain: 10%
- [x] Form edit nominal alokasi per kategori secara manual jika user ingin menyesuaikan persentase default.
- [x] Daftar transaksi pembayaran (DP / pelunasan) terhubung ke vendor masing-masing.
- [x] Indikator status pembayaran ('pending', 'dp', 'paid') terhitung otomatis dari total paket vendor dibanding total transaksi terbayar.

## Blocked by
- [Issue 3: Project Management & Invitation Slug Router](file:///c:/laragon/www/wedding_planner/docs/issue_3_projects.md)

## Financial Safety & Robustness Decisions
1. **Model Lifecycle Sync (Anti-Desinkronisasi)**: Menggunakan model observer static `booted()` event listener (`saved` dan `deleted` pada model `Payment`) untuk memicu rekalkulasi total terbayar (`paid_amount`) dan pembaharuan status vendor secara real-time. Hal ini menjamin status vendor konsisten dengan log transaksi di database.
2. **Decimal Precision Guard**: Menggunakan integer parsing dan decimal(12, 2) untuk kalkulasi uang agar meminimalkan problem round-off precision float arithmetic.
3. **Budget Overrun Warning Alert**: Menampilkan peringatan visual di frontend jika akumulasi harga paket vendor melebihi nominal anggaran alokasi yang direncanakan.

