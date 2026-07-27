<script setup>
import { useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { formatCurrency, formatDate } from '@/utils/format';

const props = defineProps({
    penawaran: {
        type: Object,
        required: true,
    },
});

const today = new Date().toISOString().slice(0, 10);

const form = useForm({
    penawaran_id: props.penawaran.id,
    nomor_po: props.penawaran?.purchasing_order?.nomor_po ?? '',
    tanggal_po: today,
    dokumen: null,
});

function submit() {
    form.post(route('purchasing-order.store'), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => form.reset('dokumen'),
    });
}
</script>

<template>
    <article class="rounded-[1.5rem] border border-slate-200 bg-slate-50 p-5 shadow-sm">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-xs uppercase tracking-[0.22em] text-blue-700">Penawaran #{{ penawaran.id }}</p>
                <h3 class="mt-2 text-xl font-semibold text-slate-950">{{ penawaran.nomor }}</h3>
                <p class="mt-1 text-sm text-slate-500">{{ penawaran.to_company }} · {{ formatDate(penawaran.tanggal) }}</p>
            </div>

            <div class="rounded-full bg-slate-900 px-3 py-1 text-xs font-semibold text-white">
                {{ formatCurrency(penawaran.total) }}
            </div>
        </div>

        <div class="mt-5 grid gap-4 lg:grid-cols-[1fr_1fr]">
            <div>
                <InputLabel value="Nomor PO" />
                <TextInput v-model="form.nomor_po" class="mt-2 block w-full" />
                <InputError class="mt-2" :message="form.errors.nomor_po" />
            </div>

            <div>
                <InputLabel value="Tanggal PO" />
                <TextInput v-model="form.tanggal_po" type="date" class="mt-2 block w-full" />
                <InputError class="mt-2" :message="form.errors.tanggal_po" />
            </div>
        </div>

        <div class="mt-4">
            <InputLabel value="Dokumen" />
            <input
                type="file"
                accept=".pdf,.jpg,.jpeg,.png"
                class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 shadow-sm"
                @change="(event) => { form.dokumen = event.target.files?.[0] ?? null }"
            >
            <InputError class="mt-2" :message="form.errors.dokumen" />
        </div>

        <form class="mt-5" @submit.prevent="submit">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <p class="text-sm text-slate-600">
                    {{ penawaran.items?.length || 0 }} item penawaran
                </p>

                <PrimaryButton type="submit" :disabled="form.processing">
                    {{ form.processing ? 'Mengunggah...' : 'Upload PO' }}
                </PrimaryButton>
            </div>
        </form>
    </article>
</template>
