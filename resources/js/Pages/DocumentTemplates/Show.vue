<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { formatDate } from '@/utils/format';

const props = defineProps({
    template: {
        type: Object,
        required: true,
    },
    options: {
        type: Object,
        default: () => ({}),
    },
});

const typeLabels = {
    penawaran: 'Penawaran',
    invoice: 'Invoice',
    surat_jalan: 'Surat Jalan',
    berita_acara: 'Berita Acara',
    faktur_pajak: 'Faktur Pajak',
    nota_toko: 'Nota Toko',
};

function destroy() {
    if (!confirm(`Hapus template ${props.template.name}?`)) {
        return;
    }

    router.delete(route('document-templates.destroy', props.template.id));
}
</script>

<template>
    <Head :title="template.name" />

    <AuthenticatedLayout theme="login">
        <div class="relative min-h-screen overflow-hidden bg-[#08111f] text-slate-100">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(59,130,246,0.14),transparent_30%),radial-gradient(circle_at_top_right,rgba(239,68,68,0.12),transparent_28%),linear-gradient(180deg,rgba(8,17,31,0.98),rgba(8,17,31,1))]"></div>
            <div class="absolute left-[-6rem] top-24 h-72 w-72 rounded-full bg-blue-500/20 blur-3xl"></div>
            <div class="absolute right-[-5rem] top-40 h-80 w-80 rounded-full bg-rose-500/15 blur-3xl"></div>

            <div class="relative mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="text-xs uppercase tracking-[0.3em] text-sky-300">Detail Template</p>
                        <h1 class="mt-2 text-3xl font-bold text-white">{{ template.name }}</h1>
                        <p class="mt-2 text-sm text-slate-300">Template ini dipakai oleh resolver dokumen sesuai tipe yang dipilih.</p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <Link :href="route('document-templates.index')" class="rounded-full border border-white/10 bg-white/5 px-4 py-2 text-sm font-semibold text-slate-100">
                            Kembali
                        </Link>
                        <Link :href="route('document-templates.edit', template.id)" class="rounded-full border border-white/10 bg-white/5 px-4 py-2 text-sm font-semibold text-slate-100">
                            Edit
                        </Link>
                        <button @click="destroy" type="button" class="rounded-full bg-gradient-to-r from-blue-600 via-indigo-600 to-rose-600 px-4 py-2 text-sm font-semibold text-white">
                            Hapus
                        </button>
                    </div>
                </div>

                <section class="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
                    <article class="rounded-[1.5rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-slate-950/30 backdrop-blur">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Tipe Dokumen</p>
                                <p class="mt-2 text-lg font-semibold text-white">{{ typeLabels[template.document_type] || template.document_type }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Default</p>
                                <p class="mt-2 text-lg font-semibold text-white">{{ template.is_default ? 'Ya' : 'Tidak' }}</p>
                            </div>
                            <div class="md:col-span-2">
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Path</p>
                                <p class="mt-2 break-all text-sm leading-6 text-slate-300">{{ template.file_path || '-' }}</p>
                            </div>
                        </div>
                    </article>

                    <article class="space-y-4 rounded-[1.5rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-slate-950/30 backdrop-blur">
                        <div class="rounded-2xl border border-white/10 bg-slate-950/40 p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Diperbarui</p>
                            <p class="mt-2 text-sm text-slate-300">{{ formatDate(template.updated_at) }}</p>
                        </div>

                        <div class="rounded-2xl border border-white/10 bg-slate-950/40 p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Pratinjau</p>
                            <p class="mt-2 text-sm text-slate-300">
                                Jika path menunjuk ke file yang ada di storage public, resolver akan memakainya langsung. Jika mengarah ke view, resolver akan mencoba render view tersebut.
                            </p>
                        </div>

                        <div v-if="template.storage_url" class="rounded-2xl border border-white/10 bg-slate-950/40 p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">File</p>
                            <a :href="template.storage_url" target="_blank" rel="noreferrer" class="mt-2 inline-flex text-sm font-semibold text-sky-300">
                                Buka file template
                            </a>
                        </div>
                    </article>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
