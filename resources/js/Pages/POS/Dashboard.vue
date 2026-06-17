<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Delete, LogOut, ScanBarcode, ShoppingCart } from 'lucide-vue-next';
import { ref } from 'vue';
import POSLayout from '../../Layouts/POSLayout.vue';
import { useCartStore } from '../../Stores/cartStore';
import { usePosStore } from '../../Stores/posStore';

const posStore = usePosStore();
const cartStore = useCartStore();

// PIN Pad State
const currentPin = ref('');

function appendPin(num: number) {
    if (currentPin.value.length < 4) {
        currentPin.value += num.toString();
    }
}

function clearPin() {
    currentPin.value = '';
}

function submitPin() {
    if (!posStore.login(currentPin.value)) {
        alert('Invalid PIN');
        clearPin();
    }
}

// Mock products for POS
const quickProducts = [
    { id: 1, name: 'Agua Mineral 500ml', price: 1.0, image: '' },
    { id: 2, name: 'Refresco Cola 1L', price: 2.5, image: '' },
    { id: 3, name: 'Papas Fritas Clásicas', price: 1.8, image: '' },
    { id: 4, name: 'Galletas de Chocolate', price: 1.2, image: '' },
    { id: 5, name: 'Chicle Menta', price: 0.5, image: '' },
    { id: 6, name: 'Pan de Molde', price: 2.2, image: '' },
];
</script>

