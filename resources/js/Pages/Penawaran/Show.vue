<script setup>
import { computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { formatCurrency, formatDate } from '@/utils/format';

const props = defineProps({
    penawaran: {
        type: Object,
        required: true,
    },
    snapshot: {
        type: Object,
        default: () => ({}),
    },
    meta: {
        type: Object,
        default: () => ({}),
    },
});

const actionBase = 'inline-flex flex-none items-center justify-center rounded-full px-4 py-2 text-sm font-semibold leading-none transition duration-200 hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-offset-2 whitespace-nowrap';
const actionSecondary = `${actionBase} border border-slate-200 bg-white text-slate-700 shadow-sm hover:border-slate-300 hover:bg-slate-50 focus:ring-slate-400`;
const actionPrimary = `${actionBase} bg-slate-950 text-white shadow-lg shadow-slate-950/15 hover:bg-slate-800 focus:ring-slate-900`;
const actionInfo = `${actionBase} bg-blue-600 text-white shadow-lg shadow-blue-600/20 hover:bg-blue-500 focus:ring-blue-500`;
const actionSuccess = `${actionBase} bg-emerald-600 text-white shadow-lg shadow-emerald-600/20 hover:bg-emerald-500 focus:ring-emerald-500`;
const actionDanger = `${actionBase} bg-rose-600 text-white shadow-lg shadow-rose-600/20 hover:bg-rose-500 focus:ring-rose-500`;

const previewTemplate = computed(() => {
    const template = props.snapshot?.template ?? props.penawaran?.snapshot_data?.template ?? {};
    const path = template?.path || '';
    const url = route('penawaran.preview-pdf', props.penawaran.id);

    return {
        scope: template?.scope || 'company',
        path,
        url,
    };
});

function approve() {
    router.post(route('penawaran.approve', props.penawaran.id), {}, {
        preserveScroll: true,
    });
}

function destroy() {
    if (!confirm(`Hapus penawaran ${props.penawaran.nomor}?`)) {
        return;
    }

    router.delete(route('penawaran.destroy', props.penawaran.id));
}
</script>

<template>
    <Head :title="`Penawaran ${penawaran.nomor}`" />

    <AuthenticatedLayout>
        <div class="bg-[#fff2d9]">
            <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="text-xs uppercase tracking-[0.3em] text-blue-700">Detail Penawaran</p>
                        <h1 class="mt-2 text-3xl font-bold text-slate-950">{{ penawaran.nomor }}</h1>
                        <p class="mt-2 text-sm text-slate-500">{{ penawaran.to_company }}</p>
                    </div>

                    <div class="flex flex-wrap gap-2 sm:flex-nowrap">
                        <Link :href="route('penawaran.index')" :class="actionSecondary">
                            Kembali
                        </Link>
                        <Link :href="route('penawaran.edit', penawaran.id)" :class="actionSecondary">
                            Edit
                        </Link>
                        <a :href="route('penawaran.preview-pdf', { penawaran: penawaran.id, download: 1 })" target="_blank" rel="noreferrer" :class="actionInfo">
                            Download PDF
                        </a>
                        <Link v-if="penawaran.status === 'approved'" :href="route('purchasing-order.index')" :class="actionPrimary">
                            Lanjut PO
                        </Link>
                        <button v-if="penawaran.status !== 'approved'" @click="approve" type="button" :class="actionSuccess">
                            Approve
                        </button>
                        <button @click="destroy" type="button" :class="actionDanger">
                            Hapus
                        </button>
                    </div>
                </div>

                <section class="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
                    <article class="rounded-[1.5rem] border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Tanggal</p>
                                <p class="mt-2 text-lg font-semibold text-slate-950">{{ formatDate(penawaran.tanggal) }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Status</p>
                                <p class="mt-2 text-lg font-semibold text-slate-950 capitalize">{{ penawaran.status }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Jenis Kontrak</p>
                                <p class="mt-2 text-lg font-semibold text-slate-950">{{ penawaran.jenis_kontrak }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Role TTD</p>
                                <p class="mt-2 text-lg font-semibold text-slate-950">{{ penawaran.signature_role }}</p>
                            </div>
                        </div>

                        <div class="mt-6">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Alamat Customer</p>
                            <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-700">{{ penawaran.to_address || '-' }}</p>
                        </div>

                        <div class="mt-6">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Keterangan</p>
                            <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-700">{{ penawaran.keterangan || '-' }}</p>
                        </div>
                    </article>

                    <article class="rounded-[1.5rem] border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="grid gap-3">
                            <div class="rounded-2xl bg-[#fff2d9] p-4">
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Subtotal</p>
                                <p class="mt-2 text-xl font-semibold text-slate-950">{{ formatCurrency(penawaran.subtotal) }}</p>
                            </div>
                            <div class="rounded-2xl bg-[#fff2d9] p-4">
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Tax</p>
                                <p class="mt-2 text-xl font-semibold text-slate-950">{{ formatCurrency(penawaran.tax_amount) }}</p>
                            </div>
                            <div class="rounded-2xl bg-blue-50 p-4">
                                <p class="text-xs uppercase tracking-[0.2em] text-blue-700">Total</p>
                                <p class="mt-2 text-xl font-semibold text-blue-950">{{ formatCurrency(penawaran.total) }}</p>
                            </div>
                        </div>
                    </article>
                </section>

                <section class="mt-6 overflow-hidden rounded-[1.5rem] border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 px-6 py-4">
                        <h2 class="text-lg font-semibold text-slate-950">Item Penawaran</h2>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-[#fff2d9]">
                                <tr class="text-left text-xs uppercase tracking-[0.2em] text-slate-500">
                                    <th class="px-6 py-4">Nama</th>
                                    <th class="px-6 py-4">Rincian</th>
                                    <th class="px-6 py-4">Qty</th>
                                    <th class="px-6 py-4">Satuan</th>
                                    <th class="px-6 py-4">Harga</th>
                                    <th class="px-6 py-4">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="item in penawaran.items" :key="item.id">
                                    <td class="px-6 py-4 font-medium text-slate-950">{{ item.nama }}</td>
                                    <td class="px-6 py-4 text-slate-600">{{ item.rincian || '-' }}</td>
                                    <td class="px-6 py-4 text-slate-600">{{ item.qty }}</td>
                                    <td class="px-6 py-4 text-slate-600">{{ item.satuan }}</td>
                                    <td class="px-6 py-4 text-slate-600">{{ formatCurrency(item.unit_price) }}</td>
                                    <td class="px-6 py-4 font-semibold text-slate-950">{{ formatCurrency(item.amount) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="mt-6 grid gap-6 lg:grid-cols-[1.25fr_0.75fr]">
                    <article class="overflow-hidden rounded-[1.5rem] border border-slate-200 bg-white shadow-sm">
                        <div class="border-b border-slate-200 px-6 py-4">
                            <h2 class="text-lg font-semibold text-slate-950">Preview Template</h2>
                        </div>

                        <div class="p-6">
                            <div v-if="previewTemplate.url" class="overflow-hidden rounded-2xl border border-slate-200 bg-slate-50">
                                <object :data="previewTemplate.url" type="application/pdf" class="h-[80vh] w-full">
                                    <div class="flex h-[80vh] items-center justify-center px-6 text-center">
                                        <div>
                                            <p class="text-lg font-semibold text-slate-950">Preview PDF tidak didukung di browser ini.</p>
                                            <a :href="previewTemplate.url" target="_blank" rel="noreferrer" class="mt-4 inline-flex rounded-full bg-blue-600 px-4 py-2 text-sm font-semibold text-white">
                                                Buka preview PDF
                                            </a>
                                        </div>
                                    </div>
                                </object>
                            </div>
                            <div v-else class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-sm text-slate-600">
                                Belum ada template yang terhubung untuk penawaran ini. Pastikan sudah upload template pada menu <span class="font-semibold">Document Templates</span> dengan tipe <span class="font-semibold">penawaran</span>.
                            </div>
                        </div>
                    </article>

                    <aside class="space-y-4 rounded-[1.5rem] border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="rounded-2xl bg-[#fff2d9] p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Sumber Template</p>
                            <p class="mt-2 text-sm font-semibold text-slate-950">
                                {{ previewTemplate.scope === 'mitra' ? 'Template Mitra' : 'Template Perusahaan' }}
                            </p>
                            <p class="mt-2 text-sm leading-6 text-slate-700">
                                Preview ini sekarang merender penawaran dengan data yang sudah diinput, lalu menumpangkan template perusahaan yang tersimpan di menu Document Templates.
                            </p>
                        </div>

                        <div class="rounded-2xl bg-[#fff2d9] p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Path Template</p>
                            <p class="mt-2 break-all text-sm text-slate-700">{{ previewTemplate.path || '-' }}</p>
                        </div>

                        <div class="rounded-2xl bg-[#fff2d9] p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Catatan</p>
                            <p class="mt-2 text-sm leading-6 text-slate-700">
                                Jika file template baru saja diganti, buka ulang halaman detail ini supaya preview mengambil snapshot template terbaru.
                            </p>
                        </div>
                    </aside>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
