<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import BeritaAcaraForm from './Partials/Form.vue';

defineProps({
    availableInvoices: {
        type: Array,
        default: () => [],
    },
    meta: {
        type: Object,
        default: () => ({}),
    },
});
</script>

<template>
    <Head title="Tambah Berita Acara" />

    <AuthenticatedLayout theme="login">
        <div class="relative min-h-screen overflow-hidden bg-[#08111f] text-slate-100">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(59,130,246,0.14),transparent_30%),radial-gradient(circle_at_top_right,rgba(239,68,68,0.12),transparent_28%),linear-gradient(180deg,rgba(8,17,31,0.98),rgba(8,17,31,1))]"></div>
            <div class="absolute left-[-6rem] top-24 h-72 w-72 rounded-full bg-blue-500/20 blur-3xl"></div>
            <div class="absolute right-[-5rem] top-40 h-80 w-80 rounded-full bg-rose-500/15 blur-3xl"></div>

            <div class="relative mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <div class="mb-6">
                    <p class="text-xs uppercase tracking-[0.3em] text-sky-300">Closing</p>
                    <h1 class="mt-2 text-3xl font-bold text-white">Tambah Berita Acara</h1>
                    <p class="mt-2 text-sm text-slate-300">Pilih invoice yang sudah tersedia lalu lengkapi keterangan penutup.</p>
                </div>

                <BeritaAcaraForm
                    :action="route('berita-acara.store')"
                    method="post"
                    :availableInvoices="availableInvoices"
                    :meta="meta"
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
