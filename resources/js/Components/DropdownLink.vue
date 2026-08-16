<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    theme: {
        type: String,
        default: 'light',
    },
});

const classes = computed(() =>
    props.theme === 'dark'
        ? 'block w-full px-4 py-2 text-start text-sm leading-5 text-slate-200 transition duration-150 ease-in-out hover:bg-white/6 hover:text-white focus:bg-white/6 focus:text-white focus:outline-none'
        : 'block w-full px-4 py-2 text-start text-sm leading-5 text-slate-700 transition duration-150 ease-in-out hover:bg-blue-50 hover:text-blue-900 focus:bg-blue-50 focus:text-blue-900 focus:outline-none',
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
