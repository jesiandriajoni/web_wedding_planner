<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';

const props = defineProps({
    project: Object
});

const photoUrl = (path) => '/storage/' + path;

const formatDateTime = (v) => {
    if (!v) return null;
    return new Date(v).toLocaleString('id-ID', {
        weekday: 'long', day: 'numeric', month: 'long', year: 'numeric',
        hour: '2-digit', minute: '2-digit',
    });
};

const hasCoupleNames = computed(() => !!(props.project.groom_name || props.project.bride_name));

const groomInitial = computed(() => (props.project.groom_name || 'G').trim().charAt(0).toUpperCase());
const brideInitial = computed(() => (props.project.bride_name || 'B').trim().charAt(0).toUpperCase());

// Invited guest name from personalized link (?to=Name) or ?guest=ID
const invitedName = ref(new URLSearchParams(window.location.search).get('to') || '');

onMounted(() => {
    const params = new URLSearchParams(window.location.search);
    const guestId = params.get('guest') || params.get('id');
    const toParam = params.get('to');

    if (guestId && props.project.all_guests) {
        const found = props.project.all_guests.find(g => String(g.id) === String(guestId));
        if (found) {
            rsvpForm.guest_id = found.id;
            if (!invitedName.value) invitedName.value = found.name;
        }
    } else if (toParam && props.project.all_guests) {
        const found = props.project.all_guests.find(g => g.name.trim().toLowerCase() === toParam.trim().toLowerCase());
        if (found) {
            rsvpForm.guest_id = found.id;
        }
    }
});

// Prewed slideshow
const currentSlide = ref(0);
const slideCount = computed(() => (props.project.prewed_photos || []).length);
const nextSlide = () => { if (slideCount.value > 0) currentSlide.value = (currentSlide.value + 1) % slideCount.value; };
const prevSlide = () => { if (slideCount.value > 0) currentSlide.value = (currentSlide.value - 1 + slideCount.value) % slideCount.value; };

// Music plays in the background
const musicEmbed = computed(() => {
    const url = props.project.music_url;
    if (!url) return null;
    const yt = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([\w-]{11})/);
    if (yt) return { type: 'youtube', src: `https://www.youtube.com/embed/${yt[1]}?autoplay=1&loop=1&playlist=${yt[1]}` };
    if (/\.(mp3|ogg)(\?.*)?$/i.test(url)) return { type: 'audio', src: url };
    return { type: 'link', src: url };
});

const musicPlaying = ref(false);
const audioEl = ref(null);
const toggleMusic = () => {
    musicPlaying.value = !musicPlaying.value;
    if (musicEmbed.value?.type === 'audio' && audioEl.value) {
        musicPlaying.value ? audioEl.value.play() : audioEl.value.pause();
    }
};

// RSVP Form
const rsvpForm = useForm({
    guest_id: '',
    rsvp: 'hadir',
    pax: 2,
    guest_book_message: ''
});

const submitSuccess = ref(false);

const submitRSVP = () => {
    rsvpForm.post(route('public.invitation.rsvp', props.project.slug), {
        onSuccess: () => {
            submitSuccess.value = true;
            rsvpForm.reset('guest_book_message');
        }
    });
};

// Current Template Configuration
const templateKey = computed(() => props.project.invitation_template || 'romantic-luxury');

