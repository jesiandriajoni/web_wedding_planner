## What to build
Halaman Panduan (User Onboarding) statis atau semi-statis untuk memandu pengguna baru (Pengantin dan kru WO) tentang cara alur kerja aplikasi agar tidak bingung.

## Acceptance criteria
- [x] Halaman panduan statis berbasis komponen Vue 3 yang menjelaskan:
  - Cara membuat & mengelola project.
  - Alur pengelolaan budget pernikahan.
  - Cara mengirim undangan online & memantau RSVP.
  - Alur status logistik Seserahan.
- [x] Desain antarmuka bersih dan elegan (Sage/Forest Green) menggunakan ilustrasi atau FAQ accordions yang menarik.

## Blocked by
- [Issue 1: Setup Framework & Breeze](file:///c:/laragon/www/wedding_planner/docs/issue_1_setup.md)

## Onboarding & Usability Decisions
1. **Navigasi Langsung Berkonteks (High Usability)**: Menyediakan Inertia Links yang terikat secara dinamis ke ID proyek saat ini di setiap langkah panduan. Hal ini membantu pengguna baru langsung beralih ke modul yang bersangkutan tanpa kebingungan mencari menu.
2. **Keamanan Otorisasi (Auth Guard)**: Halaman panduan dilindungi oleh middleware auth dan pengecekan member proyek, memastikan petunjuk operasional dan data proyek aman dari akses tidak sah.
3. **FAQ Accordion Interaktif**: Menggunakan state accordion Vue yang reaktif untuk menampilkan jawaban atas pertanyaan yang sering diajukan, menjaga tampilan tetap minimalis, canggih, dan bersih.

