<script setup lang="ts">
const categories = [
    {
        name: 'Bebidas',
        id: 1,
        icon: '🥤',
        bgColor: 'bg-[#FF2E7A]/10',
        color: 'text-[#FF2E7A]',
    },
    {
        name: 'Snacks',
        id: 2,
        icon: '🍟',
        bgColor: 'bg-[#FFD232]/20',
        color: 'text-[#D9A400]',
    },
    {
        name: 'Abarrotes',
        id: 3,
        icon: '🥫',
        bgColor: 'bg-zinc-100 dark:bg-zinc-800',
        color: 'text-zinc-600 dark:text-zinc-300',
    },
    {
        name: 'Lácteos',
        id: 4,
        icon: '🥛',
        bgColor: 'bg-[#FF92B7]/20',
        color: 'text-[#FF2E7A]',
    },
    {
        name: 'Limpieza',
        id: 5,
        icon: '🧼',
        bgColor: 'bg-[#FFF0B3]/50',
        color: 'text-[#D9A400]',
    },
    {
        name: 'Tecnología',
        id: 6,
        icon: '🔌',
        bgColor: 'bg-zinc-800 dark:bg-zinc-200',
        color: 'text-white dark:text-zinc-900',
    },
];
</script>

<template>
    <section
        id="categorias"
        class="bg-white py-20 transition-colors dark:bg-zinc-950"
    >
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 flex items-end justify-between">
                <div>
                    <h2
                        class="text-3xl font-black leading-tight text-zinc-900 dark:text-white md:text-4xl"
                    >
                        Explorar Categorías
                    </h2>
                    <p
                        class="mt-2 font-medium text-zinc-500 dark:text-zinc-400"
                    >
                        Tenemos todo lo que buscas
                    </p>
                </div>
                <button
                    class="text-primario hidden items-center gap-2 font-bold transition-all hover:gap-3 sm:flex"
                >
                    Ver catálogo completo
                    <svg
                        class="h-5 w-5"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M17 8l4 4m0 0l-4 4m4-4H3"
                        />
                    </svg>
                </button>
            </div>

            <div
                class="grid grid-cols-2 gap-4 sm:gap-6 md:grid-cols-3 lg:grid-cols-6"
            >
                <!-- Category Cards -->
                <a
                    href="#"
                    v-for="cat in categories"
                    :key="cat.id"
                    class="hover:shadow-primario/10 group flex transform flex-col items-center justify-center rounded-3xl border border-zinc-100 bg-white p-6 transition-all duration-300 hover:-translate-y-2 hover:border-transparent hover:shadow-xl dark:border-zinc-800 dark:bg-zinc-900"
                >
                    <div
                        :class="[
                            'mb-4 flex h-20 w-20 items-center justify-center rounded-2xl text-4xl transition-transform duration-300 group-hover:rotate-3 group-hover:scale-110',
                            cat.bgColor,
                        ]"
                    >
                        {{ cat.icon }}
                    </div>

                    <h3
                        class="group-hover:text-primario text-center font-black text-zinc-800 transition-colors dark:text-zinc-200"
                    >
                        {{ cat.name }}
                    </h3>
                </a>
            </div>

            <div class="mt-8 text-center sm:hidden">
                <button
                    class="text-primario bg-primario/5 w-full rounded-2xl py-4 font-bold"
                >
                    Ver todas las categorías
                </button>
            </div>
        </div>
    </section>
</template>
