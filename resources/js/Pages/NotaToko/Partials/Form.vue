<script setup>
import { computed } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { formatCurrency } from '@/utils/format';

const props = defineProps({
    action: {
        type: String,
        required: true,
    },
    method: {
        type: String,
        default: 'post',
    },
    notaToko: {
        type: Object,
        default: null,
    },
    meta: {
        type: Object,
        default: () => ({}),
    },
});

const defaultItem = () => ({
    nama: '',
    qty: 1,
    satuan: props.meta?.options?.satuan?.[0] || 'pcs',
    unit_price: 0,
});

const initialItems = props.notaToko?.items?.length
    ? props.notaToko.items.map((item) => ({
        nama: item.nama ?? '',
        qty: item.qty ?? 1,
        satuan: item.satuan ?? 'pcs',
        unit_price: item.unit_price ?? 0,
    }))
    : [defaultItem()];

const form = useForm({
    tanggal: props.notaToko?.tanggal ?? props.meta?.defaults?.tanggal ?? '',
    customer_nama: props.notaToko?.customer_nama ?? '',
    customer_email: props.notaToko?.customer_email ?? '',
    alamat: props.notaToko?.alamat ?? '',
    keterangan: props.notaToko?.keterangan ?? '',
    tax_percent: props.notaToko?.tax_percent ?? props.meta?.defaults?.tax_percent ?? 0,
    payment_status: props.notaToko?.payment_status ?? props.meta?.defaults?.payment_status ?? 'unpaid',
    payment_date: props.notaToko?.payment_date ?? '',
    items: initialItems,
});

const selectedCustomer = computed(() => {
    return props.meta?.customers?.find((customer) => customer.nama === form.customer_nama) || null;
});

const subtotal = computed(() => {
    return form.items.reduce((sum, item) => {
        return sum + (Number(item.qty || 0) * Number(item.unit_price || 0));
    }, 0);
});

const taxAmount = computed(() => subtotal.value * (Number(form.tax_percent || 0) / 100));
const total = computed(() => subtotal.value + taxAmount.value);

function applyCustomerData() {
    if (selectedCustomer.value?.alamat) {
        form.alamat = selectedCustomer.value.alamat;
    }

    if (selectedCustomer.value?.email) {
        form.customer_email = selectedCustomer.value.email;
    }
}

function addItem() {
    form.items.push(defaultItem());
}

function removeItem(index) {
    if (form.items.length === 1) {
        form.items.splice(0, 1, defaultItem());
        return;
    }

    form.items.splice(index, 1);
}

