<script setup lang="ts">
const benefits = [
    { id: 1, title: 'Entrega Rápida', description: 'Recibe tus productos en minutos.', icon: '🚀' },
    { id: 2, title: 'Frescura Garantizada', description: 'Seleccionamos lo mejor para ti.', icon: '🥑' },
    { id: 3, title: 'Precios Accesibles', description: 'Las mejores ofertas del barrio.', icon: '🏷️' },
    { id: 4, title: 'Pago Seguro', description: 'Protegemos todas tus transacciones.', icon: '🔒' }
];
</script>

<template>
    <section class="py-16 bg-zinc-50 dark:bg-zinc-950 transition-colors border-y border-zinc-100 dark:border-zinc-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 divide-y sm:divide-y-0 sm:divide-x divide-zinc-200 dark:divide-zinc-800">
                
                <div v-for="benefit in benefits" :key="benefit.id" class="flex flex-col items-center text-center px-6 pt-8 sm:pt-0">
                    <div class="w-16 h-16 rounded-full bg-white dark:bg-zinc-900 border border-zinc-100 dark:border-zinc-800 shadow-sm flex items-center justify-center text-3xl mb-4 group hover:scale-110 hover:border-primario/50 transition-all">
                        {{ benefit.icon }}
                    </div>
                    <h4 class="text-lg font-black text-zinc-900 dark:text-white mb-2">{{ benefit.title }}</h4>
                    <p class="text-zinc-500 dark:text-zinc-400 text-sm max-w-[200px]">{{ benefit.description }}</p>
                </div>

            </div>
        </div>
    </section>
</template>
