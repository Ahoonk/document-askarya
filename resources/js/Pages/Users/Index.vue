<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { formatDate } from '@/utils/format';

const props = defineProps({
    users: {
        type: Array,
        default: () => [],
    },
    stats: {
        type: Object,
        default: () => ({}),
    },
    current_user_id: {
        type: [String, Number],
        default: null,
    },
});

const roleLabels = {
    superadmin: 'Superadmin',
    admin: 'Admin',
};

const roleStyles = {
    superadmin: 'bg-emerald-100 text-emerald-800',
    admin: 'bg-blue-100 text-blue-800',
};

function destroy(user) {
    if (!confirm(`Hapus user ${user.name}?`)) {
        return;
    }

    router.delete(route('users.destroy', user.id), {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Users" />

    <AuthenticatedLayout theme="login">
        <div class="relative min-h-screen overflow-hidden bg-[#08111f] text-slate-100">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(59,130,246,0.14),transparent_30%),radial-gradient(circle_at_top_right,rgba(239,68,68,0.12),transparent_28%),linear-gradient(180deg,rgba(8,17,31,0.98),rgba(8,17,31,1))]"></div>
            <div class="absolute left-[-6rem] top-24 h-72 w-72 rounded-full bg-blue-500/20 blur-3xl"></div>
            <div class="absolute right-[-5rem] top-40 h-80 w-80 rounded-full bg-rose-500/15 blur-3xl"></div>

            <div class="relative mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <section class="rounded-[2rem] bg-slate-950 p-6 text-white shadow-2xl shadow-slate-900/20">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <p class="text-xs uppercase tracking-[0.3em] text-blue-200/70">Access Control</p>
                            <h1 class="mt-3 text-3xl font-bold sm:text-4xl">Users</h1>
                            <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-300">
                                Pengelolaan akun admin dan superadmin. Halaman ini hanya bisa dibuka oleh akun superadmin.
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-3">
                            <Link :href="route('users.create')" class="rounded-full bg-blue-400 px-5 py-3 text-sm font-semibold text-slate-950">
                                Tambah User
                            </Link>
                            <Link :href="route('profile.edit')" class="rounded-full border border-white/10 bg-white/5 px-5 py-3 text-sm font-semibold text-white transition hover:bg-white/10">
                                Profil Saya
                            </Link>
                        </div>
                    </div>

                    <div class="mt-6 grid gap-4 md:grid-cols-3">
                        <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Total Akun</p>
                            <p class="mt-2 text-2xl font-semibold">{{ stats.total ?? 0 }}</p>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Superadmin</p>
                            <p class="mt-2 text-2xl font-semibold">{{ stats.superadmin ?? 0 }}</p>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Admin</p>
                            <p class="mt-2 text-2xl font-semibold">{{ stats.admin ?? 0 }}</p>
                        </div>
                    </div>
                </section>

                <section class="mt-8 overflow-hidden rounded-[1.75rem] border border-white/10 bg-slate-950/80 shadow-2xl shadow-slate-950/30 backdrop-blur">
                    <div class="border-b border-white/10 px-6 py-4">
                        <h2 class="text-lg font-semibold text-white">Daftar Users</h2>
                    </div>

                    <div v-if="users.length" class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-white/10">
                            <thead class="bg-white/5">
                                <tr class="text-left text-xs uppercase tracking-[0.2em] text-slate-400">
                                    <th class="px-6 py-4">Nama</th>
                                    <th class="px-6 py-4">Email</th>
                                    <th class="px-6 py-4">Perusahaan</th>
                                    <th class="px-6 py-4">Role</th>
                                    <th class="px-6 py-4">Diperbarui</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/10">
                                <tr
                                    v-for="user in users"
                                    :key="user.id"
                                    class="hover:bg-white/5"
                                    :class="user.id === current_user_id ? 'bg-emerald-500/10' : ''"
                                >
                                    <td class="px-6 py-4">
                                        <div class="font-semibold text-white">
                                            {{ user.name }}
                                            <span v-if="user.id === current_user_id" class="ml-2 rounded-full bg-emerald-500/20 px-2 py-1 text-[10px] font-semibold uppercase tracking-[0.18em] text-emerald-200">
                                                Aktif
                                            </span>
                                        </div>
                                        <div class="text-sm text-slate-400">ID #{{ user.id }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-300">
                                        {{ user.email }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-300">
                                        {{ user.company?.name || '-' }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span
                                            class="rounded-full px-3 py-1 text-xs font-semibold"
                                            :class="roleStyles[user.role] || 'bg-white/10 text-slate-200'"
                                        >
                                            {{ roleLabels[user.role] || user.role }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-300">
                                        {{ formatDate(user.updated_at) }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-wrap gap-2">
                                            <Link :href="route('users.edit', user.id)" class="rounded-full border border-white/10 bg-white/5 px-3 py-2 text-xs font-semibold text-slate-100">
                                                Edit
                                            </Link>
                                            <button
                                                type="button"
                                                @click="destroy(user)"
                                                :disabled="user.id === current_user_id"
                                                class="rounded-full px-3 py-2 text-xs font-semibold text-white"
                                                :class="user.id === current_user_id ? 'cursor-not-allowed bg-white/10 text-slate-400' : 'bg-gradient-to-r from-blue-600 via-indigo-600 to-rose-600'"
                                            >
                                                Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-else class="px-6 py-16 text-center">
                        <p class="text-lg font-semibold text-white">Belum ada user</p>
                        <p class="mt-2 text-sm text-slate-300">Tambahkan akun admin atau superadmin terlebih dahulu.</p>
                    </div>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
