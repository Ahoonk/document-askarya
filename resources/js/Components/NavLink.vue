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
});

const classes = computed(() =>
    props.active
        ? 'inline-flex items-center whitespace-nowrap rounded-full border border-red-200 bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-red-500/15 transition duration-150 ease-in-out focus:outline-none focus:ring-2 focus:ring-red-300/60'
        : 'inline-flex items-center whitespace-nowrap rounded-full border border-transparent px-4 py-2 text-sm font-medium text-slate-700 transition duration-150 ease-in-out hover:border-red-200 hover:bg-red-50 hover:text-red-700 focus:outline-none focus:ring-2 focus:ring-blue-300/40',
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
