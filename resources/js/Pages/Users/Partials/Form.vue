<script setup>
import { computed } from 'vue';
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
    user: {
        type: Object,
        default: null,
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

const form = useForm({
    company_id: props.user?.company_id ?? '',
    name: props.user?.name ?? '',
    email: props.user?.email ?? '',
    password: '',
    password_confirmation: '',
    role: props.user?.role ?? 'admin',
});

const isEdit = computed(() => Boolean(props.user?.id));

function submit() {
    const options = {
        preserveScroll: true,
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
        <section class="rounded-[1.5rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-slate-950/25 backdrop-blur">
            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <InputLabel for="name" value="Nama" />
                    <TextInput id="name" v-model="form.name" type="text" class="mt-2 block w-full" />
                    <InputError class="mt-2" :message="form.errors.name" />
                </div>

                <div>
                    <InputLabel for="email" value="Email" />
                    <TextInput id="email" v-model="form.email" type="email" class="mt-2 block w-full" />
                    <InputError class="mt-2" :message="form.errors.email" />
                </div>

                <div>
                    <InputLabel for="company_id" value="Company" />
                    <select id="company_id" v-model="form.company_id" class="mt-2 block w-full rounded-xl border-white/10 bg-slate-950/50 text-slate-100 shadow-sm focus:border-sky-400 focus:ring-sky-400">
                        <option value="">-</option>
                        <option v-for="company in companies" :key="company.id" :value="company.id">
                            {{ company.name }}
                        </option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.company_id" />
                </div>

                <div>
                    <InputLabel for="role" value="Role" />
                    <select id="role" v-model="form.role" class="mt-2 block w-full rounded-xl border-white/10 bg-slate-950/50 text-slate-100 shadow-sm focus:border-sky-400 focus:ring-sky-400">
                        <option v-for="role in options?.roles || []" :key="role" :value="role">
                            {{ role === 'superadmin' ? 'Superadmin' : 'Admin' }}
                        </option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.role" />
                </div>

                <div>
                    <InputLabel for="password" :value="isEdit ? 'Password Baru' : 'Password'" />
                    <TextInput id="password" v-model="form.password" type="password" class="mt-2 block w-full" autocomplete="new-password" />
                    <InputError class="mt-2" :message="form.errors.password" />
                </div>

                <div>
                    <InputLabel for="password_confirmation" value="Konfirmasi Password" />
                    <TextInput id="password_confirmation" v-model="form.password_confirmation" type="password" class="mt-2 block w-full" autocomplete="new-password" />
                    <InputError class="mt-2" :message="form.errors.password_confirmation" />
                </div>
            </div>
        </section>

        <section class="rounded-[1.5rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-slate-950/25 backdrop-blur">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <Link :href="route('users.index')" class="text-sm font-semibold text-slate-300">
                    Batal
                </Link>
                <PrimaryButton :disabled="form.processing" class="bg-gradient-to-r from-blue-600 via-indigo-600 to-rose-600">
                    {{ form.processing ? 'Menyimpan...' : 'Simpan User' }}
                </PrimaryButton>
            </div>
        </section>
    </form>
</template>
