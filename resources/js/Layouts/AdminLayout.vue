<script setup lang="ts">
import AdminSidebar from '@/Components/Layout/AdminSidebar.vue';
import Header from '@/Components/Layout/Header.vue';
import { usePage } from '@inertiajs/vue3';
import { CheckCircle, X, XCircle } from 'lucide-vue-next';
import { onMounted, onUnmounted, ref, watch } from 'vue';

const sidebarOpen = ref(false);
const isDark = ref(false);
const showToast = ref(false);
const toastType = ref<'success' | 'error'>('success');
const toastMessage = ref('');

function toggleSidebar() {
    sidebarOpen.value = !sidebarOpen.value;
}
function toggleDark() {
    isDark.value = !isDark.value;
    document.documentElement.classList.toggle('dark', isDark.value);
}

function handleResize() {
    // Keep sidebar open by default only on desktop
    if (window.innerWidth >= 1024) {
        sidebarOpen.value = true;
    } else {
        sidebarOpen.value = false;
    }
}

// Close sidebar on navigation if we are in mobile/tablet viewport
const page = usePage();
watch(
    () => page.url,
    () => {
        if (window.innerWidth < 1024) {
            sidebarOpen.value = false;
        }
    },
);

onMounted(() => {
    handleResize();
    window.addEventListener('resize', handleResize);
});

onUnmounted(() => {
    window.removeEventListener('resize', handleResize);
});

watch(
    () => usePage().props.flash as any,
    (flash: any) => {
        if (flash?.success) {
            toastMessage.value = flash.success;
            toastType.value = 'success';
            showToast.value = true;
            setTimeout(() => {
                showToast.value = false;
            }, 4000);
        } else if (flash?.error) {
            toastMessage.value = flash.error;
            toastType.value = 'error';
            showToast.value = true;
            setTimeout(() => {
                showToast.value = false;
            }, 5000);
        }
    },
    { deep: true, immediate: true },
);
</script>

<template>
    <div
        class="relative flex h-screen overflow-hidden bg-gray-50 font-sans dark:bg-bg-dark"
    >
        <!-- Mobile Backdrop Overlay -->
        <Transition
            enter-active-class="transition-opacity duration-300 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-200 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="sidebarOpen"
                @click="sidebarOpen = false"
                class="fixed inset-0 z-40 bg-black/40 backdrop-blur-sm lg:hidden"
            />
        </Transition>

        <AdminSidebar
            :isOpen="sidebarOpen"
            @close="sidebarOpen = false"
            class="flex-shrink-0"
        />

        <div class="relative z-10 flex min-w-0 flex-1 flex-col overflow-hidden">
            <Header
                :isDark="isDark"
                @toggleSidebar="toggleSidebar"
                @toggleDark="toggleDark"
            />

            <main class="relative flex-1 overflow-y-auto p-4 md:p-6">
                <Transition
                    enter-active-class="transition duration-300 ease-out"
                    enter-from-class="translate-y-[-100%] opacity-0"
                    enter-to-class="translate-y-0 opacity-100"
                    leave-active-class="transition duration-200 ease-in"
                    leave-from-class="translate-y-0 opacity-100"
                    leave-to-class="translate-y-[-100%] opacity-0"
                >
                    <div
                        v-if="showToast"
                        :class="
                            toastType === 'success'
                                ? 'bg-success text-white'
                                : 'bg-danger text-white'
                        "
                        class="absolute left-1/2 top-0 z-50 flex -translate-x-1/2 items-center gap-3 rounded-2xl px-5 py-3 text-sm font-bold shadow-lg"
                    >
                        <CheckCircle
                            v-if="toastType === 'success'"
                            class="h-5 w-5 flex-shrink-0"
                        />
                        <XCircle v-else class="h-5 w-5 flex-shrink-0" />
                        <span>{{ toastMessage }}</span>
                        <button
                            @click="showToast = false"
                            class="ml-2 rounded-full p-0.5 transition-colors hover:bg-white/20"
                        >
                            <X class="h-4 w-4" />
                        </button>
                    </div>
                </Transition>
                <slot />
            </main>
        </div>
    </div>
</template>
