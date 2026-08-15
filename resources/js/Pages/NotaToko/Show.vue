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

    <AuthenticatedLayout>
        <div class="bg-[#fff2d9]">
            <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="text-xs uppercase tracking-[0.3em] text-blue-700">Detail Nota Toko</p>
                        <h1 class="mt-2 text-3xl font-bold text-slate-950">{{ notaToko.nomor }}</h1>
                        <p class="mt-2 text-sm text-slate-500">{{ notaToko.customer_nama }}</p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <button v-if="notaToko.preview_url" type="button" @click="openPdf(notaToko.preview_url)" class="rounded-full border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700">
                            Preview PDF
                        </button>
                        <button v-if="notaToko.preview_url" type="button" @click="openPdf(notaToko.preview_url, true)" class="rounded-full bg-blue-600 px-4 py-2 text-sm font-semibold text-white">
                            Unduh PDF
                        </button>
                        <Link :href="route('nota-toko.index')" class="rounded-full border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700">
                            Kembali
                        </Link>
                        <Link :href="route('nota-toko.edit', notaToko.id)" class="rounded-full border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700">
                            Edit
                        </Link>
                        <button @click="destroy" type="button" class="rounded-full bg-rose-600 px-4 py-2 text-sm font-semibold text-white">
                            Hapus
                        </button>
                    </div>
                </div>

                <section class="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
                    <article class="rounded-[1.5rem] border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Tanggal</p>
                                <p class="mt-2 text-lg font-semibold text-slate-950">{{ formatDate(notaToko.tanggal) }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Status Pembayaran</p>
                                <p class="mt-2 text-lg font-semibold capitalize text-slate-950">
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold" :class="paymentStyles[notaToko.payment_status] || 'bg-slate-100 text-slate-700'">
                                        {{ notaToko.payment_status }}
                                    </span>
                                </p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Email Customer</p>
                                <p class="mt-2 text-lg font-semibold text-slate-950">{{ notaToko.customer_email || '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Tanggal Bayar</p>
                                <p class="mt-2 text-lg font-semibold text-slate-950">{{ notaToko.payment_date ? formatDate(notaToko.payment_date) : '-' }}</p>
                            </div>
                        </div>

                        <div class="mt-6">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Alamat</p>
                            <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-700">{{ notaToko.alamat || '-' }}</p>
                        </div>

                        <div class="mt-6">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Keterangan</p>
                            <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-700">{{ notaToko.keterangan || '-' }}</p>
                        </div>
                    </article>

                    <article class="space-y-4 rounded-[1.5rem] border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="rounded-2xl bg-[#fff2d9] p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Subtotal</p>
                            <p class="mt-2 text-xl font-semibold text-slate-950">{{ formatCurrency(notaToko.subtotal) }}</p>
                        </div>
                        <div class="rounded-2xl bg-[#fff2d9] p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Tax</p>
                            <p class="mt-2 text-xl font-semibold text-slate-950">{{ formatCurrency(notaToko.tax_amount) }}</p>
                        </div>
                        <div class="rounded-2xl bg-blue-50 p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-blue-700">Total</p>
                            <p class="mt-2 text-xl font-semibold text-blue-950">{{ formatCurrency(notaToko.total) }}</p>
                        </div>
                        <div class="rounded-2xl bg-[#fff2d9] p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Snapshot</p>
                            <p class="mt-2 text-sm text-slate-700">Snapshot tersimpan supaya nota tetap punya salinan data saat sumber berubah.</p>
                        </div>
                    </article>
                </section>

                <section class="mt-6 overflow-hidden rounded-[1.5rem] border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 px-6 py-4">
                        <h2 class="text-lg font-semibold text-slate-950">Item Nota Toko</h2>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-[#fff2d9]">
                                <tr class="text-left text-xs uppercase tracking-[0.2em] text-slate-500">
                                    <th class="px-6 py-4">Nama</th>
                                    <th class="px-6 py-4">Qty</th>
                                    <th class="px-6 py-4">Satuan</th>
                                    <th class="px-6 py-4">Harga</th>
                                    <th class="px-6 py-4">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="item in notaToko.items" :key="item.id">
                                    <td class="px-6 py-4 font-medium text-slate-950">{{ item.nama }}</td>
                                    <td class="px-6 py-4 text-slate-600">{{ item.qty }}</td>
                                    <td class="px-6 py-4 text-slate-600">{{ item.satuan }}</td>
                                    <td class="px-6 py-4 text-slate-600">{{ formatCurrency(item.unit_price) }}</td>
                                    <td class="px-6 py-4 font-semibold text-slate-950">{{ formatCurrency(item.amount) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
