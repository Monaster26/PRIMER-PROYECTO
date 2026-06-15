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
        isDiscount: true 
    },
    { 
        id: 2, 
        name: 'Cabernet Sauvignon Premium', 
        category: 'Vinos', 
        price: 24900, 
        image: '/images/landing/product2.png', 
        isNew: true 
    },
    { 
        id: 3, 
        name: 'Miel Orgánica de Pradera', 
        category: 'Natural', 
        price: 8900, 
        image: '/images/landing/product3.png' 
    },
    { 
        id: 4, 
        name: 'Chocolate Artesanal 70% Cacao', 
        category: 'Confites', 
        price: 5500, 
        image: '/images/landing/product4.png' 
    },
    { 
        id: 5, 
        name: 'Néctar de Naranja Cold-Pressed', 
        category: 'Bebidas', 
        price: 3900, 
        image: '/images/landing/product5.png',
        isNew: true
    },
];
</script>

<template>
    <section id="ofertas" class="py-32 bg-white dark:bg-zinc-950 transition-colors overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
            
            <!-- Background Decoration -->
            <div class="absolute -top-24 -right-24 w-96 h-96 bg-primario/5 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-secundario/5 rounded-full blur-3xl"></div>

            <div class="flex flex-col md:flex-row md:items-end justify-between mb-20 gap-8 relative z-10">
                <div class="max-w-2xl">
                    <div class="inline-flex items-center gap-3 text-primario font-black tracking-[0.3em] text-[10px] uppercase mb-4">
                        <span class="w-12 h-[2px] bg-primario"></span> 
                        Colección Exclusiva
                    </div>
                    <h2 class="text-4xl md:text-5xl lg:text-6xl font-black text-zinc-900 dark:text-white leading-[1.1] tracking-tighter">
                        Selección <span class="text-transparent bg-clip-text bg-gradient-to-r from-primario to-acento italic">Gourmet</span>
                    </h2>
                    <p class="mt-6 text-lg text-zinc-500 dark:text-zinc-400 font-medium">
                        Descubre productos de clase mundial, seleccionados cuidadosamente para satisfacer los paladares más exigentes.
                    </p>
                </div>
                <button class="group flex items-center gap-3 text-zinc-900 dark:text-white font-black text-sm uppercase tracking-widest self-start md:self-auto hover:text-primario transition-colors">
                    Explorar Todo el Catálogo
                    <span class="w-10 h-10 rounded-full border-2 border-zinc-900 dark:border-white flex items-center justify-center group-hover:bg-primario group-hover:border-primario group-hover:text-white transition-all transform group-hover:translate-x-2">
                        &rarr;
                    </span>
                </button>
            </div>

            <!-- Products Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-8 lg:gap-10 relative z-10">
                <ProductCard v-for="product in products" :key="product.id" :product="product" />
            </div>

            <!-- Promotional Banner Bottom -->
            <div class="mt-32 p-12 lg:p-20 rounded-[3rem] bg-zinc-900 dark:bg-zinc-800 relative overflow-hidden group">
                <div class="absolute inset-0 bg-gradient-to-r from-primario/20 to-secundario/20 opacity-0 group-hover:opacity-100 transition-opacity duration-1000"></div>
                <div class="relative z-10 grid lg:grid-cols-2 gap-12 items-center">
                    <div>
                        <h3 class="text-3xl lg:text-5xl font-black text-white leading-tight mb-6">
                            ¿Buscas algo <br/>
                            <span class="text-secundario">más exclusivo?</span>
                        </h3>
                        <p class="text-zinc-400 text-lg mb-10 max-w-md italic">
                            "Nuestra misión es traerte lo que no encuentras en ningún otro lugar."
                        </p>
                        <button class="px-8 py-4 bg-white text-zinc-900 rounded-2xl font-black uppercase tracking-widest hover:bg-secundario transition-all active:scale-95">
                            Solicitar Pedido Especial
                        </button>
                    </div>
                    <div class="hidden lg:block relative h-64">
                        <div class="absolute inset-0 bg-white/5 rounded-[2rem] backdrop-blur-3xl transform rotate-3 scale-110"></div>
                        <div class="absolute inset-0 flex items-center justify-center text-9xl">🎁</div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

