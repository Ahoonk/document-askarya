<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
    type: String,
    required: true,
});

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        class="rounded-xl border border-white/10 bg-slate-950/50 px-3 py-2 text-slate-100 shadow-sm placeholder:text-slate-500 focus:border-sky-400 focus:ring-sky-400"
        v-model="model"
        ref="input"
    />
</template>
