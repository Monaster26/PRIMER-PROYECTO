<script setup lang="ts">
import { useProductSearch } from '@/composables/useProductSearch';
import { ref } from 'vue';
import DiscountModeInput from './DiscountModeInput.vue';

const props = defineProps<{
    form: any;
    discountMode: 'pct' | 'fixed';
}>();
const emit = defineEmits<{
    'update:discountMode': [v: 'pct' | 'fixed'];
}>();

const productNames = ref<Record<number, string>>({});

function addProduct(product: any) {
    const ids = props.form.conditions.product_ids || [];
    if (!ids.includes(product.id)) {
        props.form.conditions.product_ids = [...ids, product.id];
        productNames.value[product.id] = product.name;
    }
}

function removeProduct(productId: number) {
    props.form.conditions.product_ids = (
        props.form.conditions.product_ids || []
    ).filter((id: number) => id !== productId);
    delete productNames.value[productId];
}

const {
    results,
    skuResults,
    open,
    searchName,
    searchSku,
    selectProduct,
    onBlur,
    onFocus,
} = useProductSearch(addProduct);
</script>

<template>
    <div class="rounded-2xl bg-gray-50 p-4 dark:bg-gray-800/50">
        <h3
            class="mb-3 text-xs font-bold uppercase tracking-wider text-content-muted"
        >
            Condiciones — Combo
        </h3>
        <div class="mb-4">
            <label
                class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted"
                >Productos del combo</label
            >
            <div class="grid grid-cols-2 gap-2">
                <div class="relative">
                    <input
                        @input="
                            searchName(
                                ($event.target as HTMLInputElement).value,
                            )
                        "
                        @blur="onBlur()"
                        @focus="onFocus()"
                        type="text"
                        placeholder="Buscar por nombre..."
                        class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                    />
                    <div
                        v-if="results.length && open"
                        class="absolute left-0 right-0 top-full z-50 mt-1 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-900"
                    >
                        <button
                            v-for="pr in results"
                            :key="pr.id"
                            type="button"
                            @mousedown.prevent="selectProduct(pr)"
                            class="flex w-full items-center gap-3 px-4 py-3 text-left text-sm transition-colors hover:bg-gray-50 dark:hover:bg-gray-800"
                        >
                            <span
                                class="flex-1 font-medium text-content-primary dark:text-white"
                                >{{ pr.name }}</span
                            >
                            <span class="text-xs text-content-muted"
                                >Stock: {{ pr.stock }}</span
                            >
                        </button>
                    </div>
                </div>
                <div class="relative">
                    <input
                        @input="
                            searchSku(($event.target as HTMLInputElement).value)
                        "
                        @blur="onBlur()"
                        @focus="onFocus()"
                        type="text"
                        placeholder="SKU / Código de barras..."
                        class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                    />
                    <div
                        v-if="skuResults.length && open"
                        class="absolute left-0 right-0 top-full z-50 mt-1 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-900"
                    >
                        <button
                            v-for="pr in skuResults"
                            :key="pr.id"
                            type="button"
                            @mousedown.prevent="selectProduct(pr)"
                            class="flex w-full items-center gap-3 px-4 py-3 text-left text-sm transition-colors hover:bg-gray-50 dark:hover:bg-gray-800"
                        >
                            <span
                                class="flex-1 font-medium text-content-primary dark:text-white"
                                >{{ pr.name }}</span
                            >
                            <span class="text-xs text-content-muted"
                                >Stock: {{ pr.stock }}</span
                            >
                        </button>
                    </div>
                </div>
            </div>
            <div
                v-if="(form.conditions.product_ids || []).length"
                class="mt-2 flex flex-wrap gap-2"
            >
                <span
                    v-for="pid in form.conditions.product_ids"
                    :key="pid"
                    class="flex items-center gap-1 rounded-xl bg-primary-100 px-3 py-1.5 text-xs font-bold text-primary-700 dark:bg-primary-900/30 dark:text-primary-300"
                >
                    {{ productNames[pid] || '#' + pid }}
                    <button
                        type="button"
                        @click="removeProduct(pid)"
                        class="ml-0.5 hover:text-danger"
                    >
                        &times;
                    </button>
                </span>
            </div>
        </div>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <DiscountModeInput
                :model-value="discountMode"
                @update:model-value="emit('update:discountMode', $event)"
            />
            <div v-if="discountMode === 'pct'">
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
    </div>
</template>
