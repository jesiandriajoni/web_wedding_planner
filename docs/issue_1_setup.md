## What to build
Setup awal framework Laravel 13 dengan Inertia.js & Vue 3 menggunakan Laravel Breeze, konfigurasi MySQL database, serta install package `barryvdh/laravel-dompdf`.

## Acceptance criteria
- [x] Laravel Breeze (Inertia + Vue) terinstall dan berjalan sukses.
- [x] Database MySQL terbuat dan koneksi sukses (`.env`).
- [x] Package `barryvdh/laravel-dompdf` terinstall via composer.
- [x] Halaman login, register, dan dashboard bawaan Breeze dapat diakses tanpa error.
- [x] Asset frontend tercompile dengan sukses via `npm run build`.

## Blocked by
None - can start immediately.

## Robustness & Optimization Decisions
1. **MySQL Database**: Dibuat dan dikoneksikan dengan sukses, menggantikan SQLite atas permintaan user.
2. **Dompdf Custom Temp Path**: Path diatur ke `storage/app/temp` untuk keamanan izin tulis dan kestabilan.
3. **Tailwind CSS v4 Integration**: Menggunakan compiler native `@tailwindcss/vite` untuk build super cepat dan future-proof.
4. **Vite Font Optimization**: Menggunakan font Bunny `Instrument Sans` untuk performa rendering modern dan cepat.

