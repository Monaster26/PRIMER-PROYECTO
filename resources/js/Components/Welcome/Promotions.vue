<script setup lang="ts"></script>

<template>
    <section
        class="overflow-hidden bg-white py-24 transition-colors dark:bg-zinc-950"
    >
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-8 lg:grid-cols-2">
                <!-- Promo 1: TODO A LUK -->
                <div
                    class="from-primario to-acento group relative flex min-h-[350px] flex-col justify-center overflow-hidden rounded-[3rem] bg-gradient-to-br p-10"
                >
                    <div
                        class="absolute right-0 top-0 h-64 w-64 rounded-full bg-white/10 blur-3xl transition-transform duration-700 group-hover:scale-150"
                    ></div>
                    <div class="relative z-10 w-[70%]">
                        <span
                            class="mb-4 inline-flex items-center gap-1 rounded-lg bg-white/20 px-3 py-1 text-xs font-black uppercase text-white backdrop-blur-md"
                        >
                            <span
                                class="h-2 w-2 animate-pulse rounded-full bg-white"
                            ></span>
                            Lo más llevado
                        </span>
                        <h3
                            class="mb-3 font-display text-5xl font-black leading-none text-white drop-shadow-sm lg:text-6xl"
                        >
                            TODO A <span class="text-secundario">LUK</span>
                        </h3>
                        <p class="mb-6 text-lg font-bold text-white/90">
                            Lleva cualquier producto de esta sección por $1.000
                            o menos.
                        </p>
                        <button
                            class="bg-secundario shadow-secundario/30 rounded-xl px-6 py-3 font-sans font-bold text-zinc-900 shadow-lg transition-all hover:bg-white hover:shadow-xl active:scale-95"
                        >
                            Ver catálogo a luka
                        </button>
                    </div>
                    <!-- Promo image mock -->
                    <div
                        class="absolute -bottom-8 -right-8 text-[14rem] drop-shadow-2xl transition-transform duration-500 group-hover:-rotate-12 group-hover:scale-110"
                    >
                        🤑
                    </div>
                </div>

                <!-- Promo 2 -->
                <div
                    class="group relative flex min-h-[350px] flex-col justify-center overflow-hidden rounded-[3rem] border border-transparent bg-zinc-900 p-10 dark:border-zinc-800 dark:bg-zinc-900"
                >
                    <div
                        class="bg-secundario/20 group-hover:bg-secundario/30 absolute -bottom-10 -left-10 h-64 w-64 rounded-full blur-3xl transition-colors duration-700"
                    ></div>
                    <div class="relative z-10 w-2/3">
                        <span
                            class="bg-secundario mb-4 inline-block rounded-lg px-3 py-1 text-xs font-black uppercase text-zinc-900"
                            >Mundo Bebé</span
                        >
                        <h3
                            class="mb-4 text-balance text-4xl font-black leading-tight text-white"
                        >
                            Todo para el cuidado de los más pequeños
                        </h3>
                        <button
                            class="bg-secundario shadow-secundario/20 rounded-xl px-6 py-3 font-bold text-zinc-900 shadow-lg transition-colors hover:bg-white active:scale-95"
                        >
                            Ver Sección
                        </button>
                    </div>
                    <!-- Promo image mock -->
                    <div
                        class="absolute -right-4 top-10 text-[10rem] drop-shadow-2xl transition-transform duration-500 group-hover:scale-110"
                    >
                        👶
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