<template>
    <Head title="POS Terminal" />

    <POSLayout>
        <!-- LOGIN SCREEN -->
        <div
            v-if="!posStore.posModeActive"
            class="flex flex-1 flex-col items-center justify-center bg-bg-dark text-content-inverted"
        >
            <div class="mb-8 text-center">
                <h1
                    class="mb-2 font-display text-4xl font-bold text-primary-500"
                >
                    Monasterios Market
                </h1>
                <p class="text-xl text-content-muted">
                    Terminal Punto de Venta
                </p>
            </div>

            <div
                class="w-96 max-w-full rounded-4xl bg-surface-dark p-8 shadow-glass-dark"
            >
                <div class="mb-6 text-center">
                    <div
                        class="mb-4 flex h-16 items-center justify-center rounded-2xl bg-gray-900"
                    >
                        <span class="text-3xl tracking-widest"
                            >{{ '*'.repeat(currentPin.length)
                            }}<span
                                v-if="currentPin.length === 0"
                                class="text-gray-600"
                                >PIN</span
                            ></span
                        >
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <button
                        v-for="num in 9"
                        :key="num"
                        @click="appendPin(num)"
                        class="h-16 rounded-full bg-gray-800 text-2xl font-bold transition-colors hover:bg-primary-600"
                    >
                        {{ num }}
                    </button>
                    <button
                        @click="clearPin"
                        class="flex h-16 items-center justify-center rounded-full bg-gray-800 text-2xl font-bold transition-colors hover:bg-danger-dark"
                    >
                        <Delete class="h-6 w-6" />
                    </button>
                    <button
                        @click="appendPin(0)"
                        class="h-16 rounded-full bg-gray-800 text-2xl font-bold transition-colors hover:bg-primary-600"
                    >
                        0
                    </button>
                    <button
                        @click="submitPin"
                        class="h-16 rounded-full bg-accent-500 text-xl font-bold text-surface-dark transition-colors hover:bg-accent-400"
                    >
                        OK
                    </button>
                </div>
            </div>
        </div>

        <!-- MAIN POS INTERFACE -->
        <div v-else class="flex w-full flex-1 flex-row">
            <!-- Left Side: Products & Barcode -->
            <div
                class="flex flex-1 flex-col border-r border-gray-200 bg-bg-light p-4 dark:border-gray-800 dark:bg-bg-dark sm:p-6"
            >
                <!-- Header / Barcode Scanner -->
                <div class="mb-6 flex items-center space-x-4">
                    <div class="relative flex-1">
                        <div
                            class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4"
                        >
                            <ScanBarcode class="h-6 w-6 text-content-muted" />
                        </div>
                        <input
                            v-model="posStore.barcodeInput"
                            @keyup.enter="posStore.processBarcode"
                            type="text"
                            placeholder="Escanear código de barras o buscar..."
                            class="block w-full rounded-2xl border-2 border-primary-200 bg-surface py-4 pl-12 pr-4 text-lg text-content-primary placeholder-content-muted transition-colors focus:border-primary-500 focus:outline-none focus:ring-0 dark:border-primary-800 dark:bg-surface-dark dark:text-content-inverted"
                            autofocus
                        />
                    </div>
                    <button
                        @click="
                            posStore.posModeActive = false;
                            currentPin = '';
                        "
                        class="rounded-2xl bg-surface p-4 text-content-secondary shadow-sm transition-all hover:text-danger hover:shadow-md dark:bg-surface-dark"
                    >
                        <LogOut class="h-6 w-6" />
                    </button>
                </div>

                <!-- Quick Add Grid -->
                <div class="flex-1 overflow-y-auto">
                    <h2
                        class="mb-4 text-xl font-bold text-content-primary dark:text-content-inverted"
                    >
                        Acceso Rápido
                    </h2>
                    <div
                        class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4"
                    >
                        <button
                            v-for="product in quickProducts"
                            :key="product.id"
                            @click="cartStore.addToCart(product, 1)"
                            class="flex flex-col items-center justify-center rounded-2xl border-2 border-transparent bg-surface p-4 shadow-sm transition-all hover:border-accent-500 hover:shadow-accent-sm active:scale-95 dark:bg-surface-dark"
                        >
                            <div
                                class="mb-3 flex h-16 w-16 items-center justify-center rounded-full bg-gradient-to-tr from-primary-100 to-accent-100 font-bold text-primary-500 dark:from-primary-900 dark:to-accent-900"
                            >
                                {{ product.name.charAt(0) }}
                            </div>
                            <span
                                class="line-clamp-2 text-center text-sm font-medium text-content-primary dark:text-content-inverted"
                                >{{ product.name }}</span
                            >
                            <span class="mt-1 font-bold text-primary-500"
                                >${{ product.price.toFixed(2) }}</span
                            >
                        </button>
                    </div>
                </div>
            </div>

            <!-- Right Side: Ticket / Receipt -->
            <div
                class="z-10 flex w-96 flex-col bg-surface shadow-glass dark:bg-surface-dark dark:shadow-glass-dark"
            >
                <!-- Ticket Header -->
                <div
                    class="flex items-center justify-between border-b border-gray-200 bg-primary-500 p-6 text-white dark:border-gray-800"
                >
                    <h2
                        class="flex items-center font-display text-xl font-bold"
                    >
                        <ShoppingCart class="mr-2 h-6 w-6" />
                        Ticket
                    </h2>
                    <span class="rounded bg-white/20 px-2 py-1 text-sm"
                        >Caja 1</span
                    >
                </div>

                <!-- Cart Items -->
                <div class="flex-1 space-y-4 overflow-y-auto p-4">
                    <div
                        v-if="cartStore.items.length === 0"
                        class="flex h-full flex-col items-center justify-center text-content-muted"
                    >
                        <ShoppingCart class="mb-4 h-16 w-16 opacity-50" />
                        <p>El ticket está vacío</p>
                    </div>

                    <div
                        v-for="item in cartStore.items"
                        :key="item.product.id"
                        class="flex items-center justify-between rounded-xl bg-bg-light p-3 dark:bg-bg-dark"
                    >
                        <div class="flex-1 pr-2">
                            <h3
                                class="line-clamp-1 font-bold text-content-primary dark:text-content-inverted"
                            >
                                {{ item.product.name }}
                            </h3>
                            <div class="mt-1 text-sm text-content-secondary">
                                ${{ item.product.price.toFixed(2) }} x
                                {{ item.quantity }}
                            </div>
                        </div>
                        <div class="flex items-center space-x-3">
                            <span class="font-bold text-primary-500"
                                >${{
                                    (
                                        item.product.price * item.quantity
                                    ).toFixed(2)
                                }}</span
                            >
                            <button
                                @click="
                                    cartStore.removeFromCart(item.product.id)
                                "
                                class="p-1 text-danger transition-colors hover:text-danger-dark"
                            >
                                <Delete class="h-5 w-5" />
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Totals & Checkout -->
                <div
                    class="border-t border-gray-200 bg-gray-50 p-6 dark:border-gray-800 dark:bg-gray-900"
                >
                    <div
                        class="mb-2 flex items-center justify-between text-content-secondary"
                    >
                        <span>Subtotal</span>
                        <span>${{ cartStore.subtotal.toFixed(2) }}</span>
                    </div>
                    <div
                        class="mb-4 flex items-center justify-between text-content-secondary"
                    >
                        <span>Impuestos (16%)</span>
                        <span
                            >${{ (cartStore.subtotal * 0.16).toFixed(2) }}</span
                        >
                    </div>
                    <div
                        class="mb-6 flex items-center justify-between text-2xl font-bold text-content-primary dark:text-content-inverted"
                    >
                        <span>Total</span>
                        <span class="text-accent-500"
                            >${{ (cartStore.subtotal * 1.16).toFixed(2) }}</span
                        >
                    </div>

                    <button
                        @click="posStore.checkout"
                        :disabled="cartStore.items.length === 0"
                        class="w-full rounded-2xl bg-primary-500 py-4 text-xl font-bold text-white shadow-primary-md transition-all hover:bg-primary-600 active:scale-95 disabled:cursor-not-allowed disabled:bg-gray-400"
                    >
                        Cobrar
                    </button>
                </div>
            </div>
        </div>
    </POSLayout>
</template>
