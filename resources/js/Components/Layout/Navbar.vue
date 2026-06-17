<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Moon, Search, ShoppingCart, Sun, User } from 'lucide-vue-next';
import { useCartStore } from '../../Stores/cartStore';
import { useUiStore } from '../../Stores/uiStore';
import CategoryMenu from './CategoryMenu.vue';

const cartStore = useCartStore();
const uiStore = useUiStore();
</script>

<template>
    <nav
        class="dark:from-secondary-950 sticky top-0 z-50 w-full border-b border-gray-100 bg-gradient-to-b from-secondary-50 via-white to-white shadow-sm backdrop-blur-md transition-colors duration-300 dark:border-gray-800 dark:via-bg-dark dark:to-bg-dark"
    >
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between gap-4">
                <!-- Logo -->
                <div class="flex flex-shrink-0 items-center">
                    <Link href="/" class="group flex items-center gap-3">
                        <img
                            src="/images/logo.png"
                            alt="Logo"
                            class="h-20 w-auto object-contain transition-transform duration-300 group-hover:rotate-12"
                        />
                        <span
                            class="font-display text-xl font-bold text-primary-500"
                        >
                            Monasterios<span class="text-accent-500"
                                >Market</span
                            >
                        </span>
                    </Link>
                </div>

                <!-- Categories Mega Menu -->
                <CategoryMenu />

                <!-- Search Bar -->
                <div class="hidden max-w-lg flex-1 sm:flex">
                    <div class="relative w-full">
                        <div
                            class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"
                        >
                            <Search class="h-5 w-5 text-content-muted" />
                        </div>
                        <input
                            type="text"
                            placeholder="Buscar productos, categorías..."
                            class="block w-full rounded-full border border-gray-300 bg-bg-light py-2 pl-10 pr-3 leading-5 text-content-primary placeholder-content-muted transition-colors duration-300 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:border-gray-700 dark:bg-bg-dark dark:text-content-inverted sm:text-sm"
                        />
                    </div>
                </div>

                <!-- Navigation Icons -->
                <div class="flex items-center space-x-4 sm:space-x-5">
                    <button
                        @click="uiStore.toggleDarkMode()"
                        class="text-content-secondary transition-colors hover:text-primary-500"
                    >
                        <Sun v-if="uiStore.isDark" class="h-5 w-5" />
                        <Moon v-else class="h-5 w-5" />
                    </button>

                    <Link
                        href="/login"
                        class="hidden text-content-secondary transition-colors hover:text-primary-500 sm:flex"
                    >
                        <User class="h-6 w-6" />
                    </Link>

                    <button
                        @click="uiStore.toggleCart()"
                        class="relative text-content-secondary transition-colors hover:text-primary-500"
                    >
                        <ShoppingCart class="h-6 w-6" />
                        <span
                            v-if="cartStore.cartCount > 0"
                            class="absolute -right-2 -top-2 flex h-5 w-5 animate-pop-in items-center justify-center rounded-full bg-accent-500 text-xs font-bold text-surface-dark"
                        >
                            {{ cartStore.cartCount }}
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </nav>
</template>
