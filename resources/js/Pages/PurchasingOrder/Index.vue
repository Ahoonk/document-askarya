<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { formatCurrency, formatDate } from '@/utils/format';
import UploadCard from './Partials/UploadCard.vue';

defineProps({
    approvedSatuan: {
        type: Array,
        default: () => [],
    },
    approvedKontrak: {
        type: Array,
        default: () => [],
    },
    existingData: {
        type: Array,
        default: () => [],
    },
});

function createInvoice(penawaran) {
    router.post(route('purchasing-order.create-invoice', penawaran.id), {}, {
        preserveScroll: true,
    });
}

function nextInvoice(penawaran) {
    router.post(route('purchasing-order.next-invoice', penawaran.id), {
    }, {
        preserveScroll: true,
    });
}

function openInvoice(invoiceId) {
    router.visit(route('invoice.show', invoiceId));
}

function cancelApproved(penawaran) {
    if (!confirm('Kembalikan status penawaran ke submitted?')) {
        return;
    }

    router.post(route('purchasing-order.cancel', penawaran.id), {}, {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Purchasing Order" />

    <AuthenticatedLayout>
        <div class="bg-[#fff2d9]">
            <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <section class="rounded-[2rem] bg-slate-950 p-6 text-white shadow-2xl shadow-slate-900/20">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <p class="text-xs uppercase tracking-[0.3em] text-blue-200/70">Approval Bridge</p>
                            <h1 class="mt-3 text-3xl font-bold sm:text-4xl">Purchasing Order</h1>
                            <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-300">
                                Penawaran yang sudah approved masuk ke sini. Upload PO terlebih dahulu, lalu invoice bisa dibuat.
                            </p>
                        </div>

                        <Link :href="route('penawaran.index')" class="rounded-full bg-blue-400 px-5 py-3 text-sm font-semibold text-slate-950">
                            Lihat Penawaran
                        </Link>
                    </div>
                </section>

                <section class="mt-8">
                    <div class="flex flex-wrap items-end justify-between gap-4">
                        <div>
                            <p class="text-xs uppercase tracking-[0.3em] text-blue-700">Siap Upload</p>
                            <h2 class="mt-2 text-2xl font-bold text-slate-950">Approved tanpa PO</h2>
                        </div>
                        <p class="text-sm text-slate-500">
                            Kategori ini meniru alur lama: satu untuk kontrak satuan, satu untuk kontrak reguler.
                        </p>
                    </div>

                    <div class="mt-5 grid gap-4 xl:grid-cols-2">
                        <UploadCard v-for="penawaran in approvedSatuan" :key="penawaran.id" :penawaran="penawaran" />
                    </div>

                    <div class="mt-5 grid gap-4 xl:grid-cols-2">
                        <UploadCard v-for="penawaran in approvedKontrak" :key="penawaran.id" :penawaran="penawaran" />
                    </div>

                    <div v-if="!approvedSatuan.length && !approvedKontrak.length" class="mt-5 rounded-[1.5rem] border border-dashed border-slate-300 bg-white p-10 text-center">
                        <p class="text-lg font-semibold text-slate-900">Belum ada penawaran approved tanpa PO</p>
                        <p class="mt-2 text-sm text-slate-500">Approve penawaran dulu dari menu Penawaran agar muncul di sini.</p>
                    </div>
                </section>

                <section class="mt-10">
                    <div class="flex flex-wrap items-end justify-between gap-4">
                        <div>
                            <p class="text-xs uppercase tracking-[0.3em] text-blue-700">Sudah Lengkap</p>
                            <h2 class="mt-2 text-2xl font-bold text-slate-950">Data yang sudah punya PO</h2>
                        </div>
                    </div>

                    <div v-if="existingData.length" class="mt-5 grid gap-4">
                        <article v-for="penawaran in existingData" :key="penawaran.id" class="rounded-[1.5rem] border border-slate-200 bg-white p-5 shadow-sm">
                            <div class="flex flex-wrap items-start justify-between gap-4">
                                <div>
                                    <p class="text-xs uppercase tracking-[0.22em] text-blue-700">#{{ penawaran.id }}</p>
                                    <h3 class="mt-2 text-xl font-semibold text-slate-950">{{ penawaran.nomor }}</h3>
                                    <p class="mt-1 text-sm text-slate-500">{{ penawaran.to_company }} · {{ formatDate(penawaran.tanggal) }}</p>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <Link :href="route('invoice.index')" class="rounded-full border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700">
                                        Invoice
                                    </Link>
                                    <button
                                        v-if="!penawaran.latest_invoice"
                                        type="button"
                                        @click="createInvoice(penawaran)"
                                        class="rounded-full bg-emerald-600 px-4 py-2 text-sm font-semibold text-white"
                                    >
                                        Cetak Invoice
                                    </button>
                                    <button
                                        v-else-if="penawaran.jenis_kontrak === 'kontrak'"
                                        type="button"
                                        @click="nextInvoice(penawaran)"
                                        class="rounded-full bg-slate-900 px-4 py-2 text-sm font-semibold text-white"
                                    >
                                        Next Invoice
                                    </button>
                                    <button
                                        v-else
                                        type="button"
                                        @click="openInvoice(penawaran.latest_invoice.id)"
                                        class="rounded-full bg-blue-600 px-4 py-2 text-sm font-semibold text-white"
                                    >
                                        Buka Invoice
                                    </button>
                                    <button type="button" @click="cancelApproved(penawaran)" class="rounded-full bg-rose-600 px-4 py-2 text-sm font-semibold text-white">
                                        Batalkan
                                    </button>
                                </div>
                            </div>

                            <div class="mt-5 grid gap-3 md:grid-cols-4">
                                <div class="rounded-2xl bg-[#fff2d9] p-4">
                                    <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Total</p>
                                    <p class="mt-2 text-lg font-semibold text-slate-950">{{ formatCurrency(penawaran.total) }}</p>
                                </div>
                                <div class="rounded-2xl bg-[#fff2d9] p-4">
                                    <p class="text-xs uppercase tracking-[0.2em] text-slate-500">PO</p>
                                    <p class="mt-2 text-sm font-semibold text-slate-950">{{ penawaran.purchasing_order?.nomor_po || '-' }}</p>
                                </div>
                                <div class="rounded-2xl bg-[#fff2d9] p-4">
                                    <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Invoice</p>
                                    <p class="mt-2 text-sm font-semibold text-slate-950">{{ penawaran.latest_invoice?.nomor || '-' }}</p>
                                </div>
                                <div class="rounded-2xl bg-[#fff2d9] p-4">
                                    <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Status</p>
                                    <p class="mt-2 text-sm font-semibold capitalize text-slate-950">{{ penawaran.status }}</p>
                                </div>
                            </div>

                            <div class="mt-5 rounded-[1.25rem] border border-slate-200 bg-slate-50 p-4">
                                <div class="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Dokumen PO</p>
                                    <p class="mt-1 text-sm font-semibold text-slate-950">{{ penawaran.purchasing_order?.dokumen_name || '-' }}</p>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <a
                                        v-if="penawaran.purchasing_order?.preview_url"
                                        :href="penawaran.purchasing_order.preview_url"
                                        target="_blank"
                                        rel="noreferrer"
                                        class="rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50"
                                    >
                                        Preview
                                    </a>
                                </div>
                            </div>
                            </div>
                        </article>
                    </div>

                    <div v-else class="mt-5 rounded-[1.5rem] border border-dashed border-slate-300 bg-white p-10 text-center">
                        <p class="text-lg font-semibold text-slate-900">Belum ada transaksi yang lengkap</p>
                        <p class="mt-2 text-sm text-slate-500">Setelah PO di-upload dan invoice dibuat, data akan muncul di sini.</p>
                    </div>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
