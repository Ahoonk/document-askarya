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

    <div class="relative min-h-screen overflow-hidden bg-[#08111f] text-slate-100">
        <div class="pointer-events-none absolute inset-0">
            <div class="absolute left-[-8rem] top-[-8rem] h-96 w-96 rounded-full bg-red-500/20 blur-3xl"></div>
            <div class="absolute right-[-7rem] top-24 h-[30rem] w-[30rem] rounded-full bg-blue-500/20 blur-3xl"></div>
            <div class="absolute bottom-[-8rem] left-1/3 h-80 w-80 rounded-full bg-amber-400/10 blur-3xl"></div>
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(255,255,255,0.08),_transparent_35%),linear-gradient(135deg,_rgba(8,17,31,0.94),_rgba(9,14,27,0.98))]"></div>
        </div>

        <div class="relative mx-auto flex min-h-screen max-w-7xl items-center px-4 py-8 sm:px-6 lg:px-8">
            <div class="grid w-full gap-6 lg:grid-cols-[1.05fr_0.95fr]">
                <section class="relative overflow-hidden rounded-[2.5rem] border border-white/10 bg-white/6 p-6 shadow-2xl shadow-black/20 backdrop-blur-xl sm:p-8 lg:p-10">
                    <div class="absolute right-[-3rem] top-[-3rem] h-32 w-32 rounded-full bg-red-500/20 blur-2xl"></div>
                    <div class="absolute bottom-[-2rem] left-[-2rem] h-40 w-40 rounded-full bg-blue-400/10 blur-3xl"></div>

                    <div class="relative flex flex-col justify-between gap-8">
                        <div class="flex items-start justify-between gap-4">
                            <AskaryaLogo tone="light" :showWordmark="false" compact />

                            <div class="inline-flex flex-col items-end rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-right shadow-lg shadow-black/10">
                                <p class="text-[10px] font-semibold uppercase tracking-[0.34em] text-blue-200/70">Portal Masuk</p>
                                <p class="mt-1 text-sm font-semibold text-white">Dokumen Askarya</p>
                            </div>
                        </div>

                        <div class="max-w-xl">
                            <p class="text-xs uppercase tracking-[0.42em] text-blue-200/70">Access Control</p>
                            <h1 class="mt-4 text-4xl font-black leading-tight text-white sm:text-5xl">
                                Masuk ke sistem
                            </h1>
                            <p class="mt-5 max-w-xl text-sm leading-7 text-slate-300 sm:text-base">
                                Akses dashboard dan dokumen Askarya.
                            </p>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-3">
                            <div class="rounded-2xl border border-white/10 bg-slate-950/40 p-4">
                                <p class="text-[11px] uppercase tracking-[0.28em] text-slate-400">Dokumen</p>
                                <p class="mt-2 text-lg font-semibold text-white">Rapi</p>
                            </div>
                            <div class="rounded-2xl border border-white/10 bg-slate-950/40 p-4">
                                <p class="text-[11px] uppercase tracking-[0.28em] text-slate-400">Akses</p>
                                <p class="mt-2 text-lg font-semibold text-white">Cepat</p>
                            </div>
                            <div class="rounded-2xl border border-white/10 bg-slate-950/40 p-4">
                                <p class="text-[11px] uppercase tracking-[0.28em] text-slate-400">Output</p>
                                <p class="mt-2 text-lg font-semibold text-white">Jelas</p>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="relative overflow-hidden rounded-[2.5rem] border border-white/10 bg-white px-6 py-8 text-slate-900 shadow-2xl shadow-black/20 sm:px-8 sm:py-10">
                    <div class="absolute right-0 top-0 h-40 w-40 rounded-full bg-blue-100 blur-3xl"></div>
                    <div class="absolute left-0 bottom-0 h-40 w-40 rounded-full bg-red-100 blur-3xl"></div>

                    <div class="relative">
                        <div class="inline-flex items-center rounded-full border border-blue-100 bg-blue-50 px-4 py-2 text-xs font-semibold uppercase tracking-[0.28em] text-blue-800">
                            Masuk ke sistem
                        </div>

                        <div class="mt-6">
                            <h2 class="text-3xl font-black tracking-tight text-slate-950 sm:text-4xl">Selamat datang</h2>
                            <p class="mt-3 max-w-xl text-sm leading-7 text-slate-600 sm:text-base">
                                Silakan masuk untuk melanjutkan.
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
                                    class="mt-2 block w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-blue-500 focus:ring-blue-500"
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
                                    class="mt-2 block w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-blue-500 focus:ring-blue-500"
                                    v-model="form.password"
                                    required
                                    autocomplete="current-password"
                                />
                                <InputError class="mt-2" :message="form.errors.password" />
                            </div>

                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <label class="flex items-center gap-3 rounded-full border border-slate-200 bg-slate-50 px-4 py-2">
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
                                class="inline-flex w-full items-center justify-center rounded-2xl bg-gradient-to-r from-blue-700 via-blue-600 to-red-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-500/20 transition hover:brightness-110 focus:outline-none focus:ring-2 focus:ring-blue-200 focus:ring-offset-2 focus:ring-offset-white disabled:cursor-not-allowed disabled:opacity-25"
                                :disabled="form.processing"
                            >
                                {{ form.processing ? 'Memproses...' : 'Masuk ke Dashboard' }}
                            </button>
                        </form>
                    </div>
                </section>
            </div>
        </div>
    </div>
</template>
