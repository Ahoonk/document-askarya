<script setup>
import { computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { formatCurrency, formatDate } from '@/utils/format';

const props = defineProps({
    notaToko: {
        type: Object,
        required: true,
    },
    snapshot: {
        type: Object,
        default: () => ({}),
    },
});

const paymentStyles = {
    unpaid: 'bg-amber-100 text-amber-800',
    paid: 'bg-emerald-100 text-emerald-800',
};

function openPdf(url, download = false) {
    if (!url) {
        return;
    }

    const parsed = new URL(url, window.location.origin);
    parsed.searchParams.set('cb', String(Date.now()));

    if (download) {
        parsed.searchParams.set('download', '1');
    }

    window.open(parsed.toString(), '_blank', 'noreferrer');
}

function destroy() {
    if (!confirm(`Hapus nota toko ${props.notaToko.nomor}?`)) {
        return;
    }

    router.delete(route('nota-toko.destroy', props.notaToko.id));
}
</script>

<template>
    <Head :title="notaToko.nomor" />

    <AuthenticatedLayout theme="login">
        <div class="relative min-h-screen overflow-hidden bg-[#08111f] text-slate-100">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(59,130,246,0.14),transparent_30%),radial-gradient(circle_at_top_right,rgba(239,68,68,0.12),transparent_28%),linear-gradient(180deg,rgba(8,17,31,0.98),rgba(8,17,31,1))]"></div>
            <div class="absolute left-[-6rem] top-24 h-72 w-72 rounded-full bg-blue-500/20 blur-3xl"></div>
            <div class="absolute right-[-5rem] top-40 h-80 w-80 rounded-full bg-rose-500/15 blur-3xl"></div>

            <div class="relative mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="text-xs uppercase tracking-[0.3em] text-sky-300">Detail Nota Toko</p>
                        <h1 class="mt-2 text-3xl font-bold text-white">{{ notaToko.nomor }}</h1>
                        <p class="mt-2 text-sm text-slate-300">{{ notaToko.customer_nama }}</p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <button v-if="notaToko.preview_url" type="button" @click="openPdf(notaToko.preview_url)" class="rounded-full border border-white/10 bg-white/5 px-4 py-2 text-sm font-semibold text-slate-100 shadow-sm backdrop-blur-sm transition hover:border-sky-300/40 hover:bg-sky-500/10">
                            Preview PDF
                        </button>
                        <button v-if="notaToko.preview_url" type="button" @click="openPdf(notaToko.preview_url, true)" class="rounded-full bg-gradient-to-r from-blue-600 via-indigo-600 to-rose-600 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-rose-950/20">
                            Unduh PDF
                        </button>
                        <Link :href="route('nota-toko.index')" class="rounded-full border border-white/10 bg-white/5 px-4 py-2 text-sm font-semibold text-slate-100 shadow-sm backdrop-blur-sm transition hover:border-sky-300/40 hover:bg-sky-500/10">
                            Kembali
                        </Link>
                        <Link :href="route('nota-toko.edit', notaToko.id)" class="rounded-full border border-white/10 bg-white/5 px-4 py-2 text-sm font-semibold text-slate-100 shadow-sm backdrop-blur-sm transition hover:border-sky-300/40 hover:bg-sky-500/10">
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
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Tanggal</p>
                                <p class="mt-2 text-lg font-semibold text-white">{{ formatDate(notaToko.tanggal) }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Status Pembayaran</p>
                                <p class="mt-2 text-lg font-semibold capitalize text-white">
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold" :class="paymentStyles[notaToko.payment_status] || 'bg-white/10 text-slate-200'">
                                        {{ notaToko.payment_status }}
                                    </span>
                                </p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Email Customer</p>
                                <p class="mt-2 text-lg font-semibold text-white">{{ notaToko.customer_email || '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Tanggal Bayar</p>
                                <p class="mt-2 text-lg font-semibold text-white">{{ notaToko.payment_date ? formatDate(notaToko.payment_date) : '-' }}</p>
                            </div>
                        </div>

                        <div class="mt-6">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Alamat</p>
                            <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-300">{{ notaToko.alamat || '-' }}</p>
                        </div>

                        <div class="mt-6">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Keterangan</p>
                            <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-300">{{ notaToko.keterangan || '-' }}</p>
                        </div>
                    </article>

                    <article class="space-y-4 rounded-[1.5rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-slate-950/30 backdrop-blur">
                        <div class="rounded-2xl border border-white/10 bg-slate-950/40 p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Subtotal</p>
                            <p class="mt-2 text-xl font-semibold text-white">{{ formatCurrency(notaToko.subtotal) }}</p>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-slate-950/40 p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Tax</p>
                            <p class="mt-2 text-xl font-semibold text-white">{{ formatCurrency(notaToko.tax_amount) }}</p>
                        </div>
                        <div class="rounded-2xl border border-sky-400/20 bg-gradient-to-r from-blue-600/20 via-indigo-600/20 to-rose-600/20 p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-sky-300">Total</p>
                            <p class="mt-2 text-xl font-semibold text-white">{{ formatCurrency(notaToko.total) }}</p>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-slate-950/40 p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Snapshot</p>
                            <p class="mt-2 text-sm text-slate-300">Snapshot tersimpan supaya nota tetap punya salinan data saat sumber berubah.</p>
                        </div>
                    </article>
                </section>

                <section class="mt-6 overflow-hidden rounded-[1.5rem] border border-white/10 bg-white/5 shadow-2xl shadow-slate-950/30 backdrop-blur">
                    <div class="border-b border-white/10 px-6 py-4">
                        <h2 class="text-lg font-semibold text-white">Item Nota Toko</h2>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-white/10">
                            <thead class="bg-white/5">
                                <tr class="text-left text-xs uppercase tracking-[0.2em] text-slate-400">
                                    <th class="px-6 py-4">Nama</th>
                                    <th class="px-6 py-4">Qty</th>
                                    <th class="px-6 py-4">Satuan</th>
                                    <th class="px-6 py-4">Harga</th>
                                    <th class="px-6 py-4">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/10">
                                <tr v-for="item in notaToko.items" :key="item.id">
                                    <td class="px-6 py-4 font-medium text-white">{{ item.nama }}</td>
                                    <td class="px-6 py-4 text-slate-300">{{ item.qty }}</td>
                                    <td class="px-6 py-4 text-slate-300">{{ item.satuan }}</td>
                                    <td class="px-6 py-4 text-slate-300">{{ formatCurrency(item.unit_price) }}</td>
                                    <td class="px-6 py-4 font-semibold text-white">{{ formatCurrency(item.amount) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
