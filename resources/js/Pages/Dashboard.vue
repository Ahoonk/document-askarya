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

const quickStats = computed(() => [
    {
        label: 'Modul inti',
        value: formatCurrency(props.dashboardFinancial.total_semua ?? 0),
        note: 'jumlah nilai invoice yang sudah terbuat',
    },
    {
        label: 'Core stack',
        value: formatCurrency(props.dashboardFinancial.total_belum_dibayar ?? 0),
        note: 'jumlah nilai invoice belum dibayar',
    },
    {
        label: 'Runtime',
        value: formatCurrency(props.dashboardFinancial.pajak_belum_dibayar ?? 0),
        note: 'jumlah pajak dari invoice unpaid',
    },
    {
        label: 'Framework',
        value: Number(props.dashboardFinancial.jumlah_semua ?? 0).toLocaleString('id-ID'),
        note: 'jumlah transaksi invoice yang sudah dicetak',
    },
]);
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <div class="relative overflow-hidden bg-[#fff2d9] text-slate-900">
            <div class="pointer-events-none absolute inset-0">
                <div class="absolute left-[-6rem] top-[-6rem] h-72 w-72 rounded-full bg-blue-500/10 blur-3xl"></div>
                <div class="absolute right-[-8rem] top-24 h-80 w-80 rounded-full bg-red-500/10 blur-3xl"></div>
            </div>

            <div class="relative mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
                <section class="overflow-hidden rounded-[2rem] border border-blue-100 bg-white shadow-2xl shadow-blue-950/10">
                    <div class="grid gap-8 p-6 lg:grid-cols-[1.15fr_0.85fr] lg:p-10">
                        <div class="space-y-5">
                            <span class="inline-flex rounded-full bg-[#ff2d2d] px-4 py-1 text-xs font-semibold uppercase tracking-[0.28em] text-white shadow-[0_8px_20px_rgba(255,45,45,0.32)]">
                                Dokumen Askarya
                            </span>
                            <h1 class="max-w-3xl font-['Space_Grotesk',sans-serif] text-4xl font-bold leading-tight tracking-[-0.05em] text-blue-950 sm:text-5xl">
                                PT ALDERA SADDATECH KARYA
                            </h1>
                            <p class="max-w-2xl text-sm leading-7 text-slate-600 sm:text-base">
                                Aplikasi Internal Perusahaan Untuk Pengelolaan Dokumen Transaksi Perusahaan, Mulai Dari
                                Penawaran, Purchasing Order, Invoice, Surat Jalan, Berita Acara, Faktur Pajak, dan Nota Toko.
                            </p>

                            <div class="flex flex-wrap gap-3 pt-2">
                                <Link href="/penawaran" class="inline-flex items-center rounded-full bg-blue-700 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-600">
                                    Mulai dari Penawaran
                                </Link>
                                <Link href="/nota-toko" class="inline-flex items-center rounded-full border border-[#ff2d2d] bg-white px-5 py-3 text-sm font-semibold text-[#ff2d2d] transition hover:bg-red-50">
                                    Buka Nota Toko
                                </Link>
                            </div>
                        </div>

                        <div class="rounded-[1.75rem] border border-white/10 bg-gradient-to-br from-[#2748a8] via-[#223f98] to-[#18337f] p-5 shadow-[0_18px_42px_rgba(15,23,42,0.20)]">
                            <div class="grid gap-3 sm:grid-cols-2">
                                <article v-for="stat in quickStats" :key="stat.label" class="rounded-2xl border border-white/75 bg-gradient-to-br from-[#ff6a66] to-[#f74a44] p-4 shadow-[0_8px_18px_rgba(247,74,68,0.12)]">
                                    <p class="text-xs uppercase tracking-[0.22em] text-white/88">{{ stat.label }}</p>
                                    <p class="mt-2 text-2xl font-semibold text-white">{{ stat.value }}</p>
                                    <p class="mt-1 text-sm text-white/82">{{ stat.note }}</p>
                                </article>
                            </div>

                            <div class="mt-5 rounded-[1.5rem] border border-white/12 bg-white/8 p-5 backdrop-blur">
                                <p class="text-xs uppercase tracking-[0.25em] text-white">Struktur target</p>
                                <ul class="mt-4 space-y-3 text-sm text-white">
                                    <li>1. Pembatalan Invoice, Harap Melapor Kepada Admin</li>
                                    <li>2. Laporan Keuangan Berdasarkan Invoice yang terbit.</li>
                                    <li>3. Surat Jalan dan Berita Acara Dapat Dirubah Berdasarkan Kebutuhan.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="mt-8">
                    <div class="flex flex-wrap items-end justify-between gap-4">
                        <div>
                            <p class="text-xs uppercase tracking-[0.3em] text-blue-700">Workflow Map</p>
                            <h2 class="mt-2 text-2xl font-semibold text-blue-950">Pilihan Dokumen</h2>
                        </div>
                        <p class="max-w-2xl text-sm leading-6 text-slate-600">
                            Alur Dokumen Dimulai Dari Pengisian Surat Penawaran
                        </p>
                    </div>

                    <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                        <article v-for="module in moduleCards" :key="module.key" class="rounded-[1.5rem] border-2 border-blue-200 bg-white p-5 shadow-[0_12px_30px_rgba(30,64,175,0.08)] transition hover:-translate-y-0.5 hover:shadow-[0_16px_36px_rgba(30,64,175,0.12)]">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-xs uppercase tracking-[0.24em] text-[#ff2d2d]/95">{{ module.stage }}</p>
                                    <h3 class="mt-2 text-xl font-semibold text-blue-950">{{ module.title }}</h3>
                                </div>
                                <span class="rounded-full border-2 border-[#ff2d2d] bg-[#ff2d2d] px-3 py-1 text-xs font-semibold text-white shadow-lg shadow-[#ff2d2d]/25 ring-2 ring-white">
                                    {{ module.key }}
                                </span>
                            </div>

                            <p class="mt-3 text-sm leading-6 text-slate-600">
                                {{ module.summary }}
                            </p>

                            <div class="mt-5 flex flex-wrap gap-2">
                                <Link
                                    :href="route(module.primary_route)"
                                    class="inline-flex items-center rounded-full bg-blue-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-600"
                                >
                                    Buka
                                </Link>
                                <Link
                                    v-if="module.secondary_route"
                                    :href="route(module.secondary_route)"
                                    class="inline-flex items-center rounded-full border border-[#ff2d2d] bg-white px-4 py-2 text-sm font-semibold text-[#ff2d2d] transition hover:bg-red-50"
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
