<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    users: Array
});

const searchQuery = ref('');
const statusFilter = ref('all');
const loadingId = ref(null);

const filteredUsers = computed(() => {
    return props.users.filter(u => {
        const matchesSearch = u.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                              u.email.toLowerCase().includes(searchQuery.value.toLowerCase());
        if (statusFilter.value === 'active') return matchesSearch && u.is_active;
        if (statusFilter.value === 'pending') return matchesSearch && !u.is_active;
        return matchesSearch;
    });
});

const totalActive = computed(() => props.users.filter(u => u.is_active).length);
const totalPending = computed(() => props.users.filter(u => !u.is_active).length);

const toggleStatus = (user) => {
    loadingId.value = user.id;
    router.patch(route('admin.users.toggle_status', user.id), {}, {
        preserveScroll: true,
        onFinish: () => {
            loadingId.value = null;
        }
    });
};
</script>

<template>
    <Head title="Verifikasi & Kelola User - Admin Panel" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-3xl font-bold font-serif text-charcoal tracking-tight">
                        Verifikasi Akun Pengantin
                    </h2>
                    <p class="text-sm text-charcoal-400 mt-1">
                        Aktifkan akun user pengantin baru menggunakan tombol sliding bar agar dapat masuk ke sistem.
                    </p>
                </div>
            </div>
        </template>

        <div class="py-8 bg-ivory min-h-screen">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 space-y-6">
                
                <!-- Flash Notification -->
                <div v-if="$page.props.flash?.success"
                     class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold flex items-center gap-3 shadow-xs">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ $page.props.flash.success }}</span>
                </div>

                <!-- Stats Overview Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="bg-white p-5 rounded-2xl border border-gold-200 shadow-xs flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-charcoal-400">Total Pengantin</p>
                            <p class="text-2xl font-bold font-serif text-charcoal mt-1">{{ users.length }}</p>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-gold-50 flex items-center justify-center text-gold-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                    </div>

                    <div class="bg-white p-5 rounded-2xl border border-emerald-200 shadow-xs flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-emerald-600">Akun Aktif</p>
                            <p class="text-2xl font-bold font-serif text-emerald-700 mt-1">{{ totalActive }}</p>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                    </div>

                    <div class="bg-white p-5 rounded-2xl border border-amber-200 shadow-xs flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-amber-600">Menunggu Verifikasi</p>
                            <p class="text-2xl font-bold font-serif text-amber-700 mt-1">{{ totalPending }}</p>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center text-amber-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Controls (Search & Filter) -->
                <div class="bg-white p-4 rounded-2xl border border-gold-200 shadow-xs flex flex-col sm:flex-row gap-3 justify-between items-center">
                    <div class="relative w-full sm:w-80">
                        <input
                            type="text"
                            v-model="searchQuery"
                            placeholder="Cari nama atau email..."
                            class="w-full pl-9 pr-4 py-2 bg-ivory border border-gold-200 rounded-xl text-sm text-charcoal placeholder-charcoal-300 focus:ring-2 focus:ring-gold-400 focus:border-gold-500 transition"
                        />
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-charcoal-400 absolute left-3 top-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>

                    <div class="flex items-center gap-1.5 w-full sm:w-auto overflow-x-auto">
                        <button
                            @click="statusFilter = 'all'"
                            :class="statusFilter === 'all' ? 'bg-gold-500 text-white font-bold' : 'bg-ivory text-charcoal-500 hover:bg-gold-50'"
                            class="px-3.5 py-1.5 rounded-xl text-xs transition cursor-pointer"
                        >
                            Semua ({{ users.length }})
                        </button>
                        <button
                            @click="statusFilter = 'pending'"
                            :class="statusFilter === 'pending' ? 'bg-amber-500 text-white font-bold' : 'bg-ivory text-charcoal-500 hover:bg-gold-50'"
                            class="px-3.5 py-1.5 rounded-xl text-xs transition cursor-pointer"
                        >
                            Menunggu Verifikasi ({{ totalPending }})
                        </button>
                        <button
                            @click="statusFilter = 'active'"
                            :class="statusFilter === 'active' ? 'bg-emerald-600 text-white font-bold' : 'bg-ivory text-charcoal-500 hover:bg-gold-50'"
                            class="px-3.5 py-1.5 rounded-xl text-xs transition cursor-pointer"
                        >
                            Aktif ({{ totalActive }})
                        </button>
                    </div>
                </div>

                <!-- Users Table Card -->
                <div class="bg-white rounded-2xl shadow-xs border border-gold-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-ivory border-b border-gold-100">
                                    <th class="p-4 text-xs font-bold text-charcoal-400 uppercase tracking-wider">Pengantin</th>
                                    <th class="p-4 text-xs font-bold text-charcoal-400 uppercase tracking-wider">Email Akun</th>
                                    <th class="p-4 text-xs font-bold text-charcoal-400 uppercase tracking-wider">Terdaftar Pada</th>
                                    <th class="p-4 text-xs font-bold text-charcoal-400 uppercase tracking-wider">Status</th>
                                    <th class="p-4 text-xs font-bold text-charcoal-400 uppercase tracking-wider text-right">Aktivasi (Sliding Bar)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gold-100">
                                <tr v-for="u in filteredUsers" :key="u.id" class="hover:bg-ivory/60 transition">
                                    <td class="p-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-full bg-gold-100 text-gold-700 flex items-center justify-center font-serif font-bold text-sm">
                                                {{ u.name.charAt(0).toUpperCase() }}
                                            </div>
                                            <div>
                                                <p class="font-bold text-charcoal text-sm">{{ u.name }}</p>
                                                <span class="text-[11px] text-charcoal-400 capitalize">Role: {{ u.role }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-4 text-charcoal-600 text-sm font-medium">
                                        {{ u.email }}
                                    </td>
                                    <td class="p-4 text-xs text-charcoal-400">
                                        {{ new Date(u.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) }}
                                    </td>
                                    <td class="p-4">
                                        <span v-if="u.is_active"
                                              class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                            Aktif / Diverifikasi
                                        </span>
                                        <span v-else
                                              class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200 rounded-full">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                            Menunggu Verifikasi
                                        </span>
                                    </td>
                                    <td class="p-4 text-right">
                                        <!-- Sliding Bar Toggle Switch Button -->
                                        <div class="inline-flex items-center gap-3">
                                            <span class="text-xs font-medium text-charcoal-400 hidden sm:inline">
                                                {{ u.is_active ? 'Aktif' : 'Nonaktif' }}
                                            </span>
                                            <button
                                                type="button"
                                                role="switch"
                                                :aria-checked="u.is_active"
                                                :disabled="loadingId === u.id"
                                                @click="toggleStatus(u)"
                                                :title="u.is_active ? 'Klik untuk nonaktifkan akun' : 'Klik untuk verifikasi & aktifkan akun'"
                                                :class="[
                                                    u.is_active ? 'bg-emerald-500 hover:bg-emerald-600' : 'bg-slate-300 hover:bg-slate-400',
                                                    loadingId === u.id ? 'opacity-50 cursor-wait' : 'cursor-pointer'
                                                ]"
                                                class="relative inline-flex h-7 w-13 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-gold-400 focus:ring-offset-2"
                                            >
                                                <!-- Sliding knob / thumb -->
                                                <span
                                                    :class="u.is_active ? 'translate-x-6' : 'translate-x-0'"
                                                    class="pointer-events-none relative inline-block h-6 w-6 transform rounded-full bg-white shadow-md ring-0 transition duration-200 ease-in-out flex items-center justify-center"
                                                >
                                                    <svg v-if="loadingId === u.id" class="animate-spin h-3.5 w-3.5 text-charcoal-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                    </svg>
                                                    <svg v-else-if="u.is_active" class="h-3.5 w-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                    <svg v-else class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="filteredUsers.length === 0">
                                    <td colspan="5" class="p-12 text-center text-charcoal-400 text-sm">
                                        <div class="flex flex-col items-center justify-center gap-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-charcoal-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                            </svg>
                                            <p class="font-medium">Tidak ada akun user pengantin yang cocok dengan filter.</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
