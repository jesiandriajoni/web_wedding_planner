<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    project: Object
});

// Accordions expansion states
const activeAccordion = ref(null);

const toggleAccordion = (idx) => {
    if (activeAccordion.value === idx) {
        activeAccordion.value = null;
    } else {
        activeAccordion.value = idx;
    }
};

const guideSteps = [
    {
        title: "1. Cara Kelola Anggota & Project",
        icon: "👥",
        content: "Anda dapat membagi tugas dan mengelola anggota keluarga atau crew WO (Wedding Organizer) yang ikut mengurusi acara pernikahan. Setiap anggota dapat diberi role (WO, Pengantin, Keluarga) yang terintegrasi di pivot table.",
        actionText: "Kelola Anggota",
        actionRoute: "projects.show"
    },
    {
        title: "2. Alur Pengelolaan Budget & Pembayaran",
        icon: "💰",
        content: "Gunakan modul Budget Tracker untuk membagi alokasi anggaran otomatis (Vendor 50%, Katering 30%, Seserahan 10%, Lainnya 10%). Catat pembayaran cicilan (DP / Pelunasan) secara bertahap pada masing-masing vendor. Status pembayaran vendor terhitung otomatis.",
        actionText: "Buka Budget Tracker",
        actionRoute: "projects.budget"
    },
    {
        title: "3. Mengirim Undangan Online & Buku Tamu RSVP",
        icon: "✉️",
        content: "Tambahkan tamu undangan, tentukan pihak pengantin (pria/wanita/bersama) dan jumlah pax bawaan. Tamu dapat melakukan konfirmasi RSVP dan mengisi ucapan selamat (Guest Book) secara mandiri lewat halaman undangan publik.",
        actionText: "Buka Pengelola Tamu",
        actionRoute: "projects.guests.index"
    },
    {
        title: "4. Alur Pelacakan Logistik Seserahan",
        icon: "🎁",
        content: "Atur daftar barang bawaan seserahan melalui papan Kanban dengan 4 tahapan status pelacakan: Rencana (Pending), Dibeli (Purchased), Diterima (Delivered), dan Wadah Dikembalikan (Returned). Masukkan tautan resi pengiriman untuk melacak.",
        actionText: "Buka Board Seserahan",
        actionRoute: "projects.seserahan.index"
    }
];

const faqs = [
    {
        q: "Bagaimana cara membagikan Link Undangan Online ke Tamu?",
        a: "Buka halaman Pengelola Tamu, temukan daftar tamu yang dituju, lalu salin link undangan unik tamu tersebut (tersedia parameter to=Nama Tamu). Tautan ini dapat dikirimkan langsung melalui WhatsApp, Line, atau media sosial lainnya."
    },
    {
        q: "Apakah Porsi Katering terhitung otomatis?",
        a: "Ya! Estimasi porsi katering terhitung secara otomatis di backend dengan formula (Total Pax Tamu Hadir x 2 portions), membantu Anda memproyeksikan pesanan catering tanpa pusing."
    },
    {
        q: "Apakah berkas kontrak MoU Vendor aman diunggah?",
        a: "Sistem menggunakan uploader terproteksi yang membatasi ekstensi file yang diunggah (hanya PDF, DOCX, dan format gambar aman) dan melakukan pengacakan nama file (hashName) guna mencegah remote code execution (RCE)."
    }
];
</script>

<template>
    <Head :title="'Panduan - ' + project.name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight text-slate-800 dark:text-slate-100">
                        Panduan Onboarding Pengguna
                    </h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                        Pelajari alur kerja platform Wedding Planner agar pengelolaan berjalan lancar
                    </p>
                </div>
            </div>
        </template>

        <div class="py-12 bg-slate-50 dark:bg-slate-900 min-h-screen">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                
                <!-- Main Guide Steps -->
                <section class="bg-white dark:bg-slate-800 p-8 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-700 mb-8">
                    <h3 class="text-xl font-bold text-slate-800 dark:text-slate-100 mb-6 flex items-center gap-2">
                        <span>🚀 Panduan Langkah Kerja</span>
                    </h3>
                    <div class="flex flex-col gap-6">
                        <div v-for="(step, idx) in guideSteps" :key="idx"
                             class="p-5 bg-slate-50 dark:bg-slate-900/50 rounded-2xl border border-slate-100 dark:border-slate-700 flex flex-col sm:flex-row gap-4 items-start transition hover:border-sage-300 dark:hover:border-sage-700">
                            <span class="text-2xl p-3 bg-sage-50 dark:bg-sage-950/50 rounded-xl flex-shrink-0">
                                {{ step.icon }}
                            </span>
                            <div class="flex-1">
                                <h4 class="font-bold text-slate-800 dark:text-slate-100 text-lg">
                                    {{ step.title }}
                                </h4>
                                <p class="text-sm text-slate-500 dark:text-slate-400 mt-2 leading-relaxed">
                                    {{ step.content }}
                                </p>
                                <div class="mt-4">
                                    <!-- Dynamic Action Link contextually bound to project ID -->
                                    <Link :href="route(step.actionRoute, project.id)"
                                          class="inline-flex items-center text-xs font-bold text-forest-600 hover:text-forest-800 dark:text-forest-400 dark:hover:text-forest-300 gap-1">
                                        {{ step.actionText }} &rarr;
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- FAQ Accordions -->
                <section class="bg-white dark:bg-slate-800 p-8 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-700">
                    <h3 class="text-xl font-bold text-slate-800 dark:text-slate-100 mb-6">
                        ❓ Pertanyaan yang Sering Diajukan (FAQ)
                    </h3>
                    <div class="flex flex-col gap-4">
                        <div v-for="(faq, idx) in faqs" :key="idx" class="border-b border-slate-100 dark:border-slate-700 pb-4">
                            <button @click="toggleAccordion(idx)"
                                    class="w-full text-left font-bold text-slate-800 dark:text-slate-100 py-2 flex justify-between items-center transition hover:text-forest-600 dark:hover:text-forest-400">
                                <span>{{ faq.q }}</span>
                                <span class="text-xs transition-transform duration-200" :class="activeAccordion === idx ? 'rotate-180' : ''">
                                    ▼
                                </span>
                            </button>
                            <!-- Collapsible panel -->
                            <div v-if="activeAccordion === idx" class="mt-2 text-sm text-slate-500 dark:text-slate-400 leading-relaxed pl-1 transition-all duration-300">
                                {{ faq.a }}
                            </div>
                        </div>
                    </div>
                </section>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
