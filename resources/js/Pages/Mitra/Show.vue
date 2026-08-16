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

    <AuthenticatedLayout theme="login">
        <div class="relative min-h-screen overflow-hidden bg-[#08111f] text-slate-100">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(59,130,246,0.14),transparent_30%),radial-gradient(circle_at_top_right,rgba(239,68,68,0.12),transparent_28%),linear-gradient(180deg,rgba(8,17,31,0.98),rgba(8,17,31,1))]"></div>
            <div class="absolute left-[-6rem] top-24 h-72 w-72 rounded-full bg-blue-500/20 blur-3xl"></div>
            <div class="absolute right-[-5rem] top-40 h-80 w-80 rounded-full bg-rose-500/15 blur-3xl"></div>

            <div class="relative mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="text-xs uppercase tracking-[0.3em] text-sky-300">Detail Mitra</p>
                        <h1 class="mt-2 text-3xl font-bold text-white">{{ mitra.nama }}</h1>
                        <p class="mt-2 text-sm text-slate-300">Data partner yang bisa mengatur nomor dan template per dokumen.</p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <Link :href="route('mitra.index')" class="rounded-full border border-white/10 bg-white/5 px-4 py-2 text-sm font-semibold text-slate-100">
                            Kembali
                        </Link>
                        <Link :href="route('mitra.edit', mitra.id)" class="rounded-full border border-white/10 bg-white/5 px-4 py-2 text-sm font-semibold text-slate-100">
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
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Email</p>
                                <p class="mt-2 text-lg font-semibold text-white">{{ mitra.email || '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Diperbarui</p>
                                <p class="mt-2 text-lg font-semibold text-white">{{ formatDate(mitra.updated_at) }}</p>
                            </div>
                        </div>

                        <div class="mt-6">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Alamat</p>
                            <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-300">{{ mitra.alamat || '-' }}</p>
                        </div>

                        <div class="mt-6 grid gap-4 md:grid-cols-2">
                            <div class="rounded-2xl border border-white/10 bg-slate-950/40 p-4">
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Nomor Penawaran</p>
                                <p class="mt-2 text-sm text-slate-300">{{ mitra.nomor_penawaran || '-' }}</p>
                            </div>
                            <div class="rounded-2xl border border-white/10 bg-slate-950/40 p-4">
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Nomor Invoice</p>
                                <p class="mt-2 text-sm text-slate-300">{{ mitra.nomor_invoice || '-' }}</p>
                            </div>
                            <div class="rounded-2xl border border-white/10 bg-slate-950/40 p-4">
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Nomor Surat Jalan</p>
                                <p class="mt-2 text-sm text-slate-300">{{ mitra.nomor_surat_jalan || '-' }}</p>
                            </div>
                            <div class="rounded-2xl border border-white/10 bg-slate-950/40 p-4">
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Nomor Berita Acara</p>
                                <p class="mt-2 text-sm text-slate-300">{{ mitra.nomor_berita_acara || '-' }}</p>
                            </div>
                        </div>
                    </article>

                    <article class="space-y-4 rounded-[1.5rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-slate-950/30 backdrop-blur">
                        <div class="rounded-2xl border border-white/10 bg-slate-950/40 p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Template Penawaran</p>
                            <p class="mt-2 break-all text-sm text-slate-300">{{ mitra.template_penawaran_path || '-' }}</p>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-slate-950/40 p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Template Invoice</p>
                            <p class="mt-2 break-all text-sm text-slate-300">{{ mitra.template_invoice_path || '-' }}</p>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-slate-950/40 p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Template Surat Jalan</p>
                            <p class="mt-2 break-all text-sm text-slate-300">{{ mitra.template_surat_jalan_path || '-' }}</p>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-slate-950/40 p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Template Berita Acara</p>
                            <p class="mt-2 break-all text-sm text-slate-300">{{ mitra.template_berita_acara_path || '-' }}</p>
                        </div>
                    </article>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
