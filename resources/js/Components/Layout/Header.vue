<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { 
    Bell, ChevronDown, LogOut, Users, Sun, Moon, Search, Menu
} from 'lucide-vue-next';

defineProps<{ isDark: boolean; }>();
const emit = defineEmits(['toggleSidebar', 'toggleDark']);

const profileDropdownOpen = ref(false);
const profileRef = ref<HTMLElement | null>(null);

function handleClickOutside(e: MouseEvent) {
    if (profileRef.value && !profileRef.value.contains(e.target as Node)) {
        profileDropdownOpen.value = false;
    }
}

onMounted(() => document.addEventListener('mousedown', handleClickOutside));
onUnmounted(() => document.removeEventListener('mousedown', handleClickOutside));

function logout() {
    router.post(route('logout'));
}
</script>

<template>
    <header class="h-16 px-6 bg-white dark:bg-surface-dark border-b border-gray-100 dark:border-gray-800 flex items-center justify-between sticky top-0 z-40">
        <div class="flex items-center gap-4">
            <button @click="emit('toggleSidebar')" class="p-2 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors text-content-secondary">
                <Menu class="w-5 h-5" />
            </button>
            <div class="hidden md:flex items-center gap-2 bg-gray-50 dark:bg-gray-900 px-4 py-2 rounded-2xl border border-gray-100 dark:border-gray-800">
                <Search class="w-4 h-4 text-content-muted" />
                <input type="text" placeholder="Buscar..." class="bg-transparent border-none text-sm focus:ring-0 w-64 text-content-primary dark:text-white" />
            </div>
        </div>

        <div class="flex items-center gap-4">
            <button @click="emit('toggleDark')" class="p-2 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors text-content-secondary">
                <Sun v-if="isDark" class="w-5 h-5 text-yellow-500" />
                <Moon v-else class="w-5 h-5" />
            </button>

            <!-- Notifications -->
            <div class="relative">
                <button class="p-2 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors text-content-secondary">
                    <Bell class="w-5 h-5" />
                    <span class="absolute top-2 right-2 w-2 h-2 bg-primary-500 rounded-full border-2 border-white dark:border-surface-dark animate-pulse"></span>
                </button>
            </div>

            <!-- Profile Dropdown (AD) -->
            <div class="relative" ref="profileRef">
                <button 
                    type="button"
                    @click="profileDropdownOpen = !profileDropdownOpen"
                    class="flex items-center gap-3 p-1.5 rounded-2xl hover:bg-gray-100 dark:hover:bg-gray-800 transition-all border border-transparent hover:border-gray-200 dark:hover:border-gray-700"
                >
                    <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-primary-400 to-secondary-400 flex items-center justify-center text-white font-bold text-xs shadow-sm">
                        AD
                    </div>
                    <div class="hidden sm:block text-left">
                        <p class="text-xs font-black text-content-primary dark:text-white leading-none">Admin</p>
                        <p class="text-[10px] text-content-muted mt-1">Súper Usuario</p>
                    </div>
                    <ChevronDown class="w-4 h-4 text-content-muted transition-transform" :class="{ 'rotate-180': profileDropdownOpen }" />
                </button>

                <Transition
                    enter-active-class="transition duration-200 ease-out"
                    enter-from-class="transform scale-95 opacity-0 -translate-y-2"
                    enter-to-class="transform scale-100 opacity-100 translate-y-0"
                    leave-active-class="transition duration-150 ease-in"
                    leave-from-class="transform scale-100 opacity-100 translate-y-0"
                    leave-to-class="transform scale-95 opacity-0 -translate-y-2"
                >
                    <div v-if="profileDropdownOpen" class="absolute right-0 mt-3 w-56 bg-white dark:bg-surface-dark rounded-3xl shadow-2xl border border-gray-100 dark:border-gray-800 py-3 z-50 overflow-hidden">
                        <Link :href="route('profile.edit')" class="flex items-center gap-3 px-5 py-3 text-sm text-content-secondary dark:text-gray-400 hover:bg-primary-50 dark:hover:bg-primary-900/10 hover:text-primary-500 transition-colors">
                            <Users class="w-4 h-4" />
                            Mi Perfil
                        </Link>
                        <div class="border-t border-gray-100 dark:border-gray-800 my-2 mx-5"></div>
                        <button @click="logout" class="w-full flex items-center gap-3 px-5 py-3 text-sm text-danger hover:bg-red-50 dark:hover:bg-red-900/10 transition-colors text-left font-bold">
                            <LogOut class="w-4 h-4" />
                            Cerrar Sesión
                        </button>
                    </div>
                </Transition>
            </div>
        </div>
    </header>
</template>