const theme = computed(() => {
    switch (templateKey.value) {
        case 'minang-traditional':
            return {
                id: 'minang-traditional',
                bodyBg: 'bg-[#F5EFE0] text-[#3A2412]',
                heroBg: 'bg-gradient-to-b from-[#4A0E0E] via-[#6B1414] to-[#7B1E1E] text-[#F5EFE0]',
                heroAccent: 'text-[#C9A227]',
                heroSubtitle: "Walimatul 'Urusy",
                cardBg: 'bg-[#FBF7EE] border border-[#C9A227]/30 text-[#3A2412]',
                cardHeader: 'text-[#6B1414]',
                cardSub: 'text-[#8A5A2B]',
                accentColor: '#C9A227',
                btnPrimary: 'bg-[#6B1414] hover:bg-[#4A0E0E] text-[#F5EFE0]',
                fieldClass: 'w-full rounded-xl border border-[#C9A227]/40 bg-[#FBF7EE] p-3 text-[#3A2412] focus:ring-[#C9A227] focus:border-[#C9A227]',
                footerBg: 'bg-[#4A0E0E] text-[#C9A227] border-[#C9A227]/40',
                musicBtn: 'bg-[#6B1414] text-[#C9A227] border-[#C9A227]/50 hover:bg-[#4A0E0E]',
                badgeClass: 'bg-[#C9A227]/15 text-[#6B1414]'
            };
        case 'botanical-nature':
            return {
                id: 'botanical-nature',
                bodyBg: 'bg-[#F4F7F4] text-[#243328]',
                heroBg: 'bg-gradient-to-b from-[#13241D] via-[#1E382E] to-[#2B4E41] text-[#F4F7F4]',
                heroAccent: 'text-[#9BC4A5]',
                heroSubtitle: 'Wedding Celebration',
                cardBg: 'bg-white border border-[#87A987]/40 text-[#243328]',
                cardHeader: 'text-[#1E382E]',
                cardSub: 'text-[#4A6B53]',
                accentColor: '#608066',
                btnPrimary: 'bg-[#2B4E41] hover:bg-[#1E382E] text-[#F4F7F4]',
                fieldClass: 'w-full rounded-xl border border-[#87A987]/50 bg-[#FAFCFA] p-3 text-[#243328] focus:ring-[#4A6B53] focus:border-[#4A6B53]',
                footerBg: 'bg-[#13241D] text-[#9BC4A5] border-[#4A6B53]/40',
                musicBtn: 'bg-[#2B4E41] text-[#9BC4A5] border-[#87A987]/50 hover:bg-[#1E382E]',
                badgeClass: 'bg-[#87A987]/20 text-[#1E382E]'
            };
        case 'classic-royal':
            return {
                id: 'classic-royal',
                bodyBg: 'bg-[#F8F9FA] text-[#1A202C]',
                heroBg: 'bg-gradient-to-b from-[#09111E] via-[#0F1E36] to-[#1A2A44] text-[#F8F9FA]',
                heroAccent: 'text-[#D4AF37]',
                heroSubtitle: 'The Royal Wedding',
                cardBg: 'bg-white border-2 border-[#D4AF37]/35 text-[#1A202C]',
                cardHeader: 'text-[#0F1E36]',
                cardSub: 'text-[#4A5568]',
                accentColor: '#D4AF37',
                btnPrimary: 'bg-gradient-to-r from-[#D4AF37] to-[#B89628] hover:from-[#B89628] hover:to-[#997C20] text-slate-900 font-bold',
                fieldClass: 'w-full rounded-xl border border-[#D4AF37]/40 bg-[#FAFBFD] p-3 text-[#1A202C] focus:ring-[#D4AF37] focus:border-[#D4AF37]',
                footerBg: 'bg-[#09111E] text-[#D4AF37] border-[#D4AF37]/40',
                musicBtn: 'bg-[#0F1E36] text-[#D4AF37] border-[#D4AF37]/50 hover:bg-[#1A2A44]',
                badgeClass: 'bg-[#D4AF37]/15 text-[#0F1E36]'
            };
        case 'romantic-luxury':
        default:
            return {
                id: 'romantic-luxury',
                bodyBg: 'bg-[#FAF9F6] text-[#333333]',
                heroBg: 'bg-gradient-to-b from-[#2D1B22] via-[#4A2634] to-[#603244] text-[#FAF9F6]',
                heroAccent: 'text-[#F7CAC9]',
                heroSubtitle: 'The Wedding Celebration',
                cardBg: 'bg-white border border-[#F7CAC9] shadow-sm text-[#333333]',
                cardHeader: 'text-[#4A2634]',
                cardSub: 'text-[#7A4B5D]',
                accentColor: '#D4AF37',
                btnPrimary: 'bg-gradient-to-r from-[#D4AF37] to-[#C19B27] hover:from-[#C19B27] hover:to-[#A8841B] text-white font-bold shadow-sm',
                fieldClass: 'w-full rounded-xl border border-[#F7CAC9] bg-[#FFFDFC] p-3 text-[#333333] focus:ring-[#D4AF37] focus:border-[#D4AF37]',
                footerBg: 'bg-[#2D1B22] text-[#F7CAC9] border-[#D4AF37]/40',
                musicBtn: 'bg-[#4A2634] text-[#F7CAC9] border-[#F7CAC9]/50 hover:bg-[#2D1B22]',
                badgeClass: 'bg-[#F7CAC9]/30 text-[#4A2634]'
            };
    }
});
</script>

