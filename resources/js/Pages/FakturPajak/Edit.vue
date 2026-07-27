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

    <AuthenticatedLayout>
        <div class="bg-[#fff2d9]">
            <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <div class="mb-6">
                    <p class="text-xs uppercase tracking-[0.3em] text-blue-700">Tax</p>
                    <h1 class="mt-2 text-3xl font-bold text-slate-950">Edit Faktur Pajak</h1>
                    <p class="mt-2 text-sm text-slate-500">Perbarui status pembayaran atau upload file baru jika dibutuhkan.</p>
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
