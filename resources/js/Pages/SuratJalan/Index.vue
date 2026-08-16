<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { formatDate } from '@/utils/format';

defineProps({
    suratJalans: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <Head title="Surat Jalan" />

    <AuthenticatedLayout theme="login">
        <div class="relative min-h-screen overflow-hidden bg-[#08111f] text-slate-100">
            <div class="pointer-events-none absolute inset-0">
                <div class="absolute left-[-8rem] top-[-8rem] h-96 w-96 rounded-full bg-red-500/20 blur-3xl"></div>
                <div class="absolute right-[-7rem] top-24 h-[30rem] w-[30rem] rounded-full bg-blue-500/20 blur-3xl"></div>
                <div class="absolute bottom-[-8rem] left-1/3 h-80 w-80 rounded-full bg-amber-400/10 blur-3xl"></div>
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(255,255,255,0.08),_transparent_35%),linear-gradient(135deg,_rgba(8,17,31,0.94),_rgba(9,14,27,0.98))]"></div>
            </div>

            <div class="relative">
            <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <section class="rounded-[2rem] border border-white/10 bg-slate-950/90 p-6 text-white shadow-2xl shadow-black/20 backdrop-blur-xl">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <p class="text-xs uppercase tracking-[0.3em] text-blue-200/70">Delivery</p>
                            <h1 class="mt-3 text-3xl font-bold sm:text-4xl">Surat Jalan</h1>
                            <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-300">
                                Surat Jalan diturunkan dari invoice untuk kebutuhan pengiriman dan arsip operasional.
                            </p>
                        </div>

                        <Link :href="route('surat-jalan.create')" class="rounded-full bg-gradient-to-r from-blue-700 via-indigo-600 to-red-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-700/20 transition hover:brightness-110">
                            Buat Surat Jalan
                        </Link>
                    </div>
                </section>

                <section class="mt-8 overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 bg-gradient-to-r from-slate-50 to-blue-50 px-6 py-4">
                        <h2 class="text-lg font-semibold text-slate-950">Daftar Surat Jalan</h2>
                    </div>

                    <div v-if="suratJalans.length" class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr class="text-left text-xs uppercase tracking-[0.2em] text-slate-500">
                                    <th class="px-6 py-4">Nomor</th>
                                    <th class="px-6 py-4">Invoice</th>
                                    <th class="px-6 py-4">Tanggal</th>
                                    <th class="px-6 py-4">Penerima</th>
                                    <th class="px-6 py-4">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="suratJalan in suratJalans" :key="suratJalan.id" class="hover:bg-blue-50/70">
                                    <td class="px-6 py-4">
                                        <div class="font-semibold text-slate-950">{{ suratJalan.nomor }}</div>
                                        <div class="text-sm text-slate-500">SJ</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-slate-950">{{ suratJalan.invoice?.nomor }}</div>
                                        <div class="text-sm text-slate-500">{{ suratJalan.invoice?.customer_name }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-600">{{ formatDate(suratJalan.tanggal) }}</td>
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-slate-950">{{ suratJalan.penerima_nama || '-' }}</div>
                                        <div class="text-sm text-slate-500">{{ suratJalan.penerima_hp || '-' }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-wrap gap-2">
                                            <a v-if="suratJalan.preview_url" :href="suratJalan.preview_url" target="_blank" rel="noreferrer" class="rounded-full border border-blue-200 bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-800">
                                                Preview
                                            </a>
                                            <Link :href="route('surat-jalan.show', suratJalan.id)" class="rounded-full border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700">
                                                Detail
                                            </Link>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-else class="px-6 py-16 text-center">
                        <p class="text-lg font-semibold text-slate-900">Belum ada surat jalan</p>
                        <p class="mt-2 text-sm text-slate-500">Buat surat jalan pertama dari invoice yang sudah tersedia.</p>
                    </div>
                </section>
            </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
