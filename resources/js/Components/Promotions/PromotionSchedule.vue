<script setup lang="ts">
const props = defineProps<{
    form: any;
}>();
</script>

<template>
    <div class="rounded-2xl bg-gray-50 p-4 dark:bg-gray-800/50">
        <h3
            class="mb-3 text-xs font-bold uppercase tracking-wider text-content-muted"
        >
            Programación y opciones
        </h3>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
            <div>
                <label
                    class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted"
                    >Fecha inicio</label
                >
                <input
                    v-model="form.starts_at"
                    type="datetime-local"
                    class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                />
            </div>
            <div>
                <label
                    class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted"
                    >Fecha fin</label
                >
                <input
                    v-model="form.expires_at"
                    type="datetime-local"
                    class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                />
            </div>
            <div>
                <label
                    class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted"
                    >Prioridad</label
                >
                <input
                    v-model.number="form.priority"
                    type="number"
                    min="0"
                    class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                />
            </div>
            <div>
                <label
                    class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted"
                    >Límite de usos</label
                >
                <input
                    v-model.number="form.max_uses"
                    type="number"
                    min="1"
                    placeholder="∞ ilimitado"
                    class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                />
            </div>
            <div class="flex items-end gap-4 pb-2.5">
                <label class="flex cursor-pointer items-center gap-2">
                    <input
                        v-model="form.is_active"
                        type="checkbox"
                        class="rounded border-gray-300 text-primary-500 focus:ring-primary-500"
                    />
                    <span
                        class="text-xs font-bold uppercase tracking-wider text-content-muted"
                        >Activa</span
                    >
                </label>
                <label class="flex cursor-pointer items-center gap-2">
                    <input
                        v-model="form.is_exclusive"
                        type="checkbox"
                        class="rounded border-gray-300 text-primary-500 focus:ring-primary-500"
                    />
                    <span
                        class="text-xs font-bold uppercase tracking-wider text-content-muted"
                        >Exclusiva</span
                    >
                </label>
            </div>
        </div>
    </div>
</template>
