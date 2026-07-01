<script setup lang="ts">
import DiscountModeInput from './DiscountModeInput.vue';
import ProductSearchInput from './ProductSearchInput.vue';

const props = defineProps<{
    form: any;
    discountMode: 'pct' | 'fixed';
}>();

const emit = defineEmits<{
    'update:discountMode': [v: 'pct' | 'fixed'];
}>();
</script>

<template>
    <div class="rounded-2xl bg-gray-50 p-4 dark:bg-gray-800/50">
        <h3
            class="mb-3 text-xs font-bold uppercase tracking-wider text-content-muted"
        >
            Condiciones — Dto. por Cantidad
        </h3>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div class="relative md:col-span-2">
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
                    >Cantidad mínima</label
                >
                <input
                    v-model.number="form.conditions.min_qty"
                    type="number"
                    min="1"
                    required
                    class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                />
            </div>
            <div>
                <DiscountModeInput
                    :model-value="discountMode"
                    @update:model-value="emit('update:discountMode', $event)"
                />
            </div>
        </div>
        <div v-if="discountMode === 'pct'" class="mt-4 md:w-1/3">
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
        </div>
        <div v-else class="mt-4 md:w-1/3">
            <label
                class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted"
                >Precio especial total ($)</label
            >
            <input
                v-model.number="form.conditions.special_price"
                type="number"
                min="1"
                required
                class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
            />
        </div>
    </div>
</template>
