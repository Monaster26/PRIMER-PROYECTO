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
    <nav
        class="fixed top-0 z-50 w-full border-b border-zinc-100 bg-white/95 backdrop-blur-md transition-colors duration-300 dark:border-zinc-800 dark:bg-zinc-950/95"
    >
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-24 items-center justify-between">
                <!-- Logo & Text -->
                <div class="flex items-center gap-3">
                    <img
                        src="/images/logo.png"
                        alt="Monasterios Market"
                        class="h-16 w-auto object-contain"
                    />
                    <span
                        class="hidden font-display text-[26px] font-bold tracking-tight sm:block"
                    >
                        <span class="text-primario">Monasterios</span>
                        <span class="text-secundario">Market</span>
                    </span>
                </div>

                <!-- Main Menu Links -->
                <div class="hidden items-center gap-6 lg:flex">
                    <a
                        href="#"
                        class="hover:text-primario font-sans font-bold text-zinc-800 transition-colors dark:text-zinc-200"
                        >Inicio</a
                    >
                    <a
                        href="#categorias"
                        class="hover:text-primario font-sans font-bold text-zinc-800 transition-colors dark:text-zinc-200"
                        >Categorías</a
                    >
                    <a
                        href="#"
                        class="hover:text-primario font-sans font-bold text-zinc-800 transition-colors dark:text-zinc-200"
                        >Comida Preparada</a
                    >
                    <a
                        href="#"
                        class="hover:text-primario font-sans font-bold text-zinc-800 transition-colors dark:text-zinc-200"
                        >Servicios</a
                    >
                    <a
                        href="#sucursales"
                        class="hover:text-primario font-sans font-bold text-zinc-800 transition-colors dark:text-zinc-200"
                        >Sucursales</a
                    >
                </div>

                <!-- Right Actions -->
                <div class="flex items-center gap-4 lg:gap-6">
                    <!-- Cart Button (Pink background) -->
                    <button
                        class="bg-primario hover:shadow-primario/30 relative flex h-12 w-12 items-center justify-center rounded-full text-white transition-all hover:scale-110 hover:shadow-lg active:scale-95"
                    >
                        <svg
                            class="h-6 w-6"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"
                            />
                        </svg>
                        <span
                            class="bg-secundario absolute right-0 top-0 flex h-4 w-4 -translate-y-1/4 translate-x-1/4 transform items-center justify-center rounded-full border-2 border-white text-[9px] font-black text-zinc-900"
                        >
                            0
                        </span>
                    </button>

                    <!-- Auth/Dashboard (Hidden on Mobile for cleaner view, but accessible) -->
                    <div
                        class="hidden border-l border-zinc-200 pl-2 dark:border-zinc-700 sm:block"
                    >
                        <template v-if="canLogin">
                            <Link
                                v-if="$page.props.auth.user"
                                :href="route('dashboard')"
                                class="text-primario font-bold"
                                >Panel</Link
                            >
                            <template v-else>
                                <div class="flex flex-col text-right">
                                    <Link
                                        :href="route('login')"
                                        class="hover:text-primario text-xs font-bold text-zinc-500"
                                        >Ingresar</Link
                                    >
                                </div>
                            </template>
                        </template>
                    </div>

                    <!-- Sound Options -->
                    <button
                        @click="toggleMute"
                        class="rounded-full bg-zinc-100 p-2 text-lg transition-colors hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700"
                    >
                        {{ isMuted ? '🔇' : '🔊' }}
                    </button>

                    <!-- Mobile Menu Trigger -->
                    <button
                        class="p-2 text-zinc-800 dark:text-neutral-200 lg:hidden"
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
                                d="M4 6h16M4 12h16M4 18h16"
                            />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </nav>
</template>
