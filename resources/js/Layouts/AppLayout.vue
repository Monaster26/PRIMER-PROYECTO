<script setup lang="ts">
import Navbar from '../Components/Layout/Navbar.vue';
import CartDrawer from '../Components/Layout/CartDrawer.vue';
// import Footer from '../Components/Layout/Footer.vue';

// For the UI store (dark mode, cart drawer, etc) - I will create a basic one inside Composables or Stores.
</script>

<template>
    <div class="min-h-screen flex flex-col bg-bg-light dark:bg-bg-dark text-content-primary dark:text-content-inverted transition-colors duration-300">
        <Navbar />
        
        <CartDrawer />
        
        <main class="flex-grow">
            <slot />
        </main>
        
        <!-- <Footer /> -->
    </div>
</template>
