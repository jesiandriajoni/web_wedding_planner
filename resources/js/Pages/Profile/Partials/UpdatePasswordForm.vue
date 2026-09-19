<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value?.focus();
            }
            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value?.focus();
            }
        },
    });
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-xl font-serif font-bold text-charcoal">
                Ganti Kata Sandi
            </h2>
            <p class="mt-1 text-sm text-charcoal-400">
                Pastikan akun Anda menggunakan kata sandi yang aman dan tidak mudah ditebak untuk menjaga keamanan data.
            </p>
        </header>

        <form @submit.prevent="updatePassword" class="mt-6 space-y-4">
            <div>
                <label for="current_password" class="block text-xs font-bold uppercase tracking-wider text-charcoal-500 mb-1.5">
                    Kata Sandi Saat Ini
                </label>
                <input
                    id="current_password"
                    ref="currentPasswordInput"
                    v-model="form.current_password"
                    type="password"
                    autocomplete="current-password"
                    placeholder="Masukkan kata sandi saat ini"
                    class="w-full px-4 py-2.5 bg-ivory border border-gold-200 rounded-xl text-charcoal placeholder-charcoal-300 focus:outline-none focus:ring-2 focus:ring-gold-400 focus:border-gold-500 text-sm shadow-xs transition"
                />
                <p v-if="form.errors.current_password" class="mt-1.5 text-xs text-rose-500 font-medium">
                    {{ form.errors.current_password }}
                </p>
            </div>

            <div>
                <label for="password" class="block text-xs font-bold uppercase tracking-wider text-charcoal-500 mb-1.5">
                    Kata Sandi Baru
                </label>
                <input
                    id="password"
                    ref="passwordInput"
                    v-model="form.password"
                    type="password"
                    autocomplete="new-password"
                    placeholder="Minimal 8 karakter"
                    class="w-full px-4 py-2.5 bg-ivory border border-gold-200 rounded-xl text-charcoal placeholder-charcoal-300 focus:outline-none focus:ring-2 focus:ring-gold-400 focus:border-gold-500 text-sm shadow-xs transition"
                />
                <p v-if="form.errors.password" class="mt-1.5 text-xs text-rose-500 font-medium">
                    {{ form.errors.password }}
                </p>
            </div>

            <div>
                <label for="password_confirmation" class="block text-xs font-bold uppercase tracking-wider text-charcoal-500 mb-1.5">
                    Konfirmasi Kata Sandi Baru
                </label>
                <input
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    autocomplete="new-password"
                    placeholder="Ulangi kata sandi baru"
                    class="w-full px-4 py-2.5 bg-ivory border border-gold-200 rounded-xl text-charcoal placeholder-charcoal-300 focus:outline-none focus:ring-2 focus:ring-gold-400 focus:border-gold-500 text-sm shadow-xs transition"
                />
                <p v-if="form.errors.password_confirmation" class="mt-1.5 text-xs text-rose-500 font-medium">
                    {{ form.errors.password_confirmation }}
                </p>
            </div>

            <div class="flex items-center gap-4 pt-2">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="px-5 py-2.5 bg-gold-500 hover:bg-gold-600 active:bg-gold-700 text-white rounded-xl shadow-xs text-sm font-bold tracking-wide transition cursor-pointer disabled:opacity-50"
                >
                    Simpan Kata Sandi
                </button>

                <Transition
                    enter-active-class="transition ease-in-out duration-300"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out duration-300"
                    leave-to-class="opacity-0"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="text-xs font-bold text-emerald-600 flex items-center gap-1.5"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Kata sandi berhasil diperbarui!
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
