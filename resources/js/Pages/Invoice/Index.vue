<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { formatCurrency, formatDate } from '@/utils/format';

defineProps({
    invoices: {
        type: Array,
        default: () => [],
    },
});

const paymentStyles = {
    unpaid: 'bg-amber-100 text-amber-800',
    paid: 'bg-emerald-100 text-emerald-800',
};
</script>

<template>
    <Head title="Invoice" />

    <AuthenticatedLayout>
        <div class="bg-[#fff2d9]">
            <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <section class="rounded-[2rem] bg-slate-950 p-6 text-white shadow-2xl shadow-slate-900/20">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <p class="text-xs uppercase tracking-[0.3em] text-blue-200/70">Billing</p>
                            <h1 class="mt-3 text-3xl font-bold sm:text-4xl">Invoice</h1>
                            <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-300">
                                Semua invoice dari penawaran yang sudah lolos PO tampil di sini, lengkap dengan status pembayaran dan dokumen turunannya.
                            </p>
                        </div>

                        <Link :href="route('purchasing-order.index')" class="rounded-full bg-blue-400 px-5 py-3 text-sm font-semibold text-slate-950">
                            Ke Purchasing Order
                        </Link>
                    </div>
                </section>

                <section class="mt-8 overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 px-6 py-4">
                        <h2 class="text-lg font-semibold text-slate-950">Daftar Invoice</h2>
                    </div>

                    <div v-if="invoices.length" class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-[#fff2d9]">
                                <tr class="text-left text-xs uppercase tracking-[0.2em] text-slate-500">
                                    <th class="px-6 py-4">Nomor</th>
                                    <th class="px-6 py-4">Penawaran</th>
                                    <th class="px-6 py-4">Tanggal</th>
                                    <th class="px-6 py-4">Status Bayar</th>
                                    <th class="px-6 py-4">Total</th>
                                    <th class="px-6 py-4">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="invoice in invoices" :key="invoice.id" class="hover:bg-[#fff2d9]/60">
                                    <td class="px-6 py-4">
                                        <div class="font-semibold text-slate-950">{{ invoice.nomor }}</div>
                                        <div class="text-sm text-slate-500">Seq {{ invoice.sequence }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-slate-950">{{ invoice.penawaran?.to_company }}</div>
                                        <div class="text-sm text-slate-500">{{ invoice.penawaran?.nomor }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-600">{{ formatDate(invoice.tanggal) }}</td>
                                    <td class="px-6 py-4">
                                        <span class="rounded-full px-3 py-1 text-xs font-semibold capitalize" :class="paymentStyles[invoice.payment_status] || 'bg-slate-100 text-slate-700'">
                                            {{ invoice.payment_status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 font-semibold text-slate-950">{{ formatCurrency(invoice.total) }}</td>
                                    <td class="px-6 py-4">
                                        <a :href="route('invoice.preview-pdf', { invoice: invoice.id })" target="_blank" rel="noreferrer" class="mr-2 rounded-full border border-blue-600 px-3 py-2 text-xs font-semibold text-blue-700">
                                            Preview
                                        </a>
                                        <Link :href="route('invoice.show', invoice.id)" class="rounded-full border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700">
                                            Detail
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-else class="px-6 py-16 text-center">
                        <p class="text-lg font-semibold text-slate-900">Belum ada invoice</p>
                        <p class="mt-2 text-sm text-slate-500">Invoice akan muncul setelah Purchasing Order diproses.</p>
                    </div>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
