<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight, ShoppingBag } from 'lucide-vue-next';
</script>

<template>
    <section
        class="relative overflow-hidden bg-bg-light py-12 transition-colors duration-300 dark:bg-bg-dark sm:py-20"
    >
        <!-- Decorative background elements -->
        <div
            class="absolute -right-24 -top-24 h-96 w-96 animate-pulse-pink rounded-full bg-primary-100 opacity-70 mix-blend-multiply blur-3xl filter dark:bg-primary-900 dark:mix-blend-lighten"
        ></div>
        <div
            class="absolute -left-24 top-32 h-72 w-72 rounded-full bg-accent-100 opacity-50 mix-blend-multiply blur-3xl filter dark:bg-accent-900 dark:mix-blend-lighten"
        ></div>

        <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="items-center lg:grid lg:grid-cols-12 lg:gap-8">
                <div
                    class="sm:text-center md:mx-auto md:max-w-2xl lg:col-span-6 lg:text-left"
                >
                    <h1
                        class="font-display text-4xl font-extrabold tracking-tight text-content-primary dark:text-content-inverted sm:text-5xl md:text-6xl lg:text-5xl xl:text-6xl"
                    >
                        <span class="block xl:inline">Frescura y calidad</span>
                        {{ ' ' }}
                        <span class="block text-primary-500"
                            >directo a tu puerta</span
                        >
                    </h1>
                    <p
                        class="mt-3 text-base text-content-secondary dark:text-content-muted sm:mx-auto sm:mt-5 sm:max-w-xl sm:text-lg md:mt-5 md:text-xl lg:mx-0"
                    >
                        Descubre Monasterios Market. Los mejores productos,
                        ofertas exclusivas y una experiencia de compra diseñada
                        para hacer tu vida más fácil.
                    </p>
                    <div
                        class="mt-8 flex flex-col gap-4 sm:mx-auto sm:max-w-lg sm:flex-row sm:text-center lg:mx-0 lg:text-left"
                    >
                        <Link
                            href="/catalog"
                            class="inline-flex items-center justify-center rounded-full border border-transparent bg-primary-500 px-8 py-3 text-base font-medium text-white shadow-primary-md transition-all duration-300 ease-spring hover:-translate-y-1 hover:bg-primary-600"
                        >
                            <ShoppingBag class="mr-2 h-5 w-5" />
                            Comprar Ahora
                        </Link>
                        <Link
                            href="/offers"
                            class="inline-flex items-center justify-center rounded-full border-2 border-primary-100 bg-transparent px-8 py-3 text-base font-medium text-primary-600 transition-colors duration-300 hover:bg-primary-50 dark:border-primary-900 dark:text-primary-400 dark:hover:bg-primary-950"
                        >
                            Ver Ofertas
                            <ArrowRight class="ml-2 h-5 w-5" />
                        </Link>
                    </div>
                </div>

                <div
                    class="relative mt-12 sm:mx-auto sm:max-w-lg lg:col-span-6 lg:mx-0 lg:mt-0 lg:flex lg:max-w-none lg:items-center"
                >
                    <div
                        class="group relative mx-auto w-full transform overflow-hidden rounded-4xl shadow-glass transition-transform duration-500 ease-spring hover:scale-105 dark:shadow-glass-dark"
                    >
                        <!-- Placeholder for hero image -->
                        <div
                            class="flex h-80 w-full items-center justify-center bg-gradient-to-tr from-primary-200 to-secondary-200 dark:from-primary-800 dark:to-secondary-800"
                        >
                            <div
                                class="font-display text-4xl font-bold text-surface/80 transition-transform duration-500 group-hover:scale-110 dark:text-surface-dark/80"
                            >
                                Market Visual
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
