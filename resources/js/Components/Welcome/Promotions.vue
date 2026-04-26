<script setup lang="ts">
</script>

<template>
    <section class="py-24 bg-white dark:bg-zinc-950 transition-colors overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="grid lg:grid-cols-2 gap-8">
                <!-- Promo 1: TODO A LUK -->
                <div class="relative rounded-[3rem] bg-gradient-to-br from-primario to-acento overflow-hidden group p-10 flex flex-col justify-center min-h-[350px]">
                    <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full blur-3xl group-hover:scale-150 transition-transform duration-700"></div>
                    <div class="relative z-10 w-[70%]">
                        <span class="inline-flex items-center gap-1 px-3 py-1 bg-white/20 text-white text-xs font-black uppercase rounded-lg mb-4 backdrop-blur-md">
                            <span class="w-2 h-2 rounded-full bg-white animate-pulse"></span> Lo más llevado
                        </span>
                        <h3 class="text-5xl lg:text-6xl font-display font-black text-white leading-none mb-3 drop-shadow-sm">
                            TODO A <span class="text-secundario">LUK</span>
                        </h3>
                        <p class="text-white/90 font-bold mb-6 text-lg">
                            Lleva cualquier producto de esta sección por $1.000 o menos.
                        </p>
                        <button class="bg-secundario text-zinc-900 px-6 py-3 rounded-xl font-bold font-sans hover:bg-white hover:shadow-xl transition-all active:scale-95 shadow-lg shadow-secundario/30">
                            Ver catálogo a luka
                        </button>
                    </div>
                    <!-- Promo image mock -->
                    <div class="absolute -bottom-8 -right-8 text-[14rem] drop-shadow-2xl group-hover:scale-110 group-hover:-rotate-12 transition-transform duration-500">
                        🤑
                    </div>
                </div>

                <!-- Promo 2 -->
                <div class="relative rounded-[3rem] bg-zinc-900 dark:bg-zinc-900 border border-transparent dark:border-zinc-800 overflow-hidden group p-10 flex flex-col justify-center min-h-[350px]">
                    <div class="absolute -bottom-10 -left-10 w-64 h-64 bg-secundario/20 rounded-full blur-3xl group-hover:bg-secundario/30 transition-colors duration-700"></div>
                    <div class="relative z-10 w-2/3">
                        <span class="inline-block px-3 py-1 bg-secundario text-zinc-900 text-xs font-black uppercase rounded-lg mb-4">Mundo Bebé</span>
                        <h3 class="text-4xl font-black text-white leading-tight mb-4 text-balance">
                            Todo para el cuidado de los más pequeños
                        </h3>
                        <button class="bg-secundario text-zinc-900 px-6 py-3 rounded-xl font-bold shadow-lg shadow-secundario/20 hover:bg-white transition-colors active:scale-95">
                            Ver Sección
                        </button>
                    </div>
                    <!-- Promo image mock -->
                    <div class="absolute top-10 -right-4 text-[10rem] drop-shadow-2xl group-hover:scale-110 transition-transform duration-500">
                        👶
                    </div>
                </div>
            </div>

        </div>
    </section>
</template>
