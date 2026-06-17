<script setup lang="ts">
import { ShoppingCart, Star } from 'lucide-vue-next';
import { useCartStore, type Product } from '../../Stores/cartStore';

const props = defineProps<{
    product: Product;
}>();

const cartStore = useCartStore();

function handleAddToCart() {
    cartStore.addToCart(props.product, 1);
    // Here we could also trigger a UI toast notification from uiStore
}
</script>

<template>
    <div
        class="group relative overflow-hidden rounded-3xl bg-surface shadow-glass transition-all duration-300 ease-spring hover:-translate-y-1 hover:shadow-primary-md dark:bg-surface-dark dark:shadow-glass-dark"
    >
        <!-- Badge -->
        <div
            v-if="product.badge"
            class="absolute left-4 top-4 z-10 rounded-full bg-accent-500 px-3 py-1 text-xs font-bold text-surface-dark shadow-sm"
        >
            {{ product.badge }}
        </div>

        <!-- Image Container -->
        <div
            class="relative h-48 w-full overflow-hidden bg-gray-100 dark:bg-gray-800"
        >
            <!-- Using a placeholder color for the image if missing, or rendering the src -->
            <img
                v-if="product.image"
                :src="product.image"
                :alt="product.name"
                class="h-full w-full transform object-cover transition-transform duration-500 ease-spring group-hover:scale-105"
            />
            <div
                v-else
                class="flex h-full w-full items-center justify-center bg-gradient-to-br from-primary-100 to-secondary-100 text-primary-300 dark:from-primary-900 dark:to-secondary-900"
            >
                <span>No Image</span>
            </div>

            <!-- Quick Add Overlay (Desktop) -->
            <div
                class="absolute inset-x-0 bottom-0 hidden bg-gradient-to-t from-black/50 to-transparent p-4 opacity-0 transition-opacity duration-300 group-hover:opacity-100 sm:block"
            >
                <button
                    @click.prevent="handleAddToCart"
                    class="flex w-full items-center justify-center rounded-full bg-accent-500 py-2 font-semibold text-surface-dark shadow-sm transition-colors hover:bg-accent-400"
                >
                    <ShoppingCart class="mr-2 h-4 w-4" />
                    Añadir
                </button>
            </div>
        </div>

        <!-- Content -->
        <div class="p-5">
            <div class="mb-2 flex items-center space-x-1" v-if="product.rating">
                <Star class="h-4 w-4 fill-current text-accent-500" />
                <span
                    class="text-sm text-content-secondary dark:text-content-muted"
                    >{{ product.rating }}</span
                >
            </div>

            <h3
                class="mb-2 line-clamp-2 text-lg font-bold text-content-primary transition-colors group-hover:text-primary-500 dark:text-content-inverted"
            >
                {{ product.name }}
            </h3>

            <div class="mt-4 flex items-center justify-between">
                <span class="text-xl font-extrabold text-primary-500">
                    ${{ product.price.toFixed(2) }}
                </span>

                <!-- Mobile Add Button -->
                <button
                    @click.prevent="handleAddToCart"
                    class="rounded-full bg-accent-500 p-2 text-surface-dark shadow-sm transition-colors hover:bg-accent-400 sm:hidden"
                >
                    <ShoppingCart class="h-5 w-5" />
                </button>
            </div>
        </div>
    </div>
</template>
