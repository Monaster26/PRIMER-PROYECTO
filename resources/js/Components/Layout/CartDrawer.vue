<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Minus, Plus, ShoppingBag, Trash2, X } from 'lucide-vue-next';
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
        <div
            v-if="uiStore.isCartOpen"
            @click="uiStore.toggleCart()"
            class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm"
        ></div>
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
        <div
            v-if="uiStore.isCartOpen"
            class="fixed inset-y-0 right-0 z-50 flex h-full w-full max-w-md flex-col border-l border-gray-200 bg-surface shadow-2xl dark:border-gray-800 dark:bg-surface-dark"
        >
            <!-- Header -->
            <div
                class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-gray-800"
            >
                <h2
                    class="flex items-center font-display text-xl font-bold text-content-primary dark:text-content-inverted"
                >
                    <ShoppingBag class="mr-2 h-6 w-6 text-primary-500" />
                    Mi Carrito
                    <span
                        class="ml-2 rounded-full bg-accent-500 px-2 py-0.5 text-sm font-bold text-surface-dark"
                    >
                        {{ cartStore.cartCount }}
                    </span>
                </h2>
                <button
                    @click="uiStore.toggleCart()"
                    class="rounded-full p-2 text-content-secondary transition-colors hover:bg-gray-100 hover:text-danger dark:hover:bg-gray-800"
                >
                    <X class="h-6 w-6" />
                </button>
            </div>

            <!-- Cart Items (Scrollable) -->
            <div class="flex-1 overflow-y-auto p-6">
                <!-- Empty State -->
                <div
                    v-if="cartStore.items.length === 0"
                    class="flex h-full flex-col items-center justify-center space-y-6 text-center"
                >
                    <div
                        class="flex h-24 w-24 items-center justify-center rounded-full bg-primary-50 dark:bg-primary-900/30"
                    >
                        <ShoppingBag
                            class="h-12 w-12 text-primary-300 dark:text-primary-700"
                        />
                    </div>
                    <div>
                        <p
                            class="text-xl font-bold text-content-primary dark:text-content-inverted"
                        >
                            Tu carrito está vacío
                        </p>
                        <p
                            class="mt-2 text-content-secondary dark:text-content-muted"
                        >
                            ¡Parece que aún no has agregado nada!
                        </p>
                    </div>
                    <button
                        @click="uiStore.toggleCart()"
                        class="rounded-full bg-primary-100 px-8 py-3 font-bold text-primary-600 transition-colors hover:bg-primary-200 dark:bg-primary-900/50 dark:text-primary-400 dark:hover:bg-primary-800"
                    >
                        Seguir Comprando
                    </button>
                </div>

                <!-- Items List -->
                <ul v-else class="space-y-6">
                    <li
                        v-for="item in cartStore.items"
                        :key="item.product.id"
                        class="flex py-2"
                    >
                        <!-- Product Image -->
                        <div
                            class="relative h-24 w-24 flex-shrink-0 overflow-hidden rounded-xl border border-gray-200 bg-gray-100 dark:border-gray-700 dark:bg-gray-800"
                        >
                            <img
                                v-if="item.product.image"
                                :src="item.product.image"
                                :alt="item.product.name"
                                class="h-full w-full object-cover object-center"
                            />
                            <div
                                v-else
                                class="flex h-full w-full items-center justify-center text-2xl font-bold text-primary-300"
                            >
                                {{ item.product.name.charAt(0) }}
                            </div>
                        </div>

                        <!-- Product Info & Controls -->
                        <div class="ml-4 flex flex-1 flex-col justify-center">
                            <div>
                                <div
                                    class="flex justify-between text-base font-bold text-content-primary dark:text-content-inverted"
                                >
                                    <h3 class="line-clamp-2">
                                        <a href="#">{{ item.product.name }}</a>
                                    </h3>
                                    <p class="ml-4 text-primary-500">
                                        ${{
                                            (
                                                item.product.price *
                                                item.quantity
                                            ).toFixed(2)
                                        }}
                                    </p>
                                </div>
                            </div>
                            <div
                                class="mt-4 flex flex-1 items-end justify-between text-sm"
                            >
                                <!-- Quantity Controls -->
                                <div
                                    class="flex items-center rounded-full border border-gray-200 bg-bg-light dark:border-gray-700 dark:bg-bg-dark"
                                >
                                    <button
                                        @click="
                                            cartStore.updateQuantity(
                                                item.product.id,
                                                item.quantity - 1,
                                            )
                                        "
                                        class="p-1 text-content-secondary transition-colors hover:text-primary-500"
                                    >
                                        <Minus class="h-4 w-4" />
                                    </button>
                                    <span
                                        class="w-8 text-center font-bold text-content-primary dark:text-content-inverted"
                                        >{{ item.quantity }}</span
                                    >
                                    <button
                                        @click="
                                            cartStore.updateQuantity(
                                                item.product.id,
                                                item.quantity + 1,
                                            )
                                        "
                                        class="p-1 text-content-secondary transition-colors hover:text-primary-500"
                                    >
                                        <Plus class="h-4 w-4" />
                                    </button>
                                </div>

                                <!-- Remove Button -->
                                <div class="flex">
                                    <button
                                        @click="
                                            cartStore.removeFromCart(
                                                item.product.id,
                                            )
                                        "
                                        type="button"
                                        class="flex items-center font-medium text-danger transition-colors hover:text-danger-dark"
                                    >
                                        <Trash2 class="mr-1 h-4 w-4" /> Eliminar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>

            <!-- Footer / Checkout Button -->
            <div
                v-if="cartStore.items.length > 0"
                class="border-t border-gray-200 bg-bg-light px-6 py-6 dark:border-gray-800 dark:bg-bg-dark"
            >
                <div
                    class="mb-4 flex justify-between text-base font-bold text-content-primary dark:text-content-inverted"
                >
                    <p>Subtotal</p>
                    <p class="text-2xl">${{ cartStore.subtotal.toFixed(2) }}</p>
                </div>
                <p
                    class="mb-6 mt-0.5 text-sm text-content-secondary dark:text-content-muted"
                >
                    Envío e impuestos calculados en el pago.
                </p>

                <Link
                    href="/checkout"
                    @click="uiStore.toggleCart()"
                    class="flex w-full items-center justify-center rounded-full border border-transparent bg-primary-500 px-6 py-4 text-base font-bold text-white shadow-primary-md transition-all duration-300 ease-spring hover:-translate-y-1 hover:bg-primary-600"
                >
                    Proceder al Pago
                </Link>
            </div>
        </div>
    </Transition>
</template>
