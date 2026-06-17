<script setup lang="ts">
const benefits = [
    {
        id: 1,
        title: 'Entrega Rápida',
        description: 'Recibe tus productos en minutos.',
        icon: '🚀',
    },
    {
        id: 2,
        title: 'Frescura Garantizada',
        description: 'Seleccionamos lo mejor para ti.',
        icon: '🥑',
    },
    {
        id: 3,
        title: 'Precios Accesibles',
        description: 'Las mejores ofertas del barrio.',
        icon: '🏷️',
    },
    {
        id: 4,
        title: 'Pago Seguro',
        description: 'Protegemos todas tus transacciones.',
        icon: '🔒',
    },
];
</script>

<template>
    <section
        class="border-y border-zinc-100 bg-zinc-50 py-16 transition-colors dark:border-zinc-800 dark:bg-zinc-950"
    >
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div
                class="grid grid-cols-1 gap-8 divide-y divide-zinc-200 dark:divide-zinc-800 sm:grid-cols-2 sm:divide-x sm:divide-y-0 lg:grid-cols-4"
            >
                <div
                    v-for="benefit in benefits"
                    :key="benefit.id"
                    class="flex flex-col items-center px-6 pt-8 text-center sm:pt-0"
                >
                    <div
                        class="hover:border-primario/50 group mb-4 flex h-16 w-16 items-center justify-center rounded-full border border-zinc-100 bg-white text-3xl shadow-sm transition-all hover:scale-110 dark:border-zinc-800 dark:bg-zinc-900"
                    >
                        {{ benefit.icon }}
                    </div>
                    <h4
                        class="mb-2 text-lg font-black text-zinc-900 dark:text-white"
                    >
                        {{ benefit.title }}
                    </h4>
                    <p
                        class="max-w-[200px] text-sm text-zinc-500 dark:text-zinc-400"
                    >
                        {{ benefit.description }}
                    </p>
                </div>
            </div>
        </div>
    </section>
</template>
