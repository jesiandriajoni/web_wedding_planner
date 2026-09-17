<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    project: Object,
    guests: Array,
    catering_portions: Number
});

const page = usePage();

// Form for CRUD guests
const showAddModal = ref(false);
const editMode = ref(false);
const editingGuestId = ref(null);

const guestForm = useForm({
    name: '',
    side: 'bersama',
    rsvp: 'pending',
    pax: 2
});

// Form & state for Import guests
const showImportModal = ref(false);
const isDragging = ref(false);
const selectedFileName = ref('');
const fileInputRef = ref(null);

const importForm = useForm({
    file: null,
});

// Invitation Share State
const showInviteModal = ref(false);
const selectedGuestForInvite = ref(null);
const copiedLink = ref(false);
const copiedMessage = ref(false);

const openAddModal = () => {
    editMode.value = false;
    editingGuestId.value = null;
    guestForm.reset();
    showAddModal.value = true;
};

const openEditModal = (guest) => {
    editMode.value = true;
    editingGuestId.value = guest.id;
    guestForm.name = guest.name;
    guestForm.side = guest.side;
    guestForm.rsvp = guest.rsvp;
    guestForm.pax = guest.pax;
    showAddModal.value = true;
};

const closeAddModal = () => {
    showAddModal.value = false;
    guestForm.reset();
};

const submitGuest = () => {
    if (editMode.value) {
        guestForm.put(route('projects.guests.update', {
            project: props.project.id,
            guest: editingGuestId.value
        }), {
            onSuccess: () => closeAddModal(),
        });
    } else {
        guestForm.post(route('projects.guests.store', props.project.id), {
            onSuccess: () => closeAddModal(),
        });
    }
};

const deleteGuest = (id) => {
    if (confirm('Yakin ingin menghapus tamu ini?')) {
        guestForm.delete(route('projects.guests.destroy', {
            project: props.project.id,
            guest: id
        }));
    }
};

// Import Handlers
const openImportModal = () => {
    importForm.reset();
    importForm.clearErrors();
    selectedFileName.value = '';
    showImportModal.value = true;
};

const closeImportModal = () => {
    showImportModal.value = false;
    importForm.reset();
    importForm.clearErrors();
    selectedFileName.value = '';
};

const onFileSelected = (e) => {
    const files = e.target.files;
    if (files && files.length > 0) {
        importForm.file = files[0];
        selectedFileName.value = files[0].name;
    }
};

const onDrop = (e) => {
    isDragging.value = false;
    const files = e.dataTransfer.files;
    if (files && files.length > 0) {
        importForm.file = files[0];
        selectedFileName.value = files[0].name;
    }
};

const submitImport = () => {
    if (!importForm.file) return;

    importForm.post(route('projects.guests.import', props.project.id), {
        forceFormData: true,
        onSuccess: () => {
            closeImportModal();
        },
    });
};

const downloadTemplate = (format = 'xlsx') => {
    window.location.href = route('projects.guests.template', {
        project: props.project.id,
        format: format,
    });
};

// Invitation Generate & Share Handlers
const getGuestInvitationUrl = (guest) => {
    if (!guest) return '';
    const base = window.location.origin;
    const slug = props.project.slug;
    const guestName = encodeURIComponent(guest.name);
    return `${base}/undangan/${slug}?to=${guestName}&guest=${guest.id}`;
};

const getWhatsAppMessage = (guest) => {
    if (!guest) return '';
    const url = getGuestInvitationUrl(guest);
    const groom = props.project.groom_name || '';
    const bride = props.project.bride_name || '';
    const date = props.project.wedding_date || '';
    const couple = (groom && bride) ? `${groom} & ${bride}` : props.project.name;

    return `Kepada Yth. Bapak/Ibu/Saudara/i *${guest.name}*,\n\n` +
        `Tanpa mengurangi rasa hormat, perkenankan kami mengundang Bapak/Ibu/Saudara/i untuk menghadiri acara pernikahan kami:\n\n` +
        `💍 *${couple}*\n` +
        (date ? `📅 Tanggal: *${date}*\n\n` : `\n`) +
        `Untuk informasi detail acara dan konfirmasi kehadiran (RSVP), silakan buka tautan undangan digital kami berikut:\n` +
        `🔗 ${url}\n\n` +
        `Merupakan suatu kehormatan dan kebahagiaan bagi kami apabila Bapak/Ibu/Saudara/i berkenan hadir dan memberikan doa restu.\n\n` +
        `Terima kasih.`;
};

