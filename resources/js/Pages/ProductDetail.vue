<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import { ShoppingCart, Heart, Share2, Star, CheckCircle, ShieldCheck, Truck } from 'lucide-vue-next';
import AppLayout from '../Layouts/AppLayout.vue';
import ProductCard from '../Components/Product/ProductCard.vue';
import { useCartStore, type Product } from '../Stores/cartStore';

const cartStore = useCartStore();

// Mock product data (this would come from Inertia props in reality)
const product: Product & { oldPrice?: number } = {
    id: 1,
    name: 'Queso Gouda Artesanal de Campo',
    price: 12.50,
    oldPrice: 15.00,
    description: 'Nuestro queso Gouda artesanal es madurado durante 6 meses para obtener un sabor suave, cremoso y con sutiles notas a nuez. Perfecto para tablas de quesos, sándwiches gourmet o para derretir.',
    stock: 45,
    rating: 4.8,
    reviews: 124,
    sku: 'GOU-001',
    category: 'Lácteos y Quesos',
    badge: 'Oferta Especial',
    image: '', // Primary image
    images: [
        '', // Main image (placeholder)
        '', // Gallery 1
        '', // Gallery 2
        ''  // Gallery 3
    ]
};

const relatedProducts = [
    { id: 2, name: 'Vino Tinto Reserva Especial', price: 25.00, image: '', rating: 4.9, badge: 'Maridaje Perfecto' },
    { id: 3, name: 'Pan Campesino Recién Horneado', price: 3.20, image: '', rating: 4.5 },
    { id: 5, name: 'Jamón Serrano Ibérico', price: 18.50, image: '', rating: 4.9 },
    { id: 6, name: 'Aceitunas Verdes Rellenas', price: 4.50, image: '', rating: 4.3 },
];

const quantity = ref(1);
const selectedImage = ref(0);

function increaseQuantity() {
    if (quantity.value < (product.stock ?? 0)) {
        quantity.value++;
    }
}

function decreaseQuantity() {
    if (quantity.value > 1) {
        quantity.value--;
    }
}

function handleAddToCart() {
    cartStore.addToCart(product, quantity.value);
}
</script>

