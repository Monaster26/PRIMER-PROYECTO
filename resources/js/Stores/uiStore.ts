import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useUiStore = defineStore('ui', () => {
    const isDark = ref(false);
    const isCartOpen = ref(false);

    function toggleDarkMode() {
        isDark.value = !isDark.value;
        if (isDark.value) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    }

    function toggleCart() {
        isCartOpen.value = !isCartOpen.value;
    }

    return {
        isDark,
        isCartOpen,
        toggleDarkMode,
        toggleCart,
    };
});
