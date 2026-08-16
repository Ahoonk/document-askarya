<script setup>
import { computed, watch } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { formatCurrency, formatDate, formatDateInWords } from '@/utils/format';

const props = defineProps({
    action: {
        type: String,
        required: true,
    },
    method: {
        type: String,
        default: 'post',
    },
    beritaAcara: {
        type: Object,
        default: null,
    },
    availableInvoices: {
        type: Array,
        default: () => [],
    },
    meta: {
        type: Object,
        default: () => ({}),
    },
});

const form = useForm({
    invoice_id: props.beritaAcara?.invoice_id ?? '',
    tanggal: props.beritaAcara?.tanggal ?? props.meta?.defaults?.tanggal ?? '',
    perihal: props.beritaAcara?.perihal ?? props.meta?.defaults?.perihal ?? 'Berita Acara',
    nomor_perjanjian: props.beritaAcara?.nomor_perjanjian ?? props.beritaAcara?.invoice?.po_number ?? props.meta?.defaults?.nomor_perjanjian ?? '',
    tanggal_teks_manual: props.beritaAcara?.tanggal_teks_manual ?? props.meta?.defaults?.tanggal_teks_manual ?? '',
    pihak_pertama_nama: props.beritaAcara?.pihak_pertama_nama ?? '',
    pihak_pertama_alamat: props.beritaAcara?.pihak_pertama_alamat ?? '',
    pihak_kedua_nama: props.beritaAcara?.pihak_kedua_nama ?? props.meta?.defaults?.pihak_kedua_nama ?? '',
    pihak_kedua_alamat: props.beritaAcara?.pihak_kedua_alamat ?? props.meta?.defaults?.pihak_kedua_alamat ?? '',
    pekerjaan_manual: props.beritaAcara?.pekerjaan_manual ?? '',
    periode_manual: props.beritaAcara?.periode_manual ?? '',
    predikat_manual: props.beritaAcara?.predikat_manual ?? '',
    keterangan_akhir: props.beritaAcara?.keterangan_akhir ?? props.meta?.defaults?.keterangan_akhir ?? '',
    kota_tanggal_manual: props.beritaAcara?.kota_tanggal_manual ?? props.meta?.defaults?.kota_tanggal_manual ?? '',
});

const selectedInvoice = computed(() => {
    return props.availableInvoices.find((invoice) => String(invoice.id) === String(form.invoice_id)) || props.beritaAcara?.invoice || null;
});

watch(
    selectedInvoice,
    (invoice) => {
        if (!invoice) {
            return;
        }

        form.pihak_pertama_nama = form.pihak_pertama_nama || invoice.customer_name || '';
        form.pihak_pertama_alamat = form.pihak_pertama_alamat || invoice.customer_address || '';
        form.nomor_perjanjian = invoice.po_number || form.nomor_perjanjian || '';
    },
    { immediate: true }
);

watch(
    () => form.tanggal,
    (value) => {
        form.tanggal_teks_manual = formatDateInWords(value);
    },
    { immediate: true }
);

const canSubmit = computed(() => {
    return props.method === 'put' || props.availableInvoices.length > 0 || Boolean(props.beritaAcara?.invoice);
});

function submit() {
    if (props.method === 'put') {
        form.put(props.action, {
            preserveScroll: true,
        });
        return;
    }

    form.post(props.action, {
        preserveScroll: true,
    });
}
</script>