const openInviteModal = (guest) => {
    selectedGuestForInvite.value = guest;
    copiedLink.value = false;
    copiedMessage.value = false;
    showInviteModal.value = true;
};

const closeInviteModal = () => {
    showInviteModal.value = false;
    selectedGuestForInvite.value = null;
    copiedLink.value = false;
    copiedMessage.value = false;
};

const copyInvitationLink = (guest) => {
    const url = getGuestInvitationUrl(guest);
    navigator.clipboard.writeText(url);
    copiedLink.value = true;
    setTimeout(() => { copiedLink.value = false; }, 2500);
};

const copyWhatsAppMessage = (guest) => {
    const msg = getWhatsAppMessage(guest);
    navigator.clipboard.writeText(msg);
    copiedMessage.value = true;
    setTimeout(() => { copiedMessage.value = false; }, 2500);
};

const sendViaWhatsApp = (guest) => {
    const msg = getWhatsAppMessage(guest);
    const waUrl = `https://api.whatsapp.com/send?text=${encodeURIComponent(msg)}`;
    window.open(waUrl, '_blank');
};

// Computes
const totalGuestsCount = computed(() => props.guests.length);
const attendingGuestsCount = computed(() => props.guests.filter(g => g.rsvp === 'hadir').length);
const totalAttendingPax = computed(() => props.guests.filter(g => g.rsvp === 'hadir').reduce((sum, g) => sum + parseInt(g.pax), 0));
</script>

