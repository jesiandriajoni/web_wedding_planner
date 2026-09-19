<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    project: Object,
    allocations: Object,
    vendors: Array
});

// Format currency in IDR (rupiah)
const formatCurrency = (val) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0
    }).format(val);
};

// Selected vendor for adding payment
const selectedVendor = ref(null);
const showPaymentModal = ref(false);

const paymentForm = useForm({
    amount: '',
    notes: ''
});

const openPaymentModal = (vendor) => {
    selectedVendor.value = vendor;
    paymentForm.amount = '';
    paymentForm.notes = '';
    showPaymentModal.value = true;
};

const closePaymentModal = () => {
    showPaymentModal.value = false;
    selectedVendor.value = null;
};

const submitPayment = () => {
    paymentForm.post(route('projects.payments.store', {
        project: props.project.id,
        vendor: selectedVendor.value.id
    }), {
        onSuccess: () => closePaymentModal(),
    });
};

// Calculate total vendor package prices
const totalVendorPrice = computed(() => {
    return props.vendors.reduce((sum, v) => sum + parseFloat(v.package_price), 0);
});

// Calculate total paid amount
const totalPaidAmount = computed(() => {
    return props.vendors.reduce((sum, v) => sum + parseFloat(v.paid_amount), 0);
});

// Calculate total remaining payments
const totalRemainingPayment = computed(() => {
    return totalVendorPrice.value - totalPaidAmount.value;
});

const showBudgetEditModal = ref(false);
const formattedBudgetEdit = ref('');

const budgetForm = useForm({
    name: props.project.name,
    wedding_date: props.project.wedding_date,
    total_budget: props.project.total_budget
});

