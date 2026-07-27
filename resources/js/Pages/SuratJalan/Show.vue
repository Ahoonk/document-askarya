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

    <AuthenticatedLayout>
        <div class="bg-[#fff2d9]">
            <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="text-xs uppercase tracking-[0.3em] text-blue-700">Surat Jalan Detail</p>
                        <h1 class="mt-2 text-3xl font-bold text-slate-950">{{ suratJalan.nomor }}</h1>
                        <p class="mt-2 text-sm text-slate-500">{{ suratJalan.invoice?.customer_name }}</p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <a v-if="suratJalan.preview_url" :href="suratJalan.preview_url" target="_blank" rel="noreferrer" class="rounded-full border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700">
                            Preview
                        </a>
                        <Link :href="route('surat-jalan.index')" class="rounded-full border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700">
                            Kembali
                        </Link>
                    </div>
                </div>

                <section class="grid gap-6 lg:grid-cols-[1.15fr_0.85fr]">
                    <article class="rounded-[1.5rem] border border-slate-200 bg-white p-6 shadow-sm">
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

                        <div class="mt-6 rounded-2xl bg-[#fff2d9] p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Alamat Pengirim</p>
                            <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-700">
                                {{ suratJalan.pemberi_alamat || '-' }}
                            </p>
                        </div>
                    </article>

                    <aside class="space-y-4 rounded-[1.5rem] border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="rounded-2xl bg-[#fff2d9] p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Snapshot</p>
                            <p class="mt-2 text-sm leading-6 text-slate-700">
                                Data snapshot disimpan supaya format dokumen tetap konsisten walau data master berubah.
                            </p>
                        </div>

                        <div class="rounded-2xl bg-[#fff2d9] p-4">
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

                        <Link :href="route('invoice.show', suratJalan.invoice?.id)" class="inline-flex rounded-full bg-blue-600 px-4 py-2 text-sm font-semibold text-white">
                            Lihat Invoice
                        </Link>
                    </aside>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