<template>
    <Head :title="product.name" />

    <AppLayout>
        <!-- Breadcrumbs -->
        <div class="bg-bg-light dark:bg-bg-dark border-b border-gray-200 dark:border-gray-800 transition-colors duration-300">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <nav class="flex text-sm text-content-secondary" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li class="inline-flex items-center">
                            <a href="/" class="hover:text-primary-500 transition-colors">Inicio</a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <span class="mx-2 text-gray-400">/</span>
                                <a href="/catalog" class="hover:text-primary-500 transition-colors">{{ product.category }}</a>
                            </div>
                        </li>
                        <li aria-current="page">
                            <div class="flex items-center">
                                <span class="mx-2 text-gray-400">/</span>
                                <span class="text-content-primary dark:text-content-inverted font-medium line-clamp-1">{{ product.name }}</span>
                            </div>
                        </li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="py-12 bg-bg-light dark:bg-bg-dark transition-colors duration-300">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="lg:grid lg:grid-cols-2 lg:gap-x-12 xl:gap-x-16">
                    
                    <!-- Image Gallery -->
                    <div class="mb-10 lg:mb-0">
                        <div class="flex flex-col-reverse lg:flex-row gap-4">
                            <!-- Thumbnails (Left on desktop, bottom on mobile) -->
                            <div class="flex lg:flex-col gap-4 overflow-x-auto lg:overflow-visible py-2 lg:py-0 w-full lg:w-24 flex-shrink-0">
                                <button 
                                    v-for="(img, index) in product.images" 
                                    :key="index"
                                    @click="selectedImage = index"
                                    class="relative h-20 w-20 lg:h-24 lg:w-24 rounded-xl overflow-hidden border-2 transition-all flex-shrink-0"
                                    :class="selectedImage === index ? 'border-primary-500 shadow-primary-sm scale-105' : 'border-transparent border-gray-200 dark:border-gray-700 opacity-70 hover:opacity-100'"
                                >
                                    <div v-if="!img" class="w-full h-full bg-gradient-to-br from-primary-100 to-secondary-100 dark:from-primary-900 dark:to-secondary-900 flex items-center justify-center text-primary-300">
                                        <span class="text-xs font-bold">Img {{ index + 1 }}</span>
                                    </div>
                                    <img v-else :src="img" alt="Gallery thumbnail" class="w-full h-full object-cover" />
                                </button>
                            </div>
                            
                            <!-- Main Image -->
                            <div class="relative flex-1 bg-surface dark:bg-surface-dark rounded-3xl overflow-hidden shadow-glass dark:shadow-glass-dark border border-gray-100 dark:border-gray-800 flex items-center justify-center h-80 sm:h-96 lg:h-[500px]">
                                <div v-if="product.badge" class="absolute top-4 left-4 z-10 px-4 py-1.5 rounded-full text-sm font-bold bg-accent-500 text-surface-dark shadow-md">
                                    ¡Oferta Especial!
                                </div>
                                <button class="absolute top-4 right-4 p-3 rounded-full bg-surface/80 dark:bg-surface-dark/80 backdrop-blur-md text-content-secondary hover:text-primary-500 transition-colors shadow-sm hover:scale-110">
                                    <Heart class="w-6 h-6" />
                                </button>
                                
                                <div v-if="!product.images?.[selectedImage]" class="text-primary-300 font-display font-bold text-4xl opacity-50">
                                    Product Image
                                </div>
                                <img v-else :src="product.images?.[selectedImage]" :alt="product.name" class="w-full h-full object-cover transition-all duration-500" />
                            </div>
                        </div>
                    </div>

                    <!-- Product Info -->
                    <div class="flex flex-col">
                        <div class="mb-6">
                            <h1 class="text-3xl sm:text-4xl font-display font-extrabold text-content-primary dark:text-content-inverted tracking-tight mb-2">
                                {{ product.name }}
                            </h1>
                            
                            <!-- Rating -->
                            <div class="flex items-center space-x-4">
                                <div class="flex items-center">
                                    <Star v-for="i in 5" :key="i" class="w-5 h-5" :class="i <= Math.floor(product.rating ?? 0) ? 'text-accent-500 fill-current' : 'text-gray-300 dark:text-gray-600'" />
                                    <span class="ml-2 text-sm font-medium text-content-secondary">{{ product.rating }} ({{ product.reviews }} reseñas)</span>
                                </div>
                                <span class="text-gray-300 dark:text-gray-700">|</span>
                                <span class="text-sm font-medium text-success dark:text-success-light flex items-center">
                                    <CheckCircle class="w-4 h-4 mr-1" /> En Stock ({{ product.stock }})
                                </span>
                            </div>
                        </div>

                        <!-- Price -->
                        <div class="mb-8 p-6 bg-surface dark:bg-surface-dark rounded-3xl shadow-glass dark:shadow-glass-dark border border-gray-100 dark:border-gray-800">
                            <div class="flex items-end mb-2">
                                <span class="text-5xl font-extrabold text-primary-500">${{ product.price.toFixed(2) }}</span>
                                <span v-if="product.oldPrice" class="ml-4 text-xl text-content-muted line-through mb-1">${{ product.oldPrice.toFixed(2) }}</span>
                            </div>
                            <p v-if="product.oldPrice" class="text-sm text-accent-600 dark:text-accent-400 font-bold">
                                Ahorras ${{ (product.oldPrice - product.price).toFixed(2) }}
                            </p>
                            
                            <!-- Action Area -->
                            <div class="mt-6 flex flex-col sm:flex-row gap-4">
                                <div class="flex items-center border-2 border-gray-200 dark:border-gray-700 rounded-full h-14 bg-bg-light dark:bg-bg-dark w-full sm:w-auto">
                                    <button @click="decreaseQuantity" class="px-5 h-full text-content-secondary hover:text-primary-500 transition-colors">
                                        <Minus class="w-5 h-5" />
                                    </button>
                                    <span class="w-12 text-center font-bold text-lg text-content-primary dark:text-content-inverted">{{ quantity }}</span>
                                    <button @click="increaseQuantity" class="px-5 h-full text-content-secondary hover:text-primary-500 transition-colors">
                                        <Plus class="w-5 h-5" />
                                    </button>
                                </div>
                                
                                <button @click="handleAddToCart" class="flex-1 flex items-center justify-center h-14 bg-accent-500 hover:bg-accent-400 text-surface-dark font-bold text-lg rounded-full shadow-accent-sm hover:-translate-y-1 transition-all duration-300 ease-spring">
                                    <ShoppingCart class="w-6 h-6 mr-2" />
                                    Añadir al Carrito
                                </button>
                            </div>
                        </div>

                        <!-- Description & Details -->
                        <div class="prose prose-sm sm:prose lg:prose-lg text-content-secondary dark:text-content-muted max-w-none mb-8">
                            <p>{{ product.description }}</p>
                        </div>
                        
                        <!-- Perks List -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 border-t border-gray-200 dark:border-gray-800 pt-8">
                            <div class="flex items-center text-content-secondary">
                                <ShieldCheck class="w-6 h-6 text-accent-500 mr-3" />
                                <span class="text-sm font-medium">Calidad Garantizada</span>
                            </div>
                            <div class="flex items-center text-content-secondary">
                                <Truck class="w-6 h-6 text-primary-500 mr-3" />
                                <span class="text-sm font-medium">Envío Rápido Disponible</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Related Products Section -->
        <section class="py-16 bg-surface dark:bg-surface-dark border-t border-gray-200 dark:border-gray-800 transition-colors duration-300">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-2xl font-display font-bold text-content-primary dark:text-content-inverted mb-8 flex items-center">
                    Completa tu compra
                    <span class="ml-4 h-px flex-1 bg-gradient-to-r from-gray-200 to-transparent dark:from-gray-800"></span>
                </h2>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                    <ProductCard 
                        v-for="item in relatedProducts" 
                        :key="item.id" 
                        :product="item" 
                    />
                </div>
            </div>
        </section>

    </AppLayout>
</template>
