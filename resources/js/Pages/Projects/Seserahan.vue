<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    project: Object,
    items: Array
});

// Form for CRUD seserahan
const showAddModal = ref(false);
const editMode = ref(false);
const editingItemId = ref(null);

const itemForm = useForm({
    name: '',
    status: 'pending',
    tracking_url: ''
});

const openAddModal = () => {
    editMode.value = false;
    editingItemId.value = null;
    itemForm.reset();
    showAddModal.value = true;
};

const openEditModal = (item) => {
    editMode.value = true;
    editingItemId.value = item.id;
    itemForm.name = item.item_name;
    itemForm.status = item.status;
    itemForm.tracking_url = item.tracking_url || '';
    showAddModal.value = true;
};

const closeAddModal = () => {
    showAddModal.value = false;
    itemForm.reset();
};

const submitItem = () => {
    if (editMode.value) {
        itemForm.put(route('projects.seserahan.update', {
            project: props.project.id,
            seserahan: editingItemId.value
        }), {
            onSuccess: () => closeAddModal(),
        });
    } else {
        itemForm.post(route('projects.seserahan.store', props.project.id), {
            onSuccess: () => closeAddModal(),
        });
    }
};

const deleteItem = (id) => {
    if (confirm('Yakin ingin menghapus item seserahan ini?')) {
        itemForm.delete(route('projects.seserahan.destroy', {
            project: props.project.id,
            seserahan: id
        }));
    }
};

const moveStatus = (item, newStatus) => {
    const tempForm = useForm({
        name: item.item_name,
        status: newStatus,
        tracking_url: item.tracking_url || ''
    });
    tempForm.put(route('projects.seserahan.update', {
        project: props.project.id,
        seserahan: item.id
    }));
};

// Drag & drop between status columns (native HTML5 DnD, no deps)
// ponytail: no touch support; add a lib (SortableJS) only if mobile drag is needed
const draggedItem = ref(null);

const onDrop = (newStatus) => {
    const item = draggedItem.value;
    draggedItem.value = null;
    if (item && item.status !== newStatus) {
        moveStatus(item, newStatus);
    }
};

// Filter items by status
const pendingItems = computed(() => props.items.filter(i => i.status === 'pending'));
const purchasedItems = computed(() => props.items.filter(i => i.status === 'purchased'));
const deliveredItems = computed(() => props.items.filter(i => i.status === 'delivered'));
const returnedItems = computed(() => props.items.filter(i => i.status === 'returned'));
</script>

