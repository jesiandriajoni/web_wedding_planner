Berikut adalah ringkasan arsitektur menu untuk langsung Anda feed ke AI coding assistant Anda:🛠️ Menu Utama & Struktur File (Clean Architecture)
1. Dashboard (Pusat Kendali Progres)Fungsi: Menampilkan persentase total kesiapan pernikahan, summary budget, dan tugas terdekat.Formula Progres (Math Logic):$$\text{Total Progress} = \left( \frac{\text{Checklist Selesai}}{\text{Total Checklist}} \times 100 \right)\%$$
2. Panduan (User Onboarding)Fungsi: Halaman dokumentasi statis atau video singkat cara penggunaan aplikasi untuk Pengantin & WO agar tidak bingung.3. Budget (Financial Tracker)Fungsi: Alokasi dana otomatis, pencatatan DP/pelunasan, dan status pembayaran vendor.
4. Vendor (Vendor Directory & Hub)Fungsi: Manajemen kontak vendor, penyimpanan file kontrak (MoU), dan pencatatan komparasi harga paket.
5. Tamu (Dual-Side Guest Management & RSVP)Fungsi: Pemisahan daftar tamu (pria, wanita, bersama), kalkulator otomatis porsi katering ($Jumlah Undangan \times 2$), dan gerbang generator Undangan Online (Galeri Prewed, Detail Acara, Buku Tamu).
6. Rundown (Timeline Hari-H)Fungsi: Jadwal acara linier per menit pada hari-H lengkap dengan penanggung jawab (Assignee dari pihak WO/Keluarga) yang bisa diekspor ke PDF.
7. Seserahan (Logistics Tracker)Fungsi: Pelacakan 4 status hantaran: planning (rencana), shipping (di kurir + input resi), purchased (diterima fisik), ready (selesai dihias).💻 Vibe Coding Prompt Rules (Copy-Paste ke Cursor/Antigravity)Gunakan rules ini sebagai context instruksi agar AI Anda menulis kode yang efisien dan sesuai gaya coding Anda:Plaintext[VIBE CODING RULES]


1. Write clean PHP/Laravel 13 and Vue.js 3 components.
2. Prioritize EARLY RETURNS to eliminate 'else' blocks and deeply nested structures.
3. Keep methods short, single-responsibility, and highly readable.
4. For the 'Seserahan' and 'Tamu' modules, use explicit ENUMs in migration.
5. Provide clean Tailwind CSS classes using a warm, professional color palette (Sage/Forest Green/Slate).
🗄️ Skema Database Singkat (Migration Blueprint)Untuk mempercepat pembuatan model dan migrasi, berikut struktur esensial yang saling berelasi:PHP// 1. Checklists (Untuk Dashboard & Rundown)
Schema::create('checklists', function (Blueprint $table) {
    $table->id();
    $table->foreignId('project_id')->constrained();
    $table->string('title');
    $table->enum('status', ['pending', 'done'])->default('pending');
    $table->string('assigned_to')->nullable(); // 'WO', 'Pengantin', etc.
    $table->timestamps();
});

// 2. Tamu (Guest & RSVP)
Schema::create('guests', function (Blueprint $table) {
    $table->id();
    $table->foreignId('project_id')->constrained();
    $table->string('name');
    $table->enum('side', ['pria', 'wanita', 'bersama']);
    $table->enum('rsvp', ['pending', 'hadir', 'absen'])->default('pending');
    $table->integer('pax')->default(2);
    $table->timestamps();
});

// 3. Seserahan Items
Schema::create('seserahan_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('project_id')->constrained();
    $table->string('item_name');
    $table->enum('status', ['planning', 'shipping', 'purchased', 'ready'])->default('planning');
    $table->string('tracking_number')->nullable(); // Untuk cek resi online
    $table->decimal('price', 12, 2)->default(0);
    $table->timestamps();
});