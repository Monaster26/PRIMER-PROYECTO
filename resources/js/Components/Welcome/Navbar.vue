<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps<{
    canLogin?: boolean;
    canRegister?: boolean;
}>();

const isMuted = ref(false);

const emit = defineEmits(['toggle-sound', 'play-sound']);

const toggleMute = () => {
    isMuted.value = !isMuted.value;
    emit('toggle-sound', isMuted.value);
};
</script>

<template>
    <nav class="fixed top-0 w-full z-50 bg-white/95 backdrop-blur-md border-b border-zinc-100 dark:bg-zinc-950/95 dark:border-zinc-800 transition-colors duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-24 items-center">
                
                <!-- Logo & Text -->
                <div class="flex items-center gap-3">
                    <img src="/images/logo.png" alt="Monasterios Market" class="h-16 w-auto object-contain" />
                    <span class="text-[26px] font-display font-bold tracking-tight hidden sm:block">
                        <span class="text-primario">Monasterios</span> <span class="text-secundario">Market</span>
                    </span>
                </div>

                <!-- Main Menu Links -->
                <div class="hidden lg:flex items-center gap-6">
                    <a href="#" class="font-sans font-bold text-zinc-800 dark:text-zinc-200 hover:text-primario transition-colors">Inicio</a>
                    <a href="#categorias" class="font-sans font-bold text-zinc-800 dark:text-zinc-200 hover:text-primario transition-colors">Categorías</a>
                    <a href="#" class="font-sans font-bold text-zinc-800 dark:text-zinc-200 hover:text-primario transition-colors">Comida Preparada</a>
                    <a href="#" class="font-sans font-bold text-zinc-800 dark:text-zinc-200 hover:text-primario transition-colors">Servicios</a>
                    <a href="#sucursales" class="font-sans font-bold text-zinc-800 dark:text-zinc-200 hover:text-primario transition-colors">Sucursales</a>
                </div>

                <!-- Right Actions -->
                <div class="flex items-center gap-4 lg:gap-6">
                    <!-- Cart Button (Pink background) -->
                    <button class="relative w-12 h-12 bg-primario text-white rounded-full flex items-center justify-center hover:scale-110 hover:shadow-lg hover:shadow-primario/30 transition-all active:scale-95">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span class="absolute top-0 right-0 w-4 h-4 bg-secundario text-zinc-900 border-2 border-white rounded-full flex items-center justify-center text-[9px] font-black transform translate-x-1/4 -translate-y-1/4">
                            0
                        </span>
                    </button>

                    <!-- Auth/Dashboard (Hidden on Mobile for cleaner view, but accessible) -->
                    <div class="hidden sm:block pl-2 border-l border-zinc-200 dark:border-zinc-700">
                        <template v-if="canLogin">
                            <Link v-if="$page.props.auth.user" :href="route('dashboard')" class="text-primario font-bold">Panel</Link>
                            <template v-else>
                                <div class="flex flex-col text-right">
                                    <Link :href="route('login')" class="text-xs text-zinc-500 hover:text-primario font-bold">Ingresar</Link>
                                </div>
                            </template>
                        </template>
                    </div>

                    <!-- Sound Options -->
                    <button @click="toggleMute" class="text-lg p-2 bg-zinc-100 dark:bg-zinc-800 rounded-full hover:bg-zinc-200 dark:hover:bg-zinc-700 transition-colors">
                        {{ isMuted ? '🔇' : '🔊' }}
                    </button>

                    <!-- Mobile Menu Trigger -->
                    <button class="lg:hidden text-zinc-800 dark:text-neutral-200 p-2">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>

            </div>
        </div>
    </nav>
</template>
