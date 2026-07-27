<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import BeritaAcaraForm from './Partials/Form.vue';

defineProps({
    beritaAcara: {
        type: Object,
        required: true,
    },
    meta: {
        type: Object,
        default: () => ({}),
    },
});
</script>

<template>
    <Head :title="`Edit Berita Acara - ${beritaAcara.nomor}`" />

    <AuthenticatedLayout>
        <div class="bg-[#fff2d9]">
            <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <div class="mb-6">
                    <p class="text-xs uppercase tracking-[0.3em] text-blue-700">Closing</p>
                    <h1 class="mt-2 text-3xl font-bold text-slate-950">Edit Berita Acara</h1>
                    <p class="mt-2 text-sm text-slate-500">Perbarui isi berita acara tanpa mengubah invoice yang sudah terikat.</p>
                </div>

                <BeritaAcaraForm
                    :action="route('berita-acara.update', beritaAcara.id)"
                    method="put"
                    :beritaAcara="beritaAcara"
                    :meta="meta"
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
