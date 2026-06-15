<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight, ShoppingBag } from 'lucide-vue-next';
</script>

<template>
    <section class="relative bg-bg-light dark:bg-bg-dark overflow-hidden transition-colors duration-300 py-12 sm:py-20">
        <!-- Decorative background elements -->
        <div class="absolute -top-24 -right-24 w-96 h-96 bg-primary-100 dark:bg-primary-900 rounded-full mix-blend-multiply dark:mix-blend-lighten filter blur-3xl opacity-70 animate-pulse-pink"></div>
        <div class="absolute top-32 -left-24 w-72 h-72 bg-accent-100 dark:bg-accent-900 rounded-full mix-blend-multiply dark:mix-blend-lighten filter blur-3xl opacity-50"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="lg:grid lg:grid-cols-12 lg:gap-8 items-center">
                <div class="sm:text-center md:max-w-2xl md:mx-auto lg:col-span-6 lg:text-left">
                    <h1 class="text-4xl tracking-tight font-display font-extrabold text-content-primary dark:text-content-inverted sm:text-5xl md:text-6xl lg:text-5xl xl:text-6xl">
                        <span class="block xl:inline">Frescura y calidad</span>
                        {{ ' ' }}
                        <span class="block text-primary-500">directo a tu puerta</span>
                    </h1>
                    <p class="mt-3 text-base text-content-secondary dark:text-content-muted sm:mt-5 sm:text-lg sm:max-w-xl sm:mx-auto md:mt-5 md:text-xl lg:mx-0">
                        Descubre Monasterios Market. Los mejores productos, ofertas exclusivas y una experiencia de compra diseñada para hacer tu vida más fácil.
                    </p>
                    <div class="mt-8 sm:max-w-lg sm:mx-auto sm:text-center lg:text-left lg:mx-0 flex flex-col sm:flex-row gap-4">
                        <Link href="/catalog" class="inline-flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-full shadow-primary-md text-white bg-primary-500 hover:bg-primary-600 hover:-translate-y-1 transition-all duration-300 ease-spring">
                            <ShoppingBag class="w-5 h-5 mr-2" />
                            Comprar Ahora
                        </Link>
                        <Link href="/offers" class="inline-flex items-center justify-center px-8 py-3 border-2 border-primary-100 dark:border-primary-900 text-base font-medium rounded-full text-primary-600 dark:text-primary-400 bg-transparent hover:bg-primary-50 dark:hover:bg-primary-950 transition-colors duration-300">
                            Ver Ofertas
                            <ArrowRight class="w-5 h-5 ml-2" />
                        </Link>
                    </div>
                </div>
                
                <div class="mt-12 relative sm:max-w-lg sm:mx-auto lg:mt-0 lg:max-w-none lg:mx-0 lg:col-span-6 lg:flex lg:items-center">
                    <div class="relative mx-auto w-full rounded-4xl shadow-glass dark:shadow-glass-dark overflow-hidden transform hover:scale-105 transition-transform duration-500 ease-spring group">
                        <!-- Placeholder for hero image -->
                        <div class="w-full h-80 bg-gradient-to-tr from-primary-200 to-secondary-200 dark:from-primary-800 dark:to-secondary-800 flex items-center justify-center">
                             <div class="text-4xl font-display font-bold text-surface/80 dark:text-surface-dark/80 group-hover:scale-110 transition-transform duration-500">Market Visual</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
