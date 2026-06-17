<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import HeroSection from '../Components/Layout/HeroSection.vue';
import ProductCard from '../Components/Product/ProductCard.vue';
import AppLayout from '../Layouts/AppLayout.vue';

const popularProducts = [
    {
        id: 1,
        name: 'Queso Gouda Artesanal',
        price: 12.5,
        image: '',
        rating: 4.8,
        badge: 'Popular',
    },
    {
        id: 2,
        name: 'Vino Tinto Reserva Especial',
        price: 25.0,
        image: '',
        rating: 4.9,
        badge: 'Oferta',
    },
    {
        id: 3,
        name: 'Pan Campesino Recién Horneado',
        price: 3.2,
        image: '',
        rating: 4.5,
    },
    {
        id: 4,
        name: 'Aceite de Oliva Virgen Extra',
        price: 15.8,
        image: '',
        rating: 4.7,
        badge: 'Nuevo',
    },
];
</script>

<template>
    <Head title="Inicio" />

    <AppLayout>
        <!-- Hero Section -->
        <HeroSection />

        <!-- Popular Products Section -->
        <section
            class="bg-bg-light py-16 transition-colors duration-300 dark:bg-bg-dark"
        >
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-8 flex items-end justify-between">
                    <div>
                        <h2
                            class="font-display text-3xl font-bold text-content-primary dark:text-content-inverted"
                        >
                            Productos Populares
                        </h2>
                        <p
                            class="mt-2 text-content-secondary dark:text-content-muted"
                        >
                            Los favoritos de nuestros clientes, seleccionados
                            para ti.
                        </p>
                    </div>
                    <a
                        href="/catalog"
                        class="hidden font-medium text-primary-500 transition-colors hover:text-primary-600 sm:block"
                    >
                        Ver todos &rarr;
                    </a>
                </div>

                <div
                    class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-4"
                >
                    <ProductCard
                        v-for="product in popularProducts"
                        :key="product.id"
                        :product="product"
                    />
                </div>

                <div class="mt-8 text-center sm:hidden">
                    <a
                        href="/catalog"
                        class="inline-block font-medium text-primary-500 transition-colors hover:text-primary-600"
                    >
                        Ver todos los productos &rarr;
                    </a>
                </div>
            </div>
        </section>

        <!-- More sections (Categories, Offers) will go here -->
    </AppLayout>
</template>
