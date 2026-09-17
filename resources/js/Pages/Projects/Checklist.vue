<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    project: Object,
    checklists: Array,
});

const roleOptions = [
    { v: '', l: '— (tanpa penanggung jawab)' },
    { v: 'wo', l: 'WO' },
    { v: 'pengantin', l: 'Pengantin' },
    { v: 'keluarga', l: 'Keluarga' },
];
const roleLabel = (v) => ({ wo: 'WO', pengantin: 'Pengantin', keluarga: 'Keluarga' })[v] || 'Tidak ada';

const doneCount = computed(() => props.checklists.filter((c) => c.status === 'done').length);
const progress = computed(() =>
    props.checklists.length ? Math.round((doneCount.value / props.checklists.length) * 100) : 0
);

const routeArgs = (checklist) => ({ project: props.project.id, checklist: checklist.id });

const toggle = (item) => {
    router.put(route('projects.checklists.update', routeArgs(item)), {
        title: item.title,
        assigned_to: item.assigned_to || '',
        status: item.status === 'done' ? 'pending' : 'done',
    }, { preserveScroll: true });
};

const deleteTask = (item) => {
    if (confirm('Hapus tugas ini?')) {
        router.delete(route('projects.checklists.destroy', routeArgs(item)), { preserveScroll: true });
    }
};

// Add form
const addForm = useForm({ title: '', assigned_to: '' });
const submitAdd = () => addForm.post(route('projects.checklists.store', props.project.id), {
    preserveScroll: true,
    onSuccess: () => addForm.reset(),
});

// Edit modal
const showEdit = ref(false);
const editForm = useForm({ id: null, title: '', assigned_to: '', status: 'pending' });
const openEdit = (item) => {
    editForm.id = item.id;
    editForm.title = item.title;
    editForm.assigned_to = item.assigned_to || '';
    editForm.status = item.status;
    showEdit.value = true;
};
const submitEdit = () => editForm.put(
    route('projects.checklists.update', { project: props.project.id, checklist: editForm.id }),
    { preserveScroll: true, onSuccess: () => (showEdit.value = false) }
);

const inputClass =
    'w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 p-3 text-slate-800 dark:text-slate-100 focus:ring-sage-500 focus:border-sage-500';
</script>

<template>
    <Head :title="'Checklist - ' + project.name" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl font-bold tracking-tight text-slate-800 dark:text-slate-100">Checklist Persiapan</h2>
        </template>

        <div class="py-12 bg-slate-50 dark:bg-slate-900 min-h-screen">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 flex flex-col gap-6">

                <!-- Progress -->
                <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm font-semibold text-slate-600 dark:text-slate-300">Progres</span>
                        <span class="text-sm font-bold text-forest-600 dark:text-forest-400">{{ doneCount }} / {{ checklists.length }} tugas ({{ progress }}%)</span>
                    </div>
                    <div class="h-3 rounded-full bg-slate-200 dark:bg-slate-700 overflow-hidden">
                        <div class="h-full bg-forest-600 dark:bg-forest-400 transition-all duration-500" :style="'width:' + progress + '%'"></div>
                    </div>
                </div>

                <!-- Add form -->
                <form @submit.prevent="submitAdd" class="bg-white dark:bg-slate-800 p-4 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 flex flex-col sm:flex-row gap-3">
                    <input type="text" v-model="addForm.title" required placeholder="Tugas yang akan dikerjakan..." :class="inputClass" />
                    <select v-model="addForm.assigned_to" :class="inputClass + ' sm:w-56'">
                        <option v-for="o in roleOptions" :key="o.v" :value="o.v">{{ o.l }}</option>
                    </select>
                    <button type="submit" :disabled="addForm.processing"
                            class="shrink-0 px-5 py-3 bg-forest-600 hover:bg-forest-700 text-white rounded-xl text-sm font-semibold transition">Tambah</button>
                </form>

                <!-- List -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 divide-y divide-slate-100 dark:divide-slate-700">
                    <div v-for="item in checklists" :key="item.id" class="flex items-center gap-3 p-4">
                        <input type="checkbox" :checked="item.status === 'done'" @change="toggle(item)"
                               class="w-5 h-5 rounded border-slate-300 text-forest-600 focus:ring-forest-500 cursor-pointer" />
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-slate-800 dark:text-slate-100"
                               :class="{ 'line-through text-slate-400 dark:text-slate-500': item.status === 'done' }">
                                {{ item.title }}
                            </p>
                            <p class="text-xs text-slate-400 mt-0.5">PJ: {{ roleLabel(item.assigned_to) }}</p>
                        </div>
                        <button @click="openEdit(item)" class="text-xs text-slate-400 hover:text-slate-600">Edit</button>
                        <button @click="deleteTask(item)" class="text-xs font-bold text-rose-500 hover:text-rose-700">Hapus</button>
                    </div>
                    <div v-if="checklists.length === 0" class="p-8 text-center text-sm text-slate-400">
                        Belum ada tugas. Tambahkan tugas persiapan di atas.
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit modal -->
        <div v-if="showEdit" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
            <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-md w-full shadow-2xl border border-slate-100 dark:border-slate-700">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100">Edit Tugas</h3>
                    <button @click="showEdit = false" class="text-slate-400 hover:text-slate-600">&times;</button>
                </div>
                <form @submit.prevent="submitEdit" class="p-6 flex flex-col gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Nama Tugas</label>
                        <input type="text" v-model="editForm.title" required :class="inputClass" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Penanggung Jawab</label>
                        <select v-model="editForm.assigned_to" :class="inputClass">
                            <option v-for="o in roleOptions" :key="o.v" :value="o.v">{{ o.l }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Status</label>
                        <select v-model="editForm.status" :class="inputClass">
                            <option value="pending">Pending</option>
                            <option value="done">Selesai</option>
                        </select>
                    </div>
                    <div class="flex justify-end gap-2 mt-2">
                        <button type="button" @click="showEdit = false"
                                class="px-4 py-2 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg text-sm font-semibold">Batal</button>
                        <button type="submit" :disabled="editForm.processing"
                                class="px-4 py-2 bg-forest-600 hover:bg-forest-700 text-white rounded-lg text-sm font-semibold">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
