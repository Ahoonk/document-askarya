<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { formatDate } from '@/utils/format';

const props = defineProps({
    mitra: {
        type: Object,
        required: true,
    },
});

function destroy() {
    if (!confirm(`Hapus mitra ${props.mitra.nama}?`)) {
        return;
    }

    router.delete(route('mitra.destroy', props.mitra.id));
}
</script>

<template>
    <Head :title="mitra.nama" />

    <AuthenticatedLayout>
        <div class="bg-[#fff2d9]">
            <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="text-xs uppercase tracking-[0.3em] text-blue-700">Detail Mitra</p>
                        <h1 class="mt-2 text-3xl font-bold text-slate-950">{{ mitra.nama }}</h1>
                        <p class="mt-2 text-sm text-slate-500">Data partner yang bisa mengatur nomor dan template per dokumen.</p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <Link :href="route('mitra.index')" class="rounded-full border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700">
                            Kembali
                        </Link>
                        <Link :href="route('mitra.edit', mitra.id)" class="rounded-full border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700">
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
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Email</p>
                                <p class="mt-2 text-lg font-semibold text-slate-950">{{ mitra.email || '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Diperbarui</p>
                                <p class="mt-2 text-lg font-semibold text-slate-950">{{ formatDate(mitra.updated_at) }}</p>
                            </div>
                        </div>

                        <div class="mt-6">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Alamat</p>
                            <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-700">{{ mitra.alamat || '-' }}</p>
                        </div>

                        <div class="mt-6 grid gap-4 md:grid-cols-2">
                            <div class="rounded-2xl bg-[#fff2d9] p-4">
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Nomor Penawaran</p>
                                <p class="mt-2 text-sm text-slate-700">{{ mitra.nomor_penawaran || '-' }}</p>
                            </div>
                            <div class="rounded-2xl bg-[#fff2d9] p-4">
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Nomor Invoice</p>
                                <p class="mt-2 text-sm text-slate-700">{{ mitra.nomor_invoice || '-' }}</p>
                            </div>
                            <div class="rounded-2xl bg-[#fff2d9] p-4">
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Nomor Surat Jalan</p>
                                <p class="mt-2 text-sm text-slate-700">{{ mitra.nomor_surat_jalan || '-' }}</p>
                            </div>
                            <div class="rounded-2xl bg-[#fff2d9] p-4">
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Nomor Berita Acara</p>
                                <p class="mt-2 text-sm text-slate-700">{{ mitra.nomor_berita_acara || '-' }}</p>
                            </div>
                        </div>
                    </article>

                    <article class="space-y-4 rounded-[1.5rem] border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="rounded-2xl bg-[#fff2d9] p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Template Penawaran</p>
                            <p class="mt-2 break-all text-sm text-slate-700">{{ mitra.template_penawaran_path || '-' }}</p>
                        </div>
                        <div class="rounded-2xl bg-[#fff2d9] p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Template Invoice</p>
                            <p class="mt-2 break-all text-sm text-slate-700">{{ mitra.template_invoice_path || '-' }}</p>
                        </div>
                        <div class="rounded-2xl bg-[#fff2d9] p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Template Surat Jalan</p>
                            <p class="mt-2 break-all text-sm text-slate-700">{{ mitra.template_surat_jalan_path || '-' }}</p>
                        </div>
                        <div class="rounded-2xl bg-[#fff2d9] p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Template Berita Acara</p>
                            <p class="mt-2 break-all text-sm text-slate-700">{{ mitra.template_berita_acara_path || '-' }}</p>
                        </div>
                    </article>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
