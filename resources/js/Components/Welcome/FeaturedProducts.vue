<script setup lang="ts">
import ProductCard from './ProductCard.vue';

const products = [
    {
        id: 1,
        name: 'Reserva Especial - Café Liofilizado',
        category: 'Gourmet',
        price: 12500,
        oldPrice: 15990,
        image: '/images/landing/product1.png',
        isDiscount: true,
    },
    {
        id: 2,
        name: 'Cabernet Sauvignon Premium',
        category: 'Vinos',
        price: 24900,
        image: '/images/landing/product2.png',
        isNew: true,
    },
    {
        id: 3,
        name: 'Miel Orgánica de Pradera',
        category: 'Natural',
        price: 8900,
        image: '/images/landing/product3.png',
    },
    {
        id: 4,
        name: 'Chocolate Artesanal 70% Cacao',
        category: 'Confites',
        price: 5500,
        image: '/images/landing/product4.png',
    },
    {
        id: 5,
        name: 'Néctar de Naranja Cold-Pressed',
        category: 'Bebidas',
        price: 3900,
        image: '/images/landing/product5.png',
        isNew: true,
    },
];
</script>

<template>
    <section
        id="ofertas"
        class="overflow-hidden bg-white py-32 transition-colors dark:bg-zinc-950"
    >
        <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <!-- Background Decoration -->
            <div
                class="bg-primario/5 absolute -right-24 -top-24 h-96 w-96 rounded-full blur-3xl"
            ></div>
            <div
                class="bg-secundario/5 absolute -bottom-24 -left-24 h-96 w-96 rounded-full blur-3xl"
            ></div>

            <div
                class="relative z-10 mb-20 flex flex-col justify-between gap-8 md:flex-row md:items-end"
            >
                <div class="max-w-2xl">
                    <div
                        class="text-primario mb-4 inline-flex items-center gap-3 text-[10px] font-black uppercase tracking-[0.3em]"
                    >
                        <span class="bg-primario h-[2px] w-12"></span>
                        Colección Exclusiva
                    </div>
                    <h2
                        class="text-4xl font-black leading-[1.1] tracking-tighter text-zinc-900 dark:text-white md:text-5xl lg:text-6xl"
                    >
                        Selección
                        <span
                            class="from-primario to-acento bg-gradient-to-r bg-clip-text italic text-transparent"
                            >Gourmet</span
                        >
                    </h2>
                    <p
                        class="mt-6 text-lg font-medium text-zinc-500 dark:text-zinc-400"
                    >
                        Descubre productos de clase mundial, seleccionados
                        cuidadosamente para satisfacer los paladares más
                        exigentes.
                    </p>
                </div>
                <button
                    class="hover:text-primario group flex items-center gap-3 self-start text-sm font-black uppercase tracking-widest text-zinc-900 transition-colors dark:text-white md:self-auto"
                >
                    Explorar Todo el Catálogo
                    <span
                        class="group-hover:bg-primario group-hover:border-primario flex h-10 w-10 transform items-center justify-center rounded-full border-2 border-zinc-900 transition-all group-hover:translate-x-2 group-hover:text-white dark:border-white"
                    >
                        &rarr;
                    </span>
                </button>
            </div>

            <!-- Products Grid -->
            <div
                class="relative z-10 grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3 lg:gap-10 xl:grid-cols-5"
            >
                <ProductCard
                    v-for="product in products"
                    :key="product.id"
                    :product="product"
                />
            </div>

            <!-- Promotional Banner Bottom -->
            <div
                class="group relative mt-32 overflow-hidden rounded-[3rem] bg-zinc-900 p-12 dark:bg-zinc-800 lg:p-20"
            >
                <div
                    class="from-primario/20 to-secundario/20 absolute inset-0 bg-gradient-to-r opacity-0 transition-opacity duration-1000 group-hover:opacity-100"
                ></div>
                <div
                    class="relative z-10 grid items-center gap-12 lg:grid-cols-2"
                >
                    <div>
                        <h3
                            class="mb-6 text-3xl font-black leading-tight text-white lg:text-5xl"
                        >
                            ¿Buscas algo <br />
                            <span class="text-secundario">más exclusivo?</span>
                        </h3>
                        <p class="mb-10 max-w-md text-lg italic text-zinc-400">
                            "Nuestra misión es traerte lo que no encuentras en
                            ningún otro lugar."
                        </p>
                        <button
                            class="hover:bg-secundario rounded-2xl bg-white px-8 py-4 font-black uppercase tracking-widest text-zinc-900 transition-all active:scale-95"
                        >
                            Solicitar Pedido Especial
                        </button>
                    </div>
                    <div class="relative hidden h-64 lg:block">
                        <div
                            class="absolute inset-0 rotate-3 scale-110 transform rounded-[2rem] bg-white/5 backdrop-blur-3xl"
                        ></div>
                        <div
                            class="absolute inset-0 flex items-center justify-center text-9xl"
                        >
                            🎁
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
