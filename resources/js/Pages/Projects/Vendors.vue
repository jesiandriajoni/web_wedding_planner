<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    project: Object,
    vendors: Array,
    comparison: Object
});

// Format currency in IDR (rupiah)
const formatCurrency = (val) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0
    }).format(val);
};

// Form for CRUD vendor
const showAddModal = ref(false);
const editMode = ref(false);
const editingVendorId = ref(null);

const vendorForm = useForm({
    name: '',
    category: '',
    contact: '',
    package_price: '',
    mou: null
});

const openAddModal = () => {
    editMode.value = false;
    editingVendorId.value = null;
    vendorForm.reset();
    showAddModal.value = true;
};

const openEditModal = (vendor) => {
    editMode.value = true;
    editingVendorId.value = vendor.id;
    vendorForm.name = vendor.name;
    vendorForm.category = vendor.category;
    vendorForm.contact = vendor.contact;
    vendorForm.package_price = vendor.package_price;
    vendorForm.mou = null;
    showAddModal.value = true;
};

const closeAddModal = () => {
    showAddModal.value = false;
    vendorForm.reset();
};

const submitVendor = () => {
    if (editMode.value) {
        vendorForm.put(route('projects.vendors.update', {
            project: props.project.id,
            vendor: editingVendorId.value
        }), {
            onSuccess: () => closeAddModal(),
        });
    } else {
        vendorForm.post(route('projects.vendors.store', props.project.id), {
            onSuccess: () => closeAddModal(),
        });
    }
};

const deleteVendor = (id) => {
    if (confirm('Yakin ingin menghapus vendor ini? File MoU juga akan ikut terhapus permanen.')) {
        vendorForm.delete(route('projects.vendors.destroy', {
            project: props.project.id,
            vendor: id
        }));
    }
};

const handleFileChange = (e) => {
    vendorForm.mou = e.target.files[0];
};
</script>