function submit() {
    form.transform((data) => ({
        ...data,
        customer_email: data.customer_email || null,
        alamat: data.alamat || null,
        keterangan: data.keterangan || null,
        payment_date: data.payment_date || null,
    }));

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
        <section class="rounded-[1.5rem] border border-slate-200 bg-white p-6 shadow-sm">
            <div class="grid gap-6 lg:grid-cols-2">
                <div>
                    <InputLabel value="Nomor Nota Toko" />
                    <div class="mt-2 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700">
                        {{ notaToko?.nomor || meta?.nomor_preview || '-' }}
                    </div>
                </div>

                <div>
                    <InputLabel for="tanggal" value="Tanggal" />
                    <TextInput id="tanggal" v-model="form.tanggal" type="date" class="mt-2 block w-full" />
                    <InputError class="mt-2" :message="form.errors.tanggal" />
                </div>

                <div>
                    <InputLabel for="customer_nama" value="Nama Customer" />
                    <TextInput id="customer_nama" v-model="form.customer_nama" list="customer-list" class="mt-2 block w-full" />
                    <datalist id="customer-list">
                        <option v-for="customer in meta?.customers || []" :key="customer.id" :value="customer.nama" />
                    </datalist>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <button type="button" @click="applyCustomerData" class="rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-800">
                            Pakai data customer
                        </button>
                    </div>
                    <InputError class="mt-2" :message="form.errors.customer_nama" />
                </div>

                <div>
                    <InputLabel for="customer_email" value="Email Customer" />
                    <TextInput id="customer_email" v-model="form.customer_email" type="email" class="mt-2 block w-full" />
                    <InputError class="mt-2" :message="form.errors.customer_email" />
                </div>

                <div class="lg:col-span-2">
                    <InputLabel for="alamat" value="Alamat" />
                    <textarea id="alamat" v-model="form.alamat" rows="3" class="mt-2 block w-full rounded-xl border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                    <InputError class="mt-2" :message="form.errors.alamat" />
                </div>

                <div class="lg:col-span-2">
                    <InputLabel for="keterangan" value="Keterangan" />
                    <textarea id="keterangan" v-model="form.keterangan" rows="4" class="mt-2 block w-full rounded-xl border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                    <InputError class="mt-2" :message="form.errors.keterangan" />
                </div>

                <div>
                    <InputLabel for="tax_percent" value="Tax Percent" />
                    <TextInput id="tax_percent" v-model="form.tax_percent" type="number" step="0.01" min="0" max="100" class="mt-2 block w-full" />
                    <InputError class="mt-2" :message="form.errors.tax_percent" />
                </div>

                <div>
                    <InputLabel for="payment_status" value="Status Pembayaran" />
                    <select id="payment_status" v-model="form.payment_status" class="mt-2 block w-full rounded-xl border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option v-for="option in meta?.options?.payment_status || []" :key="option" :value="option">
                            {{ option }}
                        </option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.payment_status" />
                </div>

                <div>
                    <InputLabel for="payment_date" value="Tanggal Bayar" />
                    <TextInput id="payment_date" v-model="form.payment_date" type="date" class="mt-2 block w-full" />
                    <InputError class="mt-2" :message="form.errors.payment_date" />
                </div>
            </div>
        </section>

        <section class="rounded-[1.5rem] border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-slate-950">Item Nota Toko</h2>
                    <p class="text-sm text-slate-500">Isi item dan sistem akan hitung subtotal, tax, dan total.</p>
                </div>

                <button type="button" @click="addItem" class="rounded-full bg-slate-900 px-4 py-2 text-sm font-semibold text-white">
                    Tambah Item
                </button>
            </div>

            <div class="mt-5 space-y-4">
                <article v-for="(item, index) in form.items" :key="index" class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <div class="grid gap-4 lg:grid-cols-12">
                        <div class="lg:col-span-5">
                            <InputLabel :value="`Nama Item ${index + 1}`" />
                            <TextInput v-model="item.nama" class="mt-2 block w-full" />
                            <InputError class="mt-2" :message="form.errors[`items.${index}.nama`]" />
                        </div>

                        <div class="lg:col-span-2">
                            <InputLabel value="Qty" />
                            <TextInput v-model="item.qty" type="number" step="0.01" min="0.01" class="mt-2 block w-full" />
                            <InputError class="mt-2" :message="form.errors[`items.${index}.qty`]" />
                        </div>

                        <div class="lg:col-span-2">
                            <InputLabel value="Satuan" />
                            <select v-model="item.satuan" class="mt-2 block w-full rounded-xl border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option v-for="option in meta?.options?.satuan || []" :key="option" :value="option">
                                    {{ option }}
                                </option>
                            </select>
                            <InputError class="mt-2" :message="form.errors[`items.${index}.satuan`]" />
                        </div>

                        <div class="lg:col-span-3">
                            <InputLabel value="Harga" />
                            <TextInput v-model="item.unit_price" type="number" step="0.01" min="0" class="mt-2 block w-full" />
                            <InputError class="mt-2" :message="form.errors[`items.${index}.unit_price`]" />
                        </div>
                    </div>

                    <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                        <p class="text-sm text-slate-600">
                            Nilai item: <span class="font-semibold text-slate-900">{{ formatCurrency(Number(item.qty || 0) * Number(item.unit_price || 0)) }}</span>
                        </p>

                        <button type="button" @click="removeItem(index)" class="text-sm font-semibold text-rose-600">
                            Hapus item
                        </button>
                    </div>
                </article>
            </div>
        </section>

        <section class="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
            <div class="rounded-[1.5rem] border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-slate-950">Ringkasan</h3>
                <div class="mt-4 grid gap-3 sm:grid-cols-3">
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Subtotal</p>
                        <p class="mt-2 text-xl font-semibold text-slate-950">{{ formatCurrency(subtotal) }}</p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Tax</p>
                        <p class="mt-2 text-xl font-semibold text-slate-950">{{ formatCurrency(taxAmount) }}</p>
                    </div>
                    <div class="rounded-2xl bg-blue-50 p-4">
                        <p class="text-xs uppercase tracking-[0.2em] text-blue-700">Total</p>
                        <p class="mt-2 text-xl font-semibold text-blue-950">{{ formatCurrency(total) }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-[1.5rem] border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <Link :href="route('nota-toko.index')" class="text-sm font-semibold text-slate-600">
                        Batal
                    </Link>
                    <PrimaryButton :disabled="form.processing">
                        {{ form.processing ? 'Menyimpan...' : 'Simpan Nota Toko' }}
                    </PrimaryButton>
                </div>
            </div>
        </section>
    </form>
</template>
