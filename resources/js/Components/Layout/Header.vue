<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import {
    Bell,
    ChevronDown,
    LogOut,
    Menu,
    Moon,
    Search,
    Sun,
    Users,
} from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref } from 'vue';

defineProps<{ isDark: boolean }>();
const emit = defineEmits(['toggleSidebar', 'toggleDark']);

const user = computed(() => (usePage().props.auth as any)?.user ?? null);

const initials = computed(() => {
    if (!user.value?.name) return 'AD';
    const parts = (user.value.name as string).split(' ');
    if (parts.length >= 2) return (parts[0][0] + parts[1][0]).toUpperCase();
    return parts[0].substring(0, 2).toUpperCase();
});

const profileDropdownOpen = ref(false);
const profileRef = ref<HTMLElement | null>(null);

function handleClickOutside(e: MouseEvent) {
    if (profileRef.value && !profileRef.value.contains(e.target as Node)) {
        profileDropdownOpen.value = false;
    }
}

onMounted(() => document.addEventListener('mousedown', handleClickOutside));
onUnmounted(() =>
    document.removeEventListener('mousedown', handleClickOutside),
);

function logout() {
    localStorage.removeItem('pos_session_opened');
    router.post(route('logout'));
}
</script>

<template>
    <header
        class="sticky top-0 z-40 flex h-16 items-center justify-between border-b border-gray-100 bg-white px-6 dark:border-gray-800 dark:bg-surface-dark"
    >
        <div class="flex items-center gap-4">
            <button
                @click="emit('toggleSidebar')"
                class="rounded-xl p-2 text-content-secondary transition-colors hover:bg-gray-50 dark:hover:bg-gray-800"
            >
                <Menu class="h-5 w-5" />
            </button>
            <div
                class="hidden items-center gap-2 rounded-2xl border border-gray-100 bg-gray-50 px-4 py-2 dark:border-gray-800 dark:bg-gray-900 md:flex"
            >
                <Search class="h-4 w-4 text-content-muted" />
                <input
                    type="text"
                    placeholder="Buscar..."
                    class="w-64 border-none bg-transparent text-sm text-content-primary focus:ring-0 dark:text-white"
                />
            </div>
        </div>

        <div class="flex items-center gap-4">
            <button
                @click="emit('toggleDark')"
                class="rounded-xl p-2 text-content-secondary transition-colors hover:bg-gray-50 dark:hover:bg-gray-800"
            >
                <Sun v-if="isDark" class="h-5 w-5 text-yellow-500" />
                <Moon v-else class="h-5 w-5" />
            </button>

            <!-- Notifications -->
            <div class="relative">
                <button
                    class="rounded-xl p-2 text-content-secondary transition-colors hover:bg-gray-50 dark:hover:bg-gray-800"
                >
                    <Bell class="h-5 w-5" />
                    <span
                        class="absolute right-2 top-2 h-2 w-2 animate-pulse rounded-full border-2 border-white bg-primary-500 dark:border-surface-dark"
                    ></span>
                </button>
            </div>

            <!-- Profile Dropdown (AD) -->
            <div class="relative" ref="profileRef">
                <button
                    type="button"
                    @click="profileDropdownOpen = !profileDropdownOpen"
                    class="flex items-center gap-3 rounded-2xl border border-transparent p-1.5 transition-all hover:border-gray-200 hover:bg-gray-100 dark:hover:border-gray-700 dark:hover:bg-gray-800"
                >
                    <div
                        class="flex h-8 w-8 items-center justify-center rounded-xl bg-gradient-to-br from-primary-400 to-secondary-400 text-xs font-bold text-white shadow-sm"
                    >
                        {{ initials }}
                    </div>
                    <div class="hidden text-left sm:block">
                        <p
                            class="text-xs font-black leading-none text-content-primary dark:text-white"
                        >
                            {{ user?.name || 'Admin' }}
                        </p>
                        <p class="mt-1 text-[10px] text-content-muted">
                            {{ user?.email || 'Usuario' }}
                        </p>
                    </div>
                    <ChevronDown
                        class="h-4 w-4 text-content-muted transition-transform"
                        :class="{ 'rotate-180': profileDropdownOpen }"
                    />
                </button>

                <Transition
                    enter-active-class="transition duration-200 ease-out"
                    enter-from-class="transform scale-95 opacity-0 -translate-y-2"
                    enter-to-class="transform scale-100 opacity-100 translate-y-0"
                    leave-active-class="transition duration-150 ease-in"
                    leave-from-class="transform scale-100 opacity-100 translate-y-0"
                    leave-to-class="transform scale-95 opacity-0 -translate-y-2"
                >
                    <div
                        v-if="profileDropdownOpen"
                        class="absolute right-0 z-50 mt-3 w-56 overflow-hidden rounded-3xl border border-gray-100 bg-white py-3 shadow-2xl dark:border-gray-800 dark:bg-surface-dark"
                    >
                        <Link
                            :href="route('profile.edit')"
                            class="flex items-center gap-3 px-5 py-3 text-sm text-content-secondary transition-colors hover:bg-primary-50 hover:text-primary-500 dark:text-gray-400 dark:hover:bg-primary-900/10"
                        >
                            <Users class="h-4 w-4" />
                            Mi Perfil
                        </Link>
                        <div
                            class="mx-5 my-2 border-t border-gray-100 dark:border-gray-800"
                        ></div>
                        <button
                            @click="logout"
                            class="flex w-full items-center gap-3 px-5 py-3 text-left text-sm font-bold text-danger transition-colors hover:bg-red-50 dark:hover:bg-red-900/10"
                        >
                            <LogOut class="h-4 w-4" />
                            Cerrar Sesión
                        </button>
                    </div>
                </Transition>
            </div>
        </div>
    </header>
</template>
