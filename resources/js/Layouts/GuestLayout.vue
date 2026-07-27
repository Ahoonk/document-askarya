<script setup>
import AskaryaLogo from '@/Components/AskaryaLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="relative min-h-screen overflow-hidden bg-[#fff2d9] text-slate-900">
        <div class="pointer-events-none absolute inset-0">
            <div class="absolute left-[-7rem] top-[-6rem] h-80 w-80 rounded-full bg-blue-500/10 blur-3xl"></div>
            <div class="absolute right-[-6rem] top-20 h-72 w-72 rounded-full bg-red-500/10 blur-3xl"></div>
        </div>

        <div class="relative mx-auto flex min-h-screen w-full max-w-6xl flex-col justify-center px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-6 flex items-center justify-between text-slate-700">
                <Link href="/" class="inline-flex items-center gap-3">
                    <AskaryaLogo tone="dark" :showWordmark="false" compact />
                    <span class="hidden text-sm font-medium tracking-[0.24em] text-blue-950 sm:inline">
                        DOKUMEN ASKARYA
                    </span>
                </Link>
            </div>

            <div class="grid gap-6 lg:grid-cols-[0.9fr_1.1fr]">
                <div class="hidden rounded-[2rem] border border-blue-100 bg-white/90 p-8 text-slate-900 shadow-2xl shadow-blue-950/10 backdrop-blur lg:block">
                    <p class="text-xs uppercase tracking-[0.32em] text-blue-700">Guest Access</p>
                    <h1 class="mt-4 text-3xl font-bold leading-tight text-blue-950">
                        Masuk ke ruang kerja dokumen yang lebih rapi dan selaras dengan brand.
                    </h1>
                    <p class="mt-4 max-w-md text-sm leading-7 text-slate-600">
                        Seluruh pintu masuk memakai bahasa visual merah, putih, dan biru agar terasa konsisten dengan logo perusahaan.
                    </p>

                    <div class="mt-8 grid gap-3 sm:grid-cols-2">
                        <div class="rounded-2xl border border-blue-100 bg-blue-50 p-4">
                            <p class="text-xs uppercase tracking-[0.24em] text-blue-700">Akses</p>
                            <p class="mt-2 text-sm text-slate-700">Login, reset password, dan registrasi dalam satu pola.</p>
                        </div>
                        <div class="rounded-2xl border border-red-100 bg-red-50 p-4">
                            <p class="text-xs uppercase tracking-[0.24em] text-red-700">Brand</p>
                            <p class="mt-2 text-sm text-slate-700">Logo Askarya tampil konsisten di setiap pintu masuk.</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-[2rem] border border-blue-100 bg-white px-6 py-5 shadow-2xl shadow-blue-950/10 sm:px-8 sm:py-7">
                    <slot />
                </div>
            </div>
        </div>
    </div>
</template>
