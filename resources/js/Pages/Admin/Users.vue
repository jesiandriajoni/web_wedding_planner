<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    users: Array
});
</script>

<template>
    <Head title="Kelola User - Admin Panel" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-2xl font-bold tracking-tight text-slate-800 dark:text-slate-100">
                    Kelola User Pengantin
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Daftar akun pengantin. Buat akun baru lewat menu Buat Project.
                </p>
            </div>
        </template>

        <div class="py-12 bg-slate-50 dark:bg-slate-900 min-h-screen">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                
                <!-- Users Table Card -->
                <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-800/80 border-b border-slate-100 dark:border-slate-700">
                                <th class="p-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Nama</th>
                                <th class="p-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Email</th>
                                <th class="p-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Role</th>
                                <th class="p-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Tanggal Dibuat</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                            <tr v-for="u in users" :key="u.id" class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition">
                                <td class="p-4 font-bold text-slate-800 dark:text-slate-100">{{ u.name }}</td>
                                <td class="p-4 text-slate-500 dark:text-slate-400">{{ u.email }}</td>
                                <td class="p-4">
                                    <span class="px-2.5 py-1 text-xs font-bold bg-sage-50 text-forest-700 dark:bg-forest-950/30 dark:text-forest-400 rounded-full">
                                        {{ u.role }}
                                    </span>
                                </td>
                                <td class="p-4 text-xs text-slate-400">
                                    {{ new Date(u.created_at).toLocaleDateString('id-ID', { dateStyle: 'medium' }) }}
                                </td>
                            </tr>
                            <tr v-if="users.length === 0">
                                <td colspan="4" class="p-8 text-center text-sm text-slate-400">
                                    Belum ada user pengantin yang didaftarkan.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
