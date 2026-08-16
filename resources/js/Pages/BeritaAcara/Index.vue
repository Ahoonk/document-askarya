<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { formatCurrency, formatDate } from '@/utils/format';
import { computed } from 'vue';

const props = defineProps({
    beritaAcaras: {
        type: Array,
        default: () => [],
    },
    stats: {
        type: Object,
        default: () => ({}),
    },
});

const previewHrefs = computed(() => {
    return props.beritaAcaras.reduce((acc, beritaAcara) => {
        if (!beritaAcara.preview_url) {
            acc[beritaAcara.id] = '';
            return acc;
        }

        const separator = beritaAcara.preview_url.includes('?') ? '&' : '?';
        acc[beritaAcara.id] = `${beritaAcara.preview_url}${separator}cb=${Date.now()}`;

        return acc;
    }, {});
});

function destroy(beritaAcara) {
    if (!confirm(`Hapus berita acara ${beritaAcara.nomor}?`)) {
        return;
    }

    router.delete(route('berita-acara.destroy', beritaAcara.id), {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Berita Acara" />

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
                            <p class="text-xs uppercase tracking-[0.3em] text-blue-200/70">Closing</p>
                            <h1 class="mt-3 text-3xl font-bold sm:text-4xl">Berita Acara</h1>
                            <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-300">
                                Dokumen serah terima atau konfirmasi selesai pekerjaan yang mengikuti invoice.
                            </p>
                        </div>

                        <Link :href="route('berita-acara.create')" class="rounded-full bg-gradient-to-r from-blue-700 via-indigo-600 to-red-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-700/20 transition hover:brightness-110">
                            Tambah Berita Acara
                        </Link>
                    </div>

                    <div class="mt-6 grid gap-4 md:grid-cols-3">
                        <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Total</p>
                            <p class="mt-2 text-2xl font-semibold">{{ stats.total ?? 0 }}</p>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Bulan ini</p>
                            <p class="mt-2 text-2xl font-semibold">{{ stats.this_month ?? 0 }}</p>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Linked invoice</p>
                            <p class="mt-2 text-2xl font-semibold">{{ stats.linked_invoice ?? 0 }}</p>
                        </div>
                    </div>
                </section>

                <section class="mt-8 overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 bg-gradient-to-r from-slate-50 to-blue-50 px-6 py-4">
                        <h2 class="text-lg font-semibold text-slate-950">Daftar Berita Acara</h2>
                    </div>

                    <div v-if="beritaAcaras.length" class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr class="text-left text-xs uppercase tracking-[0.2em] text-slate-500">
                                    <th class="px-6 py-4">Nomor</th>
                                    <th class="px-6 py-4">Invoice</th>
                                    <th class="px-6 py-4">Tanggal</th>
                                    <th class="px-6 py-4">Customer</th>
                                    <th class="px-6 py-4">Total</th>
                                    <th class="px-6 py-4">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="beritaAcara in beritaAcaras" :key="beritaAcara.id" class="hover:bg-blue-50/70">
                                    <td class="px-6 py-4">
                                        <div class="font-semibold text-slate-950">{{ beritaAcara.nomor }}</div>
                                        <div class="text-sm text-slate-500">ID #{{ beritaAcara.id }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-slate-950">{{ beritaAcara.invoice?.nomor || '-' }}</div>
                                        <div class="text-sm text-slate-500">{{ beritaAcara.invoice?.po_number || '-' }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-600">{{ formatDate(beritaAcara.tanggal) }}</td>
                                    <td class="px-6 py-4 text-sm text-slate-600">{{ beritaAcara.invoice?.customer_name || '-' }}</td>
                                    <td class="px-6 py-4 font-semibold text-slate-950">{{ formatCurrency(beritaAcara.invoice?.total || 0) }}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-wrap gap-2">
                                            <a v-if="beritaAcara.preview_url" :href="previewHrefs[beritaAcara.id]" target="_blank" rel="noreferrer" class="rounded-full border border-blue-200 bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-800">
                                                Preview
                                            </a>
                                            <Link :href="route('berita-acara.show', beritaAcara.id)" class="rounded-full border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700">
                                                Detail
                                            </Link>
                                            <Link :href="route('berita-acara.edit', beritaAcara.id)" class="rounded-full border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700">
                                                Edit
                                            </Link>
                                            <button type="button" @click="destroy(beritaAcara)" class="rounded-full bg-rose-600 px-3 py-2 text-xs font-semibold text-white">
                                                Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-else class="px-6 py-16 text-center">
                        <p class="text-lg font-semibold text-slate-900">Belum ada berita acara</p>
                        <p class="mt-2 text-sm text-slate-500">Buat berita acara dari invoice yang sudah tersedia.</p>
                        <Link :href="route('berita-acara.create')" class="mt-4 inline-flex rounded-full bg-gradient-to-r from-blue-700 via-indigo-600 to-red-600 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-blue-700/20 transition hover:brightness-110">
                            Tambah Berita Acara
                        </Link>
                    </div>
                </section>
            </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
