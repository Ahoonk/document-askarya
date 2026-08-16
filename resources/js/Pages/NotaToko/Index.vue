<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { formatCurrency, formatDate } from '@/utils/format';

const props = defineProps({
    notaTokos: {
        type: Array,
        default: () => [],
    },
    stats: {
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

function destroy(notaToko) {
    if (!confirm(`Hapus nota toko ${notaToko.nomor}?`)) {
        return;
    }

    router.delete(route('nota-toko.destroy', notaToko.id), {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Nota Toko" />

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
                            <p class="text-xs uppercase tracking-[0.3em] text-blue-200/70">Retail</p>
                            <h1 class="mt-3 text-3xl font-bold sm:text-4xl">Nota Toko</h1>
                            <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-300">
                                Transaksi penjualan retail yang berdiri sendiri dari alur invoice utama.
                            </p>
                        </div>

                        <Link :href="route('nota-toko.create')" class="rounded-full bg-gradient-to-r from-blue-700 via-indigo-600 to-red-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-700/20 transition hover:brightness-110">
                            Tambah Nota Toko
                        </Link>
                    </div>

                    <div class="mt-6 grid gap-4 md:grid-cols-3">
                        <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Total</p>
                            <p class="mt-2 text-2xl font-semibold">{{ stats.total ?? 0 }}</p>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Belum dibayar</p>
                            <p class="mt-2 text-2xl font-semibold">{{ stats.unpaid ?? 0 }}</p>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Sudah dibayar</p>
                            <p class="mt-2 text-2xl font-semibold">{{ stats.paid ?? 0 }}</p>
                        </div>
                    </div>
                </section>

                <section class="mt-8 overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 bg-gradient-to-r from-slate-50 to-blue-50 px-6 py-4">
                        <h2 class="text-lg font-semibold text-slate-950">Daftar Nota Toko</h2>
                    </div>

                    <div v-if="notaTokos.length" class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr class="text-left text-xs uppercase tracking-[0.2em] text-slate-500">
                                    <th class="px-6 py-4">Nomor</th>
                                    <th class="px-6 py-4">Customer</th>
                                    <th class="px-6 py-4">Tanggal</th>
                                    <th class="px-6 py-4">Status Bayar</th>
                                    <th class="px-6 py-4">Total</th>
                                    <th class="px-6 py-4">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="notaToko in notaTokos" :key="notaToko.id" class="hover:bg-blue-50/70">
                                    <td class="px-6 py-4">
                                        <div class="font-semibold text-slate-950">{{ notaToko.nomor }}</div>
                                        <div class="text-sm text-slate-500">ID #{{ notaToko.id }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-slate-950">{{ notaToko.customer_nama }}</div>
                                        <div class="text-sm text-slate-500">{{ notaToko.customer_email || '-' }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-600">{{ formatDate(notaToko.tanggal) }}</td>
                                    <td class="px-6 py-4">
                                        <span class="rounded-full px-3 py-1 text-xs font-semibold capitalize" :class="paymentStyles[notaToko.payment_status] || 'bg-slate-100 text-slate-700'">
                                            {{ notaToko.payment_status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 font-semibold text-slate-950">{{ formatCurrency(notaToko.total) }}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-wrap gap-2">
                                            <button v-if="notaToko.preview_url" type="button" @click="openPdf(notaToko.preview_url)" class="rounded-full border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm hover:border-blue-200 hover:bg-blue-50 hover:text-blue-900">
                                                Preview
                                            </button>
                                            <Link :href="route('nota-toko.show', notaToko.id)" class="rounded-full border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700">
                                                Detail
                                            </Link>
                                            <Link :href="route('nota-toko.edit', notaToko.id)" class="rounded-full border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700">
                                                Edit
                                            </Link>
                                            <button type="button" @click="destroy(notaToko)" class="rounded-full bg-rose-600 px-3 py-2 text-xs font-semibold text-white">
                                                Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-else class="px-6 py-16 text-center">
                        <p class="text-lg font-semibold text-slate-900">Belum ada nota toko</p>
                        <p class="mt-2 text-sm text-slate-500">Mulai dari customer retail pertama untuk alur penjualan terpisah.</p>
                        <Link :href="route('nota-toko.create')" class="mt-4 inline-flex rounded-full bg-gradient-to-r from-blue-700 via-indigo-600 to-red-600 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-blue-700/20 transition hover:brightness-110">
                            Tambah Nota Toko
                        </Link>
                    </div>
                </section>
            </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
