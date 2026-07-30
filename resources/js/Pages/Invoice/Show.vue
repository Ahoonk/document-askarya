<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { formatCurrency, formatDate } from '@/utils/format';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    invoice: {
        type: Object,
        required: true,
    },
});

const paymentForm = useForm({
    payment_date: props.invoice.payment_date ?? new Date().toISOString().slice(0, 10),
});

function deleteInvoice() {
    if (!confirm(`Hapus invoice ${props.invoice.nomor}? Semua dokumen turunannya akan ikut dihapus.`)) {
        return;
    }

    router.delete(route('invoice.destroy', props.invoice.id), {
        preserveScroll: true,
    });
}

function verifyPayment() {
    paymentForm.post(route('invoice.verify-payment', props.invoice.id), {
        preserveScroll: true,
    });
}

function backToList() {
    router.visit(route('invoice.index'));
}
</script>

<template>
    <Head :title="`Invoice ${invoice.nomor}`" />

    <AuthenticatedLayout>
        <div class="bg-[#fff2d9]">
            <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="text-xs uppercase tracking-[0.3em] text-blue-700">Invoice Detail</p>
                        <h1 class="mt-2 text-3xl font-bold text-slate-950">{{ invoice.nomor }}</h1>
                        <p class="mt-2 text-sm text-slate-500">{{ invoice.penawaran?.to_company }}</p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <a :href="route('invoice.preview-pdf', { invoice: invoice.id })" target="_blank" rel="noreferrer" class="rounded-full border border-blue-600 px-4 py-2 text-sm font-semibold text-blue-700">
                            Preview PDF
                        </a>
                        <a :href="route('invoice.preview-pdf', { invoice: invoice.id, download: 1 })" target="_blank" rel="noreferrer" class="rounded-full bg-blue-600 px-4 py-2 text-sm font-semibold text-white">
                            Unduh PDF
                        </a>
                        <button @click="backToList" type="button" class="rounded-full border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700">
                            Kembali
                        </button>
                        <button @click="deleteInvoice" type="button" class="rounded-full border border-rose-600 bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-700">
                            Hapus Invoice
                        </button>
                        <Link :href="route('purchasing-order.index')" class="rounded-full bg-blue-600 px-4 py-2 text-sm font-semibold text-white">
                            PO
                        </Link>
                    </div>
                </div>

                <section class="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
                    <article class="rounded-[1.5rem] border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Tanggal Invoice</p>
                                <p class="mt-2 text-lg font-semibold text-slate-950">{{ formatDate(invoice.tanggal) }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Status Pembayaran</p>
                                <p class="mt-2 text-lg font-semibold capitalize text-slate-950">{{ invoice.payment_status }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Sequence</p>
                                <p class="mt-2 text-lg font-semibold text-slate-950">{{ invoice.sequence }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Total</p>
                                <p class="mt-2 text-lg font-semibold text-slate-950">{{ formatCurrency(invoice.total) }}</p>
                            </div>
                        </div>

                        <div class="mt-6 grid gap-4 md:grid-cols-2">
                            <div>
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Penawaran</p>
                                <p class="mt-2 text-sm font-semibold text-slate-950">{{ invoice.penawaran?.nomor || '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">PO</p>
                                <p class="mt-2 text-sm font-semibold text-slate-950">{{ invoice.purchasing_order?.nomor_po || '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Surat Jalan</p>
                                <p class="mt-2 text-sm font-semibold text-slate-950">{{ invoice.surat_jalan?.nomor || '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Berita Acara</p>
                                <p class="mt-2 text-sm font-semibold text-slate-950">{{ invoice.berita_acara?.nomor || '-' }}</p>
                            </div>
                        </div>
                    </article>

                    <article class="space-y-4 rounded-[1.5rem] border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="rounded-2xl bg-[#fff2d9] p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Tanggal Pembayaran</p>
                            <p class="mt-2 text-sm text-slate-700">{{ invoice.payment_date ? formatDate(invoice.payment_date) : '-' }}</p>
                        </div>

                        <div class="rounded-2xl bg-[#fff2d9] p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Faktur Pajak</p>
                            <div v-if="invoice.faktur_pajak" class="mt-2 space-y-2">
                                <p class="text-sm font-semibold text-slate-950">{{ invoice.faktur_pajak.dokumen_name }}</p>
                                <p class="text-sm text-slate-700 capitalize">{{ invoice.faktur_pajak.payment_status }}</p>
                                <p class="text-sm text-slate-700">{{ invoice.faktur_pajak.payment_date ? formatDate(invoice.faktur_pajak.payment_date) : '-' }}</p>
                                <Link :href="route('faktur-pajak.show', invoice.faktur_pajak.id)" class="inline-flex rounded-full bg-blue-600 px-4 py-2 text-xs font-semibold text-white">
                                    Buka Faktur Pajak
                                </Link>
                            </div>
                            <div v-else class="mt-2 space-y-3">
                                <p class="text-sm text-slate-700">Invoice ini belum memiliki faktur pajak.</p>
                                <Link :href="route('faktur-pajak.create', { invoice_id: invoice.id })" class="inline-flex rounded-full bg-blue-600 px-4 py-2 text-xs font-semibold text-white">
                                    Upload Faktur Pajak
                                </Link>
                            </div>
                        </div>

                        <div class="rounded-2xl bg-[#fff2d9] p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Snapshot</p>
                            <p class="mt-2 text-sm text-slate-700">Invoice, surat jalan, dan dokumen turunan sudah dipersiapkan lewat snapshot data.</p>
                        </div>

                        <div v-if="$page.props.auth.user?.role === 'superadmin'" class="rounded-2xl bg-emerald-50 p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-emerald-700">Verifikasi Pembayaran</p>
                            <div class="mt-3">
                                <InputLabel value="Tanggal Bayar" />
                                <TextInput v-model="paymentForm.payment_date" type="date" class="mt-2 block w-full" />
                                <p v-if="paymentForm.errors.payment_date" class="mt-2 text-sm text-rose-600">{{ paymentForm.errors.payment_date }}</p>
                            </div>
                            <PrimaryButton class="mt-4 bg-emerald-700 hover:bg-emerald-600" :disabled="paymentForm.processing" @click="verifyPayment">
                                Tandai Lunas
                            </PrimaryButton>
                        </div>
                    </article>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
