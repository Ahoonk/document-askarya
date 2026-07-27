<script setup>
import { computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { formatCurrency, formatDate } from '@/utils/format';

const props = defineProps({
    beritaAcara: {
        type: Object,
        required: true,
    },
    snapshot: {
        type: Object,
        default: () => ({}),
    },
});

const snapshotData = computed(() => props.snapshot || {});

const snapshotSummary = computed(() => [
    { label: 'Invoice', value: snapshotData.value.invoice_number || props.beritaAcara.invoice?.nomor || '-' },
    { label: 'Tanggal Invoice', value: snapshotData.value.invoice_date ? formatDate(snapshotData.value.invoice_date) : '-' },
    { label: 'Nomor Perjanjian', value: snapshotData.value.nomor_perjanjian || '-' },
    { label: 'Tanggal Teks', value: snapshotData.value.tanggal_teks_manual || '-' },
    { label: 'Perihal', value: snapshotData.value.subject || props.beritaAcara.perihal || '-' },
    { label: 'Pekerjaan', value: snapshotData.value.pekerjaan_manual || '-' },
    { label: 'Periode', value: snapshotData.value.periode_manual || '-' },
    { label: 'Predikat', value: snapshotData.value.predikat_manual || '-' },
]);

const snapshotParties = computed(() => [
    { label: 'Pihak Pertama', name: snapshotData.value.pihak_pertama_nama || props.beritaAcara.invoice?.customer_name || '-', address: snapshotData.value.pihak_pertama_alamat || props.beritaAcara.invoice?.customer_address || '-' },
    { label: 'Pihak Kedua', name: snapshotData.value.pihak_kedua_nama || '-', address: snapshotData.value.pihak_kedua_alamat || '-' },
]);

const snapshotItems = computed(() => snapshotData.value.items || []);
const previewHref = computed(() => {
    if (!props.beritaAcara.preview_url) {
        return '';
    }

    const separator = props.beritaAcara.preview_url.includes('?') ? '&' : '?';

    return `${props.beritaAcara.preview_url}${separator}cb=${Date.now()}`;
});

function destroy() {
    if (!confirm(`Hapus berita acara ${props.beritaAcara.nomor}?`)) {
        return;
    }

    router.delete(route('berita-acara.destroy', props.beritaAcara.id));
}
</script>

<template>
    <Head :title="beritaAcara.nomor" />

    <AuthenticatedLayout>
        <div class="bg-[#fff2d9]">
            <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="text-xs uppercase tracking-[0.3em] text-blue-700">Detail Berita Acara</p>
                        <h1 class="mt-2 text-3xl font-bold text-slate-950">{{ beritaAcara.nomor }}</h1>
                        <p class="mt-2 text-sm text-slate-500">{{ beritaAcara.invoice?.customer_name || '-' }}</p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <a v-if="beritaAcara.preview_url" :href="previewHref" target="_blank" rel="noreferrer" class="rounded-full border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700">
                            Preview
                        </a>
                        <Link :href="route('berita-acara.index')" class="rounded-full border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700">
                            Kembali
                        </Link>
                        <Link :href="route('berita-acara.edit', beritaAcara.id)" class="rounded-full border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700">
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
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Tanggal</p>
                                <p class="mt-2 text-lg font-semibold text-slate-950">{{ formatDate(beritaAcara.tanggal) }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Kota/Tanggal Manual</p>
                                <p class="mt-2 text-lg font-semibold text-slate-950">{{ beritaAcara.kota_tanggal_manual ? formatDate(beritaAcara.kota_tanggal_manual) : '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Invoice</p>
                                <p class="mt-2 text-lg font-semibold text-slate-950">{{ beritaAcara.invoice?.nomor || '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Total Invoice</p>
                                <p class="mt-2 text-lg font-semibold text-slate-950">{{ formatCurrency(beritaAcara.invoice?.total || 0) }}</p>
                            </div>
                        </div>

                        <div class="mt-6">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Perihal</p>
                            <p class="mt-2 text-sm leading-6 text-slate-700">{{ beritaAcara.perihal || '-' }}</p>
                        </div>

                        <div class="mt-6">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Keterangan Akhir</p>
                            <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-700">{{ beritaAcara.keterangan_akhir || '-' }}</p>
                        </div>
                    </article>

                    <article class="space-y-4 rounded-[1.5rem] border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="rounded-2xl bg-[#fff2d9] p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Isi Manual</p>
                            <div class="mt-3 space-y-2 text-sm text-slate-700">
                                <p><span class="font-semibold text-slate-950">Nomor Perjanjian:</span> {{ beritaAcara.nomor_perjanjian || '-' }}</p>
                                <p><span class="font-semibold text-slate-950">Tanggal Teks:</span> {{ beritaAcara.tanggal_teks_manual || '-' }}</p>
                                <p><span class="font-semibold text-slate-950">Pihak Pertama:</span> {{ beritaAcara.pihak_pertama_nama || '-' }}</p>
                                <p><span class="font-semibold text-slate-950">Pihak Kedua:</span> {{ beritaAcara.pihak_kedua_nama || '-' }}</p>
                                <p><span class="font-semibold text-slate-950">Pekerjaan:</span> {{ beritaAcara.pekerjaan_manual || '-' }}</p>
                                <p><span class="font-semibold text-slate-950">Periode:</span> {{ beritaAcara.periode_manual || '-' }}</p>
                                <p><span class="font-semibold text-slate-950">Predikat:</span> {{ beritaAcara.predikat_manual || '-' }}</p>
                            </div>
                        </div>

                        <div class="rounded-2xl bg-[#fff2d9] p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Customer</p>
                            <p class="mt-2 text-sm font-semibold text-slate-950">{{ beritaAcara.invoice?.customer_name || '-' }}</p>
                            <p class="mt-1 text-sm text-slate-700">{{ beritaAcara.invoice?.customer_address || '-' }}</p>
                        </div>

                        <div class="rounded-2xl bg-[#fff2d9] p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">PO</p>
                            <p class="mt-2 text-sm text-slate-700">{{ beritaAcara.invoice?.po_number || '-' }}</p>
                        </div>

                        <div class="rounded-2xl bg-[#fff2d9] p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Snapshot</p>
                            <p class="mt-2 text-sm text-slate-700">Snapshot disimpan agar isi berita acara tetap dapat diaudit meski invoice berubah.</p>
                        </div>
                    </article>
                </section>

                <section class="mt-6 overflow-hidden rounded-[1.5rem] border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 px-6 py-4">
                        <h2 class="text-lg font-semibold text-slate-950">Snapshot</h2>
                    </div>
                    <div class="grid gap-6 px-6 py-6 lg:grid-cols-2">
                        <div class="space-y-4">
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Ringkasan</p>
                                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                    <div v-for="item in snapshotSummary" :key="item.label" class="rounded-xl border border-slate-200 bg-white p-3">
                                        <p class="text-[11px] uppercase tracking-[0.2em] text-slate-500">{{ item.label }}</p>
                                        <p class="mt-2 text-sm font-semibold text-slate-950 break-words">{{ item.value }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Pihak</p>
                                <div class="mt-4 space-y-3">
                                    <div v-for="party in snapshotParties" :key="party.label" class="rounded-xl border border-slate-200 bg-white p-3">
                                        <p class="text-[11px] uppercase tracking-[0.2em] text-slate-500">{{ party.label }}</p>
                                        <p class="mt-2 text-sm font-semibold text-slate-950 break-words">{{ party.name }}</p>
                                        <p class="mt-1 text-sm leading-6 text-slate-600 whitespace-pre-line break-words">{{ party.address }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Items</p>
                                <div class="mt-4 overflow-hidden rounded-xl border border-slate-200 bg-white">
                                    <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                                        <thead class="bg-slate-100 text-[11px] uppercase tracking-[0.2em] text-slate-500">
                                            <tr>
                                                <th class="px-3 py-3">Nama</th>
                                                <th class="px-3 py-3">Rincian</th>
                                                <th class="px-3 py-3 text-right">Qty</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100">
                                            <tr v-if="!snapshotItems.length">
                                                <td colspan="3" class="px-3 py-4 text-sm text-slate-500">Tidak ada item di snapshot.</td>
                                            </tr>
                                            <tr v-for="item in snapshotItems" :key="`${item.nama}-${item.rincian}`">
                                                <td class="px-3 py-3 font-medium text-slate-900">{{ item.nama || '-' }}</td>
                                                <td class="px-3 py-3 text-slate-600">{{ item.rincian || '-' }}</td>
                                                <td class="px-3 py-3 text-right text-slate-700">{{ item.qty ?? '-' }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <details class="rounded-2xl bg-slate-950 text-slate-100">
                                <summary class="cursor-pointer list-none px-4 py-4 text-sm font-semibold">Raw JSON</summary>
                                <div class="border-t border-white/10 p-4">
                                    <pre class="overflow-x-auto whitespace-pre-wrap break-words text-xs leading-6 text-slate-200">{{ JSON.stringify(snapshotData, null, 2) }}</pre>
                                </div>
                            </details>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
