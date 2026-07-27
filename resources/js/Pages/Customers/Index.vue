<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { formatDate } from '@/utils/format';

const props = defineProps({
    customers: {
        type: Array,
        default: () => [],
    },
    stats: {
        type: Object,
        default: () => ({}),
    },
});

function destroy(customer) {
    if (!confirm(`Hapus customer ${customer.nama}?`)) {
        return;
    }

    router.delete(route('customers.destroy', customer.id), {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Customers" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-[#fff2d9]">
            <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <section class="rounded-[2rem] bg-slate-950 p-6 text-white shadow-2xl shadow-slate-900/20">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <p class="text-xs uppercase tracking-[0.3em] text-blue-200/70">Master Data</p>
                            <h1 class="mt-3 text-3xl font-bold sm:text-4xl">Customers</h1>
                            <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-300">
                                Daftar pelanggan untuk dipakai di penawaran, alamat pengiriman, dan arsip dokumen.
                            </p>
                        </div>

                        <Link :href="route('customers.create')" class="rounded-full bg-blue-400 px-5 py-3 text-sm font-semibold text-slate-950">
                            Tambah Customer
                        </Link>
                    </div>

                    <div class="mt-6 grid gap-4 md:grid-cols-3">
                        <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Total</p>
                            <p class="mt-2 text-2xl font-semibold">{{ stats.total ?? 0 }}</p>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Punya email</p>
                            <p class="mt-2 text-2xl font-semibold">{{ stats.with_email ?? 0 }}</p>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Punya nomor HP</p>
                            <p class="mt-2 text-2xl font-semibold">{{ stats.with_phone ?? 0 }}</p>
                        </div>
                    </div>
                </section>

                <section class="mt-8 overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 px-6 py-4">
                        <h2 class="text-lg font-semibold text-slate-950">Daftar Customers</h2>
                    </div>

                    <div v-if="customers.length" class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-[#fff2d9]">
                                <tr class="text-left text-xs uppercase tracking-[0.2em] text-slate-500">
                                    <th class="px-6 py-4">Nama</th>
                                    <th class="px-6 py-4">Kontak</th>
                                    <th class="px-6 py-4">Alamat</th>
                                    <th class="px-6 py-4">Diperbarui</th>
                                    <th class="px-6 py-4">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="customer in customers" :key="customer.id" class="hover:bg-[#fff2d9]/60">
                                    <td class="px-6 py-4">
                                        <div class="font-semibold text-slate-950">{{ customer.nama }}</div>
                                        <div class="text-sm text-slate-500">ID #{{ customer.id }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-600">
                                        <div>{{ customer.no_hp || '-' }}</div>
                                        <div>{{ customer.email || '-' }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-600">
                                        <span class="block max-w-xl whitespace-pre-line">{{ customer.alamat || '-' }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-600">
                                        {{ formatDate(customer.updated_at) }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-wrap gap-2">
                                            <Link :href="route('customers.show', customer.id)" class="rounded-full border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700">
                                                Detail
                                            </Link>
                                            <Link :href="route('customers.edit', customer.id)" class="rounded-full border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700">
                                                Edit
                                            </Link>
                                            <button type="button" @click="destroy(customer)" class="rounded-full bg-rose-600 px-3 py-2 text-xs font-semibold text-white">
                                                Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-else class="px-6 py-16 text-center">
                        <p class="text-lg font-semibold text-slate-900">Belum ada customer</p>
                        <p class="mt-2 text-sm text-slate-500">Mulai tambahkan customer pertama untuk dipakai di penawaran.</p>
                        <Link :href="route('customers.create')" class="mt-4 inline-flex rounded-full bg-blue-600 px-4 py-2 text-sm font-semibold text-white">
                            Tambah Customer
                        </Link>
                    </div>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
