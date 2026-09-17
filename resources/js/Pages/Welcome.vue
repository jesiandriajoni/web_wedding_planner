<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
});
</script>

<template>
    <Head title="Wedding Planner - Romantic Luxury Platform" />

    <div class="min-h-screen bg-ivory text-charcoal font-sans selection:bg-blush-200">
        <!-- Navigation Header -->
        <header class="sticky top-0 z-50 bg-white/90 backdrop-blur-sm border-b border-gold-200/60 shadow-xs">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
                <!-- Brand Logo -->
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blush-200 via-blush-300 to-gold-400 flex items-center justify-center shadow-xs">
                        <span class="text-white text-xl font-serif font-bold">W</span>
                    </div>
                    <div>
                        <span class="font-serif font-bold text-xl text-charcoal tracking-wide block leading-tight">
                            Wedding<span class="text-gold-600 font-normal">Planner</span>
                        </span>
                        <span class="font-script text-sm text-blush-600 -mt-1 block">
                            Romantic Luxury Organizer
                        </span>
                    </div>
                </div>

                <!-- Navigation Auth Buttons -->
                <nav v-if="canLogin" class="flex items-center gap-3">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="route('projects.index')"
                        class="px-5 py-2.5 bg-gold-500 hover:bg-gold-600 text-white rounded-xl text-sm font-bold shadow-xs transition"
                    >
                        Buka Dashboard
                    </Link>

                    <template v-else>
                        <Link
                            :href="route('login')"
                            class="px-5 py-2.5 bg-ivory hover:bg-ivory-200 border border-gold-300 text-charcoal rounded-xl text-sm font-bold transition shadow-xs"
                        >
                            Masuk
                        </Link>

                        <Link
                            v-if="canRegister"
                            :href="route('register')"
                            class="px-5 py-2.5 bg-gold-500 hover:bg-gold-600 text-white rounded-xl text-sm font-bold shadow-xs transition"
                        >
                            Daftar Akun
                        </Link>
                    </template>
                </nav>
            </div>
        </header>

        <!-- Hero Section -->
        <section class="relative overflow-hidden py-20 lg:py-28 bg-gradient-to-b from-white via-ivory to-ivory border-b border-gold-100">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
                <span class="inline-block px-4 py-1.5 rounded-full bg-blush-100 border border-blush-200 text-blush-900 text-xs font-bold uppercase tracking-widest mb-4">
                    The Ultimate Wedding Companion
                </span>
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold font-serif text-charcoal tracking-tight leading-tight">
                    Wujudkan Pernikahan Impian dengan <span class="text-gold-600 italic">Sempurna & Tenang</span>
                </h1>
                <p class="font-script text-2xl sm:text-3xl text-blush-700 mt-3">
                    Every love story deserves a beautiful beginning
                </p>
                <p class="max-w-2xl mx-auto text-base sm:text-lg text-charcoal-500 mt-4 leading-relaxed font-sans">
                    Kelola seluruh persiapan pernikahan Anda mulai dari anggaran, checklist tugas, direktori vendor, daftar tamu dengan import Excel, hingga undangan digital mewah.
                </p>

                <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-4">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="route('projects.index')"
                        class="w-full sm:w-auto px-8 py-3.5 bg-gold-500 hover:bg-gold-600 text-white rounded-xl text-base font-bold shadow-md hover:shadow-lg transition duration-200"
                    >
                        Kelola Pernikahan Saya &rarr;
                    </Link>
                    <template v-else>
                        <Link
                            :href="route('register')"
                            class="w-full sm:w-auto px-8 py-3.5 bg-gold-500 hover:bg-gold-600 text-white rounded-xl text-base font-bold shadow-md hover:shadow-lg transition duration-200"
                        >
                            Mulai Sekarang Gratis
                        </Link>
                        <Link
                            :href="route('login')"
                            class="w-full sm:w-auto px-8 py-3.5 bg-white hover:bg-ivory-100 border border-gold-300 text-charcoal rounded-xl text-base font-bold shadow-xs transition duration-200"
                        >
                            Masuk ke Akun
                        </Link>
                    </template>
                </div>
            </div>
        </section>

        <!-- Features Showcase Grid -->
        <section class="py-20 bg-ivory">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-2xl mx-auto mb-16">
                    <span class="text-xs font-bold text-gold-700 uppercase tracking-widest block">Fitur Unggulan</span>
                    <h2 class="text-3xl sm:text-4xl font-bold font-serif text-charcoal mt-2">
                        Segala yang Anda Butuhkan dalam Satu Aplikasi
                    </h2>
                    <p class="text-sm text-charcoal-400 mt-2">
                        Dirancang khusus untuk memudahkan calon pengantin dan wedding planner mengatur setiap detail tanpa stres.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <!-- Feature 1: Guest Book & RSVP -->
                    <div class="bg-white p-8 rounded-3xl border border-gold-200 shadow-xs hover:shadow-md transition duration-200 flex flex-col justify-between">
                        <div>
                            <div class="w-12 h-12 rounded-2xl bg-blush-100 text-blush-700 flex items-center justify-center font-bold text-xl mb-6">
                                💌
                            </div>
                            <h3 class="text-xl font-bold font-serif text-charcoal">
                                Guest Book & RSVP Manager
                            </h3>
                            <p class="text-sm text-charcoal-400 mt-2 leading-relaxed">
                                Import ratusan daftar tamu langsung dari file Excel/CSV, generate tautan personal otomatis, dan kirim undangan langsung via WhatsApp.
                            </p>
                        </div>
                        <div class="mt-6 pt-4 border-t border-gold-100 flex items-center gap-2 text-xs font-bold text-gold-600">
                            <span>Mendukung Import .XLSX & .CSV</span>
                        </div>
                    </div>

                    <!-- Feature 2: Budget Tracker -->
                    <div class="bg-white p-8 rounded-3xl border border-gold-200 shadow-xs hover:shadow-md transition duration-200 flex flex-col justify-between">
                        <div>
                            <div class="w-12 h-12 rounded-2xl bg-gold-100 text-gold-700 flex items-center justify-center font-bold text-xl mb-6">
                                💰
                            </div>
                            <h3 class="text-xl font-bold font-serif text-charcoal">
                                Budget Tracker & Pembayaran
                            </h3>
                            <p class="text-sm text-charcoal-400 mt-2 leading-relaxed">
                                Pantau pengeluaran anggaran, alokasi biaya, catatan DP, dan sisa pelunasan vendor secara transparan dan akurat.
                            </p>
                        </div>
                        <div class="mt-6 pt-4 border-t border-gold-100 flex items-center gap-2 text-xs font-bold text-gold-600">
                            <span>Kalkulasi Otomatis Sisa Budget</span>
                        </div>
                    </div>

                    <!-- Feature 3: Digital Invitation -->
                    <div class="bg-white p-8 rounded-3xl border border-gold-200 shadow-xs hover:shadow-md transition duration-200 flex flex-col justify-between">
                        <div>
                            <div class="w-12 h-12 rounded-2xl bg-blush-100 text-blush-700 flex items-center justify-center font-bold text-xl mb-6">
                                💍
                            </div>
                            <h3 class="text-xl font-bold font-serif text-charcoal">
                                Undangan Digital Publik
                            </h3>
                            <p class="text-sm text-charcoal-400 mt-2 leading-relaxed">
                                Undangan website elegan dengan musik latar, galeri foto prewed, rundown acara, buku tamu interaktif, dan konfirmasi kehadiran.
                            </p>
                        </div>
                        <div class="mt-6 pt-4 border-t border-gold-100 flex items-center gap-2 text-xs font-bold text-gold-600">
                            <span>Tampilan Mewah & Responsif</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-white border-t border-gold-200 py-10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-2">
                    <span class="font-serif font-bold text-lg text-charcoal">Wedding<span class="text-gold-600">Planner</span></span>
                    <span class="text-xs text-charcoal-400">&mdash; Romantic Luxury Wedding Organizer</span>
                </div>
                <p class="text-xs text-charcoal-400">
                    &copy; {{ new Date().getFullYear() }} Wedding Planner. All rights reserved.
                </p>
            </div>
        </footer>
    </div>
</template>
