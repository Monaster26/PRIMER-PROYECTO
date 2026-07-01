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
            Condiciones — Compra X, Lleva Y
        </h3>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div class="relative">
                <label
                    class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted"
                    >Producto a comprar</label
                >
                <ProductSearchInput
                    :product-id="form.conditions.buy_product_id"
                    :product-name="form.conditions.buy_product_name || ''"
                    @update:product-id="form.conditions.buy_product_id = $event"
                    @update:product-name="
                        form.conditions.buy_product_name = $event
                    "
                />
            </div>
            <div>
                <label
                    class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted"
                    >Cantidad a comprar</label
                >
                <input
                    v-model.number="form.conditions.buy_qty"
                    type="number"
                    min="1"
                    required
                    class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                />
            </div>
        </div>
        <h4
            class="mb-2 mt-4 text-xs font-bold uppercase tracking-wider text-content-muted"
        >
            Recompensa
        </h4>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div class="relative">
                <label
                    class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted"
                    >Producto a regalar</label
                >
                <ProductSearchInput
                    :product-id="form.rewards.get_product_id"
                    :product-name="form.rewards.get_product_name || ''"
                    @update:product-id="form.rewards.get_product_id = $event"
                    @update:product-name="
                        form.rewards.get_product_name = $event
                    "
                />
            </div>
            <div>
                <label
                    class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted"
                    >Cantidad a regalar</label
                >
                <input
                    v-model.number="form.rewards.get_qty"
                    type="number"
                    min="1"
                    required
                    class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                />
            </div>
            <div v-if="discountMode === 'pct'">
                <label
                    class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted"
                    >% Descuento</label
                >
                <input
                    v-model.number="form.rewards.discount_pct"
                    type="number"
                    min="0"
                    max="100"
                    class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                />
            </div>
            <div v-else>
                <label
                    class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted"
                    >Precio fijo total ($)</label
                >
                <input
                    v-model.number="form.conditions.special_price_total"
                    type="number"
                    min="1"
                    required
                    class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                />
            </div>
        </div>
        <div class="mt-4">
            <DiscountModeInput
                :model-value="discountMode"
                @update:model-value="emit('update:discountMode', $event)"
            />
        </div>
    </div>
</template>
