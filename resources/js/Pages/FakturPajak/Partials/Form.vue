<script setup>
import { computed } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { formatCurrency, formatDate } from '@/utils/format';

const props = defineProps({
    action: {
        type: String,
        required: true,
    },
    method: {
        type: String,
        default: 'post',
    },
    fakturPajak: {
        type: Object,
        default: null,
    },
    availableInvoices: {
        type: Array,
        default: () => [],
    },
    selectedInvoiceId: {
        type: [String, Number],
        default: null,
    },
    meta: {
        type: Object,
        default: () => ({}),
    },
});

const form = useForm({
    invoice_id: props.fakturPajak?.invoice_id ?? props.selectedInvoiceId ?? '',
    payment_status: props.fakturPajak?.payment_status ?? props.meta?.defaults?.payment_status ?? 'unpaid',
    payment_date: props.fakturPajak?.payment_date ?? props.meta?.defaults?.payment_date ?? '',
    dokumen: null,
});

const selectedInvoice = computed(() => {
    return props.availableInvoices.find((invoice) => String(invoice.id) === String(form.invoice_id)) || props.fakturPajak?.invoice || null;
});

const selectedFileLabel = computed(() => {
    return form.dokumen?.name || props.fakturPajak?.dokumen_name || 'Belum ada file dipilih';
});

function submit() {
    const options = {
        preserveScroll: true,
        forceFormData: true,
    };

    if (props.method === 'put') {
        form.put(props.action, options);
        return;
    }

    form.post(props.action, options);
}
</script>

<template>
    <form @submit.prevent="submit" class="space-y-6">
        <section class="rounded-[1.5rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-slate-950/25 backdrop-blur">
            <div class="grid gap-6 lg:grid-cols-[1fr_0.8fr]">
                <div class="space-y-5">
                    <div v-if="availableInvoices.length && method !== 'put'" class="space-y-2">
                        <InputLabel value="Invoice" />
                        <select v-model="form.invoice_id" class="mt-2 block w-full rounded-xl border-white/10 bg-slate-950/50 text-slate-100 shadow-sm focus:border-sky-400 focus:ring-sky-400">
                            <option value="">Pilih invoice</option>
                            <option v-for="invoice in availableInvoices" :key="invoice.id" :value="invoice.id">
                                {{ invoice.nomor }} - {{ invoice.customer_name }}
                            </option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.invoice_id" />
                    </div>

                    <div v-else class="rounded-2xl border border-white/10 bg-slate-950/40 p-4">
                        <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Invoice</p>
                        <div v-if="selectedInvoice" class="mt-2 space-y-1 text-sm text-slate-300">
                            <p class="font-semibold text-white">{{ selectedInvoice.nomor }}</p>
                            <p>{{ selectedInvoice.customer_name }}</p>
                            <p>{{ selectedInvoice.customer_address || '-' }}</p>
                            <p>Tanggal: {{ formatDate(selectedInvoice.tanggal) }}</p>
                            <p>Total: {{ formatCurrency(selectedInvoice.total || 0) }}</p>
                        </div>
                        <p v-else class="mt-2 text-sm text-slate-400">Tidak ada invoice yang tersedia.</p>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <InputLabel for="payment_status" value="Status Pembayaran" />
                            <select id="payment_status" v-model="form.payment_status" class="mt-2 block w-full rounded-xl border-white/10 bg-slate-950/50 text-slate-100 shadow-sm focus:border-sky-400 focus:ring-sky-400">
                                <option value="unpaid">unpaid</option>
                                <option value="paid">paid</option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.payment_status" />
                        </div>

                        <div>
                            <InputLabel for="payment_date" value="Tanggal Bayar" />
                            <TextInput id="payment_date" v-model="form.payment_date" type="date" class="mt-2 block w-full" />
                            <InputError class="mt-2" :message="form.errors.payment_date" />
                        </div>
                    </div>

                    <div>
                        <InputLabel for="dokumen" value="Upload Dokumen" />
                        <input
                            id="dokumen"
                            type="file"
                            class="mt-2 block w-full rounded-xl border border-white/10 bg-slate-950/50 px-3 py-2 text-slate-100 shadow-sm"
                            @change="(event) => { form.dokumen = event.target.files?.[0] ?? null }"
                        >
                        <InputError class="mt-2" :message="form.errors.dokumen" />
                    </div>
                </div>

                <aside class="space-y-4 rounded-2xl border border-white/10 bg-slate-950/40 p-4">
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                        <p class="text-xs uppercase tracking-[0.2em] text-slate-400">File Terpilih</p>
                        <p class="mt-2 text-sm font-medium text-white">{{ selectedFileLabel }}</p>
                    </div>

                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                        <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Catatan</p>
                        <p class="mt-2 text-sm leading-6 text-slate-300">
                            Jika status pembayaran diubah menjadi paid, tanggal bayar sebaiknya diisi agar arsipnya konsisten.
                        </p>
                    </div>
                </aside>
            </div>
        </section>

        <section class="rounded-[1.5rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-slate-950/25 backdrop-blur">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <Link :href="route('faktur-pajak.index')" class="text-sm font-semibold text-slate-300">
                    Batal
                </Link>
                <PrimaryButton :disabled="form.processing" class="bg-gradient-to-r from-blue-600 via-indigo-600 to-rose-600">
                    {{ form.processing ? 'Menyimpan...' : 'Simpan Faktur Pajak' }}
                </PrimaryButton>
            </div>
        </section>
    </form>
</template>
