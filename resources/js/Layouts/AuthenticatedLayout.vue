<script setup>
import { computed, ref } from 'vue';
import AskaryaLogo from '@/Components/AskaryaLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link, usePage } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);
const page = usePage();

const userInitials = computed(() => {
    const name = String(page.props.auth.user?.name ?? '').trim();

    if (!name) {
        return 'AA';
    }

    return name
        .split(/\s+/)
        .slice(0, 2)
        .map((part) => part.charAt(0).toUpperCase())
        .join('');
});

const dashboardItems = [
    { label: 'Dashboard', name: 'dashboard' },
];

const documentMenuItems = [
    { label: 'Penawaran', name: 'penawaran.index' },
    { label: 'PO', name: 'purchasing-order.index' },
    { label: 'Invoice', name: 'invoice.index' },
    { label: 'Surat Jalan', name: 'surat-jalan.index' },
    { label: 'Berita Acara', name: 'berita-acara.index' },
    { label: 'Faktur Pajak', name: 'faktur-pajak.index' },
    { label: 'Nota Toko', name: 'nota-toko.index' },
];

const settingsMenuItems = [
    { label: 'Mitra', name: 'mitra.index' },
    { label: 'User', name: 'users.index' },
    { label: 'Dokumen Template', name: 'document-templates.index' },
    { label: 'Simulasi Pembiayaan', name: 'simulasi-pembiayaan.index' },
];

const accountMenuItems = [
    { label: 'Profile', href: route('profile.edit') },
    { label: 'Log Out', href: route('logout'), method: 'post', as: 'button' },
];

const isCurrentRoute = (items) => items.some((item) => route().current(item.name));

const dropdownContentClasses = 'min-w-72 overflow-hidden rounded-2xl bg-white py-2 shadow-2xl shadow-blue-950/10 ring-1 ring-blue-100';
</script>

