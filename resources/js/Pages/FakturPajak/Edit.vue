<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import FakturPajakForm from './Partials/Form.vue';

defineProps({
    fakturPajak: {
        type: Object,
        required: true,
    },
    availableInvoices: {
        type: Array,
        default: () => [],
    },
    meta: {
        type: Object,
        default: () => ({}),
    },
});
</script>

<template>
    <Head :title="`Edit Faktur Pajak - ${fakturPajak.dokumen_name}`" />

    <AuthenticatedLayout theme="login">
        <div class="relative min-h-screen overflow-hidden bg-[#08111f] text-slate-100">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(59,130,246,0.14),transparent_30%),radial-gradient(circle_at_top_right,rgba(239,68,68,0.12),transparent_28%),linear-gradient(180deg,rgba(8,17,31,0.98),rgba(8,17,31,1))]"></div>
            <div class="absolute left-[-6rem] top-24 h-72 w-72 rounded-full bg-blue-500/20 blur-3xl"></div>
            <div class="absolute right-[-5rem] top-40 h-80 w-80 rounded-full bg-rose-500/15 blur-3xl"></div>

            <div class="relative mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <div class="mb-6">
                    <p class="text-xs uppercase tracking-[0.3em] text-sky-300">Tax</p>
                    <h1 class="mt-2 text-3xl font-bold text-white">Edit Faktur Pajak</h1>
                    <p class="mt-2 text-sm text-slate-300">Perbarui status pembayaran atau upload file baru jika dibutuhkan.</p>
                </div>

                <FakturPajakForm
                    :action="route('faktur-pajak.update', fakturPajak.id)"
                    method="put"
                    :fakturPajak="fakturPajak"
                    :availableInvoices="availableInvoices"
                    :selectedInvoiceId="fakturPajak.invoice_id"
                    :meta="meta"
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
