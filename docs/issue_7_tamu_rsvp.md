## What to build
Modul manajemen tamu (Tamu & RSVP) dan generator halaman Undangan Online publik interaktif. Menyediakan input data tamu, kalkulator porsi katering otomatis ($pax \times 2$), dan form input RSVP & Buku Tamu publik.

## Acceptance criteria
- [x] CRUD Data Tamu (Nama, Side: Pria/Wanita/Bersama, RSVP, Pax).
- [x] Fitur filter data tamu berdasarkan kategori `side`.
- [x] Kalkulator otomatis total porsi katering ($pax \times 2$) berdasarkan total undangan yang hadir + pending.
- [x] URL generator undangan unik per tamu: `/undangan/{slug}?to=Nama+Tamu`.
- [x] Halaman publik `/undangan/{slug}` yang indah (bebas login, responsif, tema Sage/Forest Green) menampilkan:
  - Nama Tamu (dari query param `?to=`)
  - Galeri Prewedding Mockup
  - Detail Acara & Waktu
  - Form RSVP (Hadir/Absen, Jumlah Pax bawaan)
  - Buku Tamu (Guest Book) untuk mengirim ucapan selamat.
- [x] RSVP & Buku Tamu yang disubmit langsung memperbarui database dan muncul di admin panel.

## Blocked by
- [Issue 3: Project Management & Invitation Slug Router](file:///c:/laragon/www/wedding_planner/docs/issue_3_projects.md)

## Security & Robustness Decisions
1. **Proteksi Mass-Assignment RSVP Publik**: Mengamankan endpoint POST `/undangan/{slug}/rsvp` secara ketat dengan whitelist assignment (hanya memperbarui `rsvp`, `pax`, dan `guest_book_message`). Perubahan atribut sensitif lainnya seperti `name`, `side`, atau `project_id` diblokir sepenuhnya demi mencegah manipulasi request payload oleh pihak luar.
2. **Eksklusi Data Sensitif (Anti-Leaking)**: Halaman RSVP publik hanya membagikan list nama dan pesan ucapan tamu lain (`guest_book_message`). Nomor telepon, email, nomor meja, dan data logistik internal WO/pengantin lainnya dikecualikan sepenuhnya dari data serialization.
3. **Kalkulator Katering Presisi**: Otomatisasi porsi katering dihitung terpusat di backend menggunakan rumus `$attendingPax * 2` untuk menyingkirkan inkonsistensi pembulatan atau manipulasi angka dari client-side.

