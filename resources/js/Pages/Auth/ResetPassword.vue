<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    email: {
        type: String,
        required: true,
    },
    token: {
        type: String,
        required: true,
    },
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Atur Ulang Kata Sandi - Wedding Planner" />

    <div class="grid grid-cols-1 lg:grid-cols-12 min-h-screen font-sans bg-ivory text-charcoal">
        <!-- Left Side: Visual Backdrop -->
        <div class="hidden lg:flex lg:col-span-7 relative flex-col justify-between p-16 text-white overflow-hidden bg-gradient-to-br from-charcoal-900 via-charcoal-800 to-[#2A1810]">
            <div class="absolute inset-0 bg-cover bg-center opacity-40 mix-blend-overlay"
                 style="background-image: radial-gradient(circle at top right, #F7CAC9 0%, transparent 60%), radial-gradient(circle at bottom left, #D4AF37 0%, transparent 60%);">
            </div>

            <!-- Branding Header -->
            <div class="relative z-10">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blush-200 to-gold-400 flex items-center justify-center shadow-md">
                        <span class="text-white text-xl font-serif font-bold">W</span>
                    </div>
                    <div>
                        <h1 class="text-2xl font-serif font-bold tracking-wide text-white">
                            Wedding<span class="text-gold-300 font-normal">Planner</span>
                        </h1>
                        <p class="text-xs font-script text-blush-200 text-base -mt-1">Romantic Luxury Organizer</p>
                    </div>
                </div>
            </div>

            <!-- Quote -->
            <div class="relative z-10 max-w-xl">
                <div class="inline-block mb-3 px-3 py-1 rounded-full bg-gold-500/20 border border-gold-400/30 text-gold-300 text-xs font-bold uppercase tracking-widest">
                    Keamanan Akun
                </div>
                <blockquote class="text-3xl font-medium font-serif leading-relaxed text-white italic">
                    "Buat kata sandi baru yang kuat untuk menjaga privasi rencana pernikahan Anda."
                </blockquote>
            </div>
        </div>

        <!-- Right Side: Reset Form -->
        <div class="col-span-1 lg:col-span-5 flex items-center justify-center p-8 sm:p-16 relative bg-white">
            <div class="w-full max-w-md relative z-10">
                <!-- Mobile Header -->
                <div class="lg:hidden text-center mb-8">
                    <div class="inline-flex w-12 h-12 rounded-2xl bg-gradient-to-br from-blush-200 to-gold-400 items-center justify-center shadow-md mb-3">
                        <span class="text-white text-2xl font-serif font-bold">W</span>
                    </div>
                    <h1 class="text-3xl font-serif font-bold text-charcoal">Wedding Planner</h1>
                </div>

                <div class="mb-6">
                    <h2 class="text-3xl font-bold font-serif text-charcoal">Atur Ulang Kata Sandi</h2>
                    <p class="text-charcoal-400 mt-2 text-sm leading-relaxed">
                        Silakan buat kata sandi baru untuk akun Anda.
                    </p>
                </div>

                <form @submit.prevent="submit" class="space-y-5">
                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-xs font-bold uppercase tracking-wider text-charcoal-500 mb-2">
                            Alamat Email
                        </label>
                        <input
                            id="email"
                            type="email"
                            v-model="form.email"
                            required
                            autofocus
                            autocomplete="username"
                            class="w-full px-4 py-3 bg-ivory border border-gold-200 rounded-xl text-charcoal placeholder-charcoal-300 focus:outline-none focus:ring-2 focus:ring-gold-400 focus:border-gold-500 transition duration-200 shadow-xs text-sm"
                        />
                        <div v-if="form.errors.email" class="mt-2 text-xs text-rose-500 font-medium">
                            {{ form.errors.email }}
                        </div>
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-xs font-bold uppercase tracking-wider text-charcoal-500 mb-2">
                            Kata Sandi Baru
                        </label>
                        <input
                            id="password"
                            type="password"
                            v-model="form.password"
                            required
                            autocomplete="new-password"
                            placeholder="Minimal 8 karakter"
                            class="w-full px-4 py-3 bg-ivory border border-gold-200 rounded-xl text-charcoal placeholder-charcoal-300 focus:outline-none focus:ring-2 focus:ring-gold-400 focus:border-gold-500 transition duration-200 shadow-xs text-sm"
                        />
                        <div v-if="form.errors.password" class="mt-2 text-xs text-rose-500 font-medium">
                            {{ form.errors.password }}
                        </div>
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label for="password_confirmation" class="block text-xs font-bold uppercase tracking-wider text-charcoal-500 mb-2">
                            Konfirmasi Kata Sandi Baru
                        </label>
                        <input
                            id="password_confirmation"
                            type="password"
                            v-model="form.password_confirmation"
                            required
                            autocomplete="new-password"
                            placeholder="Ulangi kata sandi baru"
                            class="w-full px-4 py-3 bg-ivory border border-gold-200 rounded-xl text-charcoal placeholder-charcoal-300 focus:outline-none focus:ring-2 focus:ring-gold-400 focus:border-gold-500 transition duration-200 shadow-xs text-sm"
                        />
                        <div v-if="form.errors.password_confirmation" class="mt-2 text-xs text-rose-500 font-medium">
                            {{ form.errors.password_confirmation }}
                        </div>
                    </div>

                    <div class="pt-2">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full py-3.5 px-4 bg-gold-500 hover:bg-gold-600 active:bg-gold-700 text-white rounded-xl shadow-xs hover:shadow-md text-sm font-bold tracking-wide transition duration-200 flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50"
                        >
                            <svg v-if="form.processing" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>Simpan Kata Sandi Baru</span>
                        </button>
                    </div>

                    <div class="text-center pt-2 text-sm text-charcoal-400">
                        <Link
                            :href="route('login')"
                            class="inline-flex items-center gap-1.5 font-semibold text-gold-600 hover:text-gold-700 transition"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Batal dan Kembali ke Masuk
                        </Link>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
