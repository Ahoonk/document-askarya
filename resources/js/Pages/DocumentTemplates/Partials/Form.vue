<script setup>
import { computed } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    action: {
        type: String,
        required: true,
    },
    method: {
        type: String,
        default: 'post',
    },
    template: {
        type: Object,
        default: null,
    },
    options: {
        type: Object,
        default: () => ({}),
    },
});

const form = useForm({
    name: props.template?.name ?? '',
    document_type: props.template?.document_type ?? props.options?.document_types?.[0] ?? 'penawaran',
    file_path: props.template?.file_path ?? '',
    template_file: null,
    is_default: props.template?.is_default ?? false,
});

const selectedFileLabel = computed(() => {
    return form.template_file?.name || props.template?.file_path || 'Belum ada file dipilih';
});

function submit() {
    const options = {
        preserveScroll: true,
        forceFormData: Boolean(form.template_file),
    };

    if (props.method === 'put') {
        form.put(props.action, options);
        return;
    }

    form.post(props.action, options);
}
</script>

<template>
    <form @submit.prevent="submit" class="space-y-6">
        <section class="rounded-[1.5rem] border border-slate-200 bg-white p-6 shadow-sm">
            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <InputLabel for="name" value="Nama Template" />
                    <TextInput id="name" v-model="form.name" type="text" class="mt-2 block w-full" />
                    <InputError class="mt-2" :message="form.errors.name" />
                </div>

                <div>
                    <InputLabel for="document_type" value="Tipe Dokumen" />
                    <select id="document_type" v-model="form.document_type" class="mt-2 block w-full rounded-xl border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option v-for="type in options?.document_types || []" :key="type" :value="type">
                            {{ type }}
                        </option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.document_type" />
                </div>

                <div class="md:col-span-2">
                    <InputLabel for="file_path" value="File Path / View Path" />
                    <TextInput id="file_path" v-model="form.file_path" type="text" class="mt-2 block w-full" placeholder="document-templates/penawaran.pdf atau resources.views.docs.invoice" />
                    <InputError class="mt-2" :message="form.errors.file_path" />
                    <p class="mt-2 text-xs leading-5 text-slate-500">
                        Isi dengan path file di storage public atau path view Blade. Jika upload file di bawah, path akan diisi otomatis.
                    </p>
                </div>

                <div class="md:col-span-2">
                    <InputLabel for="template_file" value="Upload File Template" />
                    <input
                        id="template_file"
                        type="file"
                        class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 shadow-sm"
                        @change="(event) => { form.template_file = event.target.files?.[0] ?? null }"
                    >
                    <InputError class="mt-2" :message="form.errors.template_file" />
                    <p class="mt-2 text-xs leading-5 text-slate-500">
                        File yang diupload disimpan ke storage public. Saat edit, upload file baru jika ingin mengganti file lama.
                    </p>
                </div>
            </div>
        </section>

        <section class="rounded-[1.5rem] border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <InputLabel for="is_default" value="Jadikan Default" />
                    <label class="mt-3 flex items-center gap-3 text-sm text-slate-700">
                        <Checkbox id="is_default" v-model:checked="form.is_default" />
                        <span>Template ini dipakai sebagai default untuk tipe dokumen yang sama.</span>
                    </label>
                    <InputError class="mt-2" :message="form.errors.is_default" />
                </div>

                <div class="text-right">
                    <p class="text-xs uppercase tracking-[0.2em] text-slate-400">File Terpilih</p>
                    <p class="mt-2 text-sm font-medium text-slate-700">{{ selectedFileLabel }}</p>
                </div>
            </div>
        </section>

        <section class="rounded-[1.5rem] border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <Link :href="route('document-templates.index')" class="text-sm font-semibold text-slate-600">
                    Batal
                </Link>
                <PrimaryButton :disabled="form.processing">
                    {{ form.processing ? 'Menyimpan...' : 'Simpan Template' }}
                </PrimaryButton>
            </div>
        </section>
    </form>
</template>
