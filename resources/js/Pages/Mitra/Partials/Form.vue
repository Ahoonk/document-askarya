<script setup>
import { Link, useForm } from '@inertiajs/vue3';
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
    mitra: {
        type: Object,
        default: null,
    },
});

const form = useForm({
    nama: props.mitra?.nama ?? '',
    email: props.mitra?.email ?? '',
    alamat: props.mitra?.alamat ?? '',
    nomor_penawaran: props.mitra?.nomor_penawaran ?? '',
    nomor_invoice: props.mitra?.nomor_invoice ?? '',
    nomor_surat_jalan: props.mitra?.nomor_surat_jalan ?? '',
    nomor_berita_acara: props.mitra?.nomor_berita_acara ?? '',
    template_penawaran_path: props.mitra?.template_penawaran_path ?? '',
    template_invoice_path: props.mitra?.template_invoice_path ?? '',
    template_surat_jalan_path: props.mitra?.template_surat_jalan_path ?? '',
    template_berita_acara_path: props.mitra?.template_berita_acara_path ?? '',
});

function submit() {
    if (props.method === 'put') {
        form.put(props.action, {
            preserveScroll: true,
        });
        return;
    }

    form.post(props.action, {
        preserveScroll: true,
    });
}
</script>

<template>
    <form @submit.prevent="submit" class="space-y-6">
        <section class="rounded-[1.5rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-slate-950/25 backdrop-blur">
            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <InputLabel for="nama" value="Nama Mitra" />
                    <TextInput id="nama" v-model="form.nama" type="text" class="mt-2 block w-full" />
                    <InputError class="mt-2" :message="form.errors.nama" />
                </div>

                <div>
                    <InputLabel for="email" value="Email" />
                    <TextInput id="email" v-model="form.email" type="email" class="mt-2 block w-full" />
                    <InputError class="mt-2" :message="form.errors.email" />
                </div>

                <div class="md:col-span-2">
                    <InputLabel for="alamat" value="Alamat" />
                    <textarea id="alamat" v-model="form.alamat" rows="5" class="mt-2 block w-full rounded-xl border-white/10 bg-slate-950/50 text-slate-100 shadow-sm focus:border-sky-400 focus:ring-sky-400"></textarea>
                    <InputError class="mt-2" :message="form.errors.alamat" />
                </div>
            </div>
        </section>

        <section class="rounded-[1.5rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-slate-950/25 backdrop-blur">
            <div class="mb-4">
                <h2 class="text-lg font-semibold text-white">Nomor Override Dokumen</h2>
                <p class="text-sm text-slate-300">Isi jika mitra punya format nomor khusus untuk dokumen turunannya.</p>
            </div>

            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <InputLabel for="nomor_penawaran" value="Nomor Penawaran" />
                    <TextInput id="nomor_penawaran" v-model="form.nomor_penawaran" type="text" class="mt-2 block w-full" />
                    <InputError class="mt-2" :message="form.errors.nomor_penawaran" />
                </div>

                <div>
                    <InputLabel for="nomor_invoice" value="Nomor Invoice" />
                    <TextInput id="nomor_invoice" v-model="form.nomor_invoice" type="text" class="mt-2 block w-full" />
                    <InputError class="mt-2" :message="form.errors.nomor_invoice" />
                </div>

                <div>
                    <InputLabel for="nomor_surat_jalan" value="Nomor Surat Jalan" />
                    <TextInput id="nomor_surat_jalan" v-model="form.nomor_surat_jalan" type="text" class="mt-2 block w-full" />
                    <InputError class="mt-2" :message="form.errors.nomor_surat_jalan" />
                </div>

                <div>
                    <InputLabel for="nomor_berita_acara" value="Nomor Berita Acara" />
                    <TextInput id="nomor_berita_acara" v-model="form.nomor_berita_acara" type="text" class="mt-2 block w-full" />
                    <InputError class="mt-2" :message="form.errors.nomor_berita_acara" />
                </div>
            </div>
        </section>

        <section class="rounded-[1.5rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-slate-950/25 backdrop-blur">
            <div class="mb-4">
                <h2 class="text-lg font-semibold text-white">Path Template</h2>
                <p class="text-sm text-slate-300">Gunakan path file bila template dokumen ingin diarahkan per mitra.</p>
            </div>

            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <InputLabel for="template_penawaran_path" value="Template Penawaran" />
                    <TextInput id="template_penawaran_path" v-model="form.template_penawaran_path" type="text" class="mt-2 block w-full" />
                    <InputError class="mt-2" :message="form.errors.template_penawaran_path" />
                </div>

                <div>
                    <InputLabel for="template_invoice_path" value="Template Invoice" />
                    <TextInput id="template_invoice_path" v-model="form.template_invoice_path" type="text" class="mt-2 block w-full" />
                    <InputError class="mt-2" :message="form.errors.template_invoice_path" />
                </div>

                <div>
                    <InputLabel for="template_surat_jalan_path" value="Template Surat Jalan" />
                    <TextInput id="template_surat_jalan_path" v-model="form.template_surat_jalan_path" type="text" class="mt-2 block w-full" />
                    <InputError class="mt-2" :message="form.errors.template_surat_jalan_path" />
                </div>

                <div>
                    <InputLabel for="template_berita_acara_path" value="Template Berita Acara" />
                    <TextInput id="template_berita_acara_path" v-model="form.template_berita_acara_path" type="text" class="mt-2 block w-full" />
                    <InputError class="mt-2" :message="form.errors.template_berita_acara_path" />
                </div>
            </div>
        </section>

        <section class="rounded-[1.5rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-slate-950/25 backdrop-blur">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <Link :href="route('mitra.index')" class="text-sm font-semibold text-slate-300">
                    Batal
                </Link>
                <PrimaryButton :disabled="form.processing" class="bg-gradient-to-r from-blue-600 via-indigo-600 to-rose-600">
                    {{ form.processing ? 'Menyimpan...' : 'Simpan Mitra' }}
                </PrimaryButton>
            </div>
        </section>
    </form>
</template>
