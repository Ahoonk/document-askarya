<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import CustomerForm from './Partials/Form.vue';

defineProps({
    customer: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <Head :title="`Edit Customer - ${customer.nama}`" />

    <AuthenticatedLayout>
        <div class="bg-[#fff2d9]">
            <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <div class="mb-6">
                    <p class="text-xs uppercase tracking-[0.3em] text-blue-700">Master Data</p>
                    <h1 class="mt-2 text-3xl font-bold text-slate-950">Edit Customer</h1>
                    <p class="mt-2 text-sm text-slate-500">Perbarui informasi customer jika ada perubahan kontak atau alamat.</p>
                </div>

                <CustomerForm
                    :action="route('customers.update', customer.id)"
                    method="put"
                    :customer="customer"
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