<template>
    <Head :title="'Vendor Hub - ' + project.name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight text-slate-800 dark:text-slate-100">
                        Vendor Directory & Hub
                    </h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                        Kelola kontak vendor, upload MoU, dan komparasi paket harga
                    </p>
                </div>
                <button @click="openAddModal"
                        class="px-4 py-2 bg-forest-600 hover:bg-forest-700 text-white rounded-lg shadow-sm text-sm font-semibold transition duration-150">
                    Tambah Vendor Baru
                </button>
            </div>
        </template>

        <div class="py-12 bg-slate-50 dark:bg-slate-900 min-h-screen">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                
                <!-- Price Comparison Widget -->
                <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 mb-8">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 mb-4 border-b pb-2">
                        Perbandingan Harga Paket Vendor (Kategori Sejenis)
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div v-for="(categoryGroup, catName) in comparison" :key="catName"
                             class="p-4 bg-slate-50 dark:bg-slate-900/50 rounded-xl border border-slate-100 dark:border-slate-700">
                            <span class="text-xs font-bold text-sage-600 dark:text-sage-400 uppercase tracking-wider block mb-3">
                                {{ catName }}
                            </span>
                            <div class="flex flex-col gap-2">
                                <div v-for="(item, idx) in categoryGroup" :key="idx" 
                                     class="flex justify-between items-center bg-white dark:bg-slate-800 p-2 rounded-lg text-sm border border-slate-100 dark:border-slate-700">
                                    <span class="font-medium text-slate-700 dark:text-slate-200">
                                        {{ idx + 1 }}. {{ item.name }}
                                    </span>
                                    <span class="font-bold text-slate-800 dark:text-slate-100">
                                        {{ formatCurrency(item.price) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div v-if="Object.keys(comparison).length === 0" class="col-span-3 text-center py-4 text-slate-400">
                            Belum ada perbandingan data. Tambahkan minimal 1 vendor.
                        </div>
                    </div>
                </div>

                <!-- Vendor Cards Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="vendor in vendors" :key="vendor.id"
                         class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-sm border border-slate-100 dark:border-slate-700 flex flex-col justify-between transition hover:shadow-md duration-200">
                        <div>
                            <div class="flex justify-between items-start mb-4">
                                <span class="px-2.5 py-1 bg-sage-50 text-sage-700 dark:bg-sage-950 dark:text-sage-300 rounded text-xs font-bold uppercase">
                                    {{ vendor.category }}
                                </span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase"
                                      :class="{
                                          'bg-rose-50 text-rose-600 dark:bg-rose-950 dark:text-rose-400': vendor.status === 'pending',
                                          'bg-amber-50 text-amber-600 dark:bg-amber-950 dark:text-amber-400': vendor.status === 'dp',
                                          'bg-emerald-50 text-emerald-600 dark:bg-emerald-950 dark:text-emerald-400': vendor.status === 'paid',
                                      }">
                                    {{ vendor.status }}
                                </span>
                            </div>
                            <h3 class="text-xl font-bold text-slate-800 dark:text-slate-100 mb-2">
                                {{ vendor.name }}
                            </h3>
                            <div class="text-sm text-slate-500 dark:text-slate-400 flex flex-col gap-1.5 mb-4">
                                <p><strong>Kontak:</strong> {{ vendor.contact }}</p>
                                <p><strong>Harga Paket:</strong> {{ formatCurrency(vendor.package_price) }}</p>
                                <p><strong>Terbayar:</strong> {{ formatCurrency(vendor.paid_amount) }}</p>
                            </div>
                        </div>

                        <div class="border-t border-slate-100 dark:border-slate-700 pt-4 flex flex-col gap-2">
                            <!-- Download MoU Contract -->
                            <div v-if="vendor.mou_path" class="mb-2">
                                <a :href="'/storage/' + vendor.mou_path" target="_blank"
                                   class="text-xs text-forest-600 hover:text-forest-800 font-semibold flex items-center gap-1.5">
                                    📄 Lihat Dokumen MoU (Kontrak)
                                </a>
                            </div>
                            <div v-else class="mb-2">
                                <span class="text-xs text-slate-400 italic">No MoU file uploaded</span>
                            </div>
                            <!-- Actions -->
                            <div class="flex justify-between items-center gap-2">
                                <button @click="openEditModal(vendor)"
                                        class="flex-1 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 text-xs font-bold rounded-lg transition">
                                    Edit
                                </button>
                                <button @click="deleteVendor(vendor.id)"
                                        class="flex-1 py-1.5 bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/50 dark:hover:bg-rose-950 text-rose-600 text-xs font-bold rounded-lg transition">
                                    Hapus
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add/Edit Vendor Modal Overlay -->
        <div v-if="showAddModal" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm transition duration-300">
            <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-md w-full shadow-2xl overflow-hidden border border-slate-100 dark:border-slate-700">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100">
                        {{ editMode ? 'Edit Vendor' : 'Tambah Vendor Baru' }}
                    </h3>
                    <button @click="closeAddModal" class="text-slate-400 hover:text-slate-600">&times;</button>
                </div>
                <form @submit.prevent="submitVendor" class="p-6 flex flex-col gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Nama Vendor</label>
                        <input type="text" v-model="vendorForm.name" required
                               class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 p-3 text-slate-800 dark:text-slate-100 focus:ring-sage-500 focus:border-sage-500" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Kategori Vendor</label>
                        <input type="text" v-model="vendorForm.category" required placeholder="Contoh: Katering, Dekorasi, Band"
                               class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 p-3 text-slate-800 dark:text-slate-100 focus:ring-sage-500 focus:border-sage-500" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Kontak (Telp/Email)</label>
                        <input type="text" v-model="vendorForm.contact" required
                               class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 p-3 text-slate-800 dark:text-slate-100 focus:ring-sage-500 focus:border-sage-500" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Harga Paket (IDR)</label>
                        <input type="number" v-model="vendorForm.package_price" required min="0"
                               class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 p-3 text-slate-800 dark:text-slate-100 focus:ring-sage-500 focus:border-sage-500" />
                        <p v-if="vendorForm.package_price" class="text-xs font-semibold text-forest-600 dark:text-forest-400 mt-1">{{ formatCurrency(vendorForm.package_price) }}</p>
                    </div>
                    <!-- MoU file upload (Only show in Create Mode to simplify) -->
                    <div v-if="!editMode">
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">
                            Berkas MoU (PDF/DOCX/JPG/PNG) - Max 10MB
                        </label>
                        <input type="file" @change="handleFileChange" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                               class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-sage-50 file:text-sage-700 hover:file:bg-sage-100" />
                    </div>
                    <div class="flex justify-end gap-2 mt-4">
                        <button type="button" @click="closeAddModal"
                                class="px-4 py-2 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg text-sm font-semibold transition">
                            Batal
                        </button>
                        <button type="submit" :disabled="vendorForm.processing"
                                class="px-4 py-2 bg-forest-600 hover:bg-forest-700 text-white rounded-lg text-sm font-semibold transition shadow-sm">
                            {{ editMode ? 'Simpan Perubahan' : 'Tambah Vendor' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
