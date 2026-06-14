<script setup lang="ts">
import { ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AdminSidebar from '@/Components/Layout/AdminSidebar.vue';
import Header from '@/Components/Layout/Header.vue';
import { CheckCircle, XCircle, X } from 'lucide-vue-next';

const sidebarOpen = ref(true);
const isDark = ref(false);
const showToast = ref(false);
const toastType = ref<'success' | 'error'>('success');
const toastMessage = ref('');

function toggleSidebar() { sidebarOpen.value = !sidebarOpen.value; }
function toggleDark() { isDark.value = !isDark.value; document.documentElement.classList.toggle('dark', isDark.value); }

watch(() => usePage().props.flash as any, (flash: any) => {
    if (flash?.success) {
        toastMessage.value = flash.success;
        toastType.value = 'success';
        showToast.value = true;
        setTimeout(() => { showToast.value = false; }, 4000);
    } else if (flash?.error) {
        toastMessage.value = flash.error;
        toastType.value = 'error';
        showToast.value = true;
        setTimeout(() => { showToast.value = false; }, 5000);
    }
}, { deep: true, immediate: true });
</script>

<template>
    <div class="flex h-screen bg-gray-50 dark:bg-bg-dark overflow-hidden font-sans relative">
        <AdminSidebar :isOpen="sidebarOpen" class="flex-shrink-0" />

        <div class="flex-1 flex flex-col min-w-0 overflow-hidden relative z-10">
            <Header :isDark="isDark" @toggleSidebar="toggleSidebar" @toggleDark="toggleDark" />

            <main class="flex-1 overflow-y-auto p-6 relative">
                <Transition enter-active-class="transition duration-300 ease-out" enter-from-class="translate-y-[-100%] opacity-0" enter-to-class="translate-y-0 opacity-100"
                            leave-active-class="transition duration-200 ease-in" leave-from-class="translate-y-0 opacity-100" leave-to-class="translate-y-[-100%] opacity-0">
                    <div v-if="showToast"
                        :class="toastType === 'success' ? 'bg-success text-white' : 'bg-danger text-white'"
                        class="absolute top-0 left-1/2 -translate-x-1/2 z-50 flex items-center gap-3 px-5 py-3 rounded-2xl shadow-lg text-sm font-bold">
                        <CheckCircle v-if="toastType === 'success'" class="w-5 h-5 flex-shrink-0" />
                        <XCircle v-else class="w-5 h-5 flex-shrink-0" />
                        <span>{{ toastMessage }}</span>
                        <button @click="showToast = false" class="p-0.5 rounded-full hover:bg-white/20 transition-colors ml-2">
                            <X class="w-4 h-4" />
                        </button>
                    </div>
                </Transition>
                <slot />
            </main>
        </div>
    </div>
</template>
