<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <section class="relative min-h-[90vh] flex items-center pt-32 pb-20 lg:pt-40 lg:pb-32 overflow-hidden bg-white dark:bg-zinc-950 transition-colors">
        <!-- Background Decorators -->
        <div class="absolute -top-40 -right-40 w-[600px] h-[600px] bg-acento/20 rounded-full blur-[120px] opacity-70 animate-pulse-slow"></div>
        <div class="absolute top-40 -left-20 w-[400px] h-[400px] bg-secundario/20 rounded-full blur-[100px] opacity-60"></div>
        <div class="absolute bottom-0 right-1/4 w-[300px] h-[300px] bg-primario/10 rounded-full blur-[80px]"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 w-full">
            <div class="grid lg:grid-cols-2 gap-16 lg:gap-12 items-center">
                
                <!-- Text Content -->
                <div class="space-y-10 animate-fade-in-up">
                    <div class="inline-flex items-center gap-3 px-5 py-2.5 rounded-full bg-zinc-900 dark:bg-zinc-800 text-white text-xs font-black tracking-[0.2em] uppercase shadow-2xl">
                        <span class="flex h-2 w-2 relative">
                          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primario opacity-75"></span>
                          <span class="relative inline-flex rounded-full h-2 w-2 bg-primario"></span>
                        </span>
                        Experiencia Premium
                    </div>
                    
                    <h1 class="text-6xl sm:text-7xl lg:text-8xl font-black text-zinc-900 dark:text-white leading-[0.95] tracking-tighter">
                        Calidad que <br/>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-primario via-acento to-secundario italic pr-4">se siente.</span>
                    </h1>
                    
                    <p class="text-xl sm:text-2xl text-zinc-600 dark:text-zinc-400 max-w-lg leading-relaxed font-medium">
                        En Monasterios Market seleccionamos lo mejor del mundo para llevarlo directo a tu mesa. Exclusividad en cada detalle.
                    </p>
                    
                    <div class="flex flex-col sm:flex-row gap-5 pt-6">
                        <Link :href="route('register')" class="group relative inline-flex items-center justify-center px-10 py-5 bg-primario text-white rounded-[2rem] font-black text-xl overflow-hidden transition-all hover:scale-105 active:scale-95 shadow-2xl shadow-primario/40">
                            <span class="absolute inset-0 w-full h-full bg-gradient-to-br from-primario via-acento to-secundario opacity-0 group-hover:opacity-100 transition-opacity duration-500 ease-out"></span>
                            <span class="relative flex items-center gap-3">
                                Comenzar pedido <svg class="w-6 h-6 group-hover:translate-x-2 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                            </span>
                        </Link>
                        
                        <a href="#ofertas" class="inline-flex items-center justify-center px-10 py-5 bg-white/10 dark:bg-zinc-900/50 backdrop-blur-md border-2 border-zinc-200 dark:border-zinc-800 text-zinc-800 dark:text-zinc-200 rounded-[2rem] font-black text-xl hover:bg-zinc-900 hover:text-white hover:border-zinc-900 dark:hover:bg-white dark:hover:text-zinc-900 dark:hover:border-white transition-all active:scale-95">
                            Ver Productos
                        </a>
                    </div>

                    <!-- Luxury Indicators -->
                    <div class="pt-10 flex flex-wrap items-center gap-8">
                        <div class="flex flex-col">
                            <span class="text-3xl font-black text-zinc-900 dark:text-white">100%</span>
                            <span class="text-xs font-bold text-zinc-500 uppercase tracking-widest">Garantizado</span>
                        </div>
                        <div class="w-px h-10 bg-zinc-200 dark:bg-zinc-800"></div>
                        <div class="flex flex-col">
                            <span class="text-3xl font-black text-zinc-900 dark:text-white">+5k</span>
                            <span class="text-xs font-bold text-zinc-500 uppercase tracking-widest">Clientes VIP</span>
                        </div>
                        <div class="w-px h-10 bg-zinc-200 dark:bg-zinc-800"></div>
                        <div class="flex flex-col">
                            <span class="text-3xl font-black text-zinc-900 dark:text-white">24h</span>
                            <span class="text-xs font-bold text-zinc-500 uppercase tracking-widest">Entrega Elite</span>
                        </div>
                    </div>
                </div>

                <!-- Hero Visual Elements -->
                <div class="relative animate-float mt-12 lg:mt-0">
                    <div class="relative w-full aspect-[4/5] sm:aspect-square max-w-xl mx-auto">
                        <!-- Background Glow -->
                        <div class="absolute inset-0 bg-gradient-to-tr from-secundario/30 via-primario/20 to-acento/30 rounded-[4rem] blur-3xl"></div>
                        
                        <!-- Premium Image Frame -->
                        <div class="absolute inset-0 rounded-[4rem] overflow-hidden border-[12px] border-white dark:border-zinc-900 shadow-2xl relative group">
                            <img 
                                src="/images/landing/hero_banner.png" 
                                alt="Monasterios Market Premium" 
                                class="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110"
                            />
                            
                            <!-- Overlays -->
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
                            
                            <!-- Floating Card -->
                            <div class="absolute bottom-8 left-8 right-8 bg-black/40 backdrop-blur-2xl p-6 rounded-[2rem] border border-white/20 text-white animate-fade-in-up" style="animation-delay: 0.5s;">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-xs font-black uppercase tracking-[0.2em] text-secundario mb-1">Nueva Colección</p>
                                        <h3 class="text-2xl font-black leading-tight">Gourmet Selection 2024</h3>
                                    </div>
                                    <div class="w-14 h-14 bg-white rounded-2xl flex items-center justify-center text-zinc-900 shadow-xl">
                                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Decorative floating elements -->
                        <div class="absolute -top-10 -right-10 w-32 h-32 bg-secundario rounded-full flex items-center justify-center text-5xl shadow-2xl animate-bounce-slow z-20">✨</div>
                        <div class="absolute top-1/2 -left-12 w-24 h-24 bg-white dark:bg-zinc-800 rounded-3xl flex items-center justify-center text-4xl shadow-2xl animate-float-slow z-20">💎</div>
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
    0%, 100% { transform: translateY(0px) rotate(0deg); }
    50% { transform: translateY(-30px) rotate(5deg); }
}
.animate-bounce-slow {
    animation: bounce 6s ease-in-out infinite;
}
@keyframes bounce {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-20px); }
}
.animate-fade-in-up {
    animation: fadeInUp 1.2s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(60px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-pulse-slow {
    animation: pulse 10s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
@keyframes pulse {
    0%, 100% { opacity: 0.7; scale: 1; }
    50% { opacity: 0.3; scale: 1.1; }
}
</style>