<template>
    <Head :title="'Seserahan Logistics - ' + project.name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight text-slate-800 dark:text-slate-100">
                        Seserahan Logistics Board
                    </h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                        Lacak persiapan barang hantaran dan link pengiriman kurir eksternal
                    </p>
                </div>
                <button @click="openAddModal"
                        class="px-4 py-2 bg-forest-600 hover:bg-forest-700 text-white rounded-lg shadow-sm text-sm font-semibold transition duration-150">
                    Tambah Barang Seserahan
                </button>
            </div>
        </template>

        <div class="py-12 bg-slate-50 dark:bg-slate-900 min-h-screen">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                
                <!-- Kanban Board Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    
                    <!-- Pending Column -->
                    <div @dragover.prevent @drop="onDrop('pending')"
                         class="bg-slate-100 dark:bg-slate-800/50 p-4 rounded-2xl flex flex-col gap-4 min-h-[500px]">
                        <div class="flex justify-between items-center pb-2 border-b border-slate-200 dark:border-slate-700">
                            <h3 class="font-bold text-slate-700 dark:text-slate-200 flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-slate-400"></span>
                                Rencana (Pending)
                            </h3>
                            <span class="bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs px-2 py-0.5 rounded-full font-bold">
                                {{ pendingItems.length }}
                            </span>
                        </div>
                        <div class="flex flex-col gap-3 overflow-y-auto">
                            <div v-for="item in pendingItems" :key="item.id" draggable="true" @dragstart="draggedItem = item" class="bg-white dark:bg-slate-800 p-4 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700 flex flex-col gap-3 cursor-grab active:cursor-grabbing">
                                <h4 class="font-bold text-slate-800 dark:text-slate-100">{{ item.item_name }}</h4>
                                <div class="flex justify-between items-center border-t border-slate-50 dark:border-slate-700/50 pt-2.5">
                                    <button @click="openEditModal(item)" class="text-xs text-slate-400 hover:text-slate-600">Edit</button>
                                    <button @click="moveStatus(item, 'purchased')" class="text-xs font-bold text-forest-600 hover:text-forest-800">Beli &rarr;</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Purchased Column -->
                    <div @dragover.prevent @drop="onDrop('purchased')"
                         class="bg-slate-100 dark:bg-slate-800/50 p-4 rounded-2xl flex flex-col gap-4 min-h-[500px]">
                        <div class="flex justify-between items-center pb-2 border-b border-slate-200 dark:border-slate-700">
                            <h3 class="font-bold text-slate-700 dark:text-slate-200 flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
                                Dibeli (Purchased)
                            </h3>
                            <span class="bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs px-2 py-0.5 rounded-full font-bold">
                                {{ purchasedItems.length }}
                            </span>
                        </div>
                        <div class="flex flex-col gap-3 overflow-y-auto">
                            <div v-for="item in purchasedItems" :key="item.id" draggable="true" @dragstart="draggedItem = item" class="bg-white dark:bg-slate-800 p-4 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700 flex flex-col gap-3 cursor-grab active:cursor-grabbing">
                                <h4 class="font-bold text-slate-800 dark:text-slate-100">{{ item.item_name }}</h4>
                                
                                <div v-if="item.tracking_url" class="text-xs">
                                    <a :href="item.tracking_url" target="_blank" class="text-forest-600 dark:text-forest-400 hover:underline">
                                        📦 Lacak Kurir Pengiriman
                                    </a>
                                </div>

                                <div class="flex justify-between items-center border-t border-slate-50 dark:border-slate-700/50 pt-2.5">
                                    <button @click="moveStatus(item, 'pending')" class="text-xs text-slate-400 hover:text-slate-600">&larr; Rencana</button>
                                    <button @click="openEditModal(item)" class="text-xs text-slate-400 hover:text-slate-600">Edit</button>
                                    <button @click="moveStatus(item, 'delivered')" class="text-xs font-bold text-forest-600 hover:text-forest-800">Kirim &rarr;</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Delivered Column -->
                    <div @dragover.prevent @drop="onDrop('delivered')"
                         class="bg-slate-100 dark:bg-slate-800/50 p-4 rounded-2xl flex flex-col gap-4 min-h-[500px]">
                        <div class="flex justify-between items-center pb-2 border-b border-slate-200 dark:border-slate-700">
                            <h3 class="font-bold text-slate-700 dark:text-slate-200 flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                                Diterima (Delivered)
                            </h3>
                            <span class="bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs px-2 py-0.5 rounded-full font-bold">
                                {{ deliveredItems.length }}
                            </span>
                        </div>
                        <div class="flex flex-col gap-3 overflow-y-auto">
                            <div v-for="item in deliveredItems" :key="item.id" draggable="true" @dragstart="draggedItem = item" class="bg-white dark:bg-slate-800 p-4 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700 flex flex-col gap-3 cursor-grab active:cursor-grabbing">
                                <h4 class="font-bold text-slate-800 dark:text-slate-100">{{ item.item_name }}</h4>
                                <div class="flex justify-between items-center border-t border-slate-50 dark:border-slate-700/50 pt-2.5">
                                    <button @click="moveStatus(item, 'purchased')" class="text-xs text-slate-400 hover:text-slate-600">&larr; Beli</button>
                                    <button @click="openEditModal(item)" class="text-xs text-slate-400 hover:text-slate-600">Edit</button>
                                    <button @click="moveStatus(item, 'returned')" class="text-xs font-bold text-forest-600 hover:text-forest-800">Kembalikan &rarr;</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Returned Column -->
                    <div @dragover.prevent @drop="onDrop('returned')"
                         class="bg-slate-100 dark:bg-slate-800/50 p-4 rounded-2xl flex flex-col gap-4 min-h-[500px]">
                        <div class="flex justify-between items-center pb-2 border-b border-slate-200 dark:border-slate-700">
                            <h3 class="font-bold text-slate-700 dark:text-slate-200 flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-rose-400"></span>
                                Dikembalikan Wadah (Returned)
                            </h3>
                            <span class="bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs px-2 py-0.5 rounded-full font-bold">
                                {{ returnedItems.length }}
                            </span>
                        </div>
                        <div class="flex flex-col gap-3 overflow-y-auto">
                            <div v-for="item in returnedItems" :key="item.id" draggable="true" @dragstart="draggedItem = item" class="bg-white dark:bg-slate-800 p-4 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700 flex flex-col gap-3 cursor-grab active:cursor-grabbing">
                                <h4 class="font-bold text-slate-800 dark:text-slate-100">{{ item.item_name }}</h4>
                                <div class="flex justify-between items-center border-t border-slate-50 dark:border-slate-700/50 pt-2.5">
                                    <button @click="moveStatus(item, 'delivered')" class="text-xs text-slate-400 hover:text-slate-600">&larr; Kirim</button>
                                    <button @click="openEditModal(item)" class="text-xs text-slate-400 hover:text-slate-600">Edit</button>
                                    <button @click="deleteItem(item.id)" class="text-xs font-bold text-rose-600 hover:text-rose-800">Hapus</button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- Add/Edit Item Modal Overlay -->
        <div v-if="showAddModal" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm transition duration-300">
            <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-md w-full shadow-2xl overflow-hidden border border-slate-100 dark:border-slate-700">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100">
                        {{ editMode ? 'Edit Barang' : 'Tambah Barang Baru' }}
                    </h3>
                    <button @click="closeAddModal" class="text-slate-400 hover:text-slate-600">&times;</button>
                </div>
                <form @submit.prevent="submitItem" class="p-6 flex flex-col gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Nama Barang</label>
                        <input type="text" v-model="itemForm.name" required placeholder="Contoh: Sepatu Pernikahan, Perhiasan Emas"
                               class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 p-3 text-slate-800 dark:text-slate-100 focus:ring-sage-500 focus:border-sage-500" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Status Pengiriman</label>
                        <select v-model="itemForm.status" required
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 p-3 text-slate-800 dark:text-slate-100 focus:ring-sage-500 focus:border-sage-500">
                            <option value="pending">Rencana (Pending)</option>
                            <option value="purchased">Dibeli (Purchased)</option>
                            <option value="delivered">Diterima (Delivered)</option>
                            <option value="returned">Dikembalikan (Returned)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">
                            Link Lacak Kurir Eksternal (Harus http:// atau https://)
                        </label>
                        <input type="url" v-model="itemForm.tracking_url" placeholder="https://jne.co.id/tracking/..."
                               class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 p-3 text-slate-800 dark:text-slate-100 focus:ring-sage-500 focus:border-sage-500" />
                    </div>
                    <div class="flex justify-end gap-2 mt-4">
                        <button type="button" @click="closeAddModal"
                                class="px-4 py-2 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg text-sm font-semibold transition">
                            Batal
                        </button>
                        <button type="submit" :disabled="itemForm.processing"
                                class="px-4 py-2 bg-forest-600 hover:bg-forest-700 text-white rounded-lg text-sm font-semibold transition shadow-sm">
                            {{ editMode ? 'Simpan Perubahan' : 'Tambah Barang' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
