<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ShoppingCart, Search, User, Sun, Moon } from 'lucide-vue-next';
import { useCartStore } from '../../Stores/cartStore';
import { useUiStore } from '../../Stores/uiStore';
import CategoryMenu from './CategoryMenu.vue';

const cartStore = useCartStore();
const uiStore = useUiStore();
</script>

<template>
    <nav class="sticky top-0 z-50 w-full bg-gradient-to-b from-secondary-50 via-white to-white dark:from-secondary-950 dark:via-bg-dark dark:to-bg-dark backdrop-blur-md border-b border-gray-100 dark:border-gray-800 transition-colors duration-300 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16 gap-4">

                <!-- Logo -->
                <div class="flex-shrink-0 flex items-center">
                    <Link href="/" class="flex items-center gap-3 group">
                        <img src="/images/logo.png" alt="Logo" class="h-20 w-auto object-contain group-hover:rotate-12 transition-transform duration-300" />
                        <span class="text-xl font-display font-bold text-primary-500">
                            Monasterios<span class="text-accent-500">Market</span>
                        </span>
                    </Link>
                </div>

                <!-- Categories Mega Menu -->
                <CategoryMenu />

                <!-- Search Bar -->
                <div class="hidden sm:flex flex-1 max-w-lg">
                    <div class="relative w-full">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <Search class="h-5 w-5 text-content-muted" />
                        </div>
                        <input
                            type="text"
                            placeholder="Buscar productos, categorías..."
                            class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-700 rounded-full leading-5 bg-bg-light dark:bg-bg-dark text-content-primary dark:text-content-inverted placeholder-content-muted focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors duration-300"
                        />
                    </div>
                </div>

                <!-- Navigation Icons -->
                <div class="flex items-center space-x-4 sm:space-x-5">
                    <button @click="uiStore.toggleDarkMode()" class="text-content-secondary hover:text-primary-500 transition-colors">
                        <Sun v-if="uiStore.isDark" class="h-5 w-5" />
                        <Moon v-else class="h-5 w-5" />
                    </button>

                    <Link href="/login" class="hidden sm:flex text-content-secondary hover:text-primary-500 transition-colors">
                        <User class="h-6 w-6" />
                    </Link>

                    <button @click="uiStore.toggleCart()" class="relative text-content-secondary hover:text-primary-500 transition-colors">
                        <ShoppingCart class="h-6 w-6" />
                        <span v-if="cartStore.cartCount > 0" class="absolute -top-2 -right-2 bg-accent-500 text-surface-dark text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center animate-pop-in">
                            {{ cartStore.cartCount }}
                        </span>
                    </button>
                </div>

            </div>
        </div>
    </nav>
</template>
