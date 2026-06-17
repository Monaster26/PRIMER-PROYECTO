<script setup lang="ts">
import CartDrawer from '../Components/Layout/CartDrawer.vue';
import Navbar from '../Components/Layout/Navbar.vue';
// import Footer from '../Components/Layout/Footer.vue';

// For the UI store (dark mode, cart drawer, etc) - I will create a basic one inside Composables or Stores.
</script>

<template>
    <div
        class="flex min-h-screen flex-col bg-bg-light text-content-primary transition-colors duration-300 dark:bg-bg-dark dark:text-content-inverted"
    >
        <Navbar />

        <CartDrawer />

        <main class="flex-grow">
            <slot />
        </main>

        <!-- <Footer /> -->
    </div>
</template>
