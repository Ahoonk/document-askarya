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
    penawaran: {
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
    rincian: '',
    qty: 1,
    satuan: props.meta?.options?.satuan?.[2] || 'item',
    unit_price: 0,
});

const initialItems = props.penawaran?.items?.length
    ? props.penawaran.items.map((item) => ({
        nama: item.nama ?? '',
        rincian: item.rincian ?? '',
        qty: item.qty ?? 1,
        satuan: item.satuan ?? 'item',
        unit_price: item.unit_price ?? 0,
    }))
    : [defaultItem()];

const form = useForm({
    mitra_id: props.penawaran?.mitra_id ?? '',
    tanggal: props.penawaran?.tanggal ?? props.meta?.defaults?.tanggal ?? '',
    to_company: props.penawaran?.to_company ?? '',
    to_address: props.penawaran?.to_address ?? '',
    jenis_kontrak: props.penawaran?.jenis_kontrak ?? props.meta?.defaults?.jenis_kontrak ?? 'satuan',
    signature_role: props.penawaran?.signature_role ?? props.meta?.defaults?.signature_role ?? 'Direktur',
    keterangan: props.penawaran?.keterangan ?? props.meta?.defaults?.keterangan ?? '',
    tax_percent: props.penawaran?.tax_percent ?? props.meta?.defaults?.tax_percent ?? 11,
    status: props.penawaran?.status ?? props.meta?.defaults?.status ?? 'draft',
    items: initialItems,
});

const selectedCustomer = computed(() => {
    return props.meta?.customers?.find((customer) => customer.nama === form.to_company) || null;
});

const selectedMitra = computed(() => {
    return props.meta?.mitras?.find((mitra) => String(mitra.id) === String(form.mitra_id)) || null;
});

const subtotal = computed(() => {
    return form.items.reduce((sum, item) => {
        return sum + (Number(item.qty || 0) * Number(item.unit_price || 0));
    }, 0);
});

const taxAmount = computed(() => subtotal.value * (Number(form.tax_percent || 0) / 100));
const total = computed(() => subtotal.value + taxAmount.value);

function applyCustomerAddress() {
    if (selectedCustomer.value?.alamat) {
        form.to_address = selectedCustomer.value.alamat;
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
        mitra_id: data.mitra_id || null,
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
                    <InputLabel value="Nomor Penawaran" />
                    <div class="mt-2 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700">
                        {{ penawaran?.nomor || selectedMitra?.nomor_penawaran || meta?.nomor_preview || '-' }}
                    </div>
                    <p v-if="form.mitra_id && !selectedMitra?.nomor_penawaran" class="mt-2 text-xs text-rose-600">
                        Nomor penawaran mitra belum diisi di master Mitra.
                    </p>
                </div>

                <div>
                    <InputLabel for="tanggal" value="Tanggal" />
                    <TextInput id="tanggal" v-model="form.tanggal" type="date" class="mt-2 block w-full" />
                    <InputError class="mt-2" :message="form.errors.tanggal" />
                </div>

                <div>
                    <InputLabel for="mitra_id" value="Mitra" />
                    <select id="mitra_id" v-model="form.mitra_id" class="mt-2 block w-full rounded-xl border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Tanpa mitra</option>
                        <option v-for="mitra in meta?.mitras || []" :key="mitra.id" :value="mitra.id">
                            {{ mitra.nama }}
                        </option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.mitra_id" />
                </div>

                <div>
                    <InputLabel for="status" value="Status" />
                    <select id="status" v-model="form.status" class="mt-2 block w-full rounded-xl border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option v-for="option in meta?.options?.status || []" :key="option" :value="option">
                            {{ option }}
                        </option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.status" />
                </div>

                <div>
                    <InputLabel for="to_company" value="Nama Customer" />
                    <TextInput id="to_company" v-model="form.to_company" list="customer-list" class="mt-2 block w-full" />
                    <datalist id="customer-list">
                        <option v-for="customer in meta?.customers || []" :key="customer.id" :value="customer.nama" />
                    </datalist>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <button type="button" @click="applyCustomerAddress" class="rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-800">
                            Pakai alamat customer
                        </button>
                    </div>
                    <InputError class="mt-2" :message="form.errors.to_company" />
                </div>

                <div>
                    <InputLabel for="to_address" value="Alamat Customer" />
                    <textarea id="to_address" v-model="form.to_address" rows="3" class="mt-2 block w-full rounded-xl border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                    <InputError class="mt-2" :message="form.errors.to_address" />
                </div>

                <div>
                    <InputLabel for="jenis_kontrak" value="Jenis Kontrak" />
                    <select id="jenis_kontrak" v-model="form.jenis_kontrak" class="mt-2 block w-full rounded-xl border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option v-for="option in meta?.options?.jenis_kontrak || []" :key="option" :value="option">
                            {{ option }}
                        </option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.jenis_kontrak" />
                </div>

                <div>
                    <InputLabel for="signature_role" value="Role Tanda Tangan" />
                    <select id="signature_role" v-model="form.signature_role" class="mt-2 block w-full rounded-xl border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option v-for="option in meta?.options?.signature_role || []" :key="option" :value="option">
                            {{ option }}
                        </option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.signature_role" />
                </div>

                <div>
                    <InputLabel for="tax_percent" value="Tax Percent" />
                    <TextInput id="tax_percent" v-model="form.tax_percent" type="number" step="0.01" min="0" max="100" class="mt-2 block w-full" />
                    <InputError class="mt-2" :message="form.errors.tax_percent" />
                </div>
            </div>

            <div class="mt-6">
                <InputLabel for="keterangan" value="Keterangan" />
                <textarea id="keterangan" v-model="form.keterangan" rows="4" class="mt-2 block w-full rounded-xl border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                <InputError class="mt-2" :message="form.errors.keterangan" />
            </div>
        </section>

        <section class="rounded-[1.5rem] border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-slate-950">Item Penawaran</h2>
                    <p class="text-sm text-slate-500">Tambah item sebanyak yang diperlukan, lalu sistem akan hitung subtotal dan pajaknya.</p>
                </div>

                <button type="button" @click="addItem" class="rounded-full bg-slate-900 px-4 py-2 text-sm font-semibold text-white">
                    Tambah Item
                </button>
            </div>

            <div class="mt-5 space-y-4">
                <article v-for="(item, index) in form.items" :key="index" class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <div class="grid gap-4 lg:grid-cols-12">
                        <div class="lg:col-span-4">
                            <InputLabel :value="`Nama Item ${index + 1}`" />
                            <TextInput v-model="item.nama" class="mt-2 block w-full" />
                            <InputError class="mt-2" :message="form.errors[`items.${index}.nama`]" />
                        </div>

                        <div class="lg:col-span-3">
                            <InputLabel value="Rincian" />
                            <TextInput v-model="item.rincian" class="mt-2 block w-full" />
                            <InputError class="mt-2" :message="form.errors[`items.${index}.rincian`]" />
                        </div>

                        <div class="lg:col-span-1">
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

                        <div class="lg:col-span-2">
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
                    <Link :href="route('penawaran.index')" class="text-sm font-semibold text-slate-600">
                        Batal
                    </Link>
                    <PrimaryButton :disabled="form.processing">
                        {{ form.processing ? 'Menyimpan...' : 'Simpan Penawaran' }}
                    </PrimaryButton>
                </div>
            </div>
        </section>
    </form>
</template>
