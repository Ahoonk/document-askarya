<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import PenawaranForm from './Partials/Form.vue';

defineProps({
    penawaran: {
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
    <Head title="Edit Penawaran" />

    <AuthenticatedLayout>
        <div class="bg-[#fff2d9]">
            <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <div class="mb-6">
                    <p class="text-xs uppercase tracking-[0.3em] text-blue-700">Surat Penawaran</p>
                    <h1 class="mt-2 text-3xl font-bold text-slate-950">Edit Penawaran</h1>
                </div>

                <PenawaranForm
                    :action="route('penawaran.update', penawaran.id)"
                    method="put"
                    :penawaran="penawaran"
                    :meta="meta"
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
