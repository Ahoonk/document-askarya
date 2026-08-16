<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import { formatCurrency, formatDate } from '@/utils/format';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    availableInvoices: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    invoice_id: '',
    tanggal: new Date().toISOString().slice(0, 10),
    pemberi_nama: '',
    pemberi_jabatan: '',
    pemberi_alamat: '',
    penerima_nama: '',
    penerima_hp: '',
    kota_tanggal_manual: '',
});

const selectedInvoice = computed(() => props.availableInvoices.find((invoice) => String(invoice.id) === String(form.invoice_id)) || null);

function store() {
    form.post(route('surat-jalan.store'), {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Buat Surat Jalan" />

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
                        <p class="text-xs uppercase tracking-[0.3em] text-blue-200/70">Delivery</p>
                        <h1 class="mt-2 text-3xl font-bold text-white">Buat Surat Jalan</h1>
                        <p class="mt-2 text-sm text-slate-300">Pilih invoice lalu lengkapi data pengiriman.</p>
                    </div>

                    <Link :href="route('surat-jalan.index')" class="rounded-full border border-white/10 bg-white/5 px-4 py-2 text-sm font-semibold text-slate-200 hover:border-blue-300/20 hover:bg-white/8">
                        Kembali
                    </Link>
                </div>

                <div class="grid gap-6 lg:grid-cols-[1fr_0.8fr]">
                    <section class="rounded-[1.5rem] border border-white/10 bg-white p-6 shadow-[0_12px_30px_rgba(0,0,0,0.12)]">
                        <div v-if="availableInvoices.length" class="space-y-5">
                            <div>
                                <InputLabel value="Invoice" />
                                <select v-model="form.invoice_id" class="mt-2 block w-full rounded-xl border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Pilih invoice</option>
                                    <option v-for="invoice in availableInvoices" :key="invoice.id" :value="invoice.id">
                                        {{ invoice.nomor }} - {{ invoice.customer_name }}
                                    </option>
                                </select>
                                <p v-if="form.errors.invoice_id" class="mt-2 text-sm text-rose-600">{{ form.errors.invoice_id }}</p>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <InputLabel value="Tanggal Surat Jalan" />
                                    <TextInput v-model="form.tanggal" type="date" class="mt-2 block w-full" />
                                    <p v-if="form.errors.tanggal" class="mt-2 text-sm text-rose-600">{{ form.errors.tanggal }}</p>
                                </div>
                                <div>
                                    <InputLabel value="Kota/Tanggal Manual" />
                                    <TextInput v-model="form.kota_tanggal_manual" type="date" class="mt-2 block w-full" />
                                    <p v-if="form.errors.kota_tanggal_manual" class="mt-2 text-sm text-rose-600">{{ form.errors.kota_tanggal_manual }}</p>
                                </div>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <InputLabel value="Pemberi Nama" />
                                    <TextInput v-model="form.pemberi_nama" type="text" class="mt-2 block w-full" />
                                </div>
                                <div>
                                    <InputLabel value="Pemberi Jabatan" />
                                    <TextInput v-model="form.pemberi_jabatan" type="text" class="mt-2 block w-full" />
                                </div>
                            </div>

                            <div>
                                <InputLabel value="Pemberi Alamat" />
                                <textarea v-model="form.pemberi_alamat" rows="4" class="mt-2 block w-full rounded-xl border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <InputLabel value="Penerima Nama" />
                                    <TextInput v-model="form.penerima_nama" type="text" class="mt-2 block w-full" />
                                </div>
                                <div>
                                    <InputLabel value="Penerima HP" />
                                    <TextInput v-model="form.penerima_hp" type="text" class="mt-2 block w-full" />
                                </div>
                            </div>

                            <PrimaryButton :disabled="form.processing" @click="store">
                                Simpan Surat Jalan
                            </PrimaryButton>
                        </div>

                        <div v-else class="rounded-3xl border border-dashed border-slate-300 bg-[#fff2d9] p-8 text-center">
                            <p class="text-lg font-semibold text-slate-900">Tidak ada invoice yang siap diproses</p>
                            <p class="mt-2 text-sm text-slate-500">
                                Semua invoice aktif sudah punya surat jalan, atau invoice belum dibuat dari alur sebelumnya.
                            </p>
                            <Link :href="route('invoice.index')" class="mt-4 inline-flex rounded-full bg-gradient-to-r from-blue-700 via-indigo-600 to-red-600 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-blue-700/20 hover:brightness-110">
                                Ke Invoice
                            </Link>
                        </div>
                    </section>

                    <aside class="space-y-4 rounded-[1.5rem] border border-white/10 bg-white p-6 shadow-[0_12px_30px_rgba(0,0,0,0.12)]">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Invoice Terpilih</p>
                            <div v-if="selectedInvoice" class="mt-3 space-y-2 text-sm text-slate-700">
                                <p class="font-semibold text-slate-950">{{ selectedInvoice.nomor }}</p>
                                <p>{{ selectedInvoice.customer_name }}</p>
                                <p>{{ selectedInvoice.customer_address || '-' }}</p>
                                <p>Tanggal: {{ formatDate(selectedInvoice.tanggal) }}</p>
                            </div>
                            <p v-else class="mt-3 text-sm text-slate-500">Belum ada pilihan invoice.</p>
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Nilai Invoice</p>
                            <p class="mt-2 text-2xl font-semibold text-slate-950">
                                {{ selectedInvoice?.total ? formatCurrency(selectedInvoice.total) : '-' }}
                            </p>
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Catatan</p>
                            <p class="mt-2 text-sm leading-6 text-slate-700">
                                Nomor surat jalan bisa mengikuti pola invoice, atau memakai seri baru jika format invoice tidak cocok.
                            </p>
                        </div>
                    </aside>
                </div>
            </div>
            </div>
    </AuthenticatedLayout>
</template>
