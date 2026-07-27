<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { formatDate } from '@/utils/format';

const props = defineProps({
    templates: {
        type: Array,
        default: () => [],
    },
    stats: {
        type: Object,
        default: () => ({}),
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

function destroy(template) {
    if (!confirm(`Hapus template ${template.name}?`)) {
        return;
    }

    router.delete(route('document-templates.destroy', template.id), {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Document Templates" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-[#fff2d9]">
            <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <section class="rounded-[2rem] bg-slate-950 p-6 text-white shadow-2xl shadow-slate-900/20">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <p class="text-xs uppercase tracking-[0.3em] text-blue-200/70">Layout System</p>
                            <h1 class="mt-3 text-3xl font-bold sm:text-4xl">Document Templates</h1>
                            <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-300">
                                Template file dan path view yang dipakai resolver untuk menampilkan dokumen per perusahaan.
                            </p>
                        </div>

                        <Link :href="route('document-templates.create')" class="rounded-full bg-blue-400 px-5 py-3 text-sm font-semibold text-slate-950">
                            Tambah Template
                        </Link>
                    </div>

                    <div class="mt-6 grid gap-4 md:grid-cols-3">
                        <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Total</p>
                            <p class="mt-2 text-2xl font-semibold">{{ stats.total ?? 0 }}</p>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Default</p>
                            <p class="mt-2 text-2xl font-semibold">{{ stats.default ?? 0 }}</p>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Tipe unik</p>
                            <p class="mt-2 text-2xl font-semibold">{{ stats.document_types ?? 0 }}</p>
                        </div>
                    </div>
                </section>

                <section class="mt-8 overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 px-6 py-4">
                        <h2 class="text-lg font-semibold text-slate-950">Daftar Template</h2>
                    </div>

                    <div v-if="templates.length" class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-[#fff2d9]">
                                <tr class="text-left text-xs uppercase tracking-[0.2em] text-slate-500">
                                    <th class="px-6 py-4">Nama</th>
                                    <th class="px-6 py-4">Tipe</th>
                                    <th class="px-6 py-4">Path</th>
                                    <th class="px-6 py-4">Default</th>
                                    <th class="px-6 py-4">Diperbarui</th>
                                    <th class="px-6 py-4">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="template in templates" :key="template.id" class="hover:bg-[#fff2d9]/60">
                                    <td class="px-6 py-4">
                                        <div class="font-semibold text-slate-950">{{ template.name }}</div>
                                        <div class="text-sm text-slate-500">ID #{{ template.id }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-600">
                                        {{ typeLabels[template.document_type] || template.document_type }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-600">
                                        <div class="max-w-xl break-all">{{ template.file_path || '-' }}</div>
                                        <a
                                            v-if="template.storage_url"
                                            :href="template.storage_url"
                                            target="_blank"
                                            rel="noreferrer"
                                            class="mt-2 inline-flex text-xs font-semibold text-blue-700"
                                        >
                                            Buka file
                                        </a>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span
                                            class="rounded-full px-3 py-1 text-xs font-semibold"
                                            :class="template.is_default ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700'"
                                        >
                                            {{ template.is_default ? 'Default' : 'Non-default' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-600">
                                        {{ formatDate(template.updated_at) }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-wrap gap-2">
                                            <Link :href="route('document-templates.show', template.id)" class="rounded-full border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700">
                                                Detail
                                            </Link>
                                            <Link :href="route('document-templates.edit', template.id)" class="rounded-full border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700">
                                                Edit
                                            </Link>
                                            <button type="button" @click="destroy(template)" class="rounded-full bg-rose-600 px-3 py-2 text-xs font-semibold text-white">
                                                Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-else class="px-6 py-16 text-center">
                        <p class="text-lg font-semibold text-slate-900">Belum ada template</p>
                        <p class="mt-2 text-sm text-slate-500">Tambahkan template untuk menghubungkan resolver dokumen dengan file atau view yang sesuai.</p>
                        <Link :href="route('document-templates.create')" class="mt-4 inline-flex rounded-full bg-blue-600 px-4 py-2 text-sm font-semibold text-white">
                            Tambah Template
                        </Link>
                    </div>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
