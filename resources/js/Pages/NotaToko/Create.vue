<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import NotaTokoForm from './Partials/Form.vue';

defineProps({
    meta: {
        type: Object,
        default: () => ({}),
    },
});
</script>

<template>
    <Head title="Tambah Nota Toko" />

    <AuthenticatedLayout>
        <div class="bg-[#fff2d9]">
            <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <div class="mb-6">
                    <p class="text-xs uppercase tracking-[0.3em] text-blue-700">Retail</p>
                    <h1 class="mt-2 text-3xl font-bold text-slate-950">Tambah Nota Toko</h1>
                    <p class="mt-2 text-sm text-slate-500">Buat nota retail dengan item, pajak, dan status pembayaran.</p>
                </div>

                <NotaTokoForm
                    :action="route('nota-toko.store')"
                    method="post"
                    :meta="meta"
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
