<script setup lang="ts">
import { ShoppingCart, Star } from 'lucide-vue-next';
import { useCartStore, type Product } from '../../Stores/cartStore';

const props = defineProps<{
    product: Product
}>();

const cartStore = useCartStore();

function handleAddToCart() {
    cartStore.addToCart(props.product, 1);
    // Here we could also trigger a UI toast notification from uiStore
}
</script>

<template>
    <div class="group relative bg-surface dark:bg-surface-dark rounded-3xl overflow-hidden shadow-glass dark:shadow-glass-dark hover:shadow-primary-md transition-all duration-300 ease-spring hover:-translate-y-1">
        <!-- Badge -->
        <div v-if="product.badge" class="absolute top-4 left-4 z-10 px-3 py-1 rounded-full text-xs font-bold bg-accent-500 text-surface-dark shadow-sm">
            {{ product.badge }}
        </div>

        <!-- Image Container -->
        <div class="relative w-full h-48 overflow-hidden bg-gray-100 dark:bg-gray-800">
            <!-- Using a placeholder color for the image if missing, or rendering the src -->
            <img v-if="product.image" :src="product.image" :alt="product.name" class="w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-500 ease-spring" />
            <div v-else class="w-full h-full bg-gradient-to-br from-primary-100 to-secondary-100 dark:from-primary-900 dark:to-secondary-900 flex items-center justify-center text-primary-300">
                <span>No Image</span>
            </div>
            
            <!-- Quick Add Overlay (Desktop) -->
            <div class="absolute inset-x-0 bottom-0 p-4 bg-gradient-to-t from-black/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 hidden sm:block">
                 <button @click.prevent="handleAddToCart" class="w-full py-2 bg-accent-500 hover:bg-accent-400 text-surface-dark rounded-full font-semibold shadow-sm transition-colors flex items-center justify-center">
                    <ShoppingCart class="w-4 h-4 mr-2" />
                    Añadir
                </button>
            </div>
        </div>

        <!-- Content -->
        <div class="p-5">
            <div class="flex items-center space-x-1 mb-2" v-if="product.rating">
                <Star class="w-4 h-4 text-accent-500 fill-current" />
                <span class="text-sm text-content-secondary dark:text-content-muted">{{ product.rating }}</span>
            </div>
            
            <h3 class="text-lg font-bold text-content-primary dark:text-content-inverted line-clamp-2 mb-2 group-hover:text-primary-500 transition-colors">
                {{ product.name }}
            </h3>
            
            <div class="flex items-center justify-between mt-4">
                <span class="text-xl font-extrabold text-primary-500">
                    ${{ product.price.toFixed(2) }}
                </span>
                
                <!-- Mobile Add Button -->
                <button @click.prevent="handleAddToCart" class="sm:hidden p-2 rounded-full bg-accent-500 text-surface-dark hover:bg-accent-400 transition-colors shadow-sm">
                    <ShoppingCart class="w-5 h-5" />
                </button>
            </div>
        </div>
    </div>
</template>
