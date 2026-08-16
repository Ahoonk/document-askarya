<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { formatDate } from '@/utils/format';

defineProps({
    suratJalan: {
        type: Object,
        required: true,
    },
    snapshot: {
        type: Object,
        default: () => ({}),
    },
});
</script>

<template>
    <Head :title="`Surat Jalan ${suratJalan.nomor}`" />

    <AuthenticatedLayout theme="login">
        <div class="relative min-h-screen overflow-hidden bg-[#08111f] text-slate-100">
            <div class="pointer-events-none absolute inset-0">
                <div class="absolute left-[-8rem] top-[-8rem] h-96 w-96 rounded-full bg-red-500/20 blur-3xl"></div>
                <div class="absolute right-[-7rem] top-24 h-[30rem] w-[30rem] rounded-full bg-blue-500/20 blur-3xl"></div>
                <div class="absolute bottom-[-8rem] left-1/3 h-80 w-80 rounded-full bg-amber-400/10 blur-3xl"></div>
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(255,255,255,0.08),_transparent_35%),linear-gradient(135deg,_rgba(8,17,31,0.94),_rgba(9,14,27,0.98))]"></div>
            </div>

            <div class="relative mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="text-xs uppercase tracking-[0.3em] text-blue-200/70">Surat Jalan Detail</p>
                        <h1 class="mt-2 text-3xl font-bold text-white">{{ suratJalan.nomor }}</h1>
                        <p class="mt-2 text-sm text-slate-300">{{ suratJalan.invoice?.customer_name }}</p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <a v-if="suratJalan.preview_url" :href="suratJalan.preview_url" target="_blank" rel="noreferrer" class="rounded-full border border-blue-200 bg-white px-4 py-2 text-sm font-semibold text-blue-800 shadow-sm hover:border-blue-300 hover:bg-blue-50">
                            Preview
                        </a>
                        <Link :href="route('surat-jalan.index')" class="rounded-full border border-white/10 bg-white/5 px-4 py-2 text-sm font-semibold text-slate-200 hover:border-blue-300/20 hover:bg-white/8">
                            Kembali
                        </Link>
                    </div>
                </div>

                <section class="grid gap-6 lg:grid-cols-[1.15fr_0.85fr]">
                    <article class="rounded-[1.5rem] border border-white/10 bg-white p-6 shadow-[0_12px_30px_rgba(0,0,0,0.12)]">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Invoice</p>
                                <p class="mt-2 text-sm font-semibold text-slate-950">{{ suratJalan.invoice?.nomor || '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Tanggal</p>
                                <p class="mt-2 text-sm font-semibold text-slate-950">{{ formatDate(suratJalan.tanggal) }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Pemberi</p>
                                <p class="mt-2 text-sm font-semibold text-slate-950">{{ suratJalan.pemberi_nama || '-' }}</p>
                                <p class="text-sm text-slate-500">{{ suratJalan.pemberi_jabatan || '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Penerima</p>
                                <p class="mt-2 text-sm font-semibold text-slate-950">{{ suratJalan.penerima_nama || '-' }}</p>
                                <p class="text-sm text-slate-500">{{ suratJalan.penerima_hp || '-' }}</p>
                            </div>
                        </div>

                        <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Alamat Pengirim</p>
                            <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-700">
                                {{ suratJalan.pemberi_alamat || '-' }}
                            </p>
                        </div>
                    </article>

                    <aside class="space-y-4 rounded-[1.5rem] border border-white/10 bg-white p-6 shadow-[0_12px_30px_rgba(0,0,0,0.12)]">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Snapshot</p>
                            <p class="mt-2 text-sm leading-6 text-slate-700">
                                Data snapshot disimpan supaya format dokumen tetap konsisten walau data master berubah.
                            </p>
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Items</p>
                            <div v-if="snapshot.items?.length" class="mt-3 space-y-3">
                                <div v-for="item in snapshot.items" :key="item.nama" class="rounded-xl border border-slate-200 bg-white p-3">
                                    <p class="font-semibold text-slate-950">{{ item.nama }}</p>
                                    <p class="text-sm text-slate-500">{{ item.rincian || '-' }}</p>
                                    <p class="text-sm text-slate-500">Qty: {{ item.qty }}</p>
                                </div>
                            </div>
                            <p v-else class="mt-2 text-sm text-slate-500">Tidak ada item snapshot.</p>
                        </div>

                        <Link :href="route('invoice.show', suratJalan.invoice?.id)" class="inline-flex rounded-full bg-gradient-to-r from-blue-700 via-indigo-600 to-red-600 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-blue-700/20 hover:brightness-110">
                            Lihat Invoice
                        </Link>
                    </aside>
                </section>
            </div>
            </div>
    </AuthenticatedLayout>
</template>
