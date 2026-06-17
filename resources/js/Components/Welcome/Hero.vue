<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <section
        class="relative flex min-h-[90vh] items-center overflow-hidden bg-white pb-20 pt-32 transition-colors dark:bg-zinc-950 lg:pb-32 lg:pt-40"
    >
        <!-- Background Decorators -->
        <div
            class="bg-acento/20 animate-pulse-slow absolute -right-40 -top-40 h-[600px] w-[600px] rounded-full opacity-70 blur-[120px]"
        ></div>
        <div
            class="bg-secundario/20 absolute -left-20 top-40 h-[400px] w-[400px] rounded-full opacity-60 blur-[100px]"
        ></div>
        <div
            class="bg-primario/10 absolute bottom-0 right-1/4 h-[300px] w-[300px] rounded-full blur-[80px]"
        ></div>

        <div
            class="relative z-10 mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8"
        >
            <div class="grid items-center gap-16 lg:grid-cols-2 lg:gap-12">
                <!-- Text Content -->
                <div class="animate-fade-in-up space-y-10">
                    <div
                        class="inline-flex items-center gap-3 rounded-full bg-zinc-900 px-5 py-2.5 text-xs font-black uppercase tracking-[0.2em] text-white shadow-2xl dark:bg-zinc-800"
                    >
                        <span class="relative flex h-2 w-2">
                            <span
                                class="bg-primario absolute inline-flex h-full w-full animate-ping rounded-full opacity-75"
                            ></span>
                            <span
                                class="bg-primario relative inline-flex h-2 w-2 rounded-full"
                            ></span>
                        </span>
                        Experiencia Premium
                    </div>

                    <h1
                        class="text-6xl font-black leading-[0.95] tracking-tighter text-zinc-900 dark:text-white sm:text-7xl lg:text-8xl"
                    >
                        Calidad que <br />
                        <span
                            class="from-primario via-acento to-secundario bg-gradient-to-r bg-clip-text pr-4 italic text-transparent"
                            >se siente.</span
                        >
                    </h1>

                    <p
                        class="max-w-lg text-xl font-medium leading-relaxed text-zinc-600 dark:text-zinc-400 sm:text-2xl"
                    >
                        En Monasterios Market seleccionamos lo mejor del mundo
                        para llevarlo directo a tu mesa. Exclusividad en cada
                        detalle.
                    </p>

                    <div class="flex flex-col gap-5 pt-6 sm:flex-row">
                        <Link
                            :href="route('register')"
                            class="bg-primario shadow-primario/40 group relative inline-flex items-center justify-center overflow-hidden rounded-[2rem] px-10 py-5 text-xl font-black text-white shadow-2xl transition-all hover:scale-105 active:scale-95"
                        >
                            <span
                                class="from-primario via-acento to-secundario absolute inset-0 h-full w-full bg-gradient-to-br opacity-0 transition-opacity duration-500 ease-out group-hover:opacity-100"
                            ></span>
                            <span class="relative flex items-center gap-3">
                                Comenzar pedido
                                <svg
                                    class="h-6 w-6 transition-transform group-hover:translate-x-2"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2.5"
                                        d="M13 7l5 5m0 0l-5 5m5-5H6"
                                    />
                                </svg>
                            </span>
                        </Link>

                        <a
                            href="#ofertas"
                            class="inline-flex items-center justify-center rounded-[2rem] border-2 border-zinc-200 bg-white/10 px-10 py-5 text-xl font-black text-zinc-800 backdrop-blur-md transition-all hover:border-zinc-900 hover:bg-zinc-900 hover:text-white active:scale-95 dark:border-zinc-800 dark:bg-zinc-900/50 dark:text-zinc-200 dark:hover:border-white dark:hover:bg-white dark:hover:text-zinc-900"
                        >
                            Ver Productos
                        </a>
                    </div>

                    <!-- Luxury Indicators -->
                    <div class="flex flex-wrap items-center gap-8 pt-10">
                        <div class="flex flex-col">
                            <span
                                class="text-3xl font-black text-zinc-900 dark:text-white"
                                >100%</span
                            >
                            <span
                                class="text-xs font-bold uppercase tracking-widest text-zinc-500"
                                >Garantizado</span
                            >
                        </div>
                        <div
                            class="h-10 w-px bg-zinc-200 dark:bg-zinc-800"
                        ></div>
                        <div class="flex flex-col">
                            <span
                                class="text-3xl font-black text-zinc-900 dark:text-white"
                                >+5k</span
                            >
                            <span
                                class="text-xs font-bold uppercase tracking-widest text-zinc-500"
                                >Clientes VIP</span
                            >
                        </div>
                        <div
                            class="h-10 w-px bg-zinc-200 dark:bg-zinc-800"
                        ></div>
                        <div class="flex flex-col">
                            <span
                                class="text-3xl font-black text-zinc-900 dark:text-white"
                                >24h</span
                            >
                            <span
                                class="text-xs font-bold uppercase tracking-widest text-zinc-500"
                                >Entrega Elite</span
                            >
                        </div>
                    </div>
                </div>

                <!-- Hero Visual Elements -->
                <div class="animate-float relative mt-12 lg:mt-0">
                    <div
                        class="relative mx-auto aspect-[4/5] w-full max-w-xl sm:aspect-square"
                    >
                        <!-- Background Glow -->
                        <div
                            class="from-secundario/30 via-primario/20 to-acento/30 absolute inset-0 rounded-[4rem] bg-gradient-to-tr blur-3xl"
                        ></div>

                        <!-- Premium Image Frame -->
                        <div
                            class="group absolute relative inset-0 overflow-hidden rounded-[4rem] border-[12px] border-white shadow-2xl dark:border-zinc-900"
                        >
                            <img
                                src="/images/landing/hero_banner.png"
                                alt="Monasterios Market Premium"
                                class="h-full w-full object-cover transition-transform duration-1000 group-hover:scale-110"
                            />

                            <!-- Overlays -->
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"
                            ></div>

                            <!-- Floating Card -->
                            <div
                                class="animate-fade-in-up absolute bottom-8 left-8 right-8 rounded-[2rem] border border-white/20 bg-black/40 p-6 text-white backdrop-blur-2xl"
                                style="animation-delay: 0.5s"
                            >
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p
                                            class="text-secundario mb-1 text-xs font-black uppercase tracking-[0.2em]"
                                        >
                                            Nueva Colección
                                        </p>
                                        <h3
                                            class="text-2xl font-black leading-tight"
                                        >
                                            Gourmet Selection 2024
                                        </h3>
                                    </div>
                                    <div
                                        class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-zinc-900 shadow-xl"
                                    >
                                        <svg
                                            class="h-8 w-8"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"
                                            />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Decorative floating elements -->
                        <div
                            class="bg-secundario animate-bounce-slow absolute -right-10 -top-10 z-20 flex h-32 w-32 items-center justify-center rounded-full text-5xl shadow-2xl"
                        >
                            ✨
                        </div>
                        <div
                            class="animate-float-slow absolute -left-12 top-1/2 z-20 flex h-24 w-24 items-center justify-center rounded-3xl bg-white text-4xl shadow-2xl dark:bg-zinc-800"
                        >
                            💎
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
.animate-float {
    animation: float 8s ease-in-out infinite;
}
.animate-float-slow {
    animation: float 12s ease-in-out infinite;
}
@keyframes float {
    0%,
    100% {
        transform: translateY(0px) rotate(0deg);
    }
    50% {
        transform: translateY(-30px) rotate(5deg);
    }
}
.animate-bounce-slow {
    animation: bounce 6s ease-in-out infinite;
}
@keyframes bounce {
    0%,
    100% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-20px);
    }
}
.animate-fade-in-up {
    animation: fadeInUp 1.2s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(60px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
.animate-pulse-slow {
    animation: pulse 10s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
@keyframes pulse {
    0%,
    100% {
        opacity: 0.7;
        scale: 1;
    }
    50% {
        opacity: 0.3;
        scale: 1.1;
    }
}
</style>
