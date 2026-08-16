<script setup>
import { computed, ref } from 'vue';
import AskaryaLogo from '@/Components/AskaryaLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link, usePage } from '@inertiajs/vue3';

const props = defineProps({
    theme: {
        type: String,
        default: 'light',
    },
});

const showingNavigationDropdown = ref(false);
const page = usePage();
const isSuperAdmin = computed(() => page.props.auth.user?.role === 'superadmin');
const isSlateTheme = computed(() => props.theme === 'slate');
const isLoginTheme = computed(() => props.theme === 'login');

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

const settingsMenuItems = computed(() => {
    const items = [
        { label: 'Mitra', name: 'mitra.index' },
        { label: 'Dokumen Template', name: 'document-templates.index' },
        { label: 'Simulasi Pembiayaan', name: 'simulasi-pembiayaan.index' },
    ];

    if (isSuperAdmin.value) {
        items.splice(1, 0, { label: 'User', name: 'users.index' });
    }

    return items;
});

const accountMenuItems = [
    { label: 'Profile', href: route('profile.edit') },
    { label: 'Log Out', href: route('logout'), method: 'post', as: 'button' },
];

const isCurrentRoute = (items) => items.some((item) => route().current(item.name));

const dropdownContentClasses = computed(() =>
    isLoginTheme.value
        ? 'min-w-72 overflow-hidden rounded-2xl border border-white/10 bg-slate-950/95 py-2 shadow-2xl shadow-black/30 ring-1 ring-white/10 backdrop-blur-xl'
        : isSlateTheme.value
            ? 'min-w-72 overflow-hidden rounded-2xl border border-white/10 bg-slate-900/95 py-2 shadow-2xl shadow-black/30 ring-1 ring-white/10 backdrop-blur-xl'
        : 'min-w-72 overflow-hidden rounded-2xl bg-white py-2 shadow-2xl shadow-blue-950/10 ring-1 ring-blue-100',
);
</script>

