<script setup lang="ts">
import { ref } from 'vue';
import Sidebar from '@/Components/Layout/Sidebar.vue';
import Header from '@/Components/Layout/Header.vue';

const sidebarOpen = ref(true);
const isDark = ref(false);

function toggleSidebar() {
    sidebarOpen.value = !sidebarOpen.value;
}

function toggleDark() {
    isDark.value = !isDark.value;
    document.documentElement.classList.toggle('dark', isDark.value);
}
</script>

<template>
    <div class="flex h-screen bg-gray-50 dark:bg-bg-dark overflow-hidden font-sans relative">
        <!-- Sidebar -->
        <Sidebar :isOpen="sidebarOpen" class="flex-shrink-0" />

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden relative z-10">
            <!-- Header -->
            <Header 
                :isDark="isDark" 
                @toggleSidebar="toggleSidebar" 
                @toggleDark="toggleDark" 
            />

            <!-- Scrollable Page Content -->
            <main class="flex-1 overflow-y-auto p-6">
                <slot />
            </main>
        </div>
    </div>
</template>