<template>
    <div class="min-h-screen bg-[#fff2d9] text-slate-900">
        <div class="pointer-events-none fixed inset-0 overflow-hidden">
            <div class="absolute left-[-8rem] top-[-7rem] h-80 w-80 rounded-full bg-blue-500/10 blur-3xl"></div>
            <div class="absolute right-[-7rem] top-28 h-80 w-80 rounded-full bg-red-500/10 blur-3xl"></div>
        </div>

        <nav class="sticky top-0 z-50 border-b border-blue-100 bg-white/95 text-slate-900 shadow-sm backdrop-blur-xl">
            <div class="flex h-1">
                <div class="w-1/3 bg-red-600"></div>
                <div class="w-1/3 bg-white"></div>
                <div class="w-1/3 bg-blue-700"></div>
            </div>

            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-20 items-center justify-between gap-4">
                    <div class="flex shrink-0 items-center">
                        <Link :href="route('dashboard')" class="inline-flex items-center gap-3 rounded-2xl border border-blue-100 bg-white px-3 py-2 transition hover:border-red-200 hover:bg-red-50">
                            <AskaryaLogo tone="dark" :showWordmark="false" compact />
                            <div class="hidden sm:block leading-tight">
                                <p class="text-sm font-semibold tracking-[0.16em] text-blue-950">ASKARYA</p>
                                <p class="text-[11px] uppercase tracking-[0.28em] text-slate-500">Dokumen Office</p>
                            </div>
                        </Link>
                    </div>

                    <div class="hidden flex-1 items-center justify-end gap-3 lg:flex">
                        <div class="flex items-center gap-3">
                            <Dropdown align="right" width="48" :content-classes="dropdownContentClasses">
                                <template #trigger>
                                    <button
                                        type="button"
                                        :class="[
                                            'inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold transition duration-150 ease-in-out focus:outline-none',
                                            isCurrentRoute(dashboardItems)
                                                ? 'border border-red-200 bg-red-600 text-white shadow-lg shadow-red-500/20'
                                                : 'border border-blue-100 bg-white text-slate-700 hover:border-red-200 hover:bg-red-50 hover:text-red-700',
                                        ]"
                                    >
                                        Dashboard
                                        <svg class="h-4 w-4 text-current/80" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.25a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </template>

                                <template #content>
                                    <div class="px-4 pb-2 pt-1">
                                        <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-red-600">Dashboard</p>
                                    </div>
                                    <DropdownLink :href="route('dashboard')">Dashboard</DropdownLink>
                                </template>
                            </Dropdown>

                            <Dropdown align="right" width="48" :content-classes="dropdownContentClasses">
                                <template #trigger>
                                    <button
                                        type="button"
                                        :class="[
                                            'inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold transition duration-150 ease-in-out focus:outline-none',
                                            isCurrentRoute(documentMenuItems)
                                                ? 'border border-red-200 bg-red-600 text-white shadow-lg shadow-red-500/20'
                                                : 'border border-blue-100 bg-white text-slate-700 hover:border-red-200 hover:bg-red-50 hover:text-red-700',
                                        ]"
                                    >
                                        Menu
                                        <svg class="h-4 w-4 text-current/80" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.25a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </template>

                                <template #content>
                                    <div class="px-4 pb-2 pt-1">
                                        <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-red-600">Menu Dokumen</p>
                                    </div>
                                    <DropdownLink v-for="item in documentMenuItems" :key="item.name" :href="route(item.name)">
                                        {{ item.label }}
                                    </DropdownLink>
                                </template>
                            </Dropdown>

                            <Dropdown align="right" width="48" :content-classes="dropdownContentClasses">
                                <template #trigger>
                                    <button
                                        type="button"
                                        :class="[
                                            'inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold transition duration-150 ease-in-out focus:outline-none',
                                            isCurrentRoute(settingsMenuItems)
                                                ? 'border border-red-200 bg-red-600 text-white shadow-lg shadow-red-500/20'
                                                : 'border border-blue-100 bg-white text-slate-700 hover:border-red-200 hover:bg-red-50 hover:text-red-700',
                                        ]"
                                    >
                                        Setting
                                        <svg class="h-4 w-4 text-current/80" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.25a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </template>

                                <template #content>
                                    <div class="px-4 pb-2 pt-1">
                                        <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-red-600">Pengaturan</p>
                                    </div>
                                    <DropdownLink v-for="item in settingsMenuItems" :key="item.name" :href="route(item.name)">
                                        {{ item.label }}
                                    </DropdownLink>
                                </template>
                            </Dropdown>
                        </div>

                        <div class="relative ms-3">
                            <Dropdown align="right" width="48" :content-classes="dropdownContentClasses">
                                <template #trigger>
                                    <span class="inline-flex rounded-full">
                                        <button
                                            type="button"
                                            class="inline-flex items-center gap-3 rounded-full border border-blue-100 bg-white px-3 py-2 text-sm font-semibold text-slate-800 transition duration-150 ease-in-out hover:border-red-200 hover:bg-red-50 focus:outline-none"
                                        >
                                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-red-600 text-xs font-bold text-white shadow-lg shadow-red-500/25">
                                                {{ userInitials }}
                                            </span>

                                            <span class="hidden md:block">{{ $page.props.auth.user.name }}</span>

                                            <svg
                                                class="ms-1 h-4 w-4 text-slate-500"
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
                                    <div class="px-4 pb-2 pt-1">
                                        <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-red-600">Akun</p>
                                    </div>
                                    <DropdownLink :href="accountMenuItems[0].href">
                                        {{ accountMenuItems[0].label }}
                                    </DropdownLink>
                                    <DropdownLink
                                        :href="accountMenuItems[1].href"
                                        method="post"
                                        as="button"
                                    >
                                        {{ accountMenuItems[1].label }}
                                    </DropdownLink>
                                </template>
                            </Dropdown>
                        </div>
                    </div>

                    <div class="-me-2 flex items-center lg:hidden">
                        <button
                            @click="showingNavigationDropdown = !showingNavigationDropdown"
                            class="inline-flex items-center justify-center rounded-full border border-white/10 bg-white/10 p-2 text-slate-100 transition duration-150 ease-in-out hover:border-blue-200/30 hover:bg-white/15 hover:text-white focus:bg-white/15 focus:text-white focus:outline-none"
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

            <div
                :class="{
                    block: showingNavigationDropdown,
                    hidden: !showingNavigationDropdown,
                }"
                class="lg:hidden"
            >
                <div class="space-y-3 px-3 pb-3 pt-3">
                    <div class="rounded-2xl border border-blue-100 bg-white p-2">
                        <p class="px-3 pb-2 text-[11px] font-semibold uppercase tracking-[0.28em] text-slate-500">Dashboard</p>
                        <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')">
                            Dashboard
                        </ResponsiveNavLink>
                    </div>

                    <div class="rounded-2xl border border-blue-100 bg-white p-2">
                        <p class="px-3 pb-2 text-[11px] font-semibold uppercase tracking-[0.28em] text-slate-500">Menu Dokumen</p>
                        <ResponsiveNavLink
                            v-for="item in documentMenuItems"
                            :key="item.name"
                            :href="route(item.name)"
                            :active="route().current(item.name)"
                        >
                            {{ item.label }}
                        </ResponsiveNavLink>
                    </div>

                    <div class="rounded-2xl border border-blue-100 bg-white p-2">
                        <p class="px-3 pb-2 text-[11px] font-semibold uppercase tracking-[0.28em] text-slate-500">Setting</p>
                        <ResponsiveNavLink
                            v-for="item in settingsMenuItems"
                            :key="item.name"
                            :href="route(item.name)"
                            :active="route().current(item.name)"
                        >
                            {{ item.label }}
                        </ResponsiveNavLink>
                    </div>
                </div>

                <div class="border-t border-blue-100 bg-white pb-2 pt-4">
                    <div class="px-4">
                        <div class="rounded-2xl bg-slate-50 px-4 py-3">
                            <div class="text-base font-medium text-blue-950">
                                {{ $page.props.auth.user.name }}
                            </div>
                            <div class="text-sm font-medium text-slate-500">
                                {{ $page.props.auth.user.email }}
                            </div>
                        </div>
                    </div>

                    <div class="mt-3 space-y-1 px-2">
                        <ResponsiveNavLink :href="route('profile.edit')">
                            Profile
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('logout')" method="post" as="button">
                            Log Out
                        </ResponsiveNavLink>
                    </div>
                </div>
            </div>
        </nav>

        <header v-if="$slots.header" class="border-b border-blue-100 bg-white/90 shadow-sm backdrop-blur">
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                <slot name="header" />
            </div>
        </header>

        <main class="relative">
            <slot />
        </main>
    </div>
</template>
