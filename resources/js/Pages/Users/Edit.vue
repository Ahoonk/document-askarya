<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import UserForm from './Partials/Form.vue';

defineProps({
    user: {
        type: Object,
        required: true,
    },
    companies: {
        type: Array,
        default: () => [],
    },
    options: {
        type: Object,
        default: () => ({}),
    },
});
</script>

<template>
    <Head :title="`Edit User - ${user.name}`" />

    <AuthenticatedLayout>
        <div class="bg-[#fff2d9]">
            <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <div class="mb-6">
                    <p class="text-xs uppercase tracking-[0.3em] text-blue-700">Access Control</p>
                    <h1 class="mt-2 text-3xl font-bold text-slate-950">Edit User</h1>
                    <p class="mt-2 text-sm text-slate-500">Perbarui identitas, company, role, atau password user.</p>
                </div>

                <UserForm
                    :action="route('users.update', user.id)"
                    method="put"
                    :user="user"
                    :companies="companies"
                    :options="options"
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
