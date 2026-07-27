<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { formatDate } from '@/utils/format';

const props = defineProps({
    customer: {
        type: Object,
        required: true,
    },
});

function destroy() {
    if (!confirm(`Hapus customer ${props.customer.nama}?`)) {
        return;
    }

    router.delete(route('customers.destroy', props.customer.id));
}
</script>

<template>
    <Head :title="customer.nama" />

    <AuthenticatedLayout>
        <div class="bg-[#fff2d9]">
            <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="text-xs uppercase tracking-[0.3em] text-blue-700">Detail Customer</p>
                        <h1 class="mt-2 text-3xl font-bold text-slate-950">{{ customer.nama }}</h1>
                        <p class="mt-2 text-sm text-slate-500">Informasi master yang dipakai pada alur dokumen.</p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <Link :href="route('customers.index')" class="rounded-full border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700">
                            Kembali
                        </Link>
                        <Link :href="route('customers.edit', customer.id)" class="rounded-full border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700">
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
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Nomor ID</p>
                                <p class="mt-2 text-lg font-semibold text-slate-950">#{{ customer.id }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Email</p>
                                <p class="mt-2 text-lg font-semibold text-slate-950">{{ customer.email || '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Nomor HP</p>
                                <p class="mt-2 text-lg font-semibold text-slate-950">{{ customer.no_hp || '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Diperbarui</p>
                                <p class="mt-2 text-lg font-semibold text-slate-950">{{ formatDate(customer.updated_at) }}</p>
                            </div>
                        </div>

                        <div class="mt-6">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Alamat</p>
                            <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-700">{{ customer.alamat || '-' }}</p>
                        </div>
                    </article>

                    <article class="space-y-4 rounded-[1.5rem] border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="rounded-2xl bg-[#fff2d9] p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Dibuat</p>
                            <p class="mt-2 text-sm text-slate-700">{{ formatDate(customer.created_at) }}</p>
                        </div>

                        <div class="rounded-2xl bg-[#fff2d9] p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Pemakaian</p>
                            <p class="mt-2 text-sm text-slate-700">Customer ini bisa dipakai sebagai referensi di alur penawaran dan pengiriman dokumen.</p>
                        </div>
                    </article>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
