<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { formatCurrency, formatDate } from '@/utils/format';

const props = defineProps({
    penawarans: {
        type: Array,
        default: () => [],
    },
    meta: {
        type: Object,
        default: () => ({}),
    },
});

const statusStyles = {
    draft: 'bg-slate-100 text-slate-700',
    submitted: 'bg-amber-100 text-amber-800',
    approved: 'bg-emerald-100 text-emerald-800',
    rejected: 'bg-rose-100 text-rose-800',
};

const actionBase = 'inline-flex flex-none items-center justify-center rounded-full px-3 py-2 text-xs font-semibold leading-none transition duration-200 hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-offset-2 whitespace-nowrap';
const actionSecondary = `${actionBase} border border-slate-200 bg-white text-slate-700 shadow-sm hover:border-blue-200 hover:bg-blue-50 hover:text-blue-900 focus:ring-blue-400`;
const actionSuccess = `${actionBase} bg-gradient-to-r from-blue-700 via-indigo-600 to-red-600 text-white shadow-lg shadow-blue-700/20 hover:brightness-110 focus:ring-blue-500`;
const actionDanger = `${actionBase} bg-rose-600 text-white shadow-lg shadow-rose-600/20 hover:bg-rose-500 focus:ring-rose-500`;
const actionInfo = `${actionBase} bg-slate-900 text-white shadow-lg shadow-slate-900/20 hover:bg-slate-800 focus:ring-slate-900`;

function approve(penawaran) {
    router.post(route('penawaran.approve', penawaran.id), {}, {
        preserveScroll: true,
    });
}

function destroy(penawaran) {
    if (!confirm(`Hapus penawaran ${penawaran.nomor}?`)) {
        return;
    }

    router.delete(route('penawaran.destroy', penawaran.id), {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Penawaran" />

    <AuthenticatedLayout theme="login">
        <div class="relative min-h-screen overflow-hidden bg-[#08111f] text-slate-100">
            <div class="pointer-events-none absolute inset-0">
                <div class="absolute left-[-8rem] top-[-8rem] h-96 w-96 rounded-full bg-red-500/20 blur-3xl"></div>
                <div class="absolute right-[-7rem] top-24 h-[30rem] w-[30rem] rounded-full bg-blue-500/20 blur-3xl"></div>
                <div class="absolute bottom-[-8rem] left-1/3 h-80 w-80 rounded-full bg-amber-400/10 blur-3xl"></div>
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(255,255,255,0.08),_transparent_35%),linear-gradient(135deg,_rgba(8,17,31,0.94),_rgba(9,14,27,0.98))]"></div>
            </div>

            <div class="relative">
            <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <section class="rounded-[2rem] border border-white/10 bg-slate-950/90 p-6 text-white shadow-2xl shadow-black/20 backdrop-blur-xl">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <p class="text-xs uppercase tracking-[0.3em] text-blue-200/70">Core Workflow</p>
                            <h1 class="mt-3 text-3xl font-bold sm:text-4xl">Surat Penawaran</h1>
                            <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-300">
                                Tempat mulai transaksi. Dari sini alur akan bergerak ke Purchasing Order, lalu Invoice dan dokumen turunan.
                            </p>
                        </div>

                        <Link :href="route('penawaran.create')" class="rounded-full bg-gradient-to-r from-blue-700 via-indigo-600 to-red-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-700/20 transition hover:brightness-110">
                            Tambah Penawaran
                        </Link>
                    </div>

                    <div class="mt-6 grid gap-4 md:grid-cols-3">
                        <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Draft</p>
                            <p class="mt-2 text-2xl font-semibold">{{ penawarans.filter((item) => item.status === 'draft').length }}</p>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Submitted</p>
                            <p class="mt-2 text-2xl font-semibold">{{ penawarans.filter((item) => item.status === 'submitted').length }}</p>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Approved</p>
                            <p class="mt-2 text-2xl font-semibold">{{ penawarans.filter((item) => item.status === 'approved').length }}</p>
                        </div>
                    </div>
                </section>

                <section class="mt-8 overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 bg-gradient-to-r from-slate-50 to-blue-50 px-6 py-4">
                        <h2 class="text-lg font-semibold text-slate-950">Daftar Penawaran</h2>
                    </div>

                    <div v-if="penawarans.length" class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr class="text-left text-xs uppercase tracking-[0.2em] text-slate-500">
                                    <th class="px-6 py-4">Nomor</th>
                                    <th class="px-6 py-4">Customer</th>
                                    <th class="px-6 py-4">Tanggal</th>
                                    <th class="px-6 py-4">Status</th>
                                    <th class="px-6 py-4">Total</th>
                                    <th class="px-6 py-4">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="penawaran in penawarans" :key="penawaran.id" class="hover:bg-blue-50/70">
                                    <td class="px-6 py-4">
                                        <div class="font-semibold text-slate-950">{{ penawaran.nomor }}</div>
                                        <div class="text-sm text-slate-500">ID #{{ penawaran.id }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-slate-950">{{ penawaran.to_company }}</div>
                                        <div class="text-sm text-slate-500">{{ penawaran.to_address || '-' }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-600">{{ formatDate(penawaran.tanggal) }}</td>
                                    <td class="px-6 py-4">
                                        <span class="rounded-full px-3 py-1 text-xs font-semibold capitalize" :class="statusStyles[penawaran.status] || 'bg-slate-100 text-slate-700'">
                                            {{ penawaran.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 font-semibold text-slate-950">{{ formatCurrency(penawaran.total) }}</td>
                                    <td class="px-6 py-4 align-top whitespace-nowrap">
                                        <div class="flex min-w-max flex-nowrap gap-1.5">
                                            <Link :href="route('penawaran.show', penawaran.id)" :class="actionSecondary">
                                                Detail
                                            </Link>
                                            <Link :href="route('penawaran.edit', penawaran.id)" :class="actionSecondary">
                                                Edit
                                            </Link>
                                            <button v-if="penawaran.status !== 'approved'" type="button" @click="approve(penawaran)" :class="actionSuccess">
                                                Approve
                                            </button>
                                            <a :href="route('penawaran.preview-pdf', { penawaran: penawaran.id, download: 1 })" target="_blank" rel="noreferrer" :class="actionInfo">
                                                PDF
                                            </a>
                                            <button type="button" @click="destroy(penawaran)" :class="actionDanger">
                                                Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-else class="px-6 py-16 text-center">
                        <p class="text-lg font-semibold text-slate-900">Belum ada penawaran</p>
                        <p class="mt-2 text-sm text-slate-500">Klik tombol tambah untuk membuat penawaran pertama di repo baru ini.</p>
                    </div>
                </section>
            </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
