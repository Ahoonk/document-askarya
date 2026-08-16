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

const printDateForm = useForm({
    tanggal: props.invoice.tanggal ?? new Date().toISOString().slice(0, 10),
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

function updatePrintDate() {
    printDateForm.post(route('invoice.update-print-date', props.invoice.id), {
        preserveScroll: true,
    });
}

function backToList() {
    router.visit(route('invoice.index'));
}
</script>

<template>
    <Head :title="`Invoice ${invoice.nomor}`" />

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
                        <p class="text-xs uppercase tracking-[0.3em] text-blue-200/70">Invoice Detail</p>
                        <h1 class="mt-2 text-3xl font-bold text-white">{{ invoice.nomor }}</h1>
                        <p class="mt-2 text-sm text-slate-300">{{ invoice.penawaran?.to_company }}</p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <a :href="route('invoice.preview-pdf', { invoice: invoice.id })" target="_blank" rel="noreferrer" class="rounded-full border border-blue-200 bg-white/90 px-4 py-2 text-sm font-semibold text-blue-800 shadow-sm hover:border-blue-300 hover:bg-blue-50">
                            Preview PDF
                        </a>
                        <a :href="route('invoice.preview-pdf', { invoice: invoice.id, download: 1 })" target="_blank" rel="noreferrer" class="rounded-full bg-gradient-to-r from-blue-700 via-indigo-600 to-red-600 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-blue-700/20 hover:brightness-110">
                            Unduh PDF
                        </a>
                        <button @click="backToList" type="button" class="rounded-full border border-white/10 bg-white/5 px-4 py-2 text-sm font-semibold text-slate-200 hover:border-blue-300/20 hover:bg-white/8">
                            Kembali
                        </button>
                        <button @click="deleteInvoice" type="button" class="rounded-full bg-rose-600 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-rose-600/20">
                            Hapus Invoice
                        </button>
                        <Link :href="route('purchasing-order.index')" class="rounded-full bg-gradient-to-r from-slate-900 via-blue-700 to-red-600 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-blue-700/20 hover:brightness-110">
                            PO
                        </Link>
                    </div>
                </div>

                <section class="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
                    <article class="rounded-[1.5rem] border border-white/10 bg-white p-6 shadow-[0_12px_30px_rgba(0,0,0,0.12)]">
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

                    <article class="space-y-4 rounded-[1.5rem] border border-white/10 bg-white p-6 shadow-[0_12px_30px_rgba(0,0,0,0.12)]">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Edit Tanggal</p>
                            <div class="mt-3">
                                <InputLabel value="Tanggal Invoice" />
                                <TextInput v-model="printDateForm.tanggal" type="date" class="mt-2 block w-full" />
                                <p v-if="printDateForm.errors.tanggal" class="mt-2 text-sm text-rose-600">{{ printDateForm.errors.tanggal }}</p>
                            </div>
                            <PrimaryButton class="mt-4" :disabled="printDateForm.processing" @click="updatePrintDate">
                                Simpan Perubahan
                            </PrimaryButton>
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Tanggal Pembayaran</p>
                            <p class="mt-2 text-sm text-slate-700">{{ invoice.payment_date ? formatDate(invoice.payment_date) : '-' }}</p>
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Faktur Pajak</p>
                            <div v-if="invoice.faktur_pajak" class="mt-2 space-y-2">
                                <p class="text-sm font-semibold text-slate-950">{{ invoice.faktur_pajak.dokumen_name }}</p>
                                <p class="text-sm text-slate-700 capitalize">{{ invoice.faktur_pajak.payment_status }}</p>
                                <p class="text-sm text-slate-700">{{ invoice.faktur_pajak.payment_date ? formatDate(invoice.faktur_pajak.payment_date) : '-' }}</p>
                                <Link :href="route('faktur-pajak.show', invoice.faktur_pajak.id)" class="inline-flex rounded-full bg-gradient-to-r from-blue-700 via-indigo-600 to-red-600 px-4 py-2 text-xs font-semibold text-white shadow-lg shadow-blue-700/20 hover:brightness-110">
                                    Buka Faktur Pajak
                                </Link>
                            </div>
                            <div v-else class="mt-2 space-y-3">
                                <p class="text-sm text-slate-700">Invoice ini belum memiliki faktur pajak.</p>
                                <Link :href="route('faktur-pajak.create', { invoice_id: invoice.id })" class="inline-flex rounded-full bg-gradient-to-r from-blue-700 via-indigo-600 to-red-600 px-4 py-2 text-xs font-semibold text-white shadow-lg shadow-blue-700/20 hover:brightness-110">
                                    Upload Faktur Pajak
                                </Link>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Snapshot</p>
                            <p class="mt-2 text-sm text-slate-700">Invoice, surat jalan, dan dokumen turunan sudah dipersiapkan lewat snapshot data.</p>
                        </div>

                        <div v-if="$page.props.auth.user?.role === 'superadmin'" class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-emerald-700">Verifikasi Pembayaran</p>
                            <div class="mt-3">
                                <InputLabel value="Tanggal Bayar" />
                                <TextInput v-model="paymentForm.payment_date" type="date" class="mt-2 block w-full" />
                                <p v-if="paymentForm.errors.payment_date" class="mt-2 text-sm text-rose-600">{{ paymentForm.errors.payment_date }}</p>
                            </div>
                            <PrimaryButton class="mt-4 bg-gradient-to-r from-blue-700 via-indigo-600 to-red-600 hover:brightness-110" :disabled="paymentForm.processing" @click="verifyPayment">
                                Tandai Lunas
                            </PrimaryButton>
                        </div>
                    </article>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
