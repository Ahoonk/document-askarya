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

    <AuthenticatedLayout>
        <div class="bg-[#fff2d9]">
            <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="text-xs uppercase tracking-[0.3em] text-blue-700">Detail Faktur Pajak</p>
                        <h1 class="mt-2 text-3xl font-bold text-slate-950">{{ fakturPajak.dokumen_name }}</h1>
                        <p class="mt-2 text-sm text-slate-500">{{ fakturPajak.invoice?.nomor }}</p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <Link :href="route('faktur-pajak.index')" class="rounded-full border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700">
                            Kembali
                        </Link>
                        <a v-if="fakturPajak.preview_url" :href="fakturPajak.preview_url" target="_blank" rel="noreferrer" class="rounded-full border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700">
                            Preview
                        </a>
                        <Link :href="route('faktur-pajak.edit', fakturPajak.id)" class="rounded-full border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700">
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
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Invoice</p>
                                <p class="mt-2 text-lg font-semibold text-slate-950">{{ fakturPajak.invoice?.nomor || '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Status</p>
                                <span class="mt-2 inline-flex rounded-full px-3 py-1 text-xs font-semibold capitalize" :class="paymentStyles[fakturPajak.payment_status] || 'bg-slate-100 text-slate-700'">
                                    {{ fakturPajak.payment_status }}
                                </span>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Tanggal Upload</p>
                                <p class="mt-2 text-lg font-semibold text-slate-950">{{ formatDate(fakturPajak.uploaded_at) }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Tanggal Bayar</p>
                                <p class="mt-2 text-lg font-semibold text-slate-950">{{ fakturPajak.payment_date ? formatDate(fakturPajak.payment_date) : '-' }}</p>
                            </div>
                        </div>

                        <div class="mt-6 grid gap-4 md:grid-cols-2">
                            <div>
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Customer</p>
                                <p class="mt-2 text-sm font-semibold text-slate-950">{{ fakturPajak.invoice?.customer_name || '-' }}</p>
                                <p class="mt-1 whitespace-pre-line text-sm text-slate-700">{{ fakturPajak.invoice?.customer_address || '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Total Invoice</p>
                                <p class="mt-2 text-2xl font-semibold text-slate-950">{{ formatCurrency(fakturPajak.invoice?.total || 0) }}</p>
                            </div>
                        </div>
                    </article>

                    <article class="space-y-4 rounded-[1.5rem] border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="rounded-2xl bg-[#fff2d9] p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Dokumen</p>
                            <a v-if="fakturPajak.preview_url" :href="fakturPajak.preview_url" target="_blank" rel="noreferrer" class="mt-2 inline-flex text-sm font-semibold text-blue-700">
                                Preview dokumen faktur
                            </a>
                            <p v-else class="mt-2 text-sm text-slate-500">Dokumen belum tersedia.</p>
                        </div>

                        <div class="rounded-2xl bg-[#fff2d9] p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">File Name</p>
                            <p class="mt-2 break-all text-sm text-slate-700">{{ fakturPajak.dokumen_name }}</p>
                        </div>

                        <div class="rounded-2xl bg-[#fff2d9] p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Catatan</p>
                            <p class="mt-2 text-sm leading-6 text-slate-700">
                                Faktur pajak ini diikat ke satu invoice, jadi arsipnya tetap konsisten walau status pembayaran berubah.
                            </p>
                        </div>
                    </article>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
