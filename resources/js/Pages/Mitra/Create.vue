<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import MitraForm from './Partials/Form.vue';
</script>

<template>
    <Head title="Tambah Mitra" />

    <AuthenticatedLayout>
        <div class="bg-[#fff2d9]">
            <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <div class="mb-6">
                    <p class="text-xs uppercase tracking-[0.3em] text-blue-700">Master Data</p>
                    <h1 class="mt-2 text-3xl font-bold text-slate-950">Tambah Mitra Baru</h1>
                    <p class="mt-2 text-sm text-slate-500">Lengkapi data mitra agar bisa dipakai di alur dokumen dan override nomor.</p>
                </div>

                <MitraForm
                    :action="route('mitra.store')"
                    method="post"
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
