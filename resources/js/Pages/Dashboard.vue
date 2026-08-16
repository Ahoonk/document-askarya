<script setup>
import { computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { formatCurrency } from '@/utils/format';

const props = defineProps({
    modules: {
        type: Object,
        default: () => ({}),
    },
    dashboardFinancial: {
        type: Object,
        default: () => ({}),
    },
    laravelVersion: {
        type: String,
        default: '',
    },
    phpVersion: {
        type: String,
        default: '',
    },
});

const moduleCards = computed(() => {
    return Object.entries(props.modules).map(([key, module]) => ({
        key,
        ...module,
    }));
});

const moduleThemes = {
    penawaran: {
        stripe: 'from-blue-700 via-indigo-600 to-red-600',
        stage: 'text-blue-700',
        badge: 'border-blue-200 bg-blue-50 text-blue-800',
        primary: 'from-blue-700 via-indigo-600 to-red-600',
        secondary: 'border-blue-200 bg-blue-50 text-blue-900 hover:border-blue-300 hover:bg-blue-100',
    },
    'purchasing-order': {
        stripe: 'from-slate-700 via-blue-700 to-indigo-600',
        stage: 'text-slate-700',
        badge: 'border-slate-200 bg-slate-50 text-slate-800',
        primary: 'from-slate-800 via-blue-700 to-red-600',
        secondary: 'border-slate-200 bg-slate-50 text-slate-700 hover:border-slate-300 hover:bg-slate-100',
    },
    invoice: {
        stripe: 'from-blue-700 via-indigo-600 to-red-600',
        stage: 'text-indigo-700',
        badge: 'border-indigo-200 bg-indigo-50 text-indigo-800',
        primary: 'from-blue-700 via-indigo-600 to-red-600',
        secondary: 'border-indigo-200 bg-indigo-50 text-indigo-900 hover:border-indigo-300 hover:bg-indigo-100',
    },
    'surat-jalan': {
        stripe: 'from-blue-600 via-slate-700 to-indigo-700',
        stage: 'text-blue-700',
        badge: 'border-blue-200 bg-blue-50 text-blue-800',
        primary: 'from-blue-700 via-indigo-700 to-slate-900',
        secondary: 'border-blue-200 bg-blue-50 text-blue-900 hover:border-blue-300 hover:bg-blue-100',
    },
    'berita-acara': {
        stripe: 'from-indigo-600 via-blue-700 to-red-600',
        stage: 'text-indigo-700',
        badge: 'border-red-200 bg-red-50 text-red-700',
        primary: 'from-indigo-600 via-blue-700 to-red-600',
        secondary: 'border-red-200 bg-red-50 text-red-700 hover:border-red-300 hover:bg-red-100',
    },
    'faktur-pajak': {
        stripe: 'from-slate-900 via-blue-700 to-red-600',
        stage: 'text-slate-700',
        badge: 'border-slate-300 bg-slate-100 text-slate-900',
        primary: 'from-slate-900 via-blue-700 to-red-600',
        secondary: 'border-slate-300 bg-slate-100 text-slate-700 hover:border-slate-400 hover:bg-slate-200',
    },
    'nota-toko': {
        stripe: 'from-red-600 via-indigo-600 to-blue-700',
        stage: 'text-red-700',
        badge: 'border-red-200 bg-red-50 text-red-700',
        primary: 'from-red-600 via-indigo-600 to-blue-700',
        secondary: 'border-red-200 bg-red-50 text-red-700 hover:border-red-300 hover:bg-red-100',
    },
    customers: {
        stripe: 'from-blue-700 via-slate-700 to-red-600',
        stage: 'text-blue-700',
        badge: 'border-blue-200 bg-blue-50 text-blue-800',
        primary: 'from-blue-700 via-slate-700 to-red-600',
        secondary: 'border-blue-200 bg-blue-50 text-blue-900 hover:border-blue-300 hover:bg-blue-100',
    },
    mitra: {
        stripe: 'from-indigo-600 via-blue-700 to-red-500',
        stage: 'text-indigo-700',
        badge: 'border-indigo-200 bg-indigo-50 text-indigo-800',
        primary: 'from-indigo-600 via-blue-700 to-red-500',
        secondary: 'border-indigo-200 bg-indigo-50 text-indigo-900 hover:border-indigo-300 hover:bg-indigo-100',
    },
    users: {
        stripe: 'from-slate-700 via-indigo-600 to-blue-700',
        stage: 'text-slate-700',
        badge: 'border-slate-200 bg-slate-50 text-slate-800',
        primary: 'from-slate-700 via-indigo-600 to-blue-700',
        secondary: 'border-slate-200 bg-slate-50 text-slate-700 hover:border-slate-300 hover:bg-slate-100',
    },
    'document-templates': {
        stripe: 'from-blue-700 via-red-600 to-indigo-600',
        stage: 'text-blue-700',
        badge: 'border-blue-200 bg-blue-50 text-blue-800',
        primary: 'from-blue-700 via-red-600 to-indigo-600',
        secondary: 'border-blue-200 bg-blue-50 text-blue-900 hover:border-blue-300 hover:bg-blue-100',
    },
    'simulasi-pembiayaan': {
        stripe: 'from-indigo-700 via-slate-700 to-blue-700',
        stage: 'text-indigo-700',
        badge: 'border-indigo-200 bg-indigo-50 text-indigo-800',
        primary: 'from-indigo-700 via-slate-700 to-blue-700',
        secondary: 'border-indigo-200 bg-indigo-50 text-indigo-900 hover:border-indigo-300 hover:bg-indigo-100',
    },
    default: {
        stripe: 'from-blue-700 via-indigo-600 to-red-600',
        stage: 'text-blue-700',
        badge: 'border-slate-200 bg-slate-50 text-slate-800',
        primary: 'from-blue-700 via-indigo-600 to-red-600',
        secondary: 'border-slate-200 bg-white text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-900',
    },
};

const getModuleTheme = (key) => moduleThemes[key] ?? moduleThemes.default;

const quickStats = computed(() => [
    {
        label: 'Total Invoice',
        value: formatCurrency(props.dashboardFinancial.total_semua ?? 0),
        note: 'Periode 2026',
    },
    {
        label: 'Unpaid Invoice',
        value: formatCurrency(props.dashboardFinancial.total_belum_dibayar ?? 0),
        note: 'Periode 2026',
    },
    {
        label: 'Unpaid Tax',
        value: formatCurrency(props.dashboardFinancial.pajak_belum_dibayar ?? 0),
        note: 'Periode 2026',
    },
    {
        label: 'Total Transaction',
        value: Number(props.dashboardFinancial.jumlah_semua ?? 0).toLocaleString('id-ID'),
        note: 'Periode 2026',
    },
]);
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout theme="login">
        <div class="relative overflow-hidden bg-[#08111f] text-slate-100">
            <div class="pointer-events-none absolute inset-0">
                <div class="absolute left-[-8rem] top-[-8rem] h-96 w-96 rounded-full bg-red-500/20 blur-3xl"></div>
                <div class="absolute right-[-7rem] top-24 h-[30rem] w-[30rem] rounded-full bg-blue-500/20 blur-3xl"></div>
                <div class="absolute bottom-[-8rem] left-1/3 h-80 w-80 rounded-full bg-amber-400/10 blur-3xl"></div>
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(255,255,255,0.08),_transparent_35%),linear-gradient(135deg,_rgba(8,17,31,0.94),_rgba(9,14,27,0.98))]"></div>
            </div>

            <div class="relative mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
                <section class="overflow-hidden rounded-[2.5rem] border border-white/10 bg-white/6 shadow-2xl shadow-black/20 backdrop-blur-xl">
                    <div class="grid gap-6 p-6 lg:grid-cols-[1.05fr_0.95fr] lg:p-8">
                        <div class="relative overflow-hidden rounded-[2rem] border border-white/10 bg-white/6 p-6 shadow-2xl shadow-black/20 sm:p-8 lg:p-10">
                            <div class="absolute right-[-3rem] top-[-3rem] h-32 w-32 rounded-full bg-red-500/20 blur-2xl"></div>
                            <div class="absolute bottom-[-2rem] left-[-2rem] h-40 w-40 rounded-full bg-blue-400/10 blur-3xl"></div>

                            <div class="relative space-y-5">
                                <span class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-4 py-1 text-xs font-semibold uppercase tracking-[0.28em] text-blue-100 shadow-[0_8px_20px_rgba(15,23,42,0.18)]">
                                    <span class="h-1.5 w-1.5 rounded-full bg-red-400/80"></span>
                                    Dokumen Askarya
                                </span>
                                <h1 class="max-w-3xl font-['Space_Grotesk',sans-serif] text-4xl font-bold leading-tight tracking-[-0.05em] text-white sm:text-5xl">
                                    PT ALDERA SADDATECH KARYA
                                </h1>
                                <p class="max-w-2xl text-sm leading-7 text-slate-300 sm:text-base">
                                    Aplikasi Internal Perusahaan Untuk Pengelolaan Dokumen Transaksi Perusahaan, Mulai Dari
                                    Penawaran, Purchasing Order, Invoice, Surat Jalan, Berita Acara, Faktur Pajak, dan Nota Toko.
                                </p>

                                <div class="flex flex-wrap gap-3 pt-2">
                                    <Link href="/penawaran" class="inline-flex items-center rounded-full bg-gradient-to-r from-blue-700 via-indigo-600 to-red-600 px-5 py-3 text-sm font-semibold text-white shadow-[0_10px_28px_rgba(59,130,246,0.28)] transition hover:-translate-y-0.5 hover:brightness-110">
                                        Mulai dari Penawaran
                                    </Link>
                                    <Link href="/nota-toko" class="inline-flex items-center rounded-full border border-white/10 bg-white/5 px-5 py-3 text-sm font-semibold text-slate-200 transition hover:border-blue-300/20 hover:bg-white/8 hover:text-white">
                                        Buka Nota Toko
                                    </Link>
                                </div>
                            </div>
                        </div>

                        <div class="relative overflow-hidden rounded-[2rem] border border-slate-500/70 bg-gradient-to-br from-[#eef4ff] via-[#e7eefb] to-[#dde7f7] px-6 py-7 text-slate-900 shadow-[0_22px_54px_rgba(15,23,42,0.16)] ring-1 ring-white/80 sm:px-8 sm:py-8">
                            <div class="absolute right-0 top-0 h-40 w-40 rounded-full bg-blue-300/65 blur-3xl"></div>
                            <div class="absolute left-0 bottom-0 h-40 w-40 rounded-full bg-red-200/45 blur-3xl"></div>
                            <div class="absolute inset-0 bg-[linear-gradient(135deg,rgba(255,255,255,0.42),rgba(255,255,255,0.08))]"></div>

                            <div class="relative">
                                <div class="inline-flex items-center rounded-full border border-blue-200 bg-white/72 px-4 py-2 text-xs font-semibold uppercase tracking-[0.28em] text-blue-800 shadow-sm backdrop-blur">
                                    Ringkasan
                                </div>

                                <div class="mt-6">
                                    <h2 class="text-3xl font-black tracking-tight text-slate-950 sm:text-4xl">Selamat datang</h2>
                                    <p class="mt-3 max-w-xl text-sm leading-7 text-slate-700 sm:text-base">
                                        Dashboard ringkas untuk memantau dokumen dan transaksi yang sedang berjalan.
                                    </p>
                                </div>

                                <div class="mt-6 grid gap-3 sm:grid-cols-2">
                                    <article
                                        v-for="stat in quickStats"
                                        :key="stat.label"
                                        class="rounded-2xl border border-slate-400/80 bg-white/78 p-4 shadow-[0_8px_24px_rgba(15,23,42,0.07)] ring-1 ring-white/70 backdrop-blur transition duration-200 ease-out hover:-translate-y-0.5 hover:border-slate-500 hover:bg-white/90 hover:shadow-[0_14px_32px_rgba(15,23,42,0.12)]"
                                    >
                                        <div class="min-w-0">
                                            <p class="text-[11px] uppercase tracking-[0.22em] text-slate-500">{{ stat.label }}</p>
                                            <p class="mt-2 text-2xl font-semibold tracking-[-0.03em] text-slate-950">{{ stat.value }}</p>
                                            <p class="mt-1 text-sm text-slate-600">{{ stat.note }}</p>
                                        </div>
                                    </article>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="mt-8">
                    <div class="flex flex-wrap items-end justify-between gap-4">
                        <div>
                            <p class="text-xs uppercase tracking-[0.3em] text-blue-200/70">Workflow Map</p>
                            <h2 class="mt-2 text-2xl font-semibold text-white">Pilihan Dokumen</h2>
                        </div>
                        <p class="max-w-2xl text-sm leading-6 text-slate-300">
                            Alur Dokumen Dimulai Dari Pengisian Surat Penawaran
                        </p>
                    </div>

                    <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                        <article
                            v-for="module in moduleCards"
                            :key="module.key"
                            class="group rounded-[1.5rem] border border-white/10 bg-white px-5 py-5 text-slate-900 shadow-[0_12px_30px_rgba(0,0,0,0.16)] transition hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-[0_16px_34px_rgba(0,0,0,0.2)]"
                        >
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-xs uppercase tracking-[0.24em]" :class="getModuleTheme(module.key).stage">{{ module.stage }}</p>
                                    <h3 class="mt-2 text-xl font-semibold text-slate-950">{{ module.title }}</h3>
                                </div>
                                <span
                                    class="rounded-full border px-3 py-1 text-xs font-semibold shadow-[0_10px_22px_rgba(220,38,38,0.12)] ring-1 ring-white/10"
                                    :class="getModuleTheme(module.key).badge"
                                >
                                    {{ module.key }}
                                </span>
                            </div>

                            <div class="mt-4 h-1.5 rounded-full" :class="`bg-gradient-to-r ${getModuleTheme(module.key).stripe}`"></div>

                            <p class="mt-3 text-sm leading-6 text-slate-600">
                                {{ module.summary }}
                            </p>

                            <div class="mt-5 flex flex-wrap gap-2">
                                <Link
                                    :href="route(module.primary_route)"
                                    class="inline-flex items-center rounded-full bg-gradient-to-r px-4 py-2 text-sm font-semibold text-white shadow-[0_10px_24px_rgba(59,130,246,0.22)] transition hover:-translate-y-0.5 hover:brightness-110"
                                    :class="getModuleTheme(module.key).primary"
                                >
                                    Buka
                                </Link>
                                <Link
                                    v-if="module.secondary_route"
                                    :href="route(module.secondary_route)"
                                    class="inline-flex items-center rounded-full border px-4 py-2 text-sm font-semibold transition"
                                    :class="getModuleTheme(module.key).secondary"
                                >
                                    Tambah Baru
                                </Link>
                            </div>
                        </article>
                    </div>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
