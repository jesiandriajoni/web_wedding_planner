<script setup>
import { ref, computed } from 'vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link, usePage, useForm } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);
const showPasswordModal = ref(false);
const page = usePage();
const projectId = computed(() => {
    return page.props.project?.id || route().params.project || null;
});

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const openChangePasswordModal = () => {
    passwordForm.reset();
    passwordForm.clearErrors();
    showPasswordModal.value = true;
};

const closeChangePasswordModal = () => {
    showPasswordModal.value = false;
    passwordForm.reset();
    passwordForm.clearErrors();
};

const submitChangePassword = () => {
    passwordForm.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => {
            closeChangePasswordModal();
        },
    });
};
</script>

<template>
    <div>
        <div class="min-h-screen bg-ivory text-charcoal font-sans">
            <nav class="border-b border-gold-200/70 bg-white shadow-xs sticky top-0 z-40">
                <!-- Primary Navigation Menu -->
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 justify-between">
                        <div class="flex items-center">
                            <!-- Logo & Brand -->
                            <div class="flex shrink-0 items-center">
                                <Link :href="route('projects.index')" class="flex items-center gap-2 group">
                                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-blush-200 via-blush-300 to-gold-400 flex items-center justify-center shadow-xs group-hover:scale-105 transition-transform duration-200">
                                        <span class="text-white text-lg font-serif font-bold">W</span>
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="font-serif font-bold text-base text-charcoal leading-tight tracking-wide">
                                            Wedding<span class="text-gold-600 font-normal">Planner</span>
                                        </span>
                                        <span class="font-script text-xs text-blush-600 -mt-1">
                                            Romantic Luxury
                                        </span>
                                    </div>
                                </Link>
                            </div>

                            <!-- Navigation Links -->
                            <div class="hidden space-x-6 sm:-my-px sm:ms-8 sm:flex">
                                <NavLink
                                    :href="route('projects.index')"
                                    :active="route().current('projects.index')"
                                >
                                    Daftar Pernikahan
                                </NavLink>

                                <NavLink
                                    v-if="$page.props.auth.user.role === 'admin'"
                                    :href="route('admin.users.index')"
                                    :active="route().current('admin.users.index')"
                                >
                                    Kelola User (Admin)
                                </NavLink>

                                <template v-if="projectId">
                                    <NavLink
                                        :href="route('projects.dashboard', projectId)"
                                        :active="route().current('projects.dashboard')"
                                    >
                                        Dashboard
                                    </NavLink>
                                    <NavLink
                                        :href="route('projects.checklists.index', projectId)"
                                        :active="route().current('projects.checklists.index')"
                                    >
                                        Checklist
                                    </NavLink>
                                    <NavLink
                                        :href="route('projects.budget', projectId)"
                                        :active="route().current('projects.budget')"
                                    >
                                        Budget
                                    </NavLink>
                                    <NavLink
                                        :href="route('projects.vendors.index', projectId)"
                                        :active="route().current('projects.vendors.index') || route().current('projects.vendors.*')"
                                    >
                                        Vendors
                                    </NavLink>
                                    <NavLink
                                        :href="route('projects.guests.index', projectId)"
                                        :active="route().current('projects.guests.index') || route().current('projects.guests.*')"
                                    >
                                        Tamu & RSVP
                                    </NavLink>
                                    <NavLink
                                        :href="route('projects.rundowns.index', projectId)"
                                        :active="route().current('projects.rundowns.index') || route().current('projects.rundowns.*')"
                                    >
                                        Rundown
                                    </NavLink>
                                    <NavLink
                                        :href="route('projects.seserahan.index', projectId)"
                                        :active="route().current('projects.seserahan.index') || route().current('projects.seserahan.*')"
                                    >
                                        Seserahan
                                    </NavLink>
                                    <NavLink
                                        :href="route('projects.invitation.edit', projectId)"
                                        :active="route().current('projects.invitation.edit')"
                                    >
                                        Undangan
                                    </NavLink>
                                    <NavLink
                                        :href="route('projects.guide.show', projectId)"
                                        :active="route().current('projects.guide.show')"
                                    >
                                        Panduan
                                    </NavLink>
                                </template>
                            </div>
                        </div>

                        <div class="hidden sm:ms-6 sm:flex sm:items-center">
                            <!-- Settings Dropdown -->
                            <div class="relative ms-3">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                class="inline-flex items-center gap-2 rounded-xl border border-gold-200/80 bg-ivory-100 px-3.5 py-2 text-sm font-semibold text-charcoal hover:bg-blush-50 hover:border-blush-300 focus:outline-none transition duration-150 shadow-xs"
                                            >
                                                <span class="w-6 h-6 rounded-full bg-blush-200 text-blush-800 text-xs font-bold flex items-center justify-center">
                                                    {{ $page.props.auth.user.name.charAt(0).toUpperCase() }}
                                                </span>
                                                <span>{{ $page.props.auth.user.name }}</span>

                                                <svg
                                                    class="-me-0.5 ms-1 h-4 w-4 text-charcoal-400"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <DropdownLink :href="route('profile.edit')">
                                            Profil Saya
                                        </DropdownLink>
                                        <button
                                            type="button"
                                            @click="openChangePasswordModal"
                                            class="block w-full px-4 py-2 text-start text-sm leading-5 text-charcoal-600 hover:bg-gold-50 focus:outline-none transition duration-150 ease-in-out cursor-pointer"
                                        >
                                            Ganti Kata Sandi
                                        </button>
                                        <DropdownLink
                                            :href="route('logout')"
                                            method="post"
                                            as="button"
                                        >
                                            Keluar (Logout)
                                        </DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Hamburger -->
                        <div class="-me-2 flex items-center sm:hidden">
                            <button
                                @click="showingNavigationDropdown = !showingNavigationDropdown"
                                class="inline-flex items-center justify-center rounded-xl p-2 text-charcoal-500 hover:bg-blush-50 hover:text-charcoal focus:outline-none transition duration-150"
                            >
                                <svg
                                    class="h-6 w-6"
                                    stroke="currentColor"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        :class="{
                                            hidden: showingNavigationDropdown,
                                            'inline-flex': !showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{
                                            hidden: !showingNavigationDropdown,
                                            'inline-flex': showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div
                    :class="{
                        block: showingNavigationDropdown,
                        hidden: !showingNavigationDropdown,
                    }"
                    class="sm:hidden border-t border-gold-100 bg-white"
                >
                    <div class="space-y-1 pb-3 pt-2">
                        <ResponsiveNavLink
                            :href="route('projects.index')"
                            :active="route().current('projects.index')"
                        >
                            Daftar Pernikahan
                        </ResponsiveNavLink>

                        <ResponsiveNavLink
                            v-if="$page.props.auth.user.role === 'admin'"
                            :href="route('admin.users.index')"
                            :active="route().current('admin.users.index')"
                        >
                            Kelola User (Admin)
                        </ResponsiveNavLink>

                        <template v-if="projectId">
                            <ResponsiveNavLink
                                :href="route('projects.dashboard', projectId)"
                                :active="route().current('projects.dashboard')"
                            >
                                Dashboard
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                :href="route('projects.checklists.index', projectId)"
                                :active="route().current('projects.checklists.index')"
                            >
                                Checklist
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                :href="route('projects.budget', projectId)"
                                :active="route().current('projects.budget')"
                            >
                                Budget
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                :href="route('projects.vendors.index', projectId)"
                                :active="route().current('projects.vendors.index')"
                            >
                                Vendors
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                :href="route('projects.guests.index', projectId)"
                                :active="route().current('projects.guests.index')"
                            >
                                Tamu & RSVP
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                :href="route('projects.rundowns.index', projectId)"
                                :active="route().current('projects.rundowns.index')"
                            >
                                Rundown
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                :href="route('projects.seserahan.index', projectId)"
                                :active="route().current('projects.seserahan.index')"
                            >
                                Seserahan
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                :href="route('projects.invitation.edit', projectId)"
                                :active="route().current('projects.invitation.edit')"
                            >
                                Undangan
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                :href="route('projects.guide.show', projectId)"
                                :active="route().current('projects.guide.show')"
                            >
                                Panduan
                            </ResponsiveNavLink>
                        </template>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div class="border-t border-gold-100 pb-3 pt-4 px-4 bg-ivory">
                        <div>
                            <div class="text-base font-bold text-charcoal">
                                {{ $page.props.auth.user.name }}
                            </div>
                            <div class="text-xs text-charcoal-400">
                                {{ $page.props.auth.user.email }}
                            </div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.edit')">
                                Profil Saya
                            </ResponsiveNavLink>
                            <button
                                type="button"
                                @click="openChangePasswordModal"
                                class="block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-charcoal-600 hover:text-charcoal-800 hover:bg-gold-50 transition duration-150 ease-in-out cursor-pointer"
                            >
                                Ganti Kata Sandi
                            </button>
                            <ResponsiveNavLink
                                :href="route('logout')"
                                method="post"
                                as="button"
                            >
                                Keluar (Logout)
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header class="bg-white/90 backdrop-blur-xs border-b border-gold-200/50 shadow-xs" v-if="$slots.header">
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main>
                <slot />
            </main>
        </div>

        <!-- Global Change Password Modal -->
        <div v-if="showPasswordModal" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs transition duration-300">
            <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl overflow-hidden border border-gold-200">
                <div class="px-6 py-4 border-b border-gold-100 flex justify-between items-center bg-ivory">
                    <h3 class="text-lg font-bold font-serif text-charcoal">
                        Ganti Kata Sandi
                    </h3>
                    <button @click="closeChangePasswordModal" class="text-charcoal-400 hover:text-charcoal text-2xl leading-none cursor-pointer">&times;</button>
                </div>
                <form @submit.prevent="submitChangePassword" class="p-6 flex flex-col gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-charcoal-500 mb-1.5">Kata Sandi Saat Ini</label>
                        <input type="password" v-model="passwordForm.current_password" required placeholder="Masukkan kata sandi saat ini"
                               :class="{'border-rose-400 focus:ring-rose-400': passwordForm.errors.current_password}"
                               class="w-full rounded-xl border border-gold-200 bg-ivory p-2.5 text-charcoal focus:ring-gold-400 focus:border-gold-500 text-sm shadow-xs" />
                        <p v-if="passwordForm.errors.current_password" class="mt-1 text-xs text-rose-500 font-semibold">{{ passwordForm.errors.current_password }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-charcoal-500 mb-1.5">Kata Sandi Baru</label>
                        <input type="password" v-model="passwordForm.password" required placeholder="Minimal 8 karakter"
                               :class="{'border-rose-400 focus:ring-rose-400': passwordForm.errors.password}"
                               class="w-full rounded-xl border border-gold-200 bg-ivory p-2.5 text-charcoal focus:ring-gold-400 focus:border-gold-500 text-sm shadow-xs" />
                        <p v-if="passwordForm.errors.password" class="mt-1 text-xs text-rose-500 font-semibold">{{ passwordForm.errors.password }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-charcoal-500 mb-1.5">Konfirmasi Kata Sandi Baru</label>
                        <input type="password" v-model="passwordForm.password_confirmation" required placeholder="Ulangi kata sandi baru"
                               :class="{'border-rose-400 focus:ring-rose-400': passwordForm.errors.password_confirmation}"
                               class="w-full rounded-xl border border-gold-200 bg-ivory p-2.5 text-charcoal focus:ring-gold-400 focus:border-gold-500 text-sm shadow-xs" />
                        <p v-if="passwordForm.errors.password_confirmation" class="mt-1 text-xs text-rose-500 font-semibold">{{ passwordForm.errors.password_confirmation }}</p>
                    </div>
                    <div class="flex justify-end gap-2 mt-2 pt-3 border-t border-gold-100">
                        <button type="button" @click="closeChangePasswordModal"
                                class="px-4 py-2 text-charcoal-500 hover:bg-ivory rounded-xl text-sm font-semibold transition cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" :disabled="passwordForm.processing"
                                class="px-5 py-2.5 bg-gold-500 hover:bg-gold-600 active:bg-gold-700 text-white rounded-xl text-sm font-bold tracking-wide transition shadow-xs cursor-pointer disabled:opacity-50">
                            Simpan Kata Sandi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
