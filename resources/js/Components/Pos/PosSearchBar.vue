<script setup lang="ts">
import type { Product } from '@/Stores/posTabsStore';
import { Search } from 'lucide-vue-next';

defineProps<{
    scannerInput: string;
    searchQuery: string;
    searchFocused: boolean;
    showSearchDropdown: boolean;
    filteredProducts: Product[];
}>();

const emit = defineEmits<{
    'update:scannerInput': [value: string];
    'update:searchQuery': [value: string];
    'select-product': [product: Product];
    blur: [];
    'enter-search': [];
    'focus-barcode': [];
}>();

function formatCLP(cents: number): string {
    return (
        '$' +
        Math.round(cents / 100).toLocaleString('es-CL', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        })
    );
}
</script>

<template>
    <div
        class="flex gap-3 border-b border-gray-100 px-4 py-3 dark:border-gray-800"
    >
        <div class="relative flex-1">
            <input
                :value="scannerInput"
                @input="
                    emit(
                        'update:scannerInput',
                        ($event.target as HTMLInputElement).value,
                    )
                "
                @focus="emit('focus-barcode')"
                type="text"
                autofocus
                placeholder="🔎 Escanea el código de barras o SKU"
                class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 font-mono text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
            />
            <span
                v-if="scannerInput"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-[10px] font-bold uppercase tracking-wider text-emerald-500"
            >
                Escaneando...
            </span>
        </div>
        <div class="relative w-72">
            <Search
                class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-content-muted"
            />
            <input
                :value="searchQuery"
                @input="
                    emit(
                        'update:searchQuery',
                        ($event.target as HTMLInputElement).value,
                    )
                "
                @focus="
                    $emit('update:searchQuery', searchQuery);
                    ($event.target as HTMLInputElement).value.length > 0
                        ? null
                        : null;
                "
                @blur="emit('blur')"
                type="text"
                placeholder="Buscar por nombre"
                class="w-full rounded-2xl border border-gray-200 bg-gray-50 py-3 pl-10 pr-4 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
            />
            <div
                v-if="showSearchDropdown && filteredProducts.length"
                class="absolute left-0 right-0 top-full z-50 mt-1 max-h-60 overflow-y-auto rounded-2xl border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-surface-dark"
            >
                <button
                    v-for="p in filteredProducts"
                    :key="p.id"
                    @mousedown="emit('select-product', p)"
                    class="flex w-full items-center gap-2 border-b border-gray-100 px-4 py-2.5 text-left text-sm last:border-0 hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-800"
                >
                    <span
                        class="min-w-0 flex-1 whitespace-normal break-words font-medium text-content-primary dark:text-white"
                        >{{ p.name }}</span
                    >
                    <span class="shrink-0 font-bold text-primary-500">{{
                        formatCLP(p.price)
                    }}</span>
                </button>
            </div>
        </div>
    </div>
</template>
