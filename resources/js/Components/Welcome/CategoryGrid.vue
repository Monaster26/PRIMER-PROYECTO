<script setup lang="ts">
const categories = [
    { name: 'Bebidas', id: 1, icon: '🥤', bgColor: 'bg-[#FF2E7A]/10', color: 'text-[#FF2E7A]' },
    { name: 'Snacks', id: 2, icon: '🍟', bgColor: 'bg-[#FFD232]/20', color: 'text-[#D9A400]' },
    { name: 'Abarrotes', id: 3, icon: '🥫', bgColor: 'bg-zinc-100 dark:bg-zinc-800', color: 'text-zinc-600 dark:text-zinc-300' },
    { name: 'Lácteos', id: 4, icon: '🥛', bgColor: 'bg-[#FF92B7]/20', color: 'text-[#FF2E7A]' },
    { name: 'Limpieza', id: 5, icon: '🧼', bgColor: 'bg-[#FFF0B3]/50', color: 'text-[#D9A400]' },
    { name: 'Tecnología', id: 6, icon: '🔌', bgColor: 'bg-zinc-800 dark:bg-zinc-200', color: 'text-white dark:text-zinc-900' },
];
</script>

<template>
    <section id="categorias" class="py-20 bg-white dark:bg-zinc-950 transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-end mb-12">
                <div>
                    <h2 class="text-3xl md:text-4xl font-black text-zinc-900 dark:text-white leading-tight">
                        Explorar Categorías
                    </h2>
                    <p class="text-zinc-500 dark:text-zinc-400 mt-2 font-medium">Tenemos todo lo que buscas</p>
                </div>
                <button class="hidden sm:flex items-center gap-2 text-primario font-bold hover:gap-3 transition-all">
                    Ver catálogo completo <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
                </button>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 sm:gap-6">
                <!-- Category Cards -->
                <a href="#" v-for="cat in categories" :key="cat.id" 
                   class="group flex flex-col items-center justify-center p-6 bg-white dark:bg-zinc-900 border border-zinc-100 dark:border-zinc-800 rounded-3xl hover:border-transparent hover:shadow-xl hover:shadow-primario/10 transition-all duration-300 transform hover:-translate-y-2">
                    
                    <div :class="['w-20 h-20 flex items-center justify-center rounded-2xl text-4xl mb-4 group-hover:scale-110 group-hover:rotate-3 transition-transform duration-300', cat.bgColor]">
                        {{ cat.icon }}
                    </div>
                    
                    <h3 class="font-black text-center text-zinc-800 dark:text-zinc-200 group-hover:text-primario transition-colors">{{ cat.name }}</h3>
                </a>
            </div>
            
            <div class="mt-8 text-center sm:hidden">
                <button class="w-full py-4 text-primario font-bold bg-primario/5 rounded-2xl">
                    Ver todas las categorías
                </button>
            </div>
        </div>
    </section>
</template>
