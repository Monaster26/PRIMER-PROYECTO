<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import {
    CheckCircle,
    Heart,
    ShieldCheck,
    ShoppingCart,
    Star,
    Truck,
} from 'lucide-vue-next';
import { ref } from 'vue';
import ProductCard from '../Components/Product/ProductCard.vue';
import AppLayout from '../Layouts/AppLayout.vue';
import { useCartStore, type Product } from '../Stores/cartStore';

const cartStore = useCartStore();

// Mock product data (this would come from Inertia props in reality)
const product: Product & { oldPrice?: number } = {
    id: 1,
    name: 'Queso Gouda Artesanal de Campo',
    price: 12.5,
    oldPrice: 15.0,
    description:
        'Nuestro queso Gouda artesanal es madurado durante 6 meses para obtener un sabor suave, cremoso y con sutiles notas a nuez. Perfecto para tablas de quesos, sándwiches gourmet o para derretir.',
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
        '', // Gallery 3
    ],
};

const relatedProducts = [
    {
        id: 2,
        name: 'Vino Tinto Reserva Especial',
        price: 25.0,
        image: '',
        rating: 4.9,
        badge: 'Maridaje Perfecto',
    },
    {
        id: 3,
        name: 'Pan Campesino Recién Horneado',
        price: 3.2,
        image: '',
        rating: 4.5,
    },
    {
        id: 5,
        name: 'Jamón Serrano Ibérico',
        price: 18.5,
        image: '',
        rating: 4.9,
    },
    {
        id: 6,
        name: 'Aceitunas Verdes Rellenas',
        price: 4.5,
        image: '',
        rating: 4.3,
    },
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
        <div
            class="border-b border-gray-200 bg-bg-light transition-colors duration-300 dark:border-gray-800 dark:bg-bg-dark"
        >
            <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8">
                <nav
                    class="flex text-sm text-content-secondary"
                    aria-label="Breadcrumb"
                >
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li class="inline-flex items-center">
                            <a
                                href="/"
                                class="transition-colors hover:text-primary-500"
                                >Inicio</a
                            >
                        </li>
                        <li>
                            <div class="flex items-center">
                                <span class="mx-2 text-gray-400">/</span>
                                <a
                                    href="/catalog"
                                    class="transition-colors hover:text-primary-500"
                                    >{{ product.category }}</a
                                >
                            </div>
                        </li>
                        <li aria-current="page">
                            <div class="flex items-center">
                                <span class="mx-2 text-gray-400">/</span>
                                <span
                                    class="line-clamp-1 font-medium text-content-primary dark:text-content-inverted"
                                    >{{ product.name }}</span
                                >
                            </div>
                        </li>
                    </ol>
                </nav>
            </div>
        </div>

        <div
            class="bg-bg-light py-12 transition-colors duration-300 dark:bg-bg-dark"
        >
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="lg:grid lg:grid-cols-2 lg:gap-x-12 xl:gap-x-16">
                    <!-- Image Gallery -->
                    <div class="mb-10 lg:mb-0">
                        <div class="flex flex-col-reverse gap-4 lg:flex-row">
                            <!-- Thumbnails (Left on desktop, bottom on mobile) -->
                            <div
                                class="flex w-full flex-shrink-0 gap-4 overflow-x-auto py-2 lg:w-24 lg:flex-col lg:overflow-visible lg:py-0"
                            >
                                <button
                                    v-for="(img, index) in product.images"
                                    :key="index"
                                    @click="selectedImage = index"
                                    class="relative h-20 w-20 flex-shrink-0 overflow-hidden rounded-xl border-2 transition-all lg:h-24 lg:w-24"
                                    :class="
                                        selectedImage === index
                                            ? 'scale-105 border-primary-500 shadow-primary-sm'
                                            : 'border-gray-200 border-transparent opacity-70 hover:opacity-100 dark:border-gray-700'
                                    "
                                >
                                    <div
                                        v-if="!img"
                                        class="flex h-full w-full items-center justify-center bg-gradient-to-br from-primary-100 to-secondary-100 text-primary-300 dark:from-primary-900 dark:to-secondary-900"
                                    >
                                        <span class="text-xs font-bold"
                                            >Img {{ index + 1 }}</span
                                        >
                                    </div>
                                    <img
                                        v-else
                                        :src="img"
                                        alt="Gallery thumbnail"
                                        class="h-full w-full object-cover"
                                    />
                                </button>
                            </div>

                            <!-- Main Image -->
                            <div
                                class="relative flex h-80 flex-1 items-center justify-center overflow-hidden rounded-3xl border border-gray-100 bg-surface shadow-glass dark:border-gray-800 dark:bg-surface-dark dark:shadow-glass-dark sm:h-96 lg:h-[500px]"
                            >
                                <div
                                    v-if="product.badge"
                                    class="absolute left-4 top-4 z-10 rounded-full bg-accent-500 px-4 py-1.5 text-sm font-bold text-surface-dark shadow-md"
                                >
                                    ¡Oferta Especial!
                                </div>
                                <button
                                    class="absolute right-4 top-4 rounded-full bg-surface/80 p-3 text-content-secondary shadow-sm backdrop-blur-md transition-colors hover:scale-110 hover:text-primary-500 dark:bg-surface-dark/80"
                                >
                                    <Heart class="h-6 w-6" />
                                </button>

                                <div
                                    v-if="!product.images?.[selectedImage]"
                                    class="font-display text-4xl font-bold text-primary-300 opacity-50"
                                >
                                    Product Image
                                </div>
                                <img
                                    v-else
                                    :src="product.images?.[selectedImage]"
                                    :alt="product.name"
                                    class="h-full w-full object-cover transition-all duration-500"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Product Info -->
                    <div class="flex flex-col">
                        <div class="mb-6">
                            <h1
                                class="mb-2 font-display text-3xl font-extrabold tracking-tight text-content-primary dark:text-content-inverted sm:text-4xl"
                            >
                                {{ product.name }}
                            </h1>

                            <!-- Rating -->
                            <div class="flex items-center space-x-4">
                                <div class="flex items-center">
                                    <Star
                                        v-for="i in 5"
                                        :key="i"
                                        class="h-5 w-5"
                                        :class="
                                            i <= Math.floor(product.rating ?? 0)
                                                ? 'fill-current text-accent-500'
                                                : 'text-gray-300 dark:text-gray-600'
                                        "
                                    />
                                    <span
                                        class="ml-2 text-sm font-medium text-content-secondary"
                                        >{{ product.rating }} ({{
                                            product.reviews
                                        }}
                                        reseñas)</span
                                    >
                                </div>
                                <span class="text-gray-300 dark:text-gray-700"
                                    >|</span
                                >
                                <span
                                    class="flex items-center text-sm font-medium text-success dark:text-success-light"
                                >
                                    <CheckCircle class="mr-1 h-4 w-4" /> En
                                    Stock ({{ product.stock }})
                                </span>
                            </div>
                        </div>

                        <!-- Price -->
                        <div
                            class="mb-8 rounded-3xl border border-gray-100 bg-surface p-6 shadow-glass dark:border-gray-800 dark:bg-surface-dark dark:shadow-glass-dark"
                        >
                            <div class="mb-2 flex items-end">
                                <span
                                    class="text-5xl font-extrabold text-primary-500"
                                    >${{ product.price.toFixed(2) }}</span
                                >
                                <span
                                    v-if="product.oldPrice"
                                    class="mb-1 ml-4 text-xl text-content-muted line-through"
                                    >${{ product.oldPrice.toFixed(2) }}</span
                                >
                            </div>
                            <p
                                v-if="product.oldPrice"
                                class="text-sm font-bold text-accent-600 dark:text-accent-400"
                            >
                                Ahorras ${{
                                    (product.oldPrice - product.price).toFixed(
                                        2,
                                    )
                                }}
                            </p>

                            <!-- Action Area -->
                            <div class="mt-6 flex flex-col gap-4 sm:flex-row">
                                <div
                                    class="flex h-14 w-full items-center rounded-full border-2 border-gray-200 bg-bg-light dark:border-gray-700 dark:bg-bg-dark sm:w-auto"
                                >
                                    <button
                                        @click="decreaseQuantity"
                                        class="h-full px-5 text-content-secondary transition-colors hover:text-primary-500"
                                    >
                                        <Minus class="h-5 w-5" />
                                    </button>
                                    <span
                                        class="w-12 text-center text-lg font-bold text-content-primary dark:text-content-inverted"
                                        >{{ quantity }}</span
                                    >
                                    <button
                                        @click="increaseQuantity"
                                        class="h-full px-5 text-content-secondary transition-colors hover:text-primary-500"
                                    >
                                        <Plus class="h-5 w-5" />
                                    </button>
                                </div>

                                <button
                                    @click="handleAddToCart"
                                    class="flex h-14 flex-1 items-center justify-center rounded-full bg-accent-500 text-lg font-bold text-surface-dark shadow-accent-sm transition-all duration-300 ease-spring hover:-translate-y-1 hover:bg-accent-400"
                                >
                                    <ShoppingCart class="mr-2 h-6 w-6" />
                                    Añadir al Carrito
                                </button>
                            </div>
                        </div>

                        <!-- Description & Details -->
                        <div
                            class="prose prose-sm sm:prose lg:prose-lg mb-8 max-w-none text-content-secondary dark:text-content-muted"
                        >
                            <p>{{ product.description }}</p>
                        </div>

                        <!-- Perks List -->
                        <div
                            class="grid grid-cols-1 gap-4 border-t border-gray-200 pt-8 dark:border-gray-800 sm:grid-cols-2"
                        >
                            <div
                                class="flex items-center text-content-secondary"
                            >
                                <ShieldCheck
                                    class="mr-3 h-6 w-6 text-accent-500"
                                />
                                <span class="text-sm font-medium"
                                    >Calidad Garantizada</span
                                >
                            </div>
                            <div
                                class="flex items-center text-content-secondary"
                            >
                                <Truck class="mr-3 h-6 w-6 text-primary-500" />
                                <span class="text-sm font-medium"
                                    >Envío Rápido Disponible</span
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Related Products Section -->
        <section
            class="border-t border-gray-200 bg-surface py-16 transition-colors duration-300 dark:border-gray-800 dark:bg-surface-dark"
        >
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <h2
                    class="mb-8 flex items-center font-display text-2xl font-bold text-content-primary dark:text-content-inverted"
                >
                    Completa tu compra
                    <span
                        class="ml-4 h-px flex-1 bg-gradient-to-r from-gray-200 to-transparent dark:from-gray-800"
                    ></span>
                </h2>

                <div
                    class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-4"
                >
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
