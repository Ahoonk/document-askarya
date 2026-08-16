<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import PenawaranForm from './Partials/Form.vue';

const props = defineProps({
    meta: {
        type: Object,
        default: () => ({}),
    },
});
</script>

<template>
    <Head title="Tambah Penawaran" />

    <AuthenticatedLayout theme="login">
        <div class="relative min-h-screen overflow-hidden bg-[#08111f] text-slate-100">
            <div class="pointer-events-none absolute inset-0">
                <div class="absolute left-[-8rem] top-[-8rem] h-96 w-96 rounded-full bg-red-500/20 blur-3xl"></div>
                <div class="absolute right-[-7rem] top-24 h-[30rem] w-[30rem] rounded-full bg-blue-500/20 blur-3xl"></div>
                <div class="absolute bottom-[-8rem] left-1/3 h-80 w-80 rounded-full bg-amber-400/10 blur-3xl"></div>
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(255,255,255,0.08),_transparent_35%),linear-gradient(135deg,_rgba(8,17,31,0.94),_rgba(9,14,27,0.98))]"></div>
            </div>

            <div class="relative mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <div class="mb-6">
                    <p class="text-xs uppercase tracking-[0.3em] text-sky-300">Surat Penawaran</p>
                    <h1 class="mt-2 text-3xl font-bold text-white">Tambah Penawaran Baru</h1>
                    <p class="mt-2 max-w-2xl text-sm text-slate-300">Isi data penawaran dalam satu panel gelap yang rapi, dengan aksen biru dan merah seperti dashboard.</p>
                </div>

                <PenawaranForm
                    :action="route('penawaran.store')"
                    method="post"
                    :meta="props.meta"
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
