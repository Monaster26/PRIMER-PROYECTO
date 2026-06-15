<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import POSLayout from '../../Layouts/POSLayout.vue';
import { usePosStore } from '../../Stores/posStore';
import { useCartStore } from '../../Stores/cartStore';
import { ref } from 'vue';
import { ScanBarcode, Delete, ShoppingCart, LogOut } from 'lucide-vue-next';

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
    { id: 1, name: 'Agua Mineral 500ml', price: 1.00, image: '' },
    { id: 2, name: 'Refresco Cola 1L', price: 2.50, image: '' },
    { id: 3, name: 'Papas Fritas Clásicas', price: 1.80, image: '' },
    { id: 4, name: 'Galletas de Chocolate', price: 1.20, image: '' },
    { id: 5, name: 'Chicle Menta', price: 0.50, image: '' },
    { id: 6, name: 'Pan de Molde', price: 2.20, image: '' },
];
</script>

<template>
    <Head title="POS Terminal" />

    <POSLayout>
        <!-- LOGIN SCREEN -->
        <div v-if="!posStore.posModeActive" class="flex-1 flex flex-col items-center justify-center bg-bg-dark text-content-inverted">
            <div class="mb-8 text-center">
                <h1 class="text-4xl font-display font-bold text-primary-500 mb-2">Monasterios Market</h1>
                <p class="text-xl text-content-muted">Terminal Punto de Venta</p>
            </div>
            
            <div class="bg-surface-dark p-8 rounded-4xl shadow-glass-dark w-96 max-w-full">
                <div class="mb-6 text-center">
                    <div class="h-16 flex items-center justify-center bg-gray-900 rounded-2xl mb-4">
                        <span class="text-3xl tracking-widest">{{ '*'.repeat(currentPin.length) }}<span v-if="currentPin.length === 0" class="text-gray-600">PIN</span></span>
                    </div>
                </div>
                
                <div class="grid grid-cols-3 gap-4">
                    <button v-for="num in 9" :key="num" @click="appendPin(num)" class="h-16 rounded-full bg-gray-800 hover:bg-primary-600 text-2xl font-bold transition-colors">
                        {{ num }}
                    </button>
                    <button @click="clearPin" class="h-16 rounded-full bg-gray-800 hover:bg-danger-dark text-2xl font-bold transition-colors flex items-center justify-center">
                        <Delete class="w-6 h-6" />
                    </button>
                    <button @click="appendPin(0)" class="h-16 rounded-full bg-gray-800 hover:bg-primary-600 text-2xl font-bold transition-colors">
                        0
                    </button>
                    <button @click="submitPin" class="h-16 rounded-full bg-accent-500 hover:bg-accent-400 text-surface-dark text-xl font-bold transition-colors">
                        OK
                    </button>
                </div>
            </div>
        </div>

        <!-- MAIN POS INTERFACE -->
        <div v-else class="flex-1 flex flex-row w-full">
            <!-- Left Side: Products & Barcode -->
            <div class="flex-1 flex flex-col p-4 sm:p-6 bg-bg-light dark:bg-bg-dark border-r border-gray-200 dark:border-gray-800">
                <!-- Header / Barcode Scanner -->
                <div class="flex items-center space-x-4 mb-6">
                    <div class="flex-1 relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <ScanBarcode class="h-6 w-6 text-content-muted" />
                        </div>
                        <input
                            v-model="posStore.barcodeInput"
                            @keyup.enter="posStore.processBarcode"
                            type="text"
                            placeholder="Escanear código de barras o buscar..."
                            class="block w-full pl-12 pr-4 py-4 text-lg border-2 border-primary-200 dark:border-primary-800 rounded-2xl bg-surface dark:bg-surface-dark text-content-primary dark:text-content-inverted placeholder-content-muted focus:outline-none focus:ring-0 focus:border-primary-500 transition-colors"
                            autofocus
                        />
                    </div>
                    <button @click="posStore.posModeActive = false; currentPin = ''" class="p-4 text-content-secondary hover:text-danger rounded-2xl bg-surface dark:bg-surface-dark shadow-sm hover:shadow-md transition-all">
                        <LogOut class="h-6 w-6" />
                    </button>
                </div>

                <!-- Quick Add Grid -->
                <div class="flex-1 overflow-y-auto">
                    <h2 class="text-xl font-bold text-content-primary dark:text-content-inverted mb-4">Acceso Rápido</h2>
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                        <button 
                            v-for="product in quickProducts" 
                            :key="product.id"
                            @click="cartStore.addToCart(product, 1)"
                            class="flex flex-col items-center justify-center p-4 bg-surface dark:bg-surface-dark rounded-2xl shadow-sm hover:shadow-accent-sm border-2 border-transparent hover:border-accent-500 transition-all active:scale-95"
                        >
                            <div class="w-16 h-16 rounded-full bg-gradient-to-tr from-primary-100 to-accent-100 dark:from-primary-900 dark:to-accent-900 mb-3 flex items-center justify-center text-primary-500 font-bold">
                                {{ product.name.charAt(0) }}
                            </div>
                            <span class="text-sm font-medium text-center text-content-primary dark:text-content-inverted line-clamp-2">{{ product.name }}</span>
                            <span class="text-primary-500 font-bold mt-1">${{ product.price.toFixed(2) }}</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Right Side: Ticket / Receipt -->
            <div class="w-96 flex flex-col bg-surface dark:bg-surface-dark shadow-glass dark:shadow-glass-dark z-10">
                <!-- Ticket Header -->
                <div class="p-6 border-b border-gray-200 dark:border-gray-800 flex justify-between items-center bg-primary-500 text-white">
                    <h2 class="text-xl font-display font-bold flex items-center">
                        <ShoppingCart class="w-6 h-6 mr-2" />
                        Ticket
                    </h2>
                    <span class="text-sm bg-white/20 px-2 py-1 rounded">Caja 1</span>
                </div>

                <!-- Cart Items -->
                <div class="flex-1 overflow-y-auto p-4 space-y-4">
                    <div v-if="cartStore.items.length === 0" class="h-full flex flex-col items-center justify-center text-content-muted">
                        <ShoppingCart class="w-16 h-16 mb-4 opacity-50" />
                        <p>El ticket está vacío</p>
                    </div>
                    
                    <div v-for="item in cartStore.items" :key="item.product.id" class="flex justify-between items-center p-3 bg-bg-light dark:bg-bg-dark rounded-xl">
                        <div class="flex-1 pr-2">
                            <h3 class="font-bold text-content-primary dark:text-content-inverted line-clamp-1">{{ item.product.name }}</h3>
                            <div class="text-sm text-content-secondary mt-1">
                                ${{ item.product.price.toFixed(2) }} x {{ item.quantity }}
                            </div>
                        </div>
                        <div class="flex items-center space-x-3">
                            <span class="font-bold text-primary-500">${{ (item.product.price * item.quantity).toFixed(2) }}</span>
                            <button @click="cartStore.removeFromCart(item.product.id)" class="text-danger hover:text-danger-dark transition-colors p-1">
                                <Delete class="w-5 h-5" />
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Totals & Checkout -->
                <div class="p-6 bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-800">
                    <div class="flex justify-between items-center mb-2 text-content-secondary">
                        <span>Subtotal</span>
                        <span>${{ cartStore.subtotal.toFixed(2) }}</span>
                    </div>
                    <div class="flex justify-between items-center mb-4 text-content-secondary">
                        <span>Impuestos (16%)</span>
                        <span>${{ (cartStore.subtotal * 0.16).toFixed(2) }}</span>
                    </div>
                    <div class="flex justify-between items-center mb-6 text-2xl font-bold text-content-primary dark:text-content-inverted">
                        <span>Total</span>
                        <span class="text-accent-500">${{ (cartStore.subtotal * 1.16).toFixed(2) }}</span>
                    </div>
                    
                    <button 
                        @click="posStore.checkout"
                        :disabled="cartStore.items.length === 0"
                        class="w-full py-4 bg-primary-500 hover:bg-primary-600 disabled:bg-gray-400 disabled:cursor-not-allowed text-white text-xl font-bold rounded-2xl shadow-primary-md transition-all active:scale-95"
                    >
                        Cobrar
                    </button>
                </div>
            </div>
        </div>
    </POSLayout>
</template>