const onBudgetEditInput = (e) => {
    const el = e.target;
    const oldVal = el.value;
    const oldSelection = el.selectionEnd || 0;
    const digitsBeforeCursor = oldVal.slice(0, oldSelection).replace(/\D/g, '').length;

    const raw = oldVal.replace(/\D/g, '');
    if (!raw) {
        formattedBudgetEdit.value = '';
        budgetForm.total_budget = '';
        return;
    }

    const numericVal = parseInt(raw, 10);
    budgetForm.total_budget = numericVal;
    const formatted = new Intl.NumberFormat('id-ID').format(numericVal);
    formattedBudgetEdit.value = formatted;

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

const openBudgetEditModal = () => {
    budgetForm.name = props.project.name;
    budgetForm.wedding_date = props.project.wedding_date;
    budgetForm.total_budget = props.project.total_budget;
    formattedBudgetEdit.value = props.project.total_budget ? new Intl.NumberFormat('id-ID').format(props.project.total_budget) : '';
    showBudgetEditModal.value = true;
};

const closeBudgetEditModal = () => {
    showBudgetEditModal.value = false;
};

const submitBudgetUpdate = () => {
    budgetForm.put(route('projects.update', props.project.id), {
        onSuccess: () => closeBudgetEditModal(),
    });
};
</script>

<template>
    <Head :title="'Budget Tracker - ' + project.name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight text-slate-800 dark:text-slate-100">
                        Budget & Financial Tracker
                    </h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                        {{ project.name }} - Kelola anggaran dan pembayaran vendor
                    </p>
                </div>
                <button @click="openBudgetEditModal"
                        class="px-4 py-2 bg-forest-600 hover:bg-forest-700 text-white rounded-lg shadow-sm text-sm font-semibold transition duration-150">
                    Edit Anggaran / Proyek
                </button>
            </div>
        </template>

        <div class="py-12 bg-slate-50 dark:bg-slate-900 min-h-screen">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Financial Overview Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700">
                        <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Total Anggaran</p>
                        <p class="text-2xl font-extrabold text-slate-800 dark:text-slate-100 mt-2">
                            {{ formatCurrency(project.total_budget) }}
                        </p>
                    </div>
                    <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700">
                        <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Total Kontrak Vendor</p>
                        <p class="text-2xl font-extrabold text-slate-800 dark:text-slate-100 mt-2" :class="totalVendorPrice > project.total_budget ? 'text-rose-500' : 'text-slate-800 dark:text-slate-100'">
                            {{ formatCurrency(totalVendorPrice) }}
                        </p>
                        <span v-if="totalVendorPrice > project.total_budget" class="text-[10px] text-rose-500 font-bold block mt-1">
                            OVER BUDGET!
                        </span>
                    </div>
                    <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700">
                        <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Total Terbayar</p>
                        <p class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400 mt-2">
                            {{ formatCurrency(totalPaidAmount) }}
                        </p>
                    </div>
                    <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700">
                        <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Sisa Pembayaran</p>
                        <p class="text-2xl font-extrabold text-amber-600 dark:text-amber-400 mt-2">
                            {{ formatCurrency(totalRemainingPayment) }}
                        </p>
                    </div>
                </div>

                <!-- Allocations Grid -->
                <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 mb-8">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 mb-4 border-b pb-2">
                        Alokasi Anggaran Otomatis (Persentase Default)
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-6">
                        <!-- Vendor Card -->
                        <div class="p-4 bg-slate-50 dark:bg-slate-900/50 rounded-xl border border-slate-100 dark:border-slate-700">
                            <span class="text-xs font-semibold text-slate-400 uppercase">Vendor (50%)</span>
                            <p class="text-lg font-extrabold text-slate-700 dark:text-slate-200 mt-1">
                                {{ formatCurrency(allocations.vendor) }}
                            </p>
                        </div>
                        <!-- Katering Card -->
                        <div class="p-4 bg-slate-50 dark:bg-slate-900/50 rounded-xl border border-slate-100 dark:border-slate-700">
                            <span class="text-xs font-semibold text-slate-400 uppercase">Katering (30%)</span>
                            <p class="text-lg font-extrabold text-slate-700 dark:text-slate-200 mt-1">
                                {{ formatCurrency(allocations.katering) }}
                            </p>
                        </div>
                        <!-- Seserahan Card -->
                        <div class="p-4 bg-slate-50 dark:bg-slate-900/50 rounded-xl border border-slate-100 dark:border-slate-700">
                            <span class="text-xs font-semibold text-slate-400 uppercase">Seserahan (10%)</span>
                            <p class="text-lg font-extrabold text-slate-700 dark:text-slate-200 mt-1">
                                {{ formatCurrency(allocations.seserahan) }}
                            </p>
                        </div>
                        <!-- Lainnya Card -->
                        <div class="p-4 bg-slate-50 dark:bg-slate-900/50 rounded-xl border border-slate-100 dark:border-slate-700">
                            <span class="text-xs font-semibold text-slate-400 uppercase">Lainnya (10%)</span>
                            <p class="text-lg font-extrabold text-slate-700 dark:text-slate-200 mt-1">
                                {{ formatCurrency(allocations.lainnya) }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Vendors Payment Table Card -->
                <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 mb-4 border-b pb-2 flex justify-between items-center">
                        <span>Status Pembayaran Vendor</span>
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
                            <thead class="bg-slate-50 dark:bg-slate-700 text-slate-700 dark:text-slate-200 uppercase text-xs font-bold">
                                <tr>
                                    <th class="px-6 py-3 rounded-l-lg">Nama Vendor</th>
                                    <th class="px-6 py-3">Kategori</th>
                                    <th class="px-6 py-3 text-right">Harga Paket</th>
                                    <th class="px-6 py-3 text-right">Terbayar</th>
                                    <th class="px-6 py-3 text-center">Status</th>
                                    <th class="px-6 py-3 rounded-r-lg text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                <tr v-for="vendor in vendors" :key="vendor.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition duration-150">
                                    <td class="px-6 py-4 font-semibold text-slate-800 dark:text-slate-100">
                                        {{ vendor.name }}
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ vendor.category }}
                                    </td>
                                    <td class="px-6 py-4 text-right font-medium">
                                        {{ formatCurrency(vendor.package_price) }}
                                    </td>
                                    <td class="px-6 py-4 text-right font-medium text-emerald-600 dark:text-emerald-400">
                                        {{ formatCurrency(vendor.paid_amount) }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-2.5 py-1 rounded-full text-xs font-bold uppercase"
                                              :class="{
                                                  'bg-rose-50 text-rose-600 dark:bg-rose-950 dark:text-rose-400': vendor.status === 'pending',
                                                  'bg-amber-50 text-amber-600 dark:bg-amber-950 dark:text-amber-400': vendor.status === 'dp',
                                                  'bg-emerald-50 text-emerald-600 dark:bg-emerald-950 dark:text-emerald-400': vendor.status === 'paid',
                                              }">
                                            {{ vendor.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <button @click="openPaymentModal(vendor)"
                                                class="text-xs bg-forest-600 hover:bg-forest-700 text-white font-semibold px-3 py-1.5 rounded-lg shadow-sm transition duration-150">
                                            Tambah Bayar
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="vendors.length === 0">
                                    <td colspan="6" class="text-center py-8 text-slate-400 dark:text-slate-500">
                                        Belum ada vendor terdaftar.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Payment Modal Overlay -->
        <div v-if="showPaymentModal" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm transition duration-300">
            <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-md w-full shadow-2xl overflow-hidden border border-slate-100 dark:border-slate-700">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100">
                        Catat Pembayaran
                    </h3>
                    <button @click="closePaymentModal" class="text-slate-400 hover:text-slate-600">&times;</button>
                </div>
                <form @submit.prevent="submitPayment" class="p-6 flex flex-col gap-4">
                    <div>
                        <p class="text-xs text-slate-400">Vendor</p>
                        <p class="text-base font-bold text-slate-700 dark:text-slate-200 mt-0.5">
                            {{ selectedVendor?.name }}
                        </p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">
                            Nominal Pembayaran (IDR)
                        </label>
                        <input type="number" v-model="paymentForm.amount" required min="0.01" step="0.01"
                               class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 p-3 text-slate-800 dark:text-slate-100 focus:ring-sage-500 focus:border-sage-500" />
                        <p v-if="paymentForm.amount" class="text-xs font-semibold text-forest-600 dark:text-forest-400 mt-1">{{ formatCurrency(paymentForm.amount) }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">
                            Catatan / Keterangan
                        </label>
                        <input type="text" v-model="paymentForm.notes" placeholder="Contoh: Pembayaran DP 1"
                               class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 p-3 text-slate-800 dark:text-slate-100 focus:ring-sage-500 focus:border-sage-500" />
                    </div>
                    <div class="flex justify-end gap-2 mt-4">
                        <button type="button" @click="closePaymentModal"
                                class="px-4 py-2 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg text-sm font-semibold transition">
                            Batal
                        </button>
                        <button type="submit" :disabled="paymentForm.processing"
                                class="px-4 py-2 bg-forest-600 hover:bg-forest-700 text-white rounded-lg text-sm font-semibold transition shadow-sm">
                            Simpan Pembayaran
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Project Budget Modal -->
        <div v-if="showBudgetEditModal" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm transition duration-300">
            <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-md w-full shadow-2xl overflow-hidden border border-slate-100 dark:border-slate-700">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100">
                        Edit Detail Project & Anggaran
                    </h3>
                    <button @click="closeBudgetEditModal" class="text-slate-400 hover:text-slate-600">&times;</button>
                </div>
                <form @submit.prevent="submitBudgetUpdate" class="p-6 flex flex-col gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Nama Pernikahan</label>
                        <input type="text" v-model="budgetForm.name" required
                               class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 p-3 text-slate-800 dark:text-slate-100 focus:ring-sage-500 focus:border-sage-500" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Tanggal Pernikahan</label>
                        <input type="date" v-model="budgetForm.wedding_date" required
                               class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 p-3 text-slate-800 dark:text-slate-100 focus:ring-sage-500 focus:border-sage-500" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Total Anggaran (IDR)</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-xs font-bold text-slate-400 select-none">Rp</span>
                            <input type="text"
                                   inputmode="numeric"
                                   :value="formattedBudgetEdit"
                                   @input="onBudgetEditInput"
                                   required
                                   placeholder="100.000.000"
                                   class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 p-3 pl-10 text-slate-800 dark:text-slate-100 focus:ring-sage-500 focus:border-sage-500 text-sm font-medium" />
                        </div>
                        <p v-if="budgetForm.total_budget" class="text-xs font-semibold text-forest-600 dark:text-forest-400 mt-1">{{ formatCurrency(budgetForm.total_budget) }}</p>
                    </div>
                    <div class="flex justify-end gap-2 mt-4">
                        <button type="button" @click="closeBudgetEditModal"
                                class="px-4 py-2 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg text-sm font-semibold transition">
                            Batal
                        </button>
                        <button type="submit" :disabled="budgetForm.processing"
                                class="px-4 py-2 bg-forest-600 hover:bg-forest-700 text-white rounded-lg text-sm font-semibold transition shadow-sm">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
