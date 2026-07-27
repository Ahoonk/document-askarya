<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import DocumentTemplateForm from './Partials/Form.vue';

defineProps({
    template: {
        type: Object,
        required: true,
    },
    options: {
        type: Object,
        default: () => ({}),
    },
});
</script>

<template>
    <Head :title="`Edit Template - ${template.name}`" />

    <AuthenticatedLayout>
        <div class="bg-[#fff2d9]">
            <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <div class="mb-6">
                    <p class="text-xs uppercase tracking-[0.3em] text-blue-700">Layout System</p>
                    <h1 class="mt-2 text-3xl font-bold text-slate-950">Edit Document Template</h1>
                    <p class="mt-2 text-sm text-slate-500">Perbarui path atau file template yang dipakai perusahaan.</p>
                </div>

                <DocumentTemplateForm
                    :action="route('document-templates.update', template.id)"
                    method="put"
                    :template="template"
                    :options="options"
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
