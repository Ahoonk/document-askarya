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
    customer: {
        type: Object,
        default: null,
    },
});

const form = useForm({
    nama: props.customer?.nama ?? '',
    alamat: props.customer?.alamat ?? '',
    no_hp: props.customer?.no_hp ?? '',
    email: props.customer?.email ?? '',
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
        <section class="rounded-[1.5rem] border border-slate-200 bg-white p-6 shadow-sm">
            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <InputLabel for="nama" value="Nama Customer" />
                    <TextInput id="nama" v-model="form.nama" type="text" class="mt-2 block w-full" />
                    <InputError class="mt-2" :message="form.errors.nama" />
                </div>

                <div>
                    <InputLabel for="no_hp" value="Nomor HP" />
                    <TextInput id="no_hp" v-model="form.no_hp" type="text" class="mt-2 block w-full" />
                    <InputError class="mt-2" :message="form.errors.no_hp" />
                </div>

                <div class="md:col-span-2">
                    <InputLabel for="email" value="Email" />
                    <TextInput id="email" v-model="form.email" type="email" class="mt-2 block w-full" />
                    <InputError class="mt-2" :message="form.errors.email" />
                </div>

                <div class="md:col-span-2">
                    <InputLabel for="alamat" value="Alamat" />
                    <textarea id="alamat" v-model="form.alamat" rows="5" class="mt-2 block w-full rounded-xl border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                    <InputError class="mt-2" :message="form.errors.alamat" />
                </div>
            </div>
        </section>

        <section class="rounded-[1.5rem] border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <Link :href="route('customers.index')" class="text-sm font-semibold text-slate-600">
                    Batal
                </Link>
                <PrimaryButton :disabled="form.processing">
                    {{ form.processing ? 'Menyimpan...' : 'Simpan Customer' }}
                </PrimaryButton>
            </div>
        </section>
    </form>
</template>
