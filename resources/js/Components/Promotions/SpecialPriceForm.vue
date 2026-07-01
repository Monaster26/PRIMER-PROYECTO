<script setup lang="ts">
import ProductSearchInput from './ProductSearchInput.vue';

const props = defineProps<{
    form: any;
}>();
</script>

<template>
    <div class="rounded-2xl bg-gray-50 p-4 dark:bg-gray-800/50">
        <h3
            class="mb-3 text-xs font-bold uppercase tracking-wider text-content-muted"
        >
            Condiciones — Precio Especial
        </h3>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div class="relative">
                <label
                    class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted"
                    >Producto</label
                >
                <ProductSearchInput
                    :product-id="form.conditions.product_id"
                    :product-name="form.conditions.product_name || ''"
                    @update:product-id="form.conditions.product_id = $event"
                    @update:product-name="form.conditions.product_name = $event"
                />
            </div>
            <div>
                <label
                    class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted"
                    >Precio especial ($)</label
                >
                <input
                    v-model.number="form.conditions.special_price"
                    type="number"
                    min="1"
                    required
                    class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                />
                <p class="mt-1 text-xs text-amber-600">
                    Aplica durante el período de vigencia configurado abajo
                </p>
            </div>
        </div>
    </div>
</template>
