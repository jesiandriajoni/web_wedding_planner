<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    project: Object,
    total_checklists: Number,
    done_checklists: Number,
    progress: Number,
    spent_budget: Number,
    upcoming_tasks: Array
});

// Format currency in IDR (rupiah)
const formatCurrency = (val) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0
    }).format(val);
};

// Calculate remaining budget
const remainingBudget = computed(() => {
    return props.project.total_budget - props.spent_budget;
});

// Calculate budget percentage spent
const budgetPercentage = computed(() => {
    if (props.project.total_budget <= 0) return 0;
    return Math.min(Math.round((props.spent_budget / props.project.total_budget) * 100), 100);
});

onMounted(() => {
    if (window.Echo) {
        window.Echo.private(`projects.${props.project.id}`)
            .listen('.dashboard.updated', () => {
                router.reload({
                    only: ['project', 'total_checklists', 'done_checklists', 'progress', 'spent_budget', 'upcoming_tasks']
                });
            });
    }
});

onUnmounted(() => {
    if (window.Echo) {
        window.Echo.leave(`projects.${props.project.id}`);
    }
});
</script>

<template>
    <Head :title="'Dashboard - ' + project.name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h2 class="text-3xl font-bold font-serif text-charcoal tracking-tight">
                        {{ project.name }}
                    </h2>
                    <p class="text-sm text-charcoal-400 mt-1 flex items-center gap-2">
                        <span>Tanggal Pernikahan:</span>
                        <span class="font-bold text-gold-600 bg-gold-50 px-2.5 py-0.5 rounded-md border border-gold-200">
                            {{ project.wedding_date }}
                        </span>
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <Link :href="route('public.invitation', project.slug)" target="_blank"
                          class="px-5 py-2.5 bg-gold-500 hover:bg-gold-600 active:bg-gold-700 text-white rounded-xl shadow-xs text-sm font-bold tracking-wide transition duration-200 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                        Lihat Undangan Publik
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-10 bg-ivory min-h-screen">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Overview Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <!-- Progress Card -->
                    <div class="bg-white p-6 rounded-2xl shadow-xs border border-gold-200/80 transition hover:shadow-md duration-200">
                        <div class="flex justify-between items-center mb-4">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-charcoal-400">
                                Kesiapan Pernikahan
                            </h4>
                            <span class="px-2.5 py-1 bg-blush-100 text-blush-800 rounded-lg text-xs font-bold">
                                Progress
                            </span>
                        </div>
                        <div class="flex items-center gap-4">
                            <!-- Circular Progress Indicator -->
                            <div class="relative w-20 h-20 flex-shrink-0">
                                <svg class="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
                                    <path class="text-ivory-300" stroke-width="3" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                    <path class="text-gold-500 transition-all duration-500" :stroke-dasharray="progress + ', 100'" stroke-width="3.5" stroke-linecap="round" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                </svg>
                                <div class="absolute inset-0 flex items-center justify-center text-lg font-bold font-serif text-charcoal">
                                    {{ progress }}%
                                </div>
                            </div>
                            <div>
                                <p class="text-2xl font-extrabold font-serif text-charcoal">
                                    {{ done_checklists }} <span class="text-sm font-normal text-charcoal-400">dari {{ total_checklists }} tugas</span>
                                </p>
                                <p class="text-xs text-charcoal-400 mt-1">
                                    Selesaikan tugas checklist untuk menaikkan progres.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Budget Card -->
                    <div class="bg-white p-6 rounded-2xl shadow-xs border border-gold-200/80 transition hover:shadow-md duration-200 col-span-1 md:col-span-2">
                        <div class="flex justify-between items-center mb-4">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-charcoal-400">
                                Anggaran & Pengeluaran
                            </h4>
                            <span class="px-2.5 py-1 bg-gold-100 text-gold-800 rounded-lg text-xs font-bold">
                                Budget
                            </span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                            <div>
                                <p class="text-xs text-charcoal-400 font-medium">Total Anggaran</p>
                                <p class="text-lg font-bold text-charcoal font-serif mt-0.5">
                                    {{ formatCurrency(project.total_budget) }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-charcoal-400 font-medium">Terbayar (DP/Lunas)</p>
                                <p class="text-lg font-bold text-gold-600 font-serif mt-0.5">
                                    {{ formatCurrency(spent_budget) }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-charcoal-400 font-medium">Sisa Anggaran</p>
                                <p class="text-lg font-bold font-serif mt-0.5" :class="remainingBudget >= 0 ? 'text-charcoal' : 'text-rose-500'">
                                    {{ formatCurrency(remainingBudget) }}
                                </p>
                            </div>
                        </div>
                        <!-- Progress Bar Budget -->
                        <div class="relative pt-1">
                            <div class="flex mb-2 items-center justify-between">
                                <div>
                                    <span class="text-xs font-bold inline-block py-0.5 px-2 uppercase rounded-full text-gold-800 bg-gold-100">
                                        Anggaran Terpakai
                                    </span>
                                </div>
                                <div class="text-right">
                                    <span class="text-xs font-bold inline-block text-gold-600 font-serif">
                                        {{ budgetPercentage }}%
                                    </span>
                                </div>
                            </div>
                            <div class="overflow-hidden h-2.5 text-xs flex rounded-full bg-ivory-200">
                                <div :style="'width: ' + budgetPercentage + '%'" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-gold-500 transition-all duration-500"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Content Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Left: Navigation / Quick links -->
                    <div class="bg-white p-6 rounded-2xl shadow-xs border border-gold-200/80 col-span-1">
                        <h3 class="text-lg font-bold font-serif text-charcoal mb-4 border-b border-gold-100 pb-2">
                            Menu Aplikasi
                        </h3>
                        <div class="flex flex-col gap-1.5">
                            <Link :href="route('projects.dashboard', project.id)"
                                  class="px-4 py-2.5 bg-gold-50 text-gold-900 rounded-xl text-sm font-bold flex items-center justify-between transition border border-gold-300">
                                <span class="flex items-center gap-2.5">
                                    <span class="w-2 h-2 bg-gold-500 rounded-full"></span> Dashboard
                                </span>
                                <span class="text-xs text-gold-600 font-bold">Aktif</span>
                            </Link>

                            <Link :href="route('projects.checklists.index', project.id)"
                                  class="px-4 py-2.5 text-charcoal-600 hover:bg-blush-50 hover:text-charcoal rounded-xl text-sm font-medium transition flex items-center gap-2.5">
                                <span class="w-2 h-2 bg-charcoal-300 rounded-full"></span> Checklist Tugas
                            </Link>

                            <Link :href="route('projects.guide.show', project.id)"
                                  class="px-4 py-2.5 text-charcoal-600 hover:bg-blush-50 hover:text-charcoal rounded-xl text-sm font-medium transition flex items-center gap-2.5">
                                <span class="w-2 h-2 bg-charcoal-300 rounded-full"></span> Panduan Pernikahan
                            </Link>

                            <Link :href="route('projects.budget', project.id)"
                                  class="px-4 py-2.5 text-charcoal-600 hover:bg-blush-50 hover:text-charcoal rounded-xl text-sm font-medium transition flex items-center gap-2.5">
                                <span class="w-2 h-2 bg-charcoal-300 rounded-full"></span> Budget Tracker
                            </Link>

                            <Link :href="route('projects.vendors.index', project.id)"
                                  class="px-4 py-2.5 text-charcoal-600 hover:bg-blush-50 hover:text-charcoal rounded-xl text-sm font-medium transition flex items-center gap-2.5">
                                <span class="w-2 h-2 bg-charcoal-300 rounded-full"></span> Vendor Directory
                            </Link>

                            <Link :href="route('projects.guests.index', project.id)"
                                  class="px-4 py-2.5 text-charcoal-600 hover:bg-blush-50 hover:text-charcoal rounded-xl text-sm font-medium transition flex items-center gap-2.5">
                                <span class="w-2 h-2 bg-charcoal-300 rounded-full"></span> Tamu & RSVP
                            </Link>

                            <Link :href="route('projects.rundowns.index', project.id)"
                                  class="px-4 py-2.5 text-charcoal-600 hover:bg-blush-50 hover:text-charcoal rounded-xl text-sm font-medium transition flex items-center gap-2.5">
                                <span class="w-2 h-2 bg-charcoal-300 rounded-full"></span> Rundown Timeline
                            </Link>

                            <Link :href="route('projects.seserahan.index', project.id)"
                                  class="px-4 py-2.5 text-charcoal-600 hover:bg-blush-50 hover:text-charcoal rounded-xl text-sm font-medium transition flex items-center gap-2.5">
                                <span class="w-2 h-2 bg-charcoal-300 rounded-full"></span> Seserahan Item
                            </Link>

                            <Link :href="route('projects.invitation.edit', project.id)"
                                  class="px-4 py-2.5 text-charcoal-600 hover:bg-blush-50 hover:text-charcoal rounded-xl text-sm font-medium transition flex items-center gap-2.5">
                                <span class="w-2 h-2 bg-charcoal-300 rounded-full"></span> Desain Undangan
                            </Link>
                        </div>
                    </div>

                    <!-- Right: Upcoming Tasks Widget -->
                    <div class="bg-white p-6 rounded-2xl shadow-xs border border-gold-200/80 col-span-1 lg:col-span-2">
                        <div class="flex justify-between items-center mb-4 border-b border-gold-100 pb-2">
                            <div>
                                <h3 class="text-lg font-bold font-serif text-charcoal">
                                    Tugas Terdekat
                                </h3>
                                <p class="text-xs text-charcoal-400">Menampilkan 5 tugas pending teratas</p>
                            </div>
                            <Link :href="route('projects.checklists.index', project.id)"
                                  class="text-xs font-bold text-gold-600 hover:text-gold-700 hover:underline">
                                Buka Semua Checklist &rarr;
                            </Link>
                        </div>
                        <div v-if="upcoming_tasks.length > 0" class="divide-y divide-gold-100">
                            <div v-for="task in upcoming_tasks" :key="task.id" class="py-3 flex justify-between items-center">
                                <div class="flex items-center gap-3">
                                    <span class="w-2.5 h-2.5 rounded-full bg-gold-400"></span>
                                    <div>
                                        <p class="text-sm font-semibold text-charcoal">
                                            {{ task.title }}
                                        </p>
                                        <p class="text-xs text-charcoal-400 mt-0.5">
                                            Diserahkan kepada: {{ task.assigned_to || 'Tidak ada' }}
                                        </p>
                                    </div>
                                </div>
                                <span class="text-xs font-bold text-charcoal-500 uppercase bg-ivory px-2.5 py-1 rounded-lg border border-gold-200/60">
                                    {{ task.status }}
                                </span>
                            </div>
                        </div>
                        <div v-else class="text-center py-12">
                            <p class="text-charcoal-400">Tidak ada tugas terdekat. Semua selesai!</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
