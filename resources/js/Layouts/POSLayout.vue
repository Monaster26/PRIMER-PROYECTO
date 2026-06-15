<script setup lang="ts">
// POS layout has no navbar or footer, it's a full screen kiosk mode app
</script>

<template>
    <div class="h-screen w-screen overflow-hidden bg-bg-light dark:bg-bg-dark text-content-primary dark:text-content-inverted transition-colors duration-300 flex">
        <slot />
    </div>
</template>