<template>
    <div
        :class="
            isLoginTheme
                ? 'min-h-screen bg-[#08111f] text-slate-100'
                : isSlateTheme
                    ? 'min-h-screen bg-[#0b1220] text-slate-100'
                    : 'min-h-screen bg-[#fff2d9] text-slate-900'
        "
    >
        <div class="pointer-events-none fixed inset-0 overflow-hidden">
            <div
                class="absolute left-[-8rem] top-[-7rem] h-80 w-80 rounded-full blur-3xl"
                :class="isLoginTheme ? 'bg-red-500/20' : isSlateTheme ? 'bg-slate-500/10' : 'bg-blue-500/10'"
            ></div>
            <div
                class="absolute right-[-7rem] top-28 h-80 w-80 rounded-full blur-3xl"
                :class="isLoginTheme ? 'bg-blue-500/20' : isSlateTheme ? 'bg-cyan-400/8' : 'bg-red-500/10'"
            ></div>
        </div>

        <nav
            class="sticky top-0 z-50 border-b backdrop-blur-xl"
            :class="
                isLoginTheme
                    ? 'border-white/10 bg-slate-950/80 text-slate-100 shadow-[0_12px_30px_rgba(2,6,23,0.25)]'
                    : isSlateTheme
                        ? 'border-white/8 bg-slate-950/80 text-slate-100 shadow-[0_12px_30px_rgba(2,6,23,0.25)]'
                    : 'border-blue-100 bg-white/95 text-slate-900 shadow-sm'
            "
        >
            <div class="flex h-1">
                <div class="w-1/3" :class="isLoginTheme ? 'bg-red-600' : isSlateTheme ? 'bg-slate-500/60' : 'bg-red-600'"></div>
                <div class="w-1/3" :class="isLoginTheme ? 'bg-blue-200/25' : isSlateTheme ? 'bg-slate-300/20' : 'bg-white'"></div>
                <div class="w-1/3" :class="isLoginTheme ? 'bg-blue-600' : isSlateTheme ? 'bg-cyan-400/50' : 'bg-blue-700'"></div>
            </div>

            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-20 items-center justify-between gap-4">
                    <div class="flex shrink-0 items-center">
                        <Link
                            :href="route('dashboard')"
                            class="inline-flex items-center gap-3 rounded-2xl border px-3 py-2 transition"
                            :class="
                                isLoginTheme
                                    ? 'border-white/10 bg-white/5 hover:border-blue-300/20 hover:bg-white/8'
                                    : isSlateTheme
                                        ? 'border-white/10 bg-white/5 hover:border-cyan-300/20 hover:bg-white/8'
                                        : 'border-blue-100 bg-white hover:border-red-200 hover:bg-red-50'
                            "
                        >
                            <AskaryaLogo :tone="isLoginTheme || isSlateTheme ? 'light' : 'dark'" :showWordmark="false" compact />
                            <div class="hidden sm:block leading-tight">
                                <p class="text-sm font-semibold tracking-[0.16em]" :class="isLoginTheme || isSlateTheme ? 'text-slate-100' : 'text-blue-950'">ASKARYA</p>
                                <p class="text-[11px] uppercase tracking-[0.28em]" :class="isLoginTheme || isSlateTheme ? 'text-slate-400' : 'text-slate-500'">Dokumen Office</p>
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
                                                ? isLoginTheme
                                                    ? 'border border-blue-300/20 bg-slate-700 text-white shadow-lg shadow-black/15'
                                                    : isSlateTheme
                                                        ? 'border border-cyan-300/20 bg-slate-700 text-white shadow-lg shadow-black/15'
                                                    : 'border border-red-200 bg-red-600 text-white shadow-lg shadow-red-500/20'
                                                : isLoginTheme
                                                    ? 'border border-white/10 bg-white/5 text-slate-200 hover:border-blue-300/20 hover:bg-white/8 hover:text-white'
                                                    : isSlateTheme
                                                        ? 'border border-white/10 bg-white/5 text-slate-200 hover:border-cyan-300/20 hover:bg-white/8 hover:text-white'
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
                                        <p class="text-[11px] font-semibold uppercase tracking-[0.28em]" :class="isLoginTheme ? 'text-blue-300/80' : isSlateTheme ? 'text-cyan-300/80' : 'text-red-600'">Dashboard</p>
                                    </div>
                                    <DropdownLink :href="route('dashboard')" :theme="isLoginTheme || isSlateTheme ? 'dark' : 'light'">Dashboard</DropdownLink>
                                </template>
                            </Dropdown>

                            <Dropdown align="right" width="48" :content-classes="dropdownContentClasses">
                                <template #trigger>
                                    <button
                                        type="button"
                                        :class="[
                                            'inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold transition duration-150 ease-in-out focus:outline-none',
                                            isCurrentRoute(documentMenuItems)
                                                ? isLoginTheme
                                                    ? 'border border-blue-300/20 bg-slate-700 text-white shadow-lg shadow-black/15'
                                                    : isSlateTheme
                                                        ? 'border border-cyan-300/20 bg-slate-700 text-white shadow-lg shadow-black/15'
                                                    : 'border border-red-200 bg-red-600 text-white shadow-lg shadow-red-500/20'
                                                : isLoginTheme
                                                    ? 'border border-white/10 bg-white/5 text-slate-200 hover:border-blue-300/20 hover:bg-white/8 hover:text-white'
                                                    : isSlateTheme
                                                        ? 'border border-white/10 bg-white/5 text-slate-200 hover:border-cyan-300/20 hover:bg-white/8 hover:text-white'
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
                                        <p class="text-[11px] font-semibold uppercase tracking-[0.28em]" :class="isLoginTheme ? 'text-blue-300/80' : isSlateTheme ? 'text-cyan-300/80' : 'text-red-600'">Menu Dokumen</p>
                                    </div>
                                    <DropdownLink
                                        v-for="item in documentMenuItems"
                                        :key="item.name"
                                        :href="route(item.name)"
                                        :theme="isLoginTheme || isSlateTheme ? 'dark' : 'light'"
                                    >
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
                                                ? isLoginTheme
                                                    ? 'border border-blue-300/20 bg-slate-700 text-white shadow-lg shadow-black/15'
                                                    : isSlateTheme
                                                        ? 'border border-cyan-300/20 bg-slate-700 text-white shadow-lg shadow-black/15'
                                                    : 'border border-red-200 bg-red-600 text-white shadow-lg shadow-red-500/20'
                                                : isLoginTheme
                                                    ? 'border border-white/10 bg-white/5 text-slate-200 hover:border-blue-300/20 hover:bg-white/8 hover:text-white'
                                                    : isSlateTheme
                                                        ? 'border border-white/10 bg-white/5 text-slate-200 hover:border-cyan-300/20 hover:bg-white/8 hover:text-white'
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
                                        <p class="text-[11px] font-semibold uppercase tracking-[0.28em]" :class="isLoginTheme ? 'text-blue-300/80' : isSlateTheme ? 'text-cyan-300/80' : 'text-red-600'">Pengaturan</p>
                                    </div>
                                    <DropdownLink
                                        v-for="item in settingsMenuItems"
                                        :key="item.name"
                                        :href="route(item.name)"
                                        :theme="isLoginTheme || isSlateTheme ? 'dark' : 'light'"
                                    >
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
                                            class="inline-flex items-center gap-3 rounded-full border px-3 py-2 text-sm font-semibold transition duration-150 ease-in-out focus:outline-none"
                                            :class="
                                                isLoginTheme
                                                    ? 'border-white/10 bg-white/5 text-slate-100 hover:border-blue-300/20 hover:bg-white/8'
                                                    : isSlateTheme
                                                        ? 'border-white/10 bg-white/5 text-slate-100 hover:border-cyan-300/20 hover:bg-white/8'
                                                        : 'border-blue-100 bg-white text-slate-800 hover:border-red-200 hover:bg-red-50'
                                            "
                                        >
                                            <span
                                                class="flex h-8 w-8 items-center justify-center rounded-full text-xs font-bold text-white shadow-lg"
                                                :class="isLoginTheme ? 'bg-blue-600 shadow-blue-500/25' : isSlateTheme ? 'bg-slate-500 shadow-black/20' : 'bg-red-600 shadow-red-500/25'"
                                            >
                                                {{ userInitials }}
                                            </span>

                                            <span class="hidden md:block">{{ $page.props.auth.user.name }}</span>

                                            <svg
                                                class="ms-1 h-4 w-4"
                                                :class="isLoginTheme || isSlateTheme ? 'text-slate-400' : 'text-slate-500'"
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
                                        <p class="text-[11px] font-semibold uppercase tracking-[0.28em]" :class="isLoginTheme ? 'text-blue-300/80' : isSlateTheme ? 'text-cyan-300/80' : 'text-red-600'">Akun</p>
                                    </div>
                                    <DropdownLink :href="accountMenuItems[0].href" :theme="isLoginTheme || isSlateTheme ? 'dark' : 'light'">
                                        {{ accountMenuItems[0].label }}
                                    </DropdownLink>
                                    <DropdownLink
                                        :href="accountMenuItems[1].href"
                                        method="post"
                                        as="button"
                                        :theme="isLoginTheme || isSlateTheme ? 'dark' : 'light'"
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
                        class="inline-flex items-center justify-center rounded-full border p-2 transition duration-150 ease-in-out focus:outline-none"
                        :class="
                            isLoginTheme
                                ? 'border-white/10 bg-white/5 text-slate-100 hover:border-blue-300/20 hover:bg-white/8 hover:text-white focus:bg-white/8 focus:text-white'
                                : isSlateTheme
                                    ? 'border-white/10 bg-white/5 text-slate-100 hover:border-cyan-300/20 hover:bg-white/8 hover:text-white focus:bg-white/8 focus:text-white'
                                    : 'border-white/10 bg-white/10 text-slate-100 hover:border-blue-200/30 hover:bg-white/15 hover:text-white focus:bg-white/15 focus:text-white'
                        "
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
                    <div class="rounded-2xl border p-2" :class="isLoginTheme ? 'border-white/10 bg-white/5' : isSlateTheme ? 'border-white/10 bg-white/5' : 'border-blue-100 bg-white'">
                        <p class="px-3 pb-2 text-[11px] font-semibold uppercase tracking-[0.28em]" :class="isLoginTheme || isSlateTheme ? 'text-slate-400' : 'text-slate-500'">Dashboard</p>
                        <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')" :theme="isLoginTheme || isSlateTheme ? 'dark' : 'light'">
                            Dashboard
                        </ResponsiveNavLink>
                    </div>

                    <div class="rounded-2xl border p-2" :class="isLoginTheme ? 'border-white/10 bg-white/5' : isSlateTheme ? 'border-white/10 bg-white/5' : 'border-blue-100 bg-white'">
                        <p class="px-3 pb-2 text-[11px] font-semibold uppercase tracking-[0.28em]" :class="isLoginTheme || isSlateTheme ? 'text-slate-400' : 'text-slate-500'">Menu Dokumen</p>
                        <ResponsiveNavLink
                            v-for="item in documentMenuItems"
                            :key="item.name"
                            :href="route(item.name)"
                            :active="route().current(item.name)"
                            :theme="isLoginTheme || isSlateTheme ? 'dark' : 'light'"
                        >
                            {{ item.label }}
                        </ResponsiveNavLink>
                    </div>

                    <div class="rounded-2xl border p-2" :class="isLoginTheme ? 'border-white/10 bg-white/5' : isSlateTheme ? 'border-white/10 bg-white/5' : 'border-blue-100 bg-white'">
                        <p class="px-3 pb-2 text-[11px] font-semibold uppercase tracking-[0.28em]" :class="isLoginTheme || isSlateTheme ? 'text-slate-400' : 'text-slate-500'">Setting</p>
                        <ResponsiveNavLink
                            v-for="item in settingsMenuItems"
                            :key="item.name"
                            :href="route(item.name)"
                            :active="route().current(item.name)"
                            :theme="isLoginTheme || isSlateTheme ? 'dark' : 'light'"
                        >
                            {{ item.label }}
                        </ResponsiveNavLink>
                    </div>
                </div>

                <div class="border-t pb-2 pt-4" :class="isLoginTheme ? 'border-white/10 bg-slate-950/90' : isSlateTheme ? 'border-white/10 bg-slate-950/90' : 'border-blue-100 bg-white'">
                    <div class="px-4">
                        <div class="rounded-2xl px-4 py-3" :class="isLoginTheme || isSlateTheme ? 'bg-white/5' : 'bg-slate-50'">
                            <div class="text-base font-medium" :class="isLoginTheme || isSlateTheme ? 'text-slate-100' : 'text-blue-950'">
                                {{ $page.props.auth.user.name }}
                            </div>
                            <div class="text-sm font-medium" :class="isLoginTheme || isSlateTheme ? 'text-slate-400' : 'text-slate-500'">
                                {{ $page.props.auth.user.email }}
                            </div>
                        </div>
                    </div>

                    <div class="mt-3 space-y-1 px-2">
                        <ResponsiveNavLink :href="route('profile.edit')" :theme="isLoginTheme || isSlateTheme ? 'dark' : 'light'">
                            Profile
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('logout')" method="post" as="button" :theme="isLoginTheme || isSlateTheme ? 'dark' : 'light'">
                            Log Out
                        </ResponsiveNavLink>
                    </div>
                </div>
            </div>
        </nav>

        <header
            v-if="$slots.header"
            class="border-b shadow-sm backdrop-blur"
            :class="isLoginTheme ? 'border-white/10 bg-slate-950/70' : isSlateTheme ? 'border-white/10 bg-slate-950/70' : 'border-blue-100 bg-white/90'"
        >
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                <slot name="header" />
            </div>
        </header>

        <main class="relative">
            <slot />
        </main>
    </div>
</template>
