<script setup lang="ts">
import { ShoppingCart, Minus, Plus, Trash2, X } from 'lucide-vue-next';
import type { CartItem } from '@/Stores/posTabsStore';
import { ref, nextTick, watch } from 'vue';

const props = defineProps<{
    cart: CartItem[];
    scannedProductIndex: number | null;
    itemCount: number;
}>();

const emit = defineEmits<{
    'increment-qty': [index: number];
    'decrement-qty': [index: number];
    'remove-item': [index: number];
    'confirm-clear-cart': [];
    'focus-barcode': [];
}>();

const showClearCartModal = ref(false);
const cancelClearBtnRef = ref<HTMLButtonElement | null>(null);

watch(showClearCartModal, (val) => {
    if (val) nextTick(() => cancelClearBtnRef.value?.focus());
});

function confirmClear() {
    emit('confirm-clear-cart');
    showClearCartModal.value = false;
}

function cancelClear() {
    showClearCartModal.value = false;
}

function formatCLP(cents: number): string {
    return '$' + Math.round(cents / 100).toLocaleString('es-CL', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
}
</script>

<template>
    <div
        class="flex items-center gap-3 border-b border-gray-100 px-4 py-3 dark:border-gray-800"
    >
        <ShoppingCart class="h-5 w-5 text-primary-500" />
        <h2
            class="flex-1 font-bold text-content-primary dark:text-white"
        >
            Venta Actual
        </h2>
        <span
            class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-bold text-content-muted dark:bg-gray-800"
        >
            {{ itemCount }}
            {{ itemCount === 1 ? 'ítem' : 'ítems' }}
        </span>
        <button
            v-if="cart.length"
            @click="showClearCartModal = true"
            class="text-xs font-bold text-danger transition-colors hover:text-danger/80"
        >
            Limpiar
        </button>
    </div>

    <div class="flex-1 overflow-y-auto">
        <table class="w-full text-left">
            <thead
                class="sticky top-0 bg-gray-50 text-xs uppercase tracking-wider text-content-muted dark:bg-gray-900/50 dark:text-gray-500"
            >
                <tr>
                    <th class="w-24 px-4 py-2.5 font-bold">Código</th>
                    <th class="px-4 py-2.5 font-bold">Descripción</th>
                    <th class="w-36 px-4 py-2.5 text-center font-bold">Cantidad</th>
                    <th class="w-28 px-4 py-2.5 text-right font-bold">P.Unitario</th>
                    <th class="w-28 px-4 py-2.5 text-right font-bold">Total</th>
                    <th class="w-12 px-4 py-2.5"></th>
                </tr>
            </thead>
            <tbody
                class="divide-y divide-gray-100 dark:divide-gray-800"
            >
                <tr v-if="!cart.length">
                    <td
                        colspan="6"
                        class="px-4 py-16 text-center text-sm text-content-muted dark:text-gray-500"
                    >
                        <ShoppingCart class="mx-auto mb-3 h-10 w-10 opacity-30" />
                        Escanea o busca productos para comenzar la venta
                    </td>
                </tr>
                <tr
                    v-for="(item, i) in cart"
                    :key="item.product.id"
                    class="transition-all duration-300 hover:bg-gray-50 dark:hover:bg-gray-800/30"
                    :class="{
                        'animate-pulse bg-emerald-50 dark:bg-emerald-900/20':
                            scannedProductIndex === i,
                    }"
                >
                    <td
                        class="px-4 py-3 font-mono text-xs text-content-muted"
                    >
                        {{ item.product.sku }}
                    </td>
                    <td
                        class="max-w-[240px] truncate px-4 py-3 text-sm font-medium text-content-primary dark:text-white"
                    >
                        {{ item.product.name }}
                        <span class="ml-1 text-[10px] text-content-muted">/{{ item.product.unit }}</span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-center gap-1">
                            <button
                                @click="emit('decrement-qty', i)"
                                class="flex h-7 w-7 items-center justify-center rounded-lg bg-gray-100 transition-colors hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700"
                            >
                                <Minus class="h-3.5 w-3.5 text-content-secondary" />
                            </button>
                            <input
                                :value="item.quantity"
                                @keydown.enter.prevent="emit('focus-barcode')"
                                type="number"
                                min="1"
                                :max="item.product.stock"
                                class="w-14 rounded-lg border border-gray-200 bg-transparent px-1 py-1.5 text-center text-sm font-bold text-content-primary [appearance:textfield] dark:border-gray-700 dark:text-white [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none"
                            />
                            <button
                                @click="emit('increment-qty', i)"
                                class="flex h-7 w-7 items-center justify-center rounded-lg bg-primary-100 transition-colors hover:bg-primary-200 dark:bg-primary-900/30 dark:hover:bg-primary-800/40"
                                :class="{
                                    'cursor-not-allowed opacity-30':
                                        item.quantity >= item.product.stock,
                                }"
                            >
                                <Plus class="h-3.5 w-3.5 text-primary-500" />
                            </button>
                        </div>
                    </td>
                    <td
                        class="px-4 py-3 text-right text-sm font-medium text-content-primary dark:text-white"
                    >
                        {{ formatCLP(item.product.price) }}
                    </td>
                    <td
                        class="px-4 py-3 text-right text-sm font-bold text-content-primary dark:text-white"
                    >
                        {{ formatCLP(item.product.price * item.quantity) }}
                    </td>
                    <td class="px-4 py-3 text-right">
                        <button
                            @click="emit('remove-item', i)"
                            class="rounded-lg p-1.5 text-danger/60 transition-colors hover:bg-red-50 hover:text-danger dark:hover:bg-red-900/20"
                        >
                            <Trash2 class="h-4 w-4" />
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Clear Cart Confirmation Modal -->
    <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
            v-if="showClearCartModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4 backdrop-blur-sm"
            @click.self="cancelClear"
        >
            <div
                class="w-full max-w-sm rounded-2xl bg-white shadow-xl dark:bg-surface-dark"
            >
                <div
                    class="flex items-center justify-between border-b border-gray-100 px-5 py-3 dark:border-gray-800"
                >
                    <h3
                        class="font-display text-sm font-bold text-content-primary dark:text-white"
                    >
                        Limpiar Carrito
                    </h3>
                    <button
                        @click="cancelClear"
                        class="rounded-lg p-1 transition-colors hover:bg-gray-100 dark:hover:bg-gray-800"
                    >
                        <X class="h-4 w-4 text-content-muted" />
                    </button>
                </div>
                <div class="space-y-4 p-5">
                    <p class="text-sm leading-relaxed text-content-secondary">
                        ¿Está seguro que desea eliminar la venta?
                    </p>
                    <div class="flex gap-2">
                        <button
                            ref="cancelClearBtnRef"
                            @click="cancelClear"
                            class="flex-1 rounded-xl bg-rose-500 py-2.5 text-xs font-bold text-white shadow-sm transition-colors hover:bg-rose-600"
                        >
                            Cancelar
                        </button>
                        <button
                            @click="confirmClear"
                            class="flex-1 rounded-xl border border-gray-200 bg-white py-2.5 text-xs font-bold text-content-secondary transition-colors hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:hover:bg-gray-700"
                        >
                            Aceptar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Transition>
</template>
