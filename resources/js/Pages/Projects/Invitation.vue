<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    project: Object
});

// datetime-local wants "YYYY-MM-DDTHH:MM"
const toInput = (v) => v ? String(v).replace(' ', 'T').slice(0, 16) : '';

const templates = [
    {
        id: 'romantic-luxury',
        name: 'Romantic Luxury',
        tag: 'Default',
        desc: 'Palet Blush Pink & Gold, tipografi Playfair Display & Great Vibes yang romantis dan mewah.',
        bg: 'from-[#FFF9F9] to-[#F7CAC9]/25',
        border: 'border-[#D4AF37]',
        swatches: ['#F7CAC9', '#D4AF37', '#FAF9F6', '#333333'],
        icon: '🌸'
    },
    {
        id: 'minang-traditional',
        name: 'Minangkabau Adat',
        tag: 'Tradisional',
        desc: 'Nuansa etnik Minang dengan Rumah Gadang, Jam Gadang, dan motif Songket merah marun & emas.',
        bg: 'from-[#4A0E0E]/10 to-[#6B1414]/20',
        border: 'border-[#C9A227]',
        swatches: ['#6B1414', '#C9A227', '#F5EFE0', '#3A2412'],
        icon: '🏛️'
    },
    {
        id: 'botanical-nature',
        name: 'Botanical Nature',
        tag: 'Rustic & Garden',
        desc: 'Nuansa alam asri dengan sentuhan Sage Green, dedaunan eucalyptus dan nuansa hangat organik.',
        bg: 'from-[#F4F7F4] to-[#87A987]/20',
        border: 'border-[#4A6B53]',
        swatches: ['#4A6B53', '#87A987', '#F4F7F4', '#2D3A2F'],
        icon: '🌿'
    },
    {
        id: 'classic-royal',
        name: 'Classic Royal',
        tag: 'Formal & Megah',
        desc: 'Nuansa pesta kerajaan berkelas dengan palet Midnight Navy, Champagne Gold & emblem megah.',
        bg: 'from-[#0F1E36]/10 to-[#1E293B]/20',
        border: 'border-[#D4AF37]',
        swatches: ['#0F1E36', '#D4AF37', '#F8F9FA', '#1A202C'],
        icon: '👑'
    },
];

const form = useForm({
    invitation_template: props.project.invitation_template || 'romantic-luxury',
    groom_name: props.project.groom_name || '',
    bride_name: props.project.bride_name || '',
    existing_photos: [...(props.project.prewed_photos || [])],
    photos: [],
    music_url: props.project.music_url || '',
    akad_location: props.project.akad_location || '',
    akad_datetime: toInput(props.project.akad_datetime),
    akad_maps_url: props.project.akad_maps_url || '',
    resepsi_location: props.project.resepsi_location || '',
    resepsi_datetime: toInput(props.project.resepsi_datetime),
    resepsi_maps_url: props.project.resepsi_maps_url || '',
});

// New uploads (with local preview)
const newPhotos = ref([]);
const totalPhotos = computed(() => form.existing_photos.length + newPhotos.value.length);

const onFilesSelected = (e) => {
    for (const f of e.target.files) {
        if (totalPhotos.value >= 10) break;
        newPhotos.value.push({ file: f, url: URL.createObjectURL(f) });
    }
    form.photos = newPhotos.value.map((p) => p.file);
    e.target.value = '';
};

const removeNew = (i) => {
    newPhotos.value.splice(i, 1);
    form.photos = newPhotos.value.map((p) => p.file);
};

const removeExisting = (i) => {
    form.existing_photos.splice(i, 1);
};

const generate = () => {
    form.post(route('projects.invitation.update', props.project.id), {
        forceFormData: true,
        preserveScroll: true,
    });
};

// Shareable link
const shareUrl = computed(() => `${window.location.origin}/undangan/${props.project.slug}`);
const copied = ref(false);
const copyLink = async () => {
    await navigator.clipboard.writeText(shareUrl.value);
    copied.value = true;
    setTimeout(() => (copied.value = false), 2000);
};

const inputClass =
    'w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 p-3 text-slate-800 dark:text-slate-100 focus:ring-gold-500 focus:border-gold-500 transition';
</script>

