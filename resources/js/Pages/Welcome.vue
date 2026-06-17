<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

// Import Custom Components
import Benefits from '@/Components/Welcome/Benefits.vue';
import CategoryGrid from '@/Components/Welcome/CategoryGrid.vue';
import FeaturedProducts from '@/Components/Welcome/FeaturedProducts.vue';
import FloatWhatsapp from '@/Components/Welcome/FloatWhatsapp.vue';
import Footer from '@/Components/Welcome/Footer.vue';
import Hero from '@/Components/Welcome/Hero.vue';
import Location from '@/Components/Welcome/Location.vue';
import Navbar from '@/Components/Welcome/Navbar.vue';
import Promotions from '@/Components/Welcome/Promotions.vue';

defineProps<{
    canLogin?: boolean;
    canRegister?: boolean;
}>();

const showSoundPrompt = ref(false);

const handleSoundToggle = (isMuted: boolean) => {
    const audio = document.getElementById(
        'startup-sound',
    ) as HTMLAudioElement | null;
    if (audio) {
        audio.muted = isMuted;
    }
};

onMounted(() => {
    const hasPlayed = localStorage.getItem('monasterios_sound_played');
    if (!hasPlayed) {
        showSoundPrompt.value = true;
    }
});

const startExperience = () => {
    showSoundPrompt.value = false;
    const audio = document.getElementById(
        'startup-sound',
    ) as HTMLAudioElement | null;
    if (audio) {
        audio
            .play()
            .catch((e) => console.log('Interacción requerida para audio'));
        localStorage.setItem('monasterios_sound_played', 'true');
    }
};
</script>

<template>
    <Head title="Monasterios Market - Tu Mini Market Pro" />

    <div
        class="selection:bg-primario min-h-screen bg-white font-sans selection:text-white dark:bg-zinc-950"
    >
        <!-- Audio Element -->
        <audio
            id="startup-sound"
            src="/sounds/startup.mp3"
            preload="auto"
        ></audio>

        <!-- Sound Prompt Overlay -->
        <transition name="fade">
            <div
                v-if="showSoundPrompt"
                class="fixed inset-0 z-[100] flex items-center justify-center bg-zinc-950/80 p-4 backdrop-blur-sm"
            >
                <div
                    class="w-full max-w-sm rounded-[2rem] border border-zinc-100 bg-white p-8 text-center shadow-2xl dark:border-zinc-800 dark:bg-zinc-900"
                >
                    <div
                        class="bg-primario/10 mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full text-4xl"
                    >
                        🏪
                    </div>
                    <h2
                        class="mb-2 font-display text-2xl font-black text-zinc-900 dark:text-white"
                    >
                        ¡Bienvenido a Monasterios Market!
                    </h2>
                    <p
                        class="mb-8 font-medium text-zinc-500 dark:text-zinc-400"
                    >
                        ¿Deseas activar el sonido de bienvenida para una mejor
                        experiencia?
                    </p>
                    <div class="flex flex-col gap-3 sm:flex-row">
                        <button
                            @click="startExperience"
                            class="bg-primario hover:shadow-primario/30 flex-1 rounded-xl py-3.5 font-bold text-white shadow-lg transition-all active:scale-95"
                        >
                            Sí, ¡claro!
                        </button>
                        <button
                            @click="
                                showSoundPrompt = false;
                                localStorage.setItem(
                                    'monasterios_sound_played',
                                    'true',
                                );
                            "
                            class="flex-1 rounded-xl bg-zinc-100 py-3.5 font-bold text-zinc-700 transition-all hover:bg-zinc-200 active:scale-95 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
                        >
                            No, gracias
                        </button>
                    </div>
                </div>
            </div>
        </transition>

        <!-- Layout Sections -->
        <Navbar
            :can-login="canLogin"
            :can-register="canRegister"
            @toggle-sound="handleSoundToggle"
        />

        <main>
            <Hero />
            <CategoryGrid />
            <Promotions />
            <FeaturedProducts id="ofertas" />
            <Location />
            <Benefits />
        </main>

        <Footer />
        <FloatWhatsapp />
    </div>
</template>

<style>
/* Global custom utility classes */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.5s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

/* Scrollbar personalizado */
::-webkit-scrollbar {
    width: 10px;
}
::-webkit-scrollbar-track {
    background: #fff5f8;
}
.dark ::-webkit-scrollbar-track {
    background: #18181b;
}
::-webkit-scrollbar-thumb {
    background: #ff92b7;
    border-radius: 10px;
}
.dark ::-webkit-scrollbar-thumb {
    background: #3f3f46;
}
::-webkit-scrollbar-thumb:hover {
    background: #ff2e7a;
}

/* Base override to ensure fonts apply correctly globally where needed */
html,
body {
    font-family: 'Nunito', sans-serif;
}
</style>
