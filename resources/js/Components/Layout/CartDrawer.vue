<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { X, Minus, Plus, Trash2, ShoppingBag } from 'lucide-vue-next';
import { useCartStore } from '../../Stores/cartStore';
import { useUiStore } from '../../Stores/uiStore';

const cartStore = useCartStore();
const uiStore = useUiStore();
</script>

<template>
    <!-- Overlay -->
    <Transition
        enter-active-class="transition-opacity ease-linear duration-300"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition-opacity ease-linear duration-300"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div v-if="uiStore.isCartOpen" @click="uiStore.toggleCart()" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50"></div>
    </Transition>

    <!-- Drawer Panel -->
    <Transition
        enter-active-class="transition ease-in-out duration-300 transform"
        enter-from-class="translate-x-full"
        enter-to-class="translate-x-0"
        leave-active-class="transition ease-in-out duration-300 transform"
        leave-from-class="translate-x-0"
        leave-to-class="translate-x-full"
    >
        <div v-if="uiStore.isCartOpen" class="fixed inset-y-0 right-0 max-w-md w-full bg-surface dark:bg-surface-dark shadow-2xl z-50 flex flex-col h-full border-l border-gray-200 dark:border-gray-800">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between">
                <h2 class="text-xl font-display font-bold text-content-primary dark:text-content-inverted flex items-center">
                    <ShoppingBag class="w-6 h-6 mr-2 text-primary-500" />
                    Mi Carrito
                    <span class="ml-2 text-sm bg-accent-500 text-surface-dark px-2 py-0.5 rounded-full font-bold">
                        {{ cartStore.cartCount }}
                    </span>
                </h2>
                <button @click="uiStore.toggleCart()" class="text-content-secondary hover:text-danger transition-colors p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-800">
                    <X class="w-6 h-6" />
                </button>
            </div>

            <!-- Cart Items (Scrollable) -->
            <div class="flex-1 overflow-y-auto p-6">
                <!-- Empty State -->
                <div v-if="cartStore.items.length === 0" class="h-full flex flex-col items-center justify-center text-center space-y-6">
                    <div class="w-24 h-24 bg-primary-50 dark:bg-primary-900/30 rounded-full flex items-center justify-center">
                        <ShoppingBag class="w-12 h-12 text-primary-300 dark:text-primary-700" />
                    </div>
                    <div>
                        <p class="text-xl font-bold text-content-primary dark:text-content-inverted">Tu carrito está vacío</p>
                        <p class="mt-2 text-content-secondary dark:text-content-muted">¡Parece que aún no has agregado nada!</p>
                    </div>
                    <button @click="uiStore.toggleCart()" class="px-8 py-3 bg-primary-100 dark:bg-primary-900/50 text-primary-600 dark:text-primary-400 font-bold rounded-full hover:bg-primary-200 dark:hover:bg-primary-800 transition-colors">
                        Seguir Comprando
                    </button>
                </div>

                <!-- Items List -->
                <ul v-else class="space-y-6">
                    <li v-for="item in cartStore.items" :key="item.product.id" class="flex py-2">
                        <!-- Product Image -->
                        <div class="h-24 w-24 flex-shrink-0 overflow-hidden rounded-xl bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 relative">
                            <img v-if="item.product.image" :src="item.product.image" :alt="item.product.name" class="h-full w-full object-cover object-center" />
                            <div v-else class="h-full w-full flex items-center justify-center text-primary-300 font-bold text-2xl">
                                {{ item.product.name.charAt(0) }}
                            </div>
                        </div>

                        <!-- Product Info & Controls -->
                        <div class="ml-4 flex flex-1 flex-col justify-center">
                            <div>
                                <div class="flex justify-between text-base font-bold text-content-primary dark:text-content-inverted">
                                    <h3 class="line-clamp-2">
                                        <a href="#">{{ item.product.name }}</a>
                                    </h3>
                                    <p class="ml-4 text-primary-500">${{ (item.product.price * item.quantity).toFixed(2) }}</p>
                                </div>
                            </div>
                            <div class="flex flex-1 items-end justify-between text-sm mt-4">
                                <!-- Quantity Controls -->
                                <div class="flex items-center border border-gray-200 dark:border-gray-700 rounded-full bg-bg-light dark:bg-bg-dark">
                                    <button @click="cartStore.updateQuantity(item.product.id, item.quantity - 1)" class="p-1 text-content-secondary hover:text-primary-500 transition-colors">
                                        <Minus class="w-4 h-4" />
                                    </button>
                                    <span class="w-8 text-center font-bold text-content-primary dark:text-content-inverted">{{ item.quantity }}</span>
                                    <button @click="cartStore.updateQuantity(item.product.id, item.quantity + 1)" class="p-1 text-content-secondary hover:text-primary-500 transition-colors">
                                        <Plus class="w-4 h-4" />
                                    </button>
                                </div>

                                <!-- Remove Button -->
                                <div class="flex">
                                    <button @click="cartStore.removeFromCart(item.product.id)" type="button" class="font-medium text-danger hover:text-danger-dark transition-colors flex items-center">
                                        <Trash2 class="w-4 h-4 mr-1" /> Eliminar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>

            <!-- Footer / Checkout Button -->
            <div v-if="cartStore.items.length > 0" class="border-t border-gray-200 dark:border-gray-800 px-6 py-6 bg-bg-light dark:bg-bg-dark">
                <div class="flex justify-between text-base font-bold text-content-primary dark:text-content-inverted mb-4">
                    <p>Subtotal</p>
                    <p class="text-2xl">${{ cartStore.subtotal.toFixed(2) }}</p>
                </div>
                <p class="mt-0.5 text-sm text-content-secondary dark:text-content-muted mb-6">Envío e impuestos calculados en el pago.</p>
                
                <Link href="/checkout" @click="uiStore.toggleCart()" class="w-full flex items-center justify-center rounded-full border border-transparent bg-primary-500 px-6 py-4 text-base font-bold text-white shadow-primary-md hover:bg-primary-600 hover:-translate-y-1 transition-all duration-300 ease-spring">
                    Proceder al Pago
                </Link>
            </div>
        </div>
    </Transition>
</template>
