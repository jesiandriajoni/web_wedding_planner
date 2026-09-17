<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Masuk - Wedding Planner" />

    <div class="grid grid-cols-1 lg:grid-cols-12 min-h-screen font-sans bg-ivory text-charcoal">
        
        <!-- Left Side: Visual Backdrop (Romantic Luxury) -->
        <div class="hidden lg:flex lg:col-span-7 relative flex-col justify-between p-16 text-white overflow-hidden bg-gradient-to-br from-charcoal-900 via-charcoal-800 to-[#2A1810]">
            <!-- Subtle Overlay -->
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

            <!-- Bottom Content (Quote) -->
            <div class="relative z-10 max-w-xl">
                <div class="inline-block mb-3 px-3 py-1 rounded-full bg-gold-500/20 border border-gold-400/30 text-gold-300 text-xs font-bold uppercase tracking-widest">
                    Hari Bahagia Anda
                </div>
                <blockquote class="text-3xl font-medium font-serif leading-relaxed text-white italic">
                    "Kisah cinta sejati memiliki awal yang indah dan abadi selamanya."
                </blockquote>
                <div class="mt-6 flex items-center gap-4 border-t border-gold-500/30 pt-6">
                    <div>
                        <p class="text-sm font-semibold text-gold-200">Wujudkan Pernikahan Impian</p>
                        <p class="text-xs text-charcoal-300 mt-0.5">Kelola anggaran, tamu, vendor, dan rundown dalam satu sistem terpadu.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side: Clean Modern Login Form -->
        <div class="col-span-1 lg:col-span-5 flex items-center justify-center p-8 sm:p-16 relative bg-white">
            <div class="w-full max-w-md relative z-10">
                <!-- Mobile Header -->
                <div class="lg:hidden text-center mb-8">
                    <div class="inline-flex w-12 h-12 rounded-2xl bg-gradient-to-br from-blush-200 to-gold-400 items-center justify-center shadow-md mb-3">
                        <span class="text-white text-2xl font-serif font-bold">W</span>
                    </div>
                    <h1 class="text-3xl font-serif font-bold text-charcoal">Wedding Planner</h1>
                    <p class="text-sm font-script text-blush-600 text-lg -mt-1">Romantic Luxury</p>
                </div>

                <!-- Form Header -->
                <div class="mb-8 hidden lg:block">
                    <h2 class="text-3xl font-bold font-serif text-charcoal">Selamat Datang</h2>
                    <p class="text-charcoal-400 mt-2 text-sm">Masuk ke akun Anda untuk mulai mengelola persiapan pernikahan.</p>
                </div>

                <div v-if="status" class="mb-6 p-4 rounded-xl text-sm font-medium text-gold-800 bg-gold-50 border border-gold-200">
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Email Address -->
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
                            placeholder="nama@email.com"
                        />
                        <div v-if="form.errors.email" class="mt-2 text-xs text-rose-500 font-medium">
                            {{ form.errors.email }}
                        </div>
                    </div>

                    <!-- Password -->
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <label for="password" class="block text-xs font-bold uppercase tracking-wider text-charcoal-500">
                                Kata Sandi
                            </label>
                            <Link
                                v-if="canResetPassword"
                                :href="route('password.request')"
                                class="text-xs text-gold-600 hover:text-gold-700 font-medium transition"
                            >
                                Lupa Kata Sandi?
                            </Link>
                        </div>
                        <input
                            id="password"
                            type="password"
                            v-model="form.password"
                            required
                            autocomplete="current-password"
                            class="w-full px-4 py-3 bg-ivory border border-gold-200 rounded-xl text-charcoal placeholder-charcoal-300 focus:outline-none focus:ring-2 focus:ring-gold-400 focus:border-gold-500 transition duration-200 shadow-xs text-sm"
                            placeholder="Masukkan kata sandi Anda"
                        />
                        <div v-if="form.errors.password" class="mt-2 text-xs text-rose-500 font-medium">
                            {{ form.errors.password }}
                        </div>
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center justify-between">
                        <label class="flex items-center select-none cursor-pointer">
                            <input 
                                type="checkbox" 
                                name="remember" 
                                v-model="form.remember"
                                class="w-4 h-4 rounded text-gold-500 border-gold-300 focus:ring-gold-400 transition"
                            />
                            <span class="ms-2.5 text-sm text-charcoal-500">
                                Ingat Saya
                            </span>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-2">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full py-3.5 px-4 bg-gold-500 hover:bg-gold-600 active:bg-gold-700 text-white rounded-xl shadow-sm hover:shadow-md text-sm font-bold tracking-wide transition duration-200 flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50"
                        >
                            <svg v-if="form.processing" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>Masuk</span>
                        </button>
                    </div>

                    <!-- Register Link -->
                    <div class="text-center pt-4 text-sm text-charcoal-400">
                        Belum memiliki akun?
                        <Link
                            :href="route('register')"
                            class="font-bold text-gold-600 hover:text-gold-700 transition underline ms-1"
                        >
                            Daftar Sekarang
                        </Link>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
