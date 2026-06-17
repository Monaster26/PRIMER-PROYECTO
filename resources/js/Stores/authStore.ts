import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useAuthStore = defineStore('auth', () => {
    const user = ref<any>(null); // Replace 'any' with a User interface
    const isAuthenticated = ref(false);

    function setUser(newUser: any) {
        user.value = newUser;
        isAuthenticated.value = !!newUser;
    }

    function logout() {
        user.value = null;
        isAuthenticated.value = false;
    }

    return {
        user,
        isAuthenticated,
        setUser,
        logout,
    };
});
