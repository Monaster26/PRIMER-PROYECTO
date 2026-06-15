<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

// Import Custom Components
import Navbar from '@/Components/Welcome/Navbar.vue';
import Hero from '@/Components/Welcome/Hero.vue';
import CategoryGrid from '@/Components/Welcome/CategoryGrid.vue';
import Promotions from '@/Components/Welcome/Promotions.vue';
import FeaturedProducts from '@/Components/Welcome/FeaturedProducts.vue';
import Benefits from '@/Components/Welcome/Benefits.vue';
import Location from '@/Components/Welcome/Location.vue';
import Footer from '@/Components/Welcome/Footer.vue';
import FloatWhatsapp from '@/Components/Welcome/FloatWhatsapp.vue';

defineProps<{
    canLogin?: boolean;
    canRegister?: boolean;
}>();

const showSoundPrompt = ref(false);

const handleSoundToggle = (isMuted: boolean) => {
    const audio = document.getElementById('startup-sound') as HTMLAudioElement | null;
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
    const audio = document.getElementById('startup-sound') as HTMLAudioElement | null;
    if (audio) {
        audio.play().catch(e => console.log('Interacción requerida para audio'));
        localStorage.setItem('monasterios_sound_played', 'true');
    }
};
</script>

<template>
    <Head title="Monasterios Market - Tu Mini Market Pro" />

    <div class="min-h-screen bg-white dark:bg-zinc-950 font-sans selection:bg-primario selection:text-white">
        
        <!-- Audio Element -->
        <audio id="startup-sound" src="/sounds/startup.mp3" preload="auto"></audio>

        <!-- Sound Prompt Overlay -->
        <transition name="fade">
            <div v-if="showSoundPrompt" class="fixed inset-0 z-[100] flex items-center justify-center bg-zinc-950/80 backdrop-blur-sm p-4">
                <div class="bg-white dark:bg-zinc-900 p-8 rounded-[2rem] shadow-2xl max-w-sm w-full text-center border border-zinc-100 dark:border-zinc-800">
                    <div class="w-20 h-20 bg-primario/10 rounded-full flex items-center justify-center text-4xl mx-auto mb-6">🏪</div>
                    <h2 class="text-2xl font-display font-black mb-2 text-zinc-900 dark:text-white">¡Bienvenido a Monasterios Market!</h2>
                    <p class="text-zinc-500 dark:text-zinc-400 mb-8 font-medium">¿Deseas activar el sonido de bienvenida para una mejor experiencia?</p>
                    <div class="flex flex-col sm:flex-row gap-3">
                        <button @click="startExperience" class="flex-1 bg-primario text-white font-bold py-3.5 rounded-xl transition-all shadow-lg hover:shadow-primario/30 active:scale-95">
                            Sí, ¡claro!
                        </button>
                        <button @click="showSoundPrompt = false; localStorage.setItem('monasterios_sound_played', 'true')" class="flex-1 bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 font-bold py-3.5 rounded-xl transition-all hover:bg-zinc-200 dark:hover:bg-zinc-700 active:scale-95">
                            No, gracias
                        </button>
                    </div>
                </div>
            </div>
        </transition>

        <!-- Layout Sections -->
        <Navbar :can-login="canLogin" :can-register="canRegister" @toggle-sound="handleSoundToggle" />
        
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
  background: #FFF5F8; 
}
.dark ::-webkit-scrollbar-track {
  background: #18181b; 
}
::-webkit-scrollbar-thumb {
  background: #FF92B7; 
  border-radius: 10px;
}
.dark ::-webkit-scrollbar-thumb {
  background: #3f3f46; 
}
::-webkit-scrollbar-thumb:hover {
  background: #FF2E7A; 
}

/* Base override to ensure fonts apply correctly globally where needed */
html, body {
  font-family: 'Nunito', sans-serif;
}
</style>
