<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '../Layouts/AppLayout.vue';
import HeroSection from '../Components/Layout/HeroSection.vue';
import ProductCard from '../Components/Product/ProductCard.vue';

const popularProducts = [
    { id: 1, name: 'Queso Gouda Artesanal', price: 12.50, image: '', rating: 4.8, badge: 'Popular' },
    { id: 2, name: 'Vino Tinto Reserva Especial', price: 25.00, image: '', rating: 4.9, badge: 'Oferta' },
    { id: 3, name: 'Pan Campesino Recién Horneado', price: 3.20, image: '', rating: 4.5 },
    { id: 4, name: 'Aceite de Oliva Virgen Extra', price: 15.80, image: '', rating: 4.7, badge: 'Nuevo' },
];
</script>

<template>
    <Head title="Inicio" />

    <AppLayout>
        <!-- Hero Section -->
        <HeroSection />

        <!-- Popular Products Section -->
        <section class="py-16 bg-bg-light dark:bg-bg-dark transition-colors duration-300">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-end justify-between mb-8">
                    <div>
                        <h2 class="text-3xl font-display font-bold text-content-primary dark:text-content-inverted">
                            Productos Populares
                        </h2>
                        <p class="mt-2 text-content-secondary dark:text-content-muted">
                            Los favoritos de nuestros clientes, seleccionados para ti.
                        </p>
                    </div>
                    <a href="/catalog" class="hidden sm:block text-primary-500 hover:text-primary-600 font-medium transition-colors">
                        Ver todos &rarr;
                    </a>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                    <ProductCard 
                        v-for="product in popularProducts" 
                        :key="product.id" 
                        :product="product" 
                    />
                </div>
                
                <div class="mt-8 sm:hidden text-center">
                    <a href="/catalog" class="inline-block text-primary-500 hover:text-primary-600 font-medium transition-colors">
                        Ver todos los productos &rarr;
                    </a>
                </div>
            </div>
        </section>
        
        <!-- More sections (Categories, Offers) will go here -->
    </AppLayout>
</template>
