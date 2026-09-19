<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    projects: Array,
    isAdmin: Boolean
});

const showAddModal = ref(false);
const formattedBudget = ref('');

const formatCurrency = (val) =>
    new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val || 0);

const projectForm = useForm({
    name: '',
    wedding_date: '',
    total_budget: '',
    pengantin_name: '',
    pengantin_email: '',
    pengantin_password: ''
});

const onBudgetInput = (e) => {
    const el = e.target;
    const oldVal = el.value;
    const oldSelection = el.selectionEnd || 0;
    const digitsBeforeCursor = oldVal.slice(0, oldSelection).replace(/\D/g, '').length;

    const raw = oldVal.replace(/\D/g, '');
    if (!raw) {
        formattedBudget.value = '';
        projectForm.total_budget = '';
        return;
    }

    const numericVal = parseInt(raw, 10);
    projectForm.total_budget = numericVal;
    const formatted = new Intl.NumberFormat('id-ID').format(numericVal);
    formattedBudget.value = formatted;

    requestAnimationFrame(() => {
        let newPos = formatted.length;
        let digitsCounted = 0;
        for (let i = 0; i < formatted.length; i++) {
            if (/\d/.test(formatted[i])) {
                digitsCounted++;
            }
            if (digitsCounted === digitsBeforeCursor) {
                newPos = i + 1;
                break;
            }
        }
        el.setSelectionRange(newPos, newPos);
    });
};

const openAddModal = () => {
    projectForm.reset();
    projectForm.clearErrors();
    formattedBudget.value = '';
    showAddModal.value = true;
};

const closeAddModal = () => {
    showAddModal.value = false;
    formattedBudget.value = '';
    projectForm.reset();
    projectForm.clearErrors();
};

const submitProject = () => {
    projectForm.post(route('projects.store'), {
        onSuccess: () => closeAddModal()
    });
};
</script>

