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

    <AuthenticatedLayout>
        <div class="bg-[#fff2d9]">
            <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="text-xs uppercase tracking-[0.3em] text-blue-700">Detail Template</p>
                        <h1 class="mt-2 text-3xl font-bold text-slate-950">{{ template.name }}</h1>
                        <p class="mt-2 text-sm text-slate-500">Template ini dipakai oleh resolver dokumen sesuai tipe yang dipilih.</p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <Link :href="route('document-templates.index')" class="rounded-full border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700">
                            Kembali
                        </Link>
                        <Link :href="route('document-templates.edit', template.id)" class="rounded-full border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700">
                            Edit
                        </Link>
                        <button @click="destroy" type="button" class="rounded-full bg-rose-600 px-4 py-2 text-sm font-semibold text-white">
                            Hapus
                        </button>
                    </div>
                </div>

                <section class="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
                    <article class="rounded-[1.5rem] border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Tipe Dokumen</p>
                                <p class="mt-2 text-lg font-semibold text-slate-950">{{ typeLabels[template.document_type] || template.document_type }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Default</p>
                                <p class="mt-2 text-lg font-semibold text-slate-950">{{ template.is_default ? 'Ya' : 'Tidak' }}</p>
                            </div>
                            <div class="md:col-span-2">
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Path</p>
                                <p class="mt-2 break-all text-sm leading-6 text-slate-700">{{ template.file_path || '-' }}</p>
                            </div>
                        </div>
                    </article>

                    <article class="space-y-4 rounded-[1.5rem] border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="rounded-2xl bg-[#fff2d9] p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Diperbarui</p>
                            <p class="mt-2 text-sm text-slate-700">{{ formatDate(template.updated_at) }}</p>
                        </div>

                        <div class="rounded-2xl bg-[#fff2d9] p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Pratinjau</p>
                            <p class="mt-2 text-sm text-slate-700">
                                Jika path menunjuk ke file yang ada di storage public, resolver akan memakainya langsung. Jika mengarah ke view, resolver akan mencoba render view tersebut.
                            </p>
                        </div>

                        <div v-if="template.storage_url" class="rounded-2xl bg-[#fff2d9] p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">File</p>
                            <a :href="template.storage_url" target="_blank" rel="noreferrer" class="mt-2 inline-flex text-sm font-semibold text-blue-700">
                                Buka file template
                            </a>
                        </div>
                    </article>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