<template>
    <Head :title="'Pengaturan Undangan Online - ' + project.name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-serif font-bold tracking-tight text-charcoal-800 dark:text-ivory-100">
                        Desain &amp; Generate Undangan Online
                    </h2>
                    <p class="text-xs text-charcoal-500 dark:text-slate-400 mt-0.5">
                        Pilih tema visual, lengkapi data mempelai, dan bagikan tautan eksklusif kepada para tamu undangan.
                    </p>
                </div>
                <div v-if="project.is_published" class="flex items-center gap-2">
                    <a :href="shareUrl" target="_blank"
                       class="inline-flex items-center gap-2 px-4 py-2 bg-gold-500 hover:bg-gold-600 text-white rounded-xl text-sm font-semibold shadow-sm transition">
                        <span>👁️ Lihat Undangan Live</span>
                    </a>
                </div>
            </div>
        </template>

        <div class="py-10 bg-ivory-50 dark:bg-slate-900 min-h-screen">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 flex flex-col gap-6">

                <!-- Published link banner -->
                <div v-if="project.is_published"
                     class="bg-blush-50 dark:bg-slate-800/80 border-2 border-gold-400/40 rounded-3xl p-5 sm:p-6 shadow-sm">
                    <div class="flex items-center justify-between gap-2 mb-2">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            <p class="text-sm font-bold text-charcoal-800 dark:text-ivory-100 font-serif">
                                Undangan Siap Disebarkan!
                            </p>
                        </div>
                        <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-gold-100 text-gold-800 border border-gold-300/50">
                            Tema Aktif: {{ templates.find(t => t.id === form.invitation_template)?.name || 'Romantic Luxury' }}
                        </span>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-2 mt-3">
                        <input readonly :value="shareUrl" :class="inputClass" class="text-xs sm:text-sm font-mono" />
                        <div class="flex gap-2">
                            <button @click="copyLink" type="button"
                                    class="shrink-0 px-4 py-2.5 bg-gold-500 hover:bg-gold-600 text-white rounded-xl text-sm font-semibold transition flex items-center gap-1.5 shadow-sm">
                                <span>📋</span>
                                <span>{{ copied ? 'Tersalin!' : 'Salin Tautan' }}</span>
                            </button>
                            <a :href="shareUrl" target="_blank"
                               class="shrink-0 px-4 py-2.5 border border-gold-300 dark:border-slate-700 bg-white dark:bg-slate-800 rounded-xl text-sm font-semibold text-charcoal-700 dark:text-slate-200 hover:bg-gold-50 dark:hover:bg-slate-700 transition flex items-center gap-1.5">
                                <span>↗️</span>
                                <span>Buka</span>
                            </a>
                        </div>
                    </div>
                </div>

                <form @submit.prevent="generate" class="bg-white dark:bg-slate-800 rounded-3xl border border-gold-200/60 dark:border-slate-700 p-6 sm:p-8 flex flex-col gap-8 shadow-sm">

                    <!-- BAGIAN 1: PEMILIHAN TEMA / TEMPLATE -->
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="text-lg font-serif font-bold text-charcoal-800 dark:text-ivory-100 flex items-center gap-2">
                                    <span>🎨</span>
                                    <span>Pilih Tema Desain Undangan</span>
                                </h3>
                                <p class="text-xs text-charcoal-500 dark:text-slate-400">
                                    Ganti tema kapan saja. Tampilan undangan digital tamu akan langsung menyesuaikan otomatis.
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div v-for="t in templates" :key="t.id"
                                 @click="form.invitation_template = t.id"
                                 class="relative cursor-pointer rounded-2xl border-2 p-4 transition-all duration-200 hover:shadow-md flex flex-col justify-between"
                                 :class="[
                                     form.invitation_template === t.id
                                         ? 'border-gold-500 bg-gradient-to-br ' + t.bg + ' ring-2 ring-gold-400/30 shadow-md'
                                         : 'border-slate-200 dark:border-slate-700 hover:border-gold-300 bg-white dark:bg-slate-850 opacity-85 hover:opacity-100'
                                 ]">
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="flex items-center gap-2">
                                            <span class="text-2xl">{{ t.icon }}</span>
                                            <h4 class="font-serif font-bold text-base text-charcoal-800 dark:text-ivory-100">
                                                {{ t.name }}
                                            </h4>
                                        </div>
                                        <span v-if="form.invitation_template === t.id"
                                              class="px-2 py-0.5 rounded-full bg-gold-500 text-white text-[11px] font-bold flex items-center gap-1 shadow-sm">
                                            <span>✓</span> Terpilih
                                        </span>
                                        <span v-else class="text-[11px] font-medium px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300">
                                            {{ t.tag }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-charcoal-600 dark:text-slate-300 leading-relaxed mb-4">
                                        {{ t.desc }}
                                    </p>
                                </div>

                                <!-- Color Swatches -->
                                <div class="pt-3 border-t border-slate-200/60 dark:border-slate-700/60 flex items-center justify-between">
                                    <span class="text-[10px] uppercase tracking-wider font-semibold text-charcoal-400 dark:text-slate-400">
                                        Palet Warna:
                                    </span>
                                    <div class="flex items-center gap-1.5">
                                        <span v-for="(c, ci) in t.swatches" :key="ci"
                                              class="w-4 h-4 rounded-full border border-black/10 shadow-sm"
                                              :style="{ backgroundColor: c }"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- BAGIAN 2: NAMA MEMPELAI -->
                    <div class="border-t border-gold-100 dark:border-slate-700 pt-6">
                        <h3 class="text-lg font-serif font-bold text-charcoal-800 dark:text-ivory-100 mb-1 flex items-center gap-2">
                            <span>💍</span>
                            <span>Informasi Mempelai</span>
                        </h3>
                        <p class="text-xs text-charcoal-500 dark:text-slate-400 mb-4">
                            Nama lengkap kedua pengantin yang akan ditampilkan pada sampul dan header undangan.
                        </p>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-charcoal-700 dark:text-slate-200 mb-2">
                                    Mempelai Pria / Marapulai
                                </label>
                                <input type="text" v-model="form.groom_name" placeholder="Contoh: Muhammad Farhan, S.Kom" :class="inputClass" />
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-charcoal-700 dark:text-slate-200 mb-2">
                                    Mempelai Wanita / Anak Daro
                                </label>
                                <input type="text" v-model="form.bride_name" placeholder="Contoh: Siti Aisyah, S.Ked" :class="inputClass" />
                            </div>
                        </div>
                    </div>

                    <!-- BAGIAN 3: FOTO PREWEDDING -->
                    <div class="border-t border-gold-100 dark:border-slate-700 pt-6">
                        <div class="flex items-center justify-between mb-1">
                            <h3 class="text-lg font-serif font-bold text-charcoal-800 dark:text-ivory-100 flex items-center gap-2">
                                <span>📸</span>
                                <span>Galeri Foto Prewedding</span>
                            </h3>
                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-gold-50 dark:bg-slate-700 text-gold-700 dark:text-gold-300">
                                {{ totalPhotos }}/10 Foto
                            </span>
                        </div>
                        <p class="text-xs text-charcoal-500 dark:text-slate-400 mb-4">
                            Unggah hingga 10 foto prewedding untuk ditampilkan dalam galeri interaktif.
                        </p>

                        <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-3">
                            <div v-for="(path, i) in form.existing_photos" :key="'e' + i" class="relative group">
                                <img :src="'/storage/' + path" class="h-28 w-full object-cover rounded-2xl border border-gold-200/60 dark:border-slate-700 shadow-sm" />
                                <button type="button" @click="removeExisting(i)"
                                        class="absolute -top-1.5 -right-1.5 bg-rose-600 hover:bg-rose-700 text-white w-6 h-6 rounded-full text-xs font-bold flex items-center justify-center shadow transition">
                                    &times;
                                </button>
                            </div>
                            <div v-for="(p, i) in newPhotos" :key="'n' + i" class="relative group">
                                <img :src="p.url" class="h-28 w-full object-cover rounded-2xl border border-gold-200/60 dark:border-slate-700 shadow-sm" />
                                <button type="button" @click="removeNew(i)"
                                        class="absolute -top-1.5 -right-1.5 bg-rose-600 hover:bg-rose-700 text-white w-6 h-6 rounded-full text-xs font-bold flex items-center justify-center shadow transition">
                                    &times;
                                </button>
                            </div>
                            <label v-if="totalPhotos < 10"
                                   class="h-28 flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-gold-300/80 dark:border-slate-700 cursor-pointer text-gold-600 dark:text-gold-400 hover:bg-gold-50/50 dark:hover:bg-slate-700/50 transition">
                                <span class="text-2xl font-bold">+</span>
                                <span class="text-[11px] font-semibold mt-1">Tambah</span>
                                <input type="file" accept="image/*" multiple class="hidden" @change="onFilesSelected" />
                            </label>
                        </div>
                        <p v-if="form.errors['photos.0']" class="text-xs text-rose-500 mt-1.5">{{ form.errors['photos.0'] }}</p>
                    </div>

                    <!-- BAGIAN 4: MUSIK LATAR -->
                    <div class="border-t border-gold-100 dark:border-slate-700 pt-6">
                        <h3 class="text-lg font-serif font-bold text-charcoal-800 dark:text-ivory-100 mb-1 flex items-center gap-2">
                            <span>🎵</span>
                            <span>Musik Latar (Background Music)</span>
                        </h3>
                        <p class="text-xs text-charcoal-500 dark:text-slate-400 mb-3">
                            Masukkan tautan video YouTube atau URL berkas audio (.mp3) untuk diputar saat tamu membuka undangan.
                        </p>
                        <input type="url" v-model="form.music_url" placeholder="https://youtube.com/watch?v=... atau https://domain.com/musik.mp3" :class="inputClass" />
                        <p v-if="form.errors.music_url" class="text-xs text-rose-500 mt-1.5">{{ form.errors.music_url }}</p>
                    </div>

                    <!-- BAGIAN 5: AKAD NIKAH -->
                    <div class="border-t border-gold-100 dark:border-slate-700 pt-6 flex flex-col gap-3">
                        <h3 class="text-lg font-serif font-bold text-charcoal-800 dark:text-ivory-100 flex items-center gap-2">
                            <span>🕌</span>
                            <span>Waktu &amp; Lokasi Akad Nikah</span>
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-charcoal-700 dark:text-slate-200 mb-1.5">
                                    Waktu &amp; Tanggal Akad
                                </label>
                                <input type="datetime-local" v-model="form.akad_datetime" :class="inputClass" />
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-charcoal-700 dark:text-slate-200 mb-1.5">
                                    Tautan Google Maps Akad
                                </label>
                                <input type="url" v-model="form.akad_maps_url" placeholder="https://maps.google.com/..." :class="inputClass" />
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-charcoal-700 dark:text-slate-200 mb-1.5">
                                Lokasi &amp; Alamat Lengkap Akad
                            </label>
                            <input type="text" v-model="form.akad_location" placeholder="Masjid Agung / Gedung Serbaguna..." :class="inputClass" />
                        </div>
                    </div>

                    <!-- BAGIAN 6: RESEPSI -->
                    <div class="border-t border-gold-100 dark:border-slate-700 pt-6 flex flex-col gap-3">
                        <h3 class="text-lg font-serif font-bold text-charcoal-800 dark:text-ivory-100 flex items-center gap-2">
                            <span>🎉</span>
                            <span>Waktu &amp; Lokasi Resepsi Pernikahan</span>
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-charcoal-700 dark:text-slate-200 mb-1.5">
                                    Waktu &amp; Tanggal Resepsi
                                </label>
                                <input type="datetime-local" v-model="form.resepsi_datetime" :class="inputClass" />
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-charcoal-700 dark:text-slate-200 mb-1.5">
                                    Tautan Google Maps Resepsi
                                </label>
                                <input type="url" v-model="form.resepsi_maps_url" placeholder="https://maps.google.com/..." :class="inputClass" />
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-charcoal-700 dark:text-slate-200 mb-1.5">
                                Lokasi &amp; Alamat Lengkap Resepsi
                            </label>
                            <input type="text" v-model="form.resepsi_location" placeholder="Grand Ballroom Hotel / Kediaman Mempelai..." :class="inputClass" />
                        </div>
                    </div>

                    <!-- ACTION BUTTON -->
                    <div class="border-t border-gold-200/80 dark:border-slate-700 pt-6 flex flex-col sm:flex-row items-center justify-between gap-4">
                        <p class="text-xs text-charcoal-500 dark:text-slate-400">
                            Perubahan tema dan data undangan akan langsung tersimpan dan terpublikasi secara real-time.
                        </p>
                        <button type="submit" :disabled="form.processing"
                                class="w-full sm:w-auto px-8 py-3.5 bg-gradient-to-r from-gold-500 to-gold-600 hover:from-gold-600 hover:to-gold-700 text-white rounded-2xl font-serif font-bold tracking-wide shadow-md hover:shadow-lg transition-all duration-200 disabled:opacity-50 flex items-center justify-center gap-2">
                            <span>✨</span>
                            <span>{{ project.is_published ? 'Simpan & Perbarui Undangan' : 'Generate & Publikasikan Undangan' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