<template>
    <Head title="Daftar Pernikahan" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-3xl font-bold font-serif text-charcoal tracking-tight">
                        Daftar Pernikahan
                    </h2>
                    <p class="text-sm text-charcoal-400 mt-1">
                        Pilih project pernikahan untuk membuka dashboard dan mengelola seluruh persiapan.
                    </p>
                </div>
                <button v-if="isAdmin" @click="openAddModal"
                        class="px-5 py-2.5 bg-gold-500 hover:bg-gold-600 active:bg-gold-700 text-white rounded-xl shadow-xs text-sm font-bold tracking-wide transition duration-150 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Buat Project Baru
                </button>
            </div>
        </template>

        <div class="py-10 bg-ivory min-h-screen">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                
                <!-- Project List Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div v-for="project in projects" :key="project.id"
                         class="bg-white p-7 rounded-3xl shadow-xs border border-gold-200/80 flex flex-col justify-between hover:shadow-md hover:border-gold-300 transition duration-200 group">
                        <div>
                            <div class="flex items-center justify-between gap-2 mb-3">
                                <span class="px-3 py-1 rounded-full bg-blush-100 text-blush-800 text-xs font-bold font-sans uppercase tracking-wider">
                                    Wedding Project
                                </span>
                                <span class="text-xs font-semibold text-charcoal-400">
                                    {{ new Date(project.wedding_date).toLocaleDateString('id-ID', { dateStyle: 'medium' }) }}
                                </span>
                            </div>
                            <h3 class="text-2xl font-bold font-serif text-charcoal group-hover:text-gold-700 transition">
                                {{ project.name }}
                            </h3>
                            <p class="text-xs text-charcoal-400 mt-1">
                                Tanggal Acara: {{ new Date(project.wedding_date).toLocaleDateString('id-ID', { dateStyle: 'long' }) }}
                            </p>
                            <div class="mt-5 p-3.5 rounded-xl bg-ivory border border-gold-100">
                                <span class="text-[11px] font-bold text-charcoal-400 uppercase tracking-wider block">Total Anggaran</span>
                                <span class="text-lg font-bold font-serif text-gold-600 block mt-0.5">
                                    {{ formatCurrency(project.total_budget) }}
                                </span>
                            </div>
                        </div>
                        <div v-if="!isAdmin" class="mt-6 flex justify-end">
                            <Link :href="route('projects.dashboard', project.id)"
                                  class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-gold-500 hover:bg-gold-600 active:bg-gold-700 text-white rounded-xl text-xs font-bold tracking-wider uppercase transition shadow-xs">
                                <span>Buka Dashboard</span>
                                <span>&rarr;</span>
                            </Link>
                        </div>
                    </div>
                </div>

                <div v-if="projects.length === 0" class="text-center py-16 bg-white rounded-3xl border border-dashed border-gold-200 shadow-xs">
                    <p class="text-charcoal-400 font-serif text-lg">Belum ada project pernikahan.</p>
                    <p class="text-xs text-charcoal-300 mt-1">Silakan buat project baru untuk mulai merencanakan pernikahan!</p>
                </div>

            </div>
        </div>

        <!-- Add Project Modal -->
        <div v-if="showAddModal" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs transition duration-300">
            <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl overflow-hidden border border-gold-200">
                <div class="px-6 py-4 border-b border-gold-100 flex justify-between items-center bg-ivory">
                    <h3 class="text-lg font-bold font-serif text-charcoal">
                        Buat Project Pernikahan Baru
                    </h3>
                    <button @click="closeAddModal" class="text-charcoal-400 hover:text-charcoal text-2xl leading-none">&times;</button>
                </div>
                <form @submit.prevent="submitProject" class="p-6 flex flex-col gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-charcoal-500 mb-1">Nama Pasangan / Judul Project</label>
                        <input type="text" v-model="projectForm.name" required placeholder="Contoh: Romeo & Juliet Wedding"
                               :class="{'border-rose-400 focus:ring-rose-400': projectForm.errors.name}"
                               class="w-full rounded-xl border border-gold-200 bg-ivory p-3 text-charcoal focus:ring-gold-400 focus:border-gold-500 text-sm shadow-xs" />
                        <p v-if="projectForm.errors.name" class="mt-1 text-xs text-rose-500 font-semibold">{{ projectForm.errors.name }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-charcoal-500 mb-1">Tanggal Pernikahan</label>
                        <input type="date" v-model="projectForm.wedding_date" required
                               :class="{'border-rose-400 focus:ring-rose-400': projectForm.errors.wedding_date}"
                               class="w-full rounded-xl border border-gold-200 bg-ivory p-3 text-charcoal focus:ring-gold-400 focus:border-gold-500 text-sm shadow-xs" />
                        <p v-if="projectForm.errors.wedding_date" class="mt-1 text-xs text-rose-500 font-semibold">{{ projectForm.errors.wedding_date }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-charcoal-500 mb-1">Total Anggaran (Rp)</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-xs font-bold text-charcoal-400 select-none">Rp</span>
                            <input type="text"
                                   inputmode="numeric"
                                   :value="formattedBudget"
                                   @input="onBudgetInput"
                                   required
                                   placeholder="100.000.000"
                                   :class="{'border-rose-400 focus:ring-rose-400': projectForm.errors.total_budget}"
                                   class="w-full rounded-xl border border-gold-200 bg-ivory p-3 pl-10 text-charcoal focus:ring-gold-400 focus:border-gold-500 text-sm shadow-xs font-medium" />
                        </div>
                        <p v-if="projectForm.errors.total_budget" class="mt-1 text-xs text-rose-500 font-semibold">{{ projectForm.errors.total_budget }}</p>
                        <p v-else-if="projectForm.total_budget" class="text-xs font-semibold text-gold-700 mt-1">
                            {{ formatCurrency(projectForm.total_budget) }}
                        </p>
                    </div>
                    <div class="border-t border-gold-100 pt-3">
                        <p class="text-xs font-bold text-gold-700 uppercase tracking-wider mb-2">Akun Calon Pengantin (Opsional)</p>
                        <div class="flex flex-col gap-3">
                            <div>
                                <input type="text" v-model="projectForm.pengantin_name" placeholder="Nama Akun Pengantin"
                                       :class="{'border-rose-400 focus:ring-rose-400': projectForm.errors.pengantin_name}"
                                       class="w-full rounded-xl border border-gold-200 bg-ivory p-2.5 text-charcoal focus:ring-gold-400 focus:border-gold-500 text-sm shadow-xs" />
                                <p v-if="projectForm.errors.pengantin_name" class="mt-1 text-xs text-rose-500 font-semibold">{{ projectForm.errors.pengantin_name }}</p>
                            </div>
                            <div>
                                <input type="email" v-model="projectForm.pengantin_email" placeholder="Email Pengantin"
                                       :class="{'border-rose-400 focus:ring-rose-400': projectForm.errors.pengantin_email}"
                                       class="w-full rounded-xl border border-gold-200 bg-ivory p-2.5 text-charcoal focus:ring-gold-400 focus:border-gold-500 text-sm shadow-xs" />
                                <p v-if="projectForm.errors.pengantin_email" class="mt-1 text-xs text-rose-500 font-semibold">{{ projectForm.errors.pengantin_email }}</p>
                            </div>
                            <div>
                                <input type="password" v-model="projectForm.pengantin_password" placeholder="Password Pengantin (Min. 8 Karakter)"
                                       :class="{'border-rose-400 focus:ring-rose-400': projectForm.errors.pengantin_password}"
                                       class="w-full rounded-xl border border-gold-200 bg-ivory p-2.5 text-charcoal focus:ring-gold-400 focus:border-gold-500 text-sm shadow-xs" />
                                <p v-if="projectForm.errors.pengantin_password" class="mt-1 text-xs text-rose-500 font-semibold">{{ projectForm.errors.pengantin_password }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 mt-4 pt-2 border-t border-gold-100">
                        <button type="button" @click="closeAddModal"
                                class="px-4 py-2 text-charcoal-500 hover:bg-ivory rounded-xl text-sm font-semibold transition">
                            Batal
                        </button>
                        <button type="submit" :disabled="projectForm.processing"
                                class="px-5 py-2.5 bg-gold-500 hover:bg-gold-600 text-white rounded-xl text-sm font-bold tracking-wide transition shadow-xs disabled:opacity-50">
                            Simpan Project
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
