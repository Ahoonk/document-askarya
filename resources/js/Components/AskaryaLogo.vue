<script setup>
defineProps({
    tone: {
        type: String,
        default: 'light',
    },
    showWordmark: {
        type: Boolean,
        default: true,
    },
    compact: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <div class="inline-flex items-center gap-3" :class="{ 'gap-0': compact }">
        <div class="flex h-14 w-14 items-center justify-center overflow-hidden rounded-2xl border border-blue-200/40 bg-white shadow-lg shadow-blue-900/10 backdrop-blur">
            <img
                src="/images/askarya-logo.png"
                alt="Logo Askarya"
                class="h-full w-full object-contain p-1"
            />
        </div>

        <div v-if="showWordmark && !compact" class="leading-tight">
            <p :class="tone === 'dark' ? 'text-blue-950' : 'text-blue-50'" class="text-lg font-extrabold tracking-[0.34em]">
                ASKA
            </p>
            <p :class="tone === 'dark' ? 'text-red-600' : 'text-red-200'" class="mt-1 text-[11px] uppercase tracking-[0.36em]">
                Dokumen Askarya
            </p>
        </div>
    </div>
</template>
