<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});
</script>

<template>
    <Head title="Pengaturan Akun & Profil" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-3xl font-bold font-serif text-charcoal tracking-tight">
                    Pengaturan Akun
                </h2>
                <p class="text-sm text-charcoal-400 mt-1">
                    Kelola informasi profil dan kata sandi akun Anda.
                </p>
            </div>
        </template>

        <div class="py-8 bg-ivory min-h-screen">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 space-y-6">
                <!-- Update Profile Info Card -->
                <div class="bg-white p-6 sm:p-8 rounded-2xl border border-gold-200 shadow-xs">
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                        class="max-w-xl"
                    />
                </div>

                <!-- Update Password Card -->
                <div id="password-section" class="bg-white p-6 sm:p-8 rounded-2xl border border-gold-200 shadow-xs">
                    <UpdatePasswordForm class="max-w-xl" />
                </div>

                <!-- Delete Account Card -->
                <div class="bg-white p-6 sm:p-8 rounded-2xl border border-gold-200 shadow-xs">
                    <DeleteUserForm class="max-w-xl" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
