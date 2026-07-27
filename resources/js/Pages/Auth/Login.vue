<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import AskaryaLogo from '@/Components/AskaryaLogo.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Masuk - Dokumen Askarya" />

    <div class="flex min-h-screen items-center justify-center bg-[#fff2d9] px-4 py-8 text-slate-900">
        <div class="w-full max-w-[620px] rounded-[2.5rem] border border-blue-100 bg-white px-6 py-8 shadow-[0_18px_50px_rgba(15,23,42,0.12)] sm:px-10 sm:py-10">
            <div class="flex items-start justify-between gap-4">
                <AskaryaLogo tone="dark" :showWordmark="false" compact />

                <div class="inline-flex min-w-[145px] flex-col items-center justify-center rounded-xl bg-red-600 px-4 py-2.5 text-center shadow-md shadow-red-500/20">
                    <p class="text-[10px] font-semibold uppercase tracking-[0.28em] text-white">Portal Masuk</p>
                    <p class="mt-1 text-sm font-medium text-white">Dokumen Askarya</p>
                </div>
            </div>

            <div class="mt-8">
                <h2 class="text-2xl font-extrabold tracking-tight text-blue-950 sm:text-[2rem]">Selamat datang</h2>
                <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-600 sm:text-base">
                    Masuk untuk melanjutkan pengelolaan dokumen milik PT Aldera Saddatech Karya.
                </p>
            </div>

            <div v-if="status" class="mt-5 rounded-2xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm font-semibold text-blue-800">
                {{ status }}
            </div>

            <form @submit.prevent="submit" class="mt-8 space-y-5">
                <div>
                    <InputLabel for="email" value="Email" class="font-semibold text-slate-700" />
                    <TextInput
                        id="email"
                        type="email"
                        class="mt-2 block w-full rounded-2xl border-slate-200 bg-white px-4 py-3 text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-blue-500 focus:ring-blue-500"
                        v-model="form.email"
                        required
                        autofocus
                        autocomplete="username"
                    />
                    <InputError class="mt-2" :message="form.errors.email" />
                </div>

                <div>
                    <InputLabel for="password" value="Password" class="font-semibold text-slate-700" />
                    <TextInput
                        id="password"
                        type="password"
                        class="mt-2 block w-full rounded-2xl border-slate-200 bg-white px-4 py-3 text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-blue-500 focus:ring-blue-500"
                        v-model="form.password"
                        required
                        autocomplete="current-password"
                    />
                    <InputError class="mt-2" :message="form.errors.password" />
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3">
                    <label class="flex items-center gap-3">
                        <Checkbox
                            name="remember"
                            v-model:checked="form.remember"
                            class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                        />
                        <span class="text-sm font-medium text-slate-600">Ingat saya</span>
                    </label>
                </div>

                <button
                    type="submit"
                    class="inline-flex w-full items-center justify-center rounded-2xl bg-blue-700 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-500/15 transition hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-200 focus:ring-offset-2 focus:ring-offset-white disabled:cursor-not-allowed disabled:opacity-25"
                    :disabled="form.processing"
                >
                    {{ form.processing ? 'Memproses...' : 'Masuk ke Dashboard' }}
                </button>
            </form>
        </div>
    </div>
</template>