<template>
    <form @submit.prevent="submit" class="space-y-6">
        <section class="rounded-[1.5rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-slate-950/25 backdrop-blur">
            <div class="grid gap-6 lg:grid-cols-[1fr_0.75fr]">
                <div class="space-y-5">
                    <div v-if="availableInvoices.length" class="space-y-2">
                        <InputLabel value="Invoice" />
                        <select v-model="form.invoice_id" class="mt-2 block w-full rounded-xl border-white/10 bg-slate-950/50 text-slate-100 shadow-sm focus:border-sky-400 focus:ring-sky-400" :disabled="method === 'put'">
                            <option value="">Pilih invoice</option>
                            <option v-for="invoice in availableInvoices" :key="invoice.id" :value="invoice.id">
                                {{ invoice.nomor }} - {{ invoice.customer_name }}
                            </option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.invoice_id" />
                    </div>

                    <div v-else-if="beritaAcara?.invoice" class="rounded-2xl border border-white/10 bg-slate-950/40 p-4">
                        <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Invoice Terpilih</p>
                        <p class="mt-2 text-sm font-semibold text-white">{{ beritaAcara.invoice.nomor }}</p>
                        <p class="mt-1 text-sm text-slate-300">{{ beritaAcara.invoice.customer_name }}</p>
                        <p class="mt-2 text-xs text-slate-400">Relasi invoice tidak bisa diubah setelah berita acara dibuat.</p>
                    </div>

                    <div v-else class="rounded-2xl border border-rose-500/20 bg-rose-500/10 p-4">
                        <p class="text-sm font-semibold text-rose-100">Tidak ada invoice siap diproses</p>
                        <p class="mt-2 text-sm leading-6 text-rose-200">
                            Berita Acara hanya bisa dibuat dari invoice yang belum punya berita acara.
                        </p>
                        <Link :href="route('invoice.index')" class="mt-3 inline-flex rounded-full bg-gradient-to-r from-blue-600 via-indigo-600 to-rose-600 px-4 py-2 text-xs font-semibold text-white">
                            Ke Invoice
                        </Link>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <InputLabel for="tanggal" value="Tanggal Berita Acara" />
                            <TextInput id="tanggal" v-model="form.tanggal" type="date" class="mt-2 block w-full" />
                            <InputError class="mt-2" :message="form.errors.tanggal" />
                        </div>

                        <div>
                            <InputLabel for="kota_tanggal_manual" value="Kota/Tanggal Manual" />
                            <TextInput id="kota_tanggal_manual" v-model="form.kota_tanggal_manual" type="date" class="mt-2 block w-full" />
                            <InputError class="mt-2" :message="form.errors.kota_tanggal_manual" />
                        </div>
                    </div>

                    <div>
                        <InputLabel for="perihal" value="Perihal" />
                        <TextInput id="perihal" v-model="form.perihal" type="text" class="mt-2 block w-full" />
                        <InputError class="mt-2" :message="form.errors.perihal" />
                    </div>

                    <div class="rounded-2xl border border-white/10 bg-slate-950/40 p-4">
                        <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Isi Manual</p>
                        <div class="mt-4 grid gap-4 md:grid-cols-2">
                            <div>
                                <InputLabel for="nomor_perjanjian" value="Nomor Perjanjian" />
                                <TextInput id="nomor_perjanjian" v-model="form.nomor_perjanjian" type="text" class="mt-2 block w-full" />
                                <InputError class="mt-2" :message="form.errors.nomor_perjanjian" />
                            </div>

                            <div>
                                <InputLabel for="tanggal_teks_manual" value="Tanggal Teks Manual" />
                                <TextInput id="tanggal_teks_manual" v-model="form.tanggal_teks_manual" type="text" class="mt-2 block w-full" placeholder="Rabu, Delapan Juni Dua Ribu Dua Puluh Enam" />
                                <p class="mt-2 text-xs leading-5 text-slate-500">Dipakai untuk kalimat pembuka seperti di gambar, dan bisa diubah bebas.</p>
                                <InputError class="mt-2" :message="form.errors.tanggal_teks_manual" />
                            </div>
                        </div>

                        <div class="mt-4 grid gap-4 md:grid-cols-2">
                            <div>
                                <InputLabel for="pihak_pertama_nama" value="Pihak Pertama Nama" />
                                <TextInput id="pihak_pertama_nama" v-model="form.pihak_pertama_nama" type="text" class="mt-2 block w-full" />
                                <InputError class="mt-2" :message="form.errors.pihak_pertama_nama" />
                            </div>

                            <div>
                                <InputLabel for="pihak_kedua_nama" value="Pihak Kedua Nama" />
                                <TextInput id="pihak_kedua_nama" v-model="form.pihak_kedua_nama" type="text" class="mt-2 block w-full" />
                                <InputError class="mt-2" :message="form.errors.pihak_kedua_nama" />
                            </div>
                        </div>

                        <div class="mt-4 grid gap-4 md:grid-cols-2">
                            <div>
                                <InputLabel for="pihak_pertama_alamat" value="Pihak Pertama Alamat" />
                                <textarea id="pihak_pertama_alamat" v-model="form.pihak_pertama_alamat" rows="4" class="mt-2 block w-full rounded-xl border-white/10 bg-slate-950/50 text-slate-100 shadow-sm focus:border-sky-400 focus:ring-sky-400"></textarea>
                                <InputError class="mt-2" :message="form.errors.pihak_pertama_alamat" />
                            </div>

                            <div>
                                <InputLabel for="pihak_kedua_alamat" value="Pihak Kedua Alamat" />
                                <textarea id="pihak_kedua_alamat" v-model="form.pihak_kedua_alamat" rows="4" class="mt-2 block w-full rounded-xl border-white/10 bg-slate-950/50 text-slate-100 shadow-sm focus:border-sky-400 focus:ring-sky-400"></textarea>
                                <InputError class="mt-2" :message="form.errors.pihak_kedua_alamat" />
                            </div>
                        </div>

                        <div class="mt-4 grid gap-4 md:grid-cols-3">
                            <div>
                                <InputLabel for="pekerjaan_manual" value="Pekerjaan Manual" />
                                <TextInput id="pekerjaan_manual" v-model="form.pekerjaan_manual" type="text" class="mt-2 block w-full" placeholder="Penggunaan Internet" />
                                <InputError class="mt-2" :message="form.errors.pekerjaan_manual" />
                            </div>

                            <div>
                                <InputLabel for="periode_manual" value="Periode Manual" />
                                <TextInput id="periode_manual" v-model="form.periode_manual" type="text" class="mt-2 block w-full" placeholder="01 s/d 31 Januari 2026" />
                                <InputError class="mt-2" :message="form.errors.periode_manual" />
                            </div>

                            <div>
                                <InputLabel for="predikat_manual" value="Predikat Manual" />
                                <TextInput id="predikat_manual" v-model="form.predikat_manual" type="text" class="mt-2 block w-full" placeholder="BAIK DAN DAPAT DITERIMA" />
                                <InputError class="mt-2" :message="form.errors.predikat_manual" />
                            </div>
                        </div>
                    </div>

                    <div>
                        <InputLabel for="keterangan_akhir" value="Keterangan Akhir" />
                        <textarea id="keterangan_akhir" v-model="form.keterangan_akhir" rows="5" class="mt-2 block w-full rounded-xl border-white/10 bg-slate-950/50 text-slate-100 shadow-sm focus:border-sky-400 focus:ring-sky-400"></textarea>
                        <InputError class="mt-2" :message="form.errors.keterangan_akhir" />
                    </div>
                </div>

                <aside class="space-y-4 rounded-2xl border border-white/10 bg-slate-950/40 p-4">
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                        <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Nomor Preview</p>
                        <p class="mt-2 text-lg font-semibold text-white">{{ beritaAcara?.nomor || '-' }}</p>
                    </div>

                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                        <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Invoice</p>
                        <div v-if="selectedInvoice" class="mt-2 space-y-1 text-sm text-slate-300">
                            <p class="font-semibold text-white">{{ selectedInvoice.nomor }}</p>
                            <p>{{ selectedInvoice.customer_name }}</p>
                            <p>{{ selectedInvoice.customer_address || '-' }}</p>
                            <p>Tanggal: {{ formatDate(selectedInvoice.tanggal) }}</p>
                            <p>Total: {{ formatCurrency(selectedInvoice.total || 0) }}</p>
                        </div>
                        <p v-else class="mt-2 text-sm text-slate-400">Belum ada invoice yang dipilih.</p>
                    </div>

                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                        <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Catatan</p>
                        <p class="mt-2 text-sm leading-6 text-slate-300">
                            Saat disimpan, berita acara akan mendapat snapshot data agar arsipnya tetap konsisten.
                        </p>
                    </div>
                </aside>
            </div>
        </section>

        <section class="rounded-[1.5rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-slate-950/25 backdrop-blur">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <Link :href="route('berita-acara.index')" class="text-sm font-semibold text-slate-300">
                    Batal
                </Link>
                <PrimaryButton :disabled="form.processing || !canSubmit" class="bg-gradient-to-r from-blue-600 via-indigo-600 to-rose-600">
                    {{ form.processing ? 'Menyimpan...' : canSubmit ? 'Simpan Berita Acara' : 'Tidak ada invoice' }}
                </PrimaryButton>
            </div>
        </section>
    </form>
</template>
