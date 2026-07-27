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

    <AuthenticatedLayout>
        <div class="min-h-screen bg-[#fff2d9]">
            <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <section class="rounded-[2rem] bg-slate-950 p-6 text-white shadow-2xl shadow-slate-900/20">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <p class="text-xs uppercase tracking-[0.3em] text-blue-200/70">Closing</p>
                            <h1 class="mt-3 text-3xl font-bold sm:text-4xl">Berita Acara</h1>
                            <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-300">
                                Dokumen serah terima atau konfirmasi selesai pekerjaan yang mengikuti invoice.
                            </p>
                        </div>

                        <Link :href="route('berita-acara.create')" class="rounded-full bg-blue-400 px-5 py-3 text-sm font-semibold text-slate-950">
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
                    <div class="border-b border-slate-200 px-6 py-4">
                        <h2 class="text-lg font-semibold text-slate-950">Daftar Berita Acara</h2>
                    </div>

                    <div v-if="beritaAcaras.length" class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-[#fff2d9]">
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
                                <tr v-for="beritaAcara in beritaAcaras" :key="beritaAcara.id" class="hover:bg-[#fff2d9]/60">
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
                                            <a v-if="beritaAcara.preview_url" :href="previewHrefs[beritaAcara.id]" target="_blank" rel="noreferrer" class="rounded-full border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700">
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
                        <Link :href="route('berita-acara.create')" class="mt-4 inline-flex rounded-full bg-blue-600 px-4 py-2 text-sm font-semibold text-white">
                            Tambah Berita Acara
                        </Link>
                    </div>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
