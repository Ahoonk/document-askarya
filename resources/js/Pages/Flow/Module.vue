<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    title: {
        type: String,
        required: true,
    },
    summary: {
        type: String,
        required: true,
    },
    stage: {
        type: String,
        default: 'Module',
    },
    module_key: {
        type: String,
        default: '',
    },
    mode: {
        type: String,
        default: 'index',
    },
    record_id: {
        type: [String, Number],
        default: null,
    },
    primary_route: {
        type: String,
        default: null,
    },
    secondary_route: {
        type: String,
        default: null,
    },
});

const modeLabel = {
    index: 'Daftar',
    create: 'Tambah',
    show: 'Detail',
    edit: 'Ubah',
}[props.mode] || props.mode;

const nextSteps = [
    'Tambahkan model dan migration sesuai kebutuhan data.',
    'Hubungkan form Vue ke controller Laravel.',
    'Bangun alur dokumen bertahap dari penawaran ke invoice.',
];
</script>

<template>
    <Head :title="`${title} - Dokumen Askarya`" />

    <AuthenticatedLayout>
        <div class="relative min-h-[calc(100vh-4rem)] overflow-hidden bg-slate-950 text-white">
            <div class="pointer-events-none absolute inset-0">
                <div class="absolute left-[-8rem] top-[-8rem] h-80 w-80 rounded-full bg-blue-400/15 blur-3xl"></div>
                <div class="absolute right-[-8rem] top-32 h-96 w-96 rounded-full bg-red-400/10 blur-3xl"></div>
            </div>

            <div class="relative mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
                <section class="overflow-hidden rounded-[2rem] border border-white/10 bg-white/5 shadow-2xl shadow-slate-950/40 backdrop-blur">
                    <div class="grid gap-6 p-6 lg:grid-cols-[1.1fr_0.9fr] lg:p-10">
                        <div>
                            <p class="text-xs uppercase tracking-[0.32em] text-blue-200/70">
                                {{ stage }} - {{ modeLabel }}
                            </p>
                            <h1 class="mt-3 text-4xl font-bold leading-tight text-white sm:text-5xl">
                                {{ title }}
                            </h1>
                            <p class="mt-4 max-w-2xl text-sm leading-7 text-slate-300 sm:text-base">
                                {{ summary }}
                            </p>

                            <div class="mt-6 flex flex-wrap gap-3">
                                <Link v-if="primary_route" :href="route(primary_route)" class="inline-flex items-center rounded-full bg-blue-400 px-5 py-3 text-sm font-semibold text-slate-950 transition hover:bg-blue-300">
                                    Ke Modul Utama
                                </Link>
                                <Link v-if="secondary_route" :href="route(secondary_route)" class="inline-flex items-center rounded-full border border-white/15 bg-white/5 px-5 py-3 text-sm font-semibold text-white transition hover:bg-white/10">
                                    Aksi Sekunder
                                </Link>
                                <span class="inline-flex items-center rounded-full border border-white/10 bg-white/5 px-4 py-2 text-sm text-slate-200">
                                    Kode modul: {{ module_key }}
                                </span>
                            </div>
                        </div>

                        <div class="rounded-[1.75rem] border border-white/10 bg-slate-900/70 p-5">
                            <p class="text-xs uppercase tracking-[0.25em] text-blue-200/70">Rencana Isi</p>
                            <div class="mt-4 space-y-3">
                                <div v-for="(step, index) in nextSteps" :key="step" class="rounded-2xl border border-white/10 bg-white/5 p-4">
                                    <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Langkah {{ index + 1 }}</p>
                                    <p class="mt-2 text-sm leading-6 text-slate-200">{{ step }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="mt-8 grid gap-4 md:grid-cols-3">
                    <article class="rounded-[1.5rem] border border-white/10 bg-white/5 p-5">
                        <p class="text-xs uppercase tracking-[0.24em] text-slate-400">Mode</p>
                        <p class="mt-3 text-2xl font-semibold text-white">{{ modeLabel }}</p>
                    </article>
                    <article class="rounded-[1.5rem] border border-white/10 bg-white/5 p-5">
                        <p class="text-xs uppercase tracking-[0.24em] text-slate-400">Target</p>
                        <p class="mt-3 text-2xl font-semibold text-white">Laravel + Vue</p>
                    </article>
                    <article class="rounded-[1.5rem] border border-white/10 bg-white/5 p-5">
                        <p class="text-xs uppercase tracking-[0.24em] text-slate-400">Status</p>
                        <p class="mt-3 text-2xl font-semibold text-white">{{ record_id ? `Record ${record_id}` : 'Blueprint' }}</p>
                    </article>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