<template>
    <Head :title="'Daftar Tamu - ' + project.name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h2 class="text-3xl font-bold font-serif text-charcoal tracking-tight">
                        Guest Book & RSVP Manager
                    </h2>
                    <p class="text-sm text-charcoal-400 mt-1">
                        Kelola data tamu, generate link undangan personal, dan monitor porsi katering otomatis
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <button @click="openImportModal"
                            class="inline-flex items-center gap-2 px-4 py-2.5 border border-gold-300 bg-gold-50/70 hover:bg-gold-100 text-gold-900 rounded-xl shadow-xs text-sm font-bold transition duration-150">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gold-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        Import Excel / CSV
                    </button>
                    <button @click="openAddModal"
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-gold-500 hover:bg-gold-600 active:bg-gold-700 text-white rounded-xl shadow-xs text-sm font-bold tracking-wide transition duration-150">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        Tambah Tamu Baru
                    </button>
                </div>
            </div>
        </template>

        <div class="py-10 bg-ivory min-h-screen">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                
                <!-- Flash Success Notification -->
                <div v-if="page.props.flash?.success"
                     class="mb-6 p-4 rounded-xl bg-gold-50 border border-gold-300 flex items-center justify-between text-gold-900 text-sm shadow-xs transition">
                    <div class="flex items-center gap-3">
                        <span class="p-1.5 rounded-lg bg-gold-200 text-gold-800 font-bold">✓</span>
                        <span class="font-medium">{{ page.props.flash.success }}</span>
                    </div>
                    <button @click="page.props.flash.success = null" class="text-gold-500 hover:text-gold-700 font-bold text-lg leading-none">&times;</button>
                </div>

                <!-- Guest & Catering Stats Summary -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Total Guest -->
                    <div class="bg-white p-6 rounded-2xl shadow-xs border border-gold-200/80">
                        <span class="text-xs font-bold text-charcoal-400 uppercase tracking-wider block">Total Undangan</span>
                        <span class="text-3xl font-extrabold font-serif text-charcoal block mt-2">
                            {{ totalGuestsCount }} <span class="text-sm font-normal text-charcoal-400 font-sans">tamu</span>
                        </span>
                    </div>
                    <!-- Attending Guest -->
                    <div class="bg-white p-6 rounded-2xl shadow-xs border border-gold-200/80">
                        <span class="text-xs font-bold text-charcoal-400 uppercase tracking-wider block">Tamu Konfirmasi Hadir</span>
                        <span class="text-3xl font-extrabold font-serif text-gold-600 block mt-2">
                            {{ attendingGuestsCount }} <span class="text-sm font-normal text-charcoal-400 font-sans">tamu</span>
                        </span>
                    </div>
                    <!-- Total Pax Attending -->
                    <div class="bg-white p-6 rounded-2xl shadow-xs border border-gold-200/80">
                        <span class="text-xs font-bold text-charcoal-400 uppercase tracking-wider block">Total Pax Hadir</span>
                        <span class="text-3xl font-extrabold font-serif text-blush-600 block mt-2">
                            {{ totalAttendingPax }} <span class="text-sm font-normal text-charcoal-400 font-sans">pax</span>
                        </span>
                    </div>
                    <!-- Catering Portions Calculation -->
                    <div class="bg-white p-6 rounded-2xl shadow-xs border border-gold-200/80">
                        <span class="text-xs font-bold text-charcoal-400 uppercase tracking-wider block">Estimasi Porsi Katering (Pax x 2)</span>
                        <span class="text-3xl font-extrabold font-serif text-gold-700 block mt-2">
                            {{ catering_portions }} <span class="text-sm font-normal text-charcoal-400 font-sans">porsi</span>
                        </span>
                    </div>
                </div>

                <!-- Guests List Card -->
                <div class="bg-white p-6 rounded-2xl shadow-xs border border-gold-200/80">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-4 border-b border-gold-100 pb-4">
                        <div>
                            <h3 class="text-lg font-bold font-serif text-charcoal">
                                Daftar Lengkap Tamu Undangan
                            </h3>
                            <p class="text-xs text-charcoal-400 mt-0.5">
                                Menampilkan total {{ totalGuestsCount }} nama tamu terdaftar
                            </p>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-charcoal">
                            <thead class="bg-ivory text-charcoal-600 uppercase text-xs font-bold border-b border-gold-100">
                                <tr>
                                    <th class="px-6 py-3 rounded-l-xl">Nama Tamu</th>
                                    <th class="px-6 py-3">Pihak (Side)</th>
                                    <th class="px-6 py-3 text-center">Pax Bawaan</th>
                                    <th class="px-6 py-3 text-center">RSVP</th>
                                    <th class="px-6 py-3">Pesan Buku Tamu</th>
                                    <th class="px-6 py-3 rounded-r-xl text-center">Aksi & Undangan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gold-100/70">
                                <tr v-for="guest in guests" :key="guest.id" class="hover:bg-ivory-100/60 transition duration-150">
                                    <td class="px-6 py-4 font-bold text-charcoal">
                                        {{ guest.name }}
                                    </td>
                                    <td class="px-6 py-4 capitalize">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold"
                                              :class="{
                                                  'bg-gold-50 text-gold-800 border border-gold-200': guest.side === 'pria',
                                                  'bg-blush-100 text-blush-800 border border-blush-200': guest.side === 'wanita',
                                                  'bg-ivory-200 text-charcoal-700 border border-charcoal-200': guest.side === 'bersama',
                                              }">
                                            {{ guest.side }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center font-bold text-charcoal font-serif">
                                        {{ guest.pax }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-2.5 py-1 rounded-full text-xs font-bold uppercase"
                                              :class="{
                                                  'bg-rose-50 text-rose-700 border border-rose-200': guest.rsvp === 'absen',
                                                  'bg-blush-100 text-blush-800 border border-blush-200': guest.rsvp === 'pending',
                                                  'bg-gold-100 text-gold-900 border border-gold-300': guest.rsvp === 'hadir',
                                              }">
                                            {{ guest.rsvp }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 max-w-xs truncate italic text-charcoal-400">
                                        {{ guest.guest_book_message || 'Belum mengisi ucapan' }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <!-- Button Generate & Share Undangan -->
                                            <button @click="openInviteModal(guest)"
                                                    title="Generate Link & Kirim Undangan Digital"
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gold-50 hover:bg-gold-100 text-gold-900 text-xs font-bold rounded-lg transition border border-gold-300 shadow-xs">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-gold-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                                                </svg>
                                                Undangan
                                            </button>

                                            <!-- Button Edit -->
                                            <button @click="openEditModal(guest)"
                                                    class="px-2.5 py-1.5 bg-ivory-100 hover:bg-ivory-200 border border-gold-100 text-charcoal text-xs font-semibold rounded-lg transition">
                                                Edit
                                            </button>

                                            <!-- Button Delete -->
                                            <button @click="deleteGuest(guest.id)"
                                                    class="px-2.5 py-1.5 bg-rose-50 hover:bg-rose-100 border border-rose-200 text-rose-700 text-xs font-semibold rounded-lg transition">
                                                Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="guests.length === 0">
                                    <td colspan="6" class="text-center py-12 text-charcoal-400">
                                        <div class="flex flex-col items-center justify-center gap-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-gold-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                            </svg>
                                            <p class="font-bold font-serif text-charcoal text-base">Belum ada tamu terdaftar</p>
                                            <p class="text-xs text-charcoal-400">Klik "Tambah Tamu Baru" atau "Import Excel / CSV" untuk memasukkan data sekaligus.</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add/Edit Guest Modal Overlay -->
        <div v-if="showAddModal" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs transition duration-300">
            <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl overflow-hidden border border-gold-200">
                <div class="px-6 py-4 border-b border-gold-100 flex justify-between items-center bg-ivory">
                    <h3 class="text-lg font-bold font-serif text-charcoal">
                        {{ editMode ? 'Edit Tamu' : 'Tambah Tamu Baru' }}
                    </h3>
                    <button @click="closeAddModal" class="text-charcoal-400 hover:text-charcoal text-2xl leading-none">&times;</button>
                </div>
                <form @submit.prevent="submitGuest" class="p-6 flex flex-col gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-charcoal-500 mb-1">Nama Tamu</label>
                        <input type="text" v-model="guestForm.name" required placeholder="Contoh: Budi Santoso"
                               class="w-full rounded-xl border border-gold-200 bg-ivory p-3 text-charcoal focus:ring-gold-400 focus:border-gold-500 shadow-xs text-sm" />
                        <span v-if="guestForm.errors.name" class="text-xs text-rose-500 mt-1 block">{{ guestForm.errors.name }}</span>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-charcoal-500 mb-1">Pihak Keluarga (Side)</label>
                        <select v-model="guestForm.side" required
                                class="w-full rounded-xl border border-gold-200 bg-ivory p-3 text-charcoal focus:ring-gold-400 focus:border-gold-500 shadow-xs text-sm">
                            <option value="pria">Pengantin Pria</option>
                            <option value="wanita">Pengantin Wanita</option>
                            <option value="bersama">Bersama</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-charcoal-500 mb-1">Status Kehadiran (RSVP)</label>
                        <select v-model="guestForm.rsvp" required
                                class="w-full rounded-xl border border-gold-200 bg-ivory p-3 text-charcoal focus:ring-gold-400 focus:border-gold-500 shadow-xs text-sm">
                            <option value="pending">Pending</option>
                            <option value="hadir">Hadir</option>
                            <option value="absen">Absen</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-charcoal-500 mb-1">Jumlah Pax Bawaan</label>
                        <input type="number" v-model="guestForm.pax" required min="1"
                               class="w-full rounded-xl border border-gold-200 bg-ivory p-3 text-charcoal focus:ring-gold-400 focus:border-gold-500 shadow-xs text-sm" />
                    </div>
                    <div class="flex justify-end gap-2 mt-4 pt-2 border-t border-gold-100">
                        <button type="button" @click="closeAddModal"
                                class="px-4 py-2 text-charcoal-500 hover:bg-ivory rounded-xl text-sm font-semibold transition">
                            Batal
                        </button>
                        <button type="submit" :disabled="guestForm.processing"
                                class="px-5 py-2.5 bg-gold-500 hover:bg-gold-600 text-white rounded-xl text-sm font-bold tracking-wide transition shadow-xs disabled:opacity-50">
                            {{ editMode ? 'Simpan Perubahan' : 'Tambah Tamu' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Bulk Import Guest Modal Overlay -->
        <div v-if="showImportModal" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs transition duration-300">
            <div class="bg-white rounded-2xl max-w-lg w-full shadow-2xl overflow-hidden border border-gold-200">
                <div class="px-6 py-4 border-b border-gold-100 flex justify-between items-center bg-ivory">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-gold-100 flex items-center justify-center text-gold-700 font-bold">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold font-serif text-charcoal">
                                Import Daftar Tamu Massal
                            </h3>
                            <p class="text-xs text-charcoal-400">Unggah ratusan tamu sekaligus via Excel atau CSV</p>
                        </div>
                    </div>
                    <button @click="closeImportModal" class="text-charcoal-400 hover:text-charcoal text-2xl leading-none">&times;</button>
                </div>

                <div class="p-6 flex flex-col gap-5">
                    <!-- Step 1: Download Template -->
                    <div class="p-4 rounded-xl bg-ivory border border-gold-200/80">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <span class="text-xs font-bold uppercase tracking-wider text-gold-700 block">Langkah 1</span>
                                <h4 class="text-sm font-bold font-serif text-charcoal mt-0.5">Unduh Template Format</h4>
                                <p class="text-xs text-charcoal-500 mt-1">
                                    Gunakan template spreadsheet ini agar kolom nama, pihak, dan PAX otomatis terbaca.
                                </p>
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-2 mt-3">
                            <button @click="downloadTemplate('xlsx')"
                                    type="button"
                                    class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-gold-500 hover:bg-gold-600 text-white rounded-lg text-xs font-bold shadow-xs transition">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zM6 4h7v5h5v11H6V4zm2 8h8v2H8v-2zm0 3h8v2H8v-2z" />
                                </svg>
                                Template Excel (.xlsx)
                            </button>
                            <button @click="downloadTemplate('csv')"
                                    type="button"
                                    class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-ivory-200 hover:bg-ivory-300 border border-gold-200 text-charcoal rounded-lg text-xs font-bold transition">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zM6 4h7v5h5v11H6V4zm2 8h8v2H8v-2zm0 3h8v2H8v-2z" />
                                </svg>
                                Template CSV (.csv)
                            </button>
                        </div>

                        <!-- Column Guide Badges -->
                        <div class="mt-3 pt-3 border-t border-gold-200/60 grid grid-cols-3 gap-2 text-[11px]">
                            <div class="bg-white p-2 rounded-lg border border-gold-200">
                                <span class="font-bold text-charcoal block">1. Nama Tamu</span>
                                <span class="text-rose-500 font-semibold">*Wajib diisi</span>
                            </div>
                            <div class="bg-white p-2 rounded-lg border border-gold-200">
                                <span class="font-bold text-charcoal block">2. Pihak (Side)</span>
                                <span class="text-charcoal-400">pria/wanita/bersama</span>
                            </div>
                            <div class="bg-white p-2 rounded-lg border border-gold-200">
                                <span class="font-bold text-charcoal block">3. Jumlah Pax</span>
                                <span class="text-charcoal-400">Default: 2 pax</span>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Upload File -->
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-gold-700 block mb-1">Langkah 2</span>
                        <h4 class="text-sm font-bold font-serif text-charcoal mb-2">Unggah File Spreadsheet</h4>

                        <!-- Drag and Drop Box -->
                        <div @dragover.prevent="isDragging = true"
                             @dragleave.prevent="isDragging = false"
                             @drop.prevent="onDrop"
                             @click="fileInputRef.click()"
                             :class="{
                                 'border-gold-500 bg-gold-50/50': isDragging,
                                 'border-gold-200 hover:border-gold-400': !isDragging,
                                 'bg-gold-50/30 border-gold-400': selectedFileName
                             }"
                             class="border-2 border-dashed rounded-2xl p-6 text-center cursor-pointer transition flex flex-col items-center justify-center gap-2">
                            
                            <input ref="fileInputRef"
                                   type="file"
                                   accept=".xlsx,.xls,.csv"
                                   @change="onFileSelected"
                                   class="hidden" />

                            <div v-if="!selectedFileName" class="flex flex-col items-center">
                                <div class="w-12 h-12 rounded-full bg-ivory flex items-center justify-center text-gold-500 mb-2 border border-gold-200">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                    </svg>
                                </div>
                                <p class="text-sm font-semibold text-charcoal">
                                    Tarik & letakkan file di sini atau <span class="text-gold-600 underline font-bold">Pilih File</span>
                                </p>
                                <p class="text-xs text-charcoal-400 mt-1">Mendukung file .XLSX dan .CSV (Maks 10MB)</p>
                            </div>

                            <div v-else class="flex items-center gap-3 w-full p-2 bg-white rounded-xl shadow-xs border border-gold-200">
                                <div class="w-9 h-9 rounded-lg bg-gold-100 text-gold-800 flex items-center justify-center flex-shrink-0 font-bold text-xs">
                                    XLS
                                </div>
                                <div class="flex-1 text-left min-w-0">
                                    <p class="text-xs font-bold text-charcoal truncate">{{ selectedFileName }}</p>
                                    <p class="text-[11px] text-gold-600 font-medium">Siap untuk diimport</p>
                                </div>
                                <button type="button" @click.stop="selectedFileName = ''; importForm.file = null" class="text-charcoal-400 hover:text-rose-500 text-lg p-1">
                                    &times;
                                </button>
                            </div>
                        </div>

                        <span v-if="importForm.errors.file" class="text-xs text-rose-500 mt-1 block font-medium">
                            {{ importForm.errors.file }}
                        </span>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end gap-2 pt-2 border-t border-gold-100">
                        <button type="button" @click="closeImportModal"
                                class="px-4 py-2 text-charcoal-500 hover:bg-ivory rounded-xl text-sm font-semibold transition">
                            Batal
                        </button>
                        <button type="button"
                                @click="submitImport"
                                :disabled="!importForm.file || importForm.processing"
                                class="inline-flex items-center gap-2 px-5 py-2.5 bg-gold-500 hover:bg-gold-600 text-white rounded-xl text-sm font-bold shadow-xs transition disabled:opacity-40 disabled:cursor-not-allowed">
                            <svg v-if="importForm.processing" class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>{{ importForm.processing ? 'Mengimpor Data...' : 'Mulai Import Tamu' }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Generate & Share Guest Invitation Modal Overlay -->
        <div v-if="showInviteModal && selectedGuestForInvite" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs transition duration-300">
            <div class="bg-white rounded-2xl max-w-lg w-full shadow-2xl overflow-hidden border border-gold-200">
                <div class="px-6 py-4 border-b border-gold-100 flex justify-between items-center bg-ivory">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-gold-100 flex items-center justify-center text-gold-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold font-serif text-charcoal">
                                Undangan Digital Tamu
                            </h3>
                            <p class="text-xs text-charcoal-400">Link personal & pesan WhatsApp khusus tamu</p>
                        </div>
                    </div>
                    <button @click="closeInviteModal" class="text-charcoal-400 hover:text-charcoal text-2xl leading-none">&times;</button>
                </div>

                <div class="p-6 flex flex-col gap-5">
                    <!-- Guest Profile Header Badge -->
                    <div class="p-3.5 rounded-xl bg-ivory border border-gold-200 flex items-center justify-between">
                        <div>
                            <span class="text-[11px] font-bold text-charcoal-400 uppercase tracking-wider block">Tamu Undangan</span>
                            <span class="text-base font-extrabold font-serif text-charcoal block mt-0.5">
                                {{ selectedGuestForInvite.name }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold uppercase bg-ivory-200 text-charcoal-700 border border-gold-200">
                                {{ selectedGuestForInvite.side }}
                            </span>
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-blush-100 text-blush-800 border border-blush-200">
                                {{ selectedGuestForInvite.pax }} Pax
                            </span>
                        </div>
                    </div>

                    <!-- 1. Personalized URL Box -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gold-700 mb-1.5">
                            Tautan Undangan Khusus (Personal URL)
                        </label>
                        <div class="flex items-center gap-2">
                            <input type="text"
                                   readonly
                                   :value="getGuestInvitationUrl(selectedGuestForInvite)"
                                   class="flex-1 text-xs rounded-xl border border-gold-200 bg-ivory p-2.5 text-charcoal select-all font-mono" />
                            <button type="button"
                                    @click="copyInvitationLink(selectedGuestForInvite)"
                                    class="inline-flex items-center gap-1 px-3.5 py-2.5 bg-charcoal hover:bg-charcoal-800 text-white rounded-xl text-xs font-bold transition shrink-0 shadow-xs">
                                <svg v-if="!copiedLink" xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                                <span>{{ copiedLink ? '✓ Tersalin' : 'Salin' }}</span>
                            </button>
                            <a :href="getGuestInvitationUrl(selectedGuestForInvite)"
                               target="_blank"
                               title="Buka Pratinjau Undangan"
                               class="p-2.5 bg-ivory hover:bg-ivory-200 border border-gold-200 text-charcoal rounded-xl transition shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gold-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg>
                            </a>
                        </div>
                    </div>

                    <!-- 2. WhatsApp Message Box -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-gold-700">
                                Pesan Siap Kirim WhatsApp
                            </label>
                            <button type="button"
                                    @click="copyWhatsAppMessage(selectedGuestForInvite)"
                                    class="text-xs font-bold text-gold-600 hover:text-gold-700 underline">
                                {{ copiedMessage ? '✓ Teks Tersalin' : 'Salin Format Teks' }}
                            </button>
                        </div>
                        <div class="p-3.5 rounded-xl border border-gold-200 bg-ivory text-xs text-charcoal font-sans whitespace-pre-line leading-relaxed max-h-48 overflow-y-auto select-all">
                            {{ getWhatsAppMessage(selectedGuestForInvite) }}
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-between items-center pt-3 border-t border-gold-100">
                        <button type="button" @click="closeInviteModal"
                                class="px-4 py-2 text-charcoal-500 hover:bg-ivory rounded-xl text-sm font-semibold transition">
                            Tutup
                        </button>
                        <button type="button"
                                @click="sendViaWhatsApp(selectedGuestForInvite)"
                                class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#25D366] hover:bg-[#20bd5a] text-white rounded-xl text-sm font-bold shadow-md hover:shadow-lg transition">
                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                                <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                            </svg>
                            Kirim via WhatsApp
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
