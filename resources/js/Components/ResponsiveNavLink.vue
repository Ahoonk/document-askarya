<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    active: {
        type: Boolean,
    },
    theme: {
        type: String,
        default: 'light',
    },
});

const classes = computed(() =>
    props.theme === 'dark'
        ? props.active
            ? 'block w-full rounded-2xl border border-cyan-300/20 bg-slate-700 px-4 py-3 text-start text-base font-semibold text-white shadow-sm transition duration-150 ease-in-out focus:outline-none focus:ring-2 focus:ring-cyan-300/30'
            : 'block w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-start text-base font-medium text-slate-200 transition duration-150 ease-in-out hover:border-cyan-300/20 hover:bg-white/8 hover:text-white focus:outline-none focus:ring-2 focus:ring-cyan-300/20'
        : props.active
            ? 'block w-full rounded-2xl border border-red-200 bg-red-600 px-4 py-3 text-start text-base font-semibold text-white shadow-sm transition duration-150 ease-in-out focus:outline-none focus:ring-2 focus:ring-red-300/60'
            : 'block w-full rounded-2xl border border-blue-100 bg-white px-4 py-3 text-start text-base font-medium text-slate-700 transition duration-150 ease-in-out hover:border-red-200 hover:bg-red-50 hover:text-red-700 focus:outline-none focus:ring-2 focus:ring-blue-300/40',
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
