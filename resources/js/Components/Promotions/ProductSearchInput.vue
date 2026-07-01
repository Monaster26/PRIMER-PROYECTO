<script setup lang="ts">
import { ref, watch } from 'vue';
import { useProductSearch } from '@/composables/useProductSearch';

const props = defineProps<{
    productId: number | null;
    productName: string;
}>();

const emit = defineEmits<{
    'update:productId': [id: number | null];
    'update:productName': [name: string];
}>();

const searchQuery = ref('');

const selectedProduct = ref<{ id: number; name: string; description: string } | null>(null);

watch(() => props.productId, async (newId) => {
    if (selectedProduct.value?.id === newId) return;
    if (newId == null) {
        selectedProduct.value = null;
        return;
    }
    try {
        const { data } = await window.axios.get(
            window.route('admin.codigos.search-sku'),
            { params: { query: String(newId) } },
        );
        if (props.productId !== newId) return;
        if (data) {
            selectedProduct.value = { id: data.id, name: data.name, description: data.description || '' };
            return;
        }
    } catch {}
    if (props.productId !== newId) return;
    selectedProduct.value = { id: newId, name: props.productName || `#${newId}`, description: '' };
}, { immediate: true });

const {
    results,
    skuResults,
    open,
    searchName,
    searchSku,
    selectProduct: selectViaSearch,
    onBlur,
    onFocus,
} = useProductSearch((product: any) => {
    selectedProduct.value = { id: product.id, name: product.name, description: product.description || '' };
    searchQuery.value = '';
    emit('update:productId', product.id);
    emit('update:productName', product.name);
});

function onSearchInput(e: Event) {
    const q = (e.target as HTMLInputElement).value;
    searchQuery.value = q;
    searchName(q);
}

function clearSelection() {
    selectedProduct.value = null;
    searchQuery.value = '';
    emit('update:productId', null);
    emit('update:productName', '');
}
</script>

<template>
    <div>
        <div v-if="productId == null" class="grid grid-cols-2 gap-2">
            <div class="relative">
                <input
                    :value="searchQuery"
                    @input="onSearchInput"
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
                        @mousedown.prevent="selectViaSearch(pr)"
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
                    @input="searchSku(($event.target as HTMLInputElement).value)"
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
                        @mousedown.prevent="selectViaSearch(pr)"
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
        <div v-else-if="selectedProduct">
            <span
                class="inline-flex items-center gap-2 rounded-xl bg-primary-100 px-3 py-1.5 text-xs font-bold text-primary-700 dark:bg-primary-900/30 dark:text-primary-300"
            >
                <span class="flex flex-col">
                    <span>{{ selectedProduct.name }}</span>
                    <span
                        v-if="selectedProduct.description"
                        class="line-clamp-1 text-[10px] font-normal text-content-muted"
                    >
                        {{ selectedProduct.description }}
                    </span>
                </span>
                <button
                    type="button"
                    @click="clearSelection"
                    class="hover:text-danger"
                >
                    &times;
                </button>
            </span>
        </div>
    </div>
</template>
