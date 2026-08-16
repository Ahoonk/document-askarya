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

    <AuthenticatedLayout theme="login">
        <div class="relative min-h-screen overflow-hidden bg-[#08111f] text-slate-100">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(59,130,246,0.14),transparent_30%),radial-gradient(circle_at_top_right,rgba(239,68,68,0.12),transparent_28%),linear-gradient(180deg,rgba(8,17,31,0.98),rgba(8,17,31,1))]"></div>
            <div class="absolute left-[-6rem] top-24 h-72 w-72 rounded-full bg-blue-500/20 blur-3xl"></div>
            <div class="absolute right-[-5rem] top-40 h-80 w-80 rounded-full bg-rose-500/15 blur-3xl"></div>

            <div class="relative mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="text-xs uppercase tracking-[0.3em] text-sky-300">Detail Berita Acara</p>
                        <h1 class="mt-2 text-3xl font-bold text-white">{{ beritaAcara.nomor }}</h1>
                        <p class="mt-2 text-sm text-slate-300">{{ beritaAcara.invoice?.customer_name || '-' }}</p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <a v-if="beritaAcara.preview_url" :href="previewHref" target="_blank" rel="noreferrer" class="rounded-full border border-white/10 bg-white/5 px-4 py-2 text-sm font-semibold text-slate-100 shadow-sm backdrop-blur-sm transition hover:border-sky-300/40 hover:bg-sky-500/10">
                            Preview
                        </a>
                        <Link :href="route('berita-acara.index')" class="rounded-full border border-white/10 bg-white/5 px-4 py-2 text-sm font-semibold text-slate-100 shadow-sm backdrop-blur-sm transition hover:border-sky-300/40 hover:bg-sky-500/10">
                            Kembali
                        </Link>
                        <Link :href="route('berita-acara.edit', beritaAcara.id)" class="rounded-full border border-white/10 bg-white/5 px-4 py-2 text-sm font-semibold text-slate-100 shadow-sm backdrop-blur-sm transition hover:border-sky-300/40 hover:bg-sky-500/10">
                            Edit
                        </Link>
                        <button @click="destroy" type="button" class="rounded-full bg-gradient-to-r from-blue-600 via-indigo-600 to-rose-600 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-rose-950/20">
                            Hapus
                        </button>
                    </div>
                </div>

                <section class="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
                    <article class="rounded-[1.5rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-slate-950/30 backdrop-blur">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Tanggal</p>
                                <p class="mt-2 text-lg font-semibold text-white">{{ formatDate(beritaAcara.tanggal) }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Kota/Tanggal Manual</p>
                                <p class="mt-2 text-lg font-semibold text-white">{{ beritaAcara.kota_tanggal_manual ? formatDate(beritaAcara.kota_tanggal_manual) : '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Invoice</p>
                                <p class="mt-2 text-lg font-semibold text-white">{{ beritaAcara.invoice?.nomor || '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Total Invoice</p>
                                <p class="mt-2 text-lg font-semibold text-white">{{ formatCurrency(beritaAcara.invoice?.total || 0) }}</p>
                            </div>
                        </div>

                        <div class="mt-6">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Perihal</p>
                            <p class="mt-2 text-sm leading-6 text-slate-300">{{ beritaAcara.perihal || '-' }}</p>
                        </div>

                        <div class="mt-6">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Keterangan Akhir</p>
                            <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-300">{{ beritaAcara.keterangan_akhir || '-' }}</p>
                        </div>
                    </article>

                    <article class="space-y-4 rounded-[1.5rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-slate-950/30 backdrop-blur">
                        <div class="rounded-2xl border border-white/10 bg-slate-950/40 p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Isi Manual</p>
                            <div class="mt-3 space-y-2 text-sm text-slate-300">
                                <p><span class="font-semibold text-white">Nomor Perjanjian:</span> {{ beritaAcara.nomor_perjanjian || '-' }}</p>
                                <p><span class="font-semibold text-white">Tanggal Teks:</span> {{ beritaAcara.tanggal_teks_manual || '-' }}</p>
                                <p><span class="font-semibold text-white">Pihak Pertama:</span> {{ beritaAcara.pihak_pertama_nama || '-' }}</p>
                                <p><span class="font-semibold text-white">Pihak Kedua:</span> {{ beritaAcara.pihak_kedua_nama || '-' }}</p>
                                <p><span class="font-semibold text-white">Pekerjaan:</span> {{ beritaAcara.pekerjaan_manual || '-' }}</p>
                                <p><span class="font-semibold text-white">Periode:</span> {{ beritaAcara.periode_manual || '-' }}</p>
                                <p><span class="font-semibold text-white">Predikat:</span> {{ beritaAcara.predikat_manual || '-' }}</p>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-white/10 bg-slate-950/40 p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Customer</p>
                            <p class="mt-2 text-sm font-semibold text-white">{{ beritaAcara.invoice?.customer_name || '-' }}</p>
                            <p class="mt-1 text-sm text-slate-300">{{ beritaAcara.invoice?.customer_address || '-' }}</p>
                        </div>

                        <div class="rounded-2xl border border-white/10 bg-slate-950/40 p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">PO</p>
                            <p class="mt-2 text-sm text-slate-300">{{ beritaAcara.invoice?.po_number || '-' }}</p>
                        </div>

                        <div class="rounded-2xl border border-white/10 bg-slate-950/40 p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Snapshot</p>
                            <p class="mt-2 text-sm text-slate-300">Snapshot disimpan agar isi berita acara tetap dapat diaudit meski invoice berubah.</p>
                        </div>
                    </article>
                </section>

                <section class="mt-6 overflow-hidden rounded-[1.5rem] border border-white/10 bg-white/5 shadow-2xl shadow-slate-950/30 backdrop-blur">
                    <div class="border-b border-white/10 px-6 py-4">
                        <h2 class="text-lg font-semibold text-white">Snapshot</h2>
                    </div>
                    <div class="grid gap-6 px-6 py-6 lg:grid-cols-2">
                        <div class="space-y-4">
                            <div class="rounded-2xl border border-white/10 bg-slate-950/40 p-4">
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Ringkasan</p>
                                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                    <div v-for="item in snapshotSummary" :key="item.label" class="rounded-xl border border-white/10 bg-white/5 p-3">
                                        <p class="text-[11px] uppercase tracking-[0.2em] text-slate-400">{{ item.label }}</p>
                                        <p class="mt-2 break-words text-sm font-semibold text-white">{{ item.value }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="rounded-2xl border border-white/10 bg-slate-950/40 p-4">
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Pihak</p>
                                <div class="mt-4 space-y-3">
                                    <div v-for="party in snapshotParties" :key="party.label" class="rounded-xl border border-white/10 bg-white/5 p-3">
                                        <p class="text-[11px] uppercase tracking-[0.2em] text-slate-400">{{ party.label }}</p>
                                        <p class="mt-2 break-words text-sm font-semibold text-white">{{ party.name }}</p>
                                        <p class="mt-1 break-words whitespace-pre-line text-sm leading-6 text-slate-300">{{ party.address }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div class="rounded-2xl border border-white/10 bg-slate-950/40 p-4">
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Items</p>
                                <div class="mt-4 overflow-hidden rounded-xl border border-white/10 bg-white/5">
                                    <table class="min-w-full divide-y divide-white/10 text-left text-sm">
                                        <thead class="bg-white/5 text-[11px] uppercase tracking-[0.2em] text-slate-400">
                                            <tr>
                                                <th class="px-3 py-3">Nama</th>
                                                <th class="px-3 py-3">Rincian</th>
                                                <th class="px-3 py-3 text-right">Qty</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-white/10">
                                            <tr v-if="!snapshotItems.length">
                                                <td colspan="3" class="px-3 py-4 text-sm text-slate-400">Tidak ada item di snapshot.</td>
                                            </tr>
                                            <tr v-for="item in snapshotItems" :key="`${item.nama}-${item.rincian}`">
                                                <td class="px-3 py-3 font-medium text-white">{{ item.nama || '-' }}</td>
                                                <td class="px-3 py-3 text-slate-300">{{ item.rincian || '-' }}</td>
                                                <td class="px-3 py-3 text-right text-slate-200">{{ item.qty ?? '-' }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <details class="rounded-2xl border border-white/10 bg-slate-950/70 text-slate-100">
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
