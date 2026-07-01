<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    form: any;
    categories: { id: number; name: string; parent_id: number | null }[];
}>();

const tree = computed(() => {
    const catMap = new Map(
        props.categories.map((c) => [c.id, { ...c, depth: 0 }]),
    );
    const roots: { id: number; name: string; depth: number }[] = [];
    for (const cat of catMap.values()) {
        if (cat.parent_id === null) {
            roots.push(cat);
            continue;
        }
        const parent = catMap.get(cat.parent_id);
        if (parent) {
            cat.depth = parent.depth + 1;
            roots.push(cat);
        } else {
            roots.push(cat);
        }
    }
    return roots.sort((a, b) => a.name.localeCompare(b.name, 'es'));
});
</script>

<template>
    <div class="rounded-2xl bg-gray-50 p-4 dark:bg-gray-800/50">
        <h3
            class="mb-3 text-xs font-bold uppercase tracking-wider text-content-muted"
        >
            Condiciones — Descuento por Categoría
        </h3>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <label
                    class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted"
                    >Categoría</label
                >
                <select
                    v-model.number="form.conditions.category_id"
                    required
                    class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                >
                    <option value="">Seleccionar categoría...</option>
                    <option v-for="cat in tree" :key="cat.id" :value="cat.id">
                        {{ '— '.repeat(cat.depth) }}{{ cat.name }}
                    </option>
                </select>
            </div>
            <div>
                <label
                    class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted"
                    >% Descuento</label
                >
                <input
                    v-model.number="form.conditions.discount_pct"
                    type="number"
                    min="1"
                    max="100"
                    required
                    class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                />
                <p class="mt-1 text-xs text-content-muted">
                    Aplica a todos los productos de esta categoría
                </p>
            </div>
        </div>
    </div>
</template>