<template>
    <Head :title="'Undangan Pernikahan - ' + project.name" />

    <div class="min-h-screen font-sans selection:bg-gold-500/30" :class="theme.bodyBg">

        <!-- ========================================== -->
        <!-- ===== 1. TEMPLATE HERO HEADERS ===== -->
        <!-- ========================================== -->

        <!-- TEMPLATE A: MINANG TRADITIONAL HERO -->
        <header v-if="theme.id === 'minang-traditional'" class="relative overflow-hidden" :class="theme.heroBg">
            <!-- Songket top border -->
            <div class="h-3 w-full bg-[repeating-linear-gradient(135deg,#C9A227_0,#C9A227_10px,#4A0E0E_10px,#4A0E0E_20px)]"></div>

            <!-- Rumah Gadang + Jam Gadang backdrop -->
            <svg class="absolute bottom-0 left-0 w-full opacity-20 text-[#C9A227]" viewBox="0 0 400 140" fill="currentColor" preserveAspectRatio="xMidYMax meet" aria-hidden="true">
                <path d="M20 140 L20 96 C40 96 30 60 55 66 C45 74 70 78 78 78 C66 62 90 56 96 70 C88 60 112 60 118 74 C110 62 134 60 140 74 C132 60 156 62 148 78 C170 78 156 74 176 66 C170 96 190 96 190 140 Z" />
                <rect x="26" y="96" width="158" height="44" />
                <g transform="translate(300 0)">
                    <path d="M20 44 C40 44 30 18 40 22 C34 30 50 32 52 32 C40 20 62 20 60 34 C58 22 80 22 80 44 Z" />
                    <rect x="34" y="44" width="52" height="14" />
                    <rect x="40" y="58" width="40" height="82" />
                    <circle cx="60" cy="82" r="13" fill="#4A0E0E" />
                    <rect x="26" y="132" width="68" height="8" />
                </g>
            </svg>

            <div class="relative px-4 py-20 md:py-28 flex flex-col items-center text-center">
                <p class="text-xs uppercase tracking-[0.35em] text-[#C9A227] font-semibold mb-6">Walimatul 'Urusy</p>

                <img src="/images/minang-couple.png" alt="Marapulai & Anak Daro"
                     class="w-56 md:w-72 mb-6 drop-shadow-2xl float-slow" />

                <template v-if="hasCoupleNames">
                    <h1 class="text-3xl md:text-5xl font-serif font-bold text-[#F5EFE0]">{{ project.groom_name || '—' }}</h1>
                    <span class="my-3 text-2xl md:text-4xl font-serif text-[#C9A227]">&amp;</span>
                    <h1 class="text-3xl md:text-5xl font-serif font-bold text-[#F5EFE0]">{{ project.bride_name || '—' }}</h1>
                </template>
                <h1 v-else class="text-4xl md:text-6xl font-serif font-bold text-[#F5EFE0]">{{ project.name }}</h1>

                <div class="mt-8 flex items-center gap-3 text-[#C9A227]">
                    <span class="h-px w-10 bg-[#C9A227]/60"></span>
                    <span class="text-sm md:text-base tracking-wide">{{ project.wedding_date }}</span>
                    <span class="h-px w-10 bg-[#C9A227]/60"></span>
                </div>
            </div>

            <div class="h-3 w-full bg-[repeating-linear-gradient(135deg,#C9A227_0,#C9A227_10px,#4A0E0E_10px,#4A0E0E_20px)]"></div>
        </header>

        <!-- TEMPLATE B: BOTANICAL NATURE HERO -->
        <header v-else-if="theme.id === 'botanical-nature'" class="relative overflow-hidden" :class="theme.heroBg">
            <!-- Leaf branch top vector -->
            <div class="absolute top-0 inset-x-0 h-16 opacity-25 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-[#9BC4A5] via-transparent to-transparent"></div>

            <div class="relative px-4 py-20 md:py-28 flex flex-col items-center text-center">
                <!-- Botanical Leaf Wreath SVG -->
                <div class="w-24 h-24 mb-4 text-[#9BC4A5] flex items-center justify-center">
                    <svg viewBox="0 0 100 100" class="w-full h-full" fill="currentColor">
                        <path d="M50 10 C30 10 15 25 15 45 C15 65 30 80 50 80 C70 80 85 65 85 45 C85 25 70 10 50 10 Z" fill="none" stroke="currentColor" stroke-width="2" stroke-dasharray="4,3"/>
                        <path d="M50 20 C42 26 38 35 44 44 C48 38 48 28 50 20 Z M50 20 C58 26 62 35 56 44 C52 38 52 28 50 20 Z" />
                        <path d="M25 45 C32 40 40 45 40 52 C32 52 27 48 25 45 Z M75 45 C68 40 60 45 60 52 C68 52 73 48 75 45 Z" />
                    </svg>
                </div>

                <p class="text-xs uppercase tracking-[0.4em] text-[#9BC4A5] font-semibold mb-3">
                    WEDDING CELEBRATION
                </p>

                <template v-if="hasCoupleNames">
                    <h1 class="text-3xl md:text-6xl font-serif font-bold text-[#F4F7F4] tracking-tight">
                        {{ project.groom_name || '—' }}
                    </h1>
                    <span class="my-2 text-2xl md:text-3xl font-script text-[#9BC4A5]">&amp;</span>
                    <h1 class="text-3xl md:text-6xl font-serif font-bold text-[#F4F7F4] tracking-tight">
                        {{ project.bride_name || '—' }}
                    </h1>
                </template>
                <h1 v-else class="text-4xl md:text-6xl font-serif font-bold text-[#F4F7F4]">{{ project.name }}</h1>

                <div class="mt-8 flex items-center gap-3 text-[#9BC4A5]">
                    <span class="h-px w-12 bg-[#9BC4A5]/50"></span>
                    <span class="text-xs md:text-sm uppercase tracking-widest">{{ project.wedding_date }}</span>
                    <span class="h-px w-12 bg-[#9BC4A5]/50"></span>
                </div>
            </div>

            <!-- Wave divider -->
            <div class="h-6 w-full bg-[#F4F7F4] rounded-t-[2.5rem]"></div>
        </header>

        <!-- TEMPLATE C: CLASSIC ROYAL HERO -->
        <header v-else-if="theme.id === 'classic-royal'" class="relative overflow-hidden" :class="theme.heroBg">
            <!-- Starry gold sparkles border -->
            <div class="h-2 w-full bg-gradient-to-r from-transparent via-[#D4AF37] to-transparent opacity-80"></div>

            <div class="relative px-4 py-20 md:py-28 flex flex-col items-center text-center">
                <!-- Royal Monogram Crest -->
                <div class="w-24 h-24 mb-5 rounded-full border-2 border-[#D4AF37] flex flex-col items-center justify-center bg-[#0F1E36]/80 shadow-[0_0_25px_rgba(212,175,55,0.3)]">
                    <span class="text-[10px] uppercase tracking-widest text-[#D4AF37] font-semibold">Crest</span>
                    <div class="flex items-center gap-1 text-[#D4AF37] font-serif font-bold text-xl">
                        <span>{{ groomInitial }}</span>
                        <span class="text-xs">&</span>
                        <span>{{ brideInitial }}</span>
                    </div>
                </div>

                <p class="text-xs uppercase tracking-[0.5em] text-[#D4AF37] font-semibold mb-4">
                    ROYAL WEDDING INVITATION
                </p>

                <template v-if="hasCoupleNames">
                    <h1 class="text-3xl md:text-5xl font-serif font-bold tracking-wider text-[#F8F9FA]">
                        {{ project.groom_name || '—' }}
                    </h1>
                    <span class="my-3 text-2xl md:text-3xl font-serif text-[#D4AF37] font-light">&amp;</span>
                    <h1 class="text-3xl md:text-5xl font-serif font-bold tracking-wider text-[#F8F9FA]">
                        {{ project.bride_name || '—' }}
                    </h1>
                </template>
                <h1 v-else class="text-4xl md:text-6xl font-serif font-bold text-[#F8F9FA]">{{ project.name }}</h1>

                <div class="mt-8 flex items-center gap-4 text-[#D4AF37]">
                    <span class="h-px w-16 bg-gradient-to-r from-transparent to-[#D4AF37]"></span>
                    <span class="text-xs md:text-sm uppercase tracking-[0.2em] font-medium">{{ project.wedding_date }}</span>
                    <span class="h-px w-16 bg-gradient-to-l from-transparent to-[#D4AF37]"></span>
                </div>
            </div>

            <div class="h-2 w-full bg-gradient-to-r from-transparent via-[#D4AF37] to-transparent opacity-80"></div>
        </header>

        <!-- TEMPLATE D: ROMANTIC LUXURY HERO (DEFAULT) -->
        <header v-else class="relative overflow-hidden" :class="theme.heroBg">
            <!-- Shimmer gold top bar -->
            <div class="h-2.5 w-full bg-gradient-to-r from-[#D4AF37]/30 via-[#D4AF37] to-[#D4AF37]/30"></div>

            <div class="relative px-4 py-20 md:py-28 flex flex-col items-center text-center">
                <span class="font-script text-3xl md:text-4xl text-[#F7CAC9] mb-1">
                    The Wedding of
                </span>
                <p class="text-[11px] uppercase tracking-[0.4em] text-[#D4AF37] font-semibold mb-6">
                    BERSATU DALAM KASIH &amp; JANJI SUCI
                </p>

                <template v-if="hasCoupleNames">
                    <h1 class="text-3xl md:text-6xl font-serif font-bold text-[#FAF9F6] tracking-tight">
                        {{ project.groom_name || '—' }}
                    </h1>
                    <span class="my-2 text-3xl md:text-4xl font-script text-[#D4AF37]">&amp;</span>
                    <h1 class="text-3xl md:text-6xl font-serif font-bold text-[#FAF9F6] tracking-tight">
                        {{ project.bride_name || '—' }}
                    </h1>
                </template>
                <h1 v-else class="text-4xl md:text-6xl font-serif font-bold text-[#FAF9F6]">{{ project.name }}</h1>

                <div class="mt-8 flex items-center gap-3 text-[#D4AF37]">
                    <span class="h-px w-12 bg-[#D4AF37]/60"></span>
                    <span class="text-sm md:text-base font-serif tracking-widest text-[#F7CAC9]">{{ project.wedding_date }}</span>
                    <span class="h-px w-12 bg-[#D4AF37]/60"></span>
                </div>
            </div>

            <div class="h-2.5 w-full bg-gradient-to-r from-[#D4AF37]/30 via-[#D4AF37] to-[#D4AF37]/30"></div>
        </header>

        <!-- ========================================== -->
        <!-- ===== BACKGROUND MUSIC CONTROLS ===== -->
        <!-- ========================================== -->
        <template v-if="musicEmbed">
            <iframe v-if="musicEmbed.type === 'youtube' && musicPlaying" :src="musicEmbed.src"
                    class="fixed w-px h-px opacity-0 pointer-events-none -z-10" allow="autoplay; encrypted-media"></iframe>
            <audio v-else-if="musicEmbed.type === 'audio'" ref="audioEl" :src="musicEmbed.src" loop></audio>

            <button v-if="musicEmbed.type !== 'link'" @click="toggleMusic"
                    :aria-label="musicPlaying ? 'Matikan musik' : 'Putar musik'"
                    class="fixed bottom-5 right-5 z-50 w-12 h-12 rounded-full shadow-xl border flex items-center justify-center transition-transform hover:scale-110"
                    :class="theme.musicBtn">
                <span v-if="musicPlaying" class="text-lg">♫</span>
                <span v-else class="text-lg">▶</span>
            </button>
        </template>

        <!-- ========================================== -->
        <!-- ===== MAIN CONTENT BODY ===== -->
        <!-- ========================================== -->
        <main class="max-w-4xl mx-auto px-4 py-14 flex flex-col gap-12">

            <!-- Minang Banner (Only for Minangkabau theme) -->
            <section v-if="theme.id === 'minang-traditional'" class="relative flex flex-col items-center">
                <img src="/images/rumah-gadang.png" alt="Rumah Gadang"
                     class="w-full max-w-2xl mix-blend-multiply opacity-95 float-slow" />
                <div class="text-center mt-2">
                    <div class="flex items-center justify-center gap-3 text-[#C9A227] mb-3">
                        <span class="h-px w-10 bg-[#C9A227]/60"></span>
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2 L15 9 L22 12 L15 15 L12 22 L9 15 L2 12 L9 9 Z" /></svg>
                        <span class="h-px w-10 bg-[#C9A227]/60"></span>
                    </div>
                    <p class="font-serif text-2xl md:text-3xl text-[#6B1414]">
                        {{ project.groom_name || 'Marapulai' }}
                        <span class="text-[#C9A227]">&amp;</span>
                        {{ project.bride_name || 'Anak Daro' }}
                    </p>
                </div>
            </section>

            <!-- Kepada Yth (Tamu Undangan) -->
            <section class="flex flex-col items-center gap-2 text-center py-7 px-8 rounded-3xl shadow-sm max-w-md mx-auto w-full transition"
                     :class="theme.cardBg">
                <p class="text-xs uppercase tracking-widest font-semibold" :class="theme.cardSub">
                    Kepada Yth. Bapak/Ibu/Saudara/i
                </p>
                <h2 class="font-serif text-2xl md:text-3xl font-bold" :class="theme.cardHeader">
                    {{ invitedName || 'Tamu Undangan' }}
                </h2>
                <div class="flex items-center justify-center gap-2 mt-1 opacity-75">
                    <span class="h-px w-8 bg-current"></span>
                    <span class="text-xs font-medium" :class="theme.cardSub">Di Tempat</span>
                    <span class="h-px w-8 bg-current"></span>
                </div>
            </section>

            <!-- Section Divider -->
            <div class="flex items-center justify-center gap-3 opacity-60">
                <span class="h-px w-16 bg-current"></span>
                <span class="text-base">✨</span>
                <span class="h-px w-16 bg-current"></span>
            </div>

            <!-- Akad & Resepsi Cards -->
            <section v-if="project.akad_location || project.resepsi_location" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Akad -->
                <div v-if="project.akad_location" class="p-7 rounded-3xl shadow-sm text-center flex flex-col gap-2.5"
                     :class="theme.cardBg">
                    <div class="text-2xl mb-1">🕌</div>
                    <h3 class="text-xl font-serif font-bold" :class="theme.cardHeader">Akad Nikah</h3>
                    <p v-if="formatDateTime(project.akad_datetime)" class="text-sm font-semibold" :class="theme.cardSub">
                        {{ formatDateTime(project.akad_datetime) }}
                    </p>
                    <p class="text-sm opacity-90 whitespace-pre-line">{{ project.akad_location }}</p>
                    <a v-if="project.akad_maps_url" :href="project.akad_maps_url" target="_blank"
                       class="mt-3 inline-flex items-center justify-center gap-1.5 text-sm font-bold hover:underline"
                       :style="{ color: theme.accentColor }">
                        <span>📍</span>
                        <span>Petunjuk Arah Google Maps</span>
                    </a>
                </div>

                <!-- Resepsi -->
                <div v-if="project.resepsi_location" class="p-7 rounded-3xl shadow-sm text-center flex flex-col gap-2.5"
                     :class="theme.cardBg">
                    <div class="text-2xl mb-1">🎉</div>
                    <h3 class="text-xl font-serif font-bold" :class="theme.cardHeader">Resepsi Pernikahan</h3>
                    <p v-if="formatDateTime(project.resepsi_datetime)" class="text-sm font-semibold" :class="theme.cardSub">
                        {{ formatDateTime(project.resepsi_datetime) }}
                    </p>
                    <p class="text-sm opacity-90 whitespace-pre-line">{{ project.resepsi_location }}</p>
                    <a v-if="project.resepsi_maps_url" :href="project.resepsi_maps_url" target="_blank"
                       class="mt-3 inline-flex items-center justify-center gap-1.5 text-sm font-bold hover:underline"
                       :style="{ color: theme.accentColor }">
                        <span>📍</span>
                        <span>Petunjuk Arah Google Maps</span>
                    </a>
                </div>
            </section>

            <!-- Prewed Photo Gallery (Slideshow) -->
            <section v-if="slideCount > 0" class="text-center">
                <h2 class="text-2xl font-serif font-bold mb-6" :class="theme.cardHeader">
                    Momen Kebahagiaan
                </h2>
                <div class="relative max-w-2xl mx-auto">
                    <div class="rounded-3xl overflow-hidden shadow-2xl border-2" :class="theme.cardBg">
                        <img :src="photoUrl(project.prewed_photos[currentSlide])"
                             class="w-full h-[55vh] md:h-[70vh] object-cover" />
                    </div>

                    <template v-if="slideCount > 1">
                        <button @click="prevSlide" aria-label="Sebelumnya"
                                class="absolute left-3 top-1/2 -translate-y-1/2 w-11 h-11 flex items-center justify-center rounded-full bg-black/60 text-white text-2xl hover:bg-black/80 transition backdrop-blur-sm">
                            ‹
                        </button>
                        <button @click="nextSlide" aria-label="Selanjutnya"
                                class="absolute right-3 top-1/2 -translate-y-1/2 w-11 h-11 flex items-center justify-center rounded-full bg-black/60 text-white text-2xl hover:bg-black/80 transition backdrop-blur-sm">
                            ›
                        </button>
                        <span class="absolute bottom-3 right-4 px-3 py-1 rounded-full bg-black/60 text-white text-xs backdrop-blur-sm">
                            {{ currentSlide + 1 }} / {{ slideCount }}
                        </span>
                    </template>
                </div>

                <div v-if="slideCount > 1" class="flex justify-center gap-2 mt-4">
                    <button v-for="(path, i) in project.prewed_photos" :key="i" @click="currentSlide = i"
                            :aria-label="'Foto ' + (i + 1)"
                            class="w-2.5 h-2.5 rounded-full transition-all"
                            :class="i === currentSlide ? 'w-6 bg-gold-500' : 'bg-slate-300 dark:bg-slate-700'"></button>
                </div>
            </section>

            <!-- Susunan Acara (Rundown) -->
            <section class="p-8 rounded-3xl shadow-sm" :class="theme.cardBg">
                <h2 class="text-2xl font-serif font-bold mb-6 text-center" :class="theme.cardHeader">
                    Susunan Acara
                </h2>
                <div v-if="(project.rundown || []).length > 0" class="flex flex-col gap-6 relative before:absolute before:left-4 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200 dark:before:bg-slate-700">
                    <div v-for="item in project.rundown" :key="item.id" class="flex gap-6 items-start relative pl-10">
                        <span class="absolute left-2.5 w-3.5 h-3.5 rounded-full bg-gold-500 border-4 border-white dark:border-slate-800"></span>
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-bold" :class="theme.cardHeader">{{ item.time }}</span>
                                <span class="text-xs font-medium" :class="theme.cardSub">| {{ item.assigned_to }}</span>
                            </div>
                            <h3 class="text-base font-bold mt-0.5">{{ item.activity }}</h3>
                            <p class="text-sm opacity-80 mt-1">{{ item.description }}</p>
                        </div>
                    </div>
                </div>
                <div v-else class="text-center py-6 text-sm" :class="theme.cardSub">
                    Rundown acara belum dipublikasikan.
                </div>
            </section>

            <!-- Konfirmasi Kehadiran (RSVP) -->
            <section class="p-8 rounded-3xl shadow-sm" :class="theme.cardBg">
                <h2 class="text-2xl font-serif font-bold mb-2 text-center" :class="theme.cardHeader">
                    Konfirmasi Kehadiran
                </h2>
                <p class="text-sm text-center mb-6" :class="theme.cardSub">
                    Mohon konfirmasi kehadiran Anda untuk membantu kelancaran persiapan acara.
                </p>

                <div v-if="submitSuccess" class="p-4 rounded-2xl text-center mb-6 text-sm font-semibold border" :class="theme.badgeClass">
                    ✨ Terima kasih! Konfirmasi kehadiran dan ucapan Anda berhasil disimpan.
                </div>

                <form @submit.prevent="submitRSVP" class="flex flex-col gap-4 max-w-md mx-auto">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider mb-1" :class="theme.cardSub">
                            Pilih Nama Anda
                        </label>
                        <select v-model="rsvpForm.guest_id" required :class="theme.fieldClass">
                            <option value="" disabled>-- Pilih nama undangan --</option>
                            <option v-for="guest in project.all_guests" :key="guest.id" :value="guest.id">
                                {{ guest.name }}
                            </option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider mb-1" :class="theme.cardSub">
                                Kehadiran
                            </label>
                            <select v-model="rsvpForm.rsvp" required :class="theme.fieldClass">
                                <option value="hadir">✓ Hadir</option>
                                <option value="absen">✕ Berhalangan</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider mb-1" :class="theme.cardSub">
                                Jumlah Pax
                            </label>
                            <input type="number" v-model="rsvpForm.pax" required min="1" max="10" :class="theme.fieldClass" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider mb-1" :class="theme.cardSub">
                            Ucapan &amp; Doa Restu
                        </label>
                        <textarea v-model="rsvpForm.guest_book_message" rows="3" placeholder="Tuliskan doa restu untuk kedua mempelai..." :class="theme.fieldClass"></textarea>
                    </div>
                    <button type="submit" :disabled="rsvpForm.processing"
                            class="w-full py-3.5 rounded-xl font-bold tracking-wide shadow-md transition disabled:opacity-50"
                            :class="theme.btnPrimary">
                        Kirim Konfirmasi RSVP
                    </button>
                </form>
            </section>

            <!-- Buku Tamu & Doa Restu -->
            <section class="p-8 rounded-3xl shadow-sm" :class="theme.cardBg">
                <h2 class="text-2xl font-serif font-bold mb-6 text-center" :class="theme.cardHeader">
                    Ucapan &amp; Doa Restu
                </h2>
                <div v-if="(project.guest_book || []).length > 0" class="flex flex-col gap-4 max-h-96 overflow-y-auto pr-1">
                    <div v-for="wish in project.guest_book" :key="wish.id" class="p-4 rounded-2xl border" :class="theme.badgeClass">
                        <div class="flex justify-between items-center mb-1">
                            <span class="font-bold text-sm" :class="theme.cardHeader">{{ wish.name }}</span>
                            <span class="text-[11px] opacity-75">{{ new Date(wish.updated_at).toLocaleDateString('id-ID') }}</span>
                        </div>
                        <p class="text-sm italic opacity-90">"{{ wish.guest_book_message }}"</p>
                    </div>
                </div>
                <div v-else class="text-center py-6 text-sm" :class="theme.cardSub">
                    Belum ada ucapan. Jadilah yang pertama memberikan doa restu bagi kedua mempelai!
                </div>
            </section>

        </main>

        <!-- Footer -->
        <footer class="text-center text-xs py-10 border-t-4" :class="theme.footerBg">
            <p class="font-serif text-base mb-1 font-bold">{{ project.name }}</p>
            <p class="opacity-70">&copy; {{ new Date().getFullYear() }} · Powered by Wedding Planner Platform</p>
        </footer>
    </div>
</template>

<style scoped>
@keyframes floatSlow {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}
.float-slow { animation: floatSlow 6s ease-in-out infinite; }
</style>
