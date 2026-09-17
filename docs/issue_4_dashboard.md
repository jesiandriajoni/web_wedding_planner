## What to build
Halaman Dashboard (Pusat Kendali Progres) sebagai landing page setelah user masuk ke aplikasi. Menampilkan total kesiapan pernikahan (checklist terhitung), ringkasan budget, dan daftar tugas terdekat.

## Acceptance criteria
- [x] Widget hitung progres checklist pernikahan dengan formula:
  $$\text{Total Progress} = \left( \frac{\text{Checklist Selesai}}{\text{Total Checklist}} \times 100 \right)\%$$
- [x] Ringkasan grafik/indikator alokasi anggaran belanja (nominal terpakai vs nominal limit anggaran).
- [x] Widget daftar tugas (checklist) terdekat/terakhir yang belum selesai.
- [x] Tampilan responsif dengan palet warna Sage/Forest Green/Slate.

## Blocked by
- [Issue 3: Project Management & Invitation Slug Router](file:///c:/laragon/www/wedding_planner/docs/issue_3_projects.md)

## Robustness & Anti-Crash Decisions
1. **Proteksi Division-by-Zero (Anti-Crash)**: Menambahkan guard condition di mana jika `total_checklists == 0` maka otomatis mengembalikan progres `0%`, menghindari pembagian dengan angka nol yang memicu fatal error.
2. **Optimasi Aggregation Query (Anti-Memory Leak)**: Melakukan kalkulasi sum dan count (`sum('paid_amount')` dan `count()`) di level database menggunakan query builder, menghindari pemuatan ratusan objek Eloquent ke memori server (RAM) untuk merangkum total budget.
3. **Responsive & Modern UI**: Layout dashboard menggunakan grid modern, transisi lembut pada hover, dan diagram lingkaran SVG interaktif dengan palet warna premium Sage/Forest Green/Slate yang ramah perangkat mobile.

