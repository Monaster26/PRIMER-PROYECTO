<script setup lang="ts">
defineProps<{
    modelValue: 'pct' | 'fixed';
}>();
const emit = defineEmits<{
    'update:modelValue': [v: 'pct' | 'fixed'];
}>();
</script>

<template>
    <div>
        <label
            class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted"
            >Modo de descuento</label
        >
        <div class="flex gap-4 pt-1">
            <label
                class="flex cursor-pointer items-center gap-2 text-sm text-content-primary dark:text-white"
            >
                <input
                    type="radio"
                    :checked="modelValue === 'pct'"
                    @change="emit('update:modelValue', 'pct')"
                    class="text-primary-500 focus:ring-primary-500"
                />
                Porcentaje (%)
            </label>
            <label
                class="flex cursor-pointer items-center gap-2 text-sm text-content-primary dark:text-white"
            >
                <input
                    type="radio"
                    :checked="modelValue === 'fixed'"
                    @change="emit('update:modelValue', 'fixed')"
                    class="text-primary-500 focus:ring-primary-500"
                />
                Precio fijo total ($)
            </label>
        </div>
    </div>
</template>
