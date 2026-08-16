<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { formatCurrency, formatDate } from '@/utils/format';

const props = defineProps({
    fakturPajak: {
        type: Object,
        required: true,
    },
});

const paymentStyles = {
    unpaid: 'bg-amber-100 text-amber-800',
    paid: 'bg-emerald-100 text-emerald-800',
};

function destroy() {
    if (!confirm(`Hapus faktur pajak ${props.fakturPajak.dokumen_name}?`)) {
        return;
    }

    router.delete(route('faktur-pajak.destroy', props.fakturPajak.id));
}
</script>

<template>
    <Head :title="fakturPajak.dokumen_name" />

    <AuthenticatedLayout theme="login">
        <div class="relative min-h-screen overflow-hidden bg-[#08111f] text-slate-100">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(59,130,246,0.14),transparent_30%),radial-gradient(circle_at_top_right,rgba(239,68,68,0.12),transparent_28%),linear-gradient(180deg,rgba(8,17,31,0.98),rgba(8,17,31,1))]"></div>
            <div class="absolute left-[-6rem] top-24 h-72 w-72 rounded-full bg-blue-500/20 blur-3xl"></div>
            <div class="absolute right-[-5rem] top-40 h-80 w-80 rounded-full bg-rose-500/15 blur-3xl"></div>

            <div class="relative mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="text-xs uppercase tracking-[0.3em] text-sky-300">Detail Faktur Pajak</p>
                        <h1 class="mt-2 text-3xl font-bold text-white">{{ fakturPajak.dokumen_name }}</h1>
                        <p class="mt-2 text-sm text-slate-300">{{ fakturPajak.invoice?.nomor }}</p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <Link :href="route('faktur-pajak.index')" class="rounded-full border border-white/10 bg-white/5 px-4 py-2 text-sm font-semibold text-slate-100 shadow-sm backdrop-blur-sm transition hover:border-sky-300/40 hover:bg-sky-500/10">
                            Kembali
                        </Link>
                        <a v-if="fakturPajak.preview_url" :href="fakturPajak.preview_url" target="_blank" rel="noreferrer" class="rounded-full border border-white/10 bg-white/5 px-4 py-2 text-sm font-semibold text-slate-100 shadow-sm backdrop-blur-sm transition hover:border-sky-300/40 hover:bg-sky-500/10">
                            Preview
                        </a>
                        <Link :href="route('faktur-pajak.edit', fakturPajak.id)" class="rounded-full border border-white/10 bg-white/5 px-4 py-2 text-sm font-semibold text-slate-100 shadow-sm backdrop-blur-sm transition hover:border-sky-300/40 hover:bg-sky-500/10">
                            Edit
                        </Link>
                        <button @click="destroy" type="button" class="rounded-full bg-gradient-to-r from-blue-600 via-indigo-600 to-rose-600 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-rose-950/20">
                            Hapus
                        </button>
                    </div>
                </div>

                <section class="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
                    <article class="rounded-[1.5rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-slate-950/30 backdrop-blur">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Invoice</p>
                                <p class="mt-2 text-lg font-semibold text-white">{{ fakturPajak.invoice?.nomor || '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Status</p>
                                <span class="mt-2 inline-flex rounded-full px-3 py-1 text-xs font-semibold capitalize" :class="paymentStyles[fakturPajak.payment_status] || 'bg-white/10 text-slate-200'">
                                    {{ fakturPajak.payment_status }}
                                </span>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Tanggal Upload</p>
                                <p class="mt-2 text-lg font-semibold text-white">{{ formatDate(fakturPajak.uploaded_at) }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Tanggal Bayar</p>
                                <p class="mt-2 text-lg font-semibold text-white">{{ fakturPajak.payment_date ? formatDate(fakturPajak.payment_date) : '-' }}</p>
                            </div>
                        </div>

                        <div class="mt-6 grid gap-4 md:grid-cols-2">
                            <div>
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Customer</p>
                                <p class="mt-2 text-sm font-semibold text-white">{{ fakturPajak.invoice?.customer_name || '-' }}</p>
                                <p class="mt-1 whitespace-pre-line text-sm text-slate-300">{{ fakturPajak.invoice?.customer_address || '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Total Invoice</p>
                                <p class="mt-2 text-2xl font-semibold text-white">{{ formatCurrency(fakturPajak.invoice?.total || 0) }}</p>
                            </div>
                        </div>
                    </article>

                    <article class="space-y-4 rounded-[1.5rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-slate-950/30 backdrop-blur">
                        <div class="rounded-2xl border border-white/10 bg-slate-950/40 p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Dokumen</p>
                            <a v-if="fakturPajak.preview_url" :href="fakturPajak.preview_url" target="_blank" rel="noreferrer" class="mt-2 inline-flex text-sm font-semibold text-sky-300">
                                Preview dokumen faktur
                            </a>
                            <p v-else class="mt-2 text-sm text-slate-400">Dokumen belum tersedia.</p>
                        </div>

                        <div class="rounded-2xl border border-white/10 bg-slate-950/40 p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">File Name</p>
                            <p class="mt-2 break-all text-sm text-slate-300">{{ fakturPajak.dokumen_name }}</p>
                        </div>

                        <div class="rounded-2xl border border-white/10 bg-slate-950/40 p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Catatan</p>
                            <p class="mt-2 text-sm leading-6 text-slate-300">
                                Faktur pajak ini diikat ke satu invoice, jadi arsipnya tetap konsisten walau status pembayaran berubah.
                            </p>
                        </div>
                    </article>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
