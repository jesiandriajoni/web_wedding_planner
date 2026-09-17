<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    project: Object,
    rundowns: Array
});

// Form for CRUD rundown
const showAddModal = ref(false);
const editMode = ref(false);
const editingRundownId = ref(null);

const rundownForm = useForm({
    time: '',
    activity: '',
    description: '',
    assigned_to: ''
});

const openAddModal = () => {
    editMode.value = false;
    editingRundownId.value = null;
    rundownForm.reset();
    showAddModal.value = true;
};

const openEditModal = (item) => {
    editMode.value = true;
    editingRundownId.value = item.id;
    rundownForm.time = item.time;
    rundownForm.activity = item.activity;
    rundownForm.description = item.description;
    rundownForm.assigned_to = item.assigned_to;
    showAddModal.value = true;
};

const closeAddModal = () => {
    showAddModal.value = false;
    rundownForm.reset();
};

const submitRundown = () => {
    if (editMode.value) {
        rundownForm.put(route('projects.rundowns.update', {
            project: props.project.id,
            rundown: editingRundownId.value
        }), {
            onSuccess: () => closeAddModal(),
        });
    } else {
        rundownForm.post(route('projects.rundowns.store', props.project.id), {
            onSuccess: () => closeAddModal(),
        });
    }
};

const deleteRundown = (id) => {
    if (confirm('Yakin ingin menghapus rundown ini?')) {
        rundownForm.delete(route('projects.rundowns.destroy', {
            project: props.project.id,
            rundown: id
        }));
    }
};
</script>

<template>
    <Head :title="'Rundown Hari-H - ' + project.name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight text-slate-800 dark:text-slate-100">
                        Rundown & Timeline Pernikahan
                    </h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                        Atur agenda menit demi menit hari pernikahan dan cetak PDF rundown
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <a :href="route('projects.rundown.pdf', project.id)" target="_blank"
                       class="px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 rounded-lg text-sm font-semibold transition duration-150">
                        📄 Ekspor PDF Rundown
                    </a>
                    <button @click="openAddModal"
                            class="px-4 py-2 bg-forest-600 hover:bg-forest-700 text-white rounded-lg shadow-sm text-sm font-semibold transition duration-150">
                        Tambah Rundown
                    </button>
                </div>
            </div>
        </template>

        <div class="py-12 bg-slate-50 dark:bg-slate-900 min-h-screen">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                
                <!-- Rundown Timeline Board -->
                <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 mb-6 border-b pb-2">
                        Timeline Hari Pernikahan
                    </h3>
                    
                    <div v-if="rundowns.length > 0" class="flex flex-col gap-8 relative before:absolute before:left-4 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-100 dark:before:bg-slate-700">
                        <div v-for="item in rundowns" :key="item.id" class="flex gap-6 items-start relative pl-10">
                            <!-- Bullet marker -->
                            <span class="absolute left-2.5 w-3.5 h-3.5 rounded-full bg-forest-600 dark:bg-forest-400 border-4 border-white dark:border-slate-800 shadow-sm"></span>
                            
                            <div class="flex-1 bg-slate-50 dark:bg-slate-900/50 p-4 rounded-xl border border-slate-100 dark:border-slate-700 flex justify-between items-start gap-4">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-extrabold text-forest-600 dark:text-forest-400">
                                            {{ item.time }} WIB
                                        </span>
                                        <span class="text-xs text-slate-400">| Penanggung jawab: {{ item.assigned_to || '-' }}</span>
                                    </div>
                                    <h4 class="text-base font-bold text-slate-800 dark:text-slate-100 mt-1">
                                        {{ item.activity }}
                                    </h4>
                                    <p v-if="item.description" class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                                        {{ item.description }}
                                    </p>
                                </div>
                                <div class="flex gap-2">
                                    <button @click="openEditModal(item)"
                                            class="text-xs bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-300 px-2 py-1 rounded transition">
                                        Edit
                                    </button>
                                    <button @click="deleteRundown(item.id)"
                                            class="text-xs bg-rose-50 hover:bg-rose-100 text-rose-600 px-2 py-1 rounded transition">
                                        Hapus
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div v-else class="text-center py-12">
                        <p class="text-slate-400 dark:text-slate-500 italic">Belum ada rundown yang dibuat. Mulai tambahkan agenda hari-H pernikahan Anda.</p>
                    </div>
                </div>

            </div>
        </div>

        <!-- Add/Edit Rundown Modal Overlay -->
        <div v-if="showAddModal" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm transition duration-300">
            <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-md w-full shadow-2xl overflow-hidden border border-slate-100 dark:border-slate-700">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100">
                        {{ editMode ? 'Edit Rundown' : 'Tambah Rundown Baru' }}
                    </h3>
                    <button @click="closeAddModal" class="text-slate-400 hover:text-slate-600">&times;</button>
                </div>
                <form @submit.prevent="submitRundown" class="p-6 flex flex-col gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">
                            Waktu Acara (Format: HH:MM, Contoh: 08:00)
                        </label>
                        <input type="text" v-model="rundownForm.time" required placeholder="08:00" pattern="[0-9]{2}:[0-9]{2}"
                               class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 p-3 text-slate-800 dark:text-slate-100 focus:ring-sage-500 focus:border-sage-500" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Nama Aktivitas</label>
                        <input type="text" v-model="rundownForm.activity" required placeholder="Contoh: Akad Nikah, Resepsi, Foto Bersama"
                               class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 p-3 text-slate-800 dark:text-slate-100 focus:ring-sage-500 focus:border-sage-500" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Penanggung Jawab (PIC)</label>
                        <input type="text" v-model="rundownForm.assigned_to" placeholder="Contoh: WO Team, Bapak Joko, Catering PIC"
                               class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 p-3 text-slate-800 dark:text-slate-100 focus:ring-sage-500 focus:border-sage-500" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Detail / Keterangan</label>
                        <textarea v-model="rundownForm.description" rows="3" placeholder="Deskripsikan detil aktivitas..."
                                  class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 p-3 text-slate-800 dark:text-slate-100 focus:ring-sage-500 focus:border-sage-500"></textarea>
                    </div>
                    <div class="flex justify-end gap-2 mt-4">
                        <button type="button" @click="closeAddModal"
                                class="px-4 py-2 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg text-sm font-semibold transition">
                            Batal
                        </button>
                        <button type="submit" :disabled="rundownForm.processing"
                                class="px-4 py-2 bg-forest-600 hover:bg-forest-700 text-white rounded-lg text-sm font-semibold transition shadow-sm">
                            {{ editMode ? 'Simpan Perubahan' : 'Tambah Rundown' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
