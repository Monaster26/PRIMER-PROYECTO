<script setup lang="ts">
// POS layout has no navbar or footer, it's a full screen kiosk mode app
</script>

<template>
    <div
        class="flex h-screen w-screen overflow-hidden bg-bg-light text-content-primary transition-colors duration-300 dark:bg-bg-dark dark:text-content-inverted"
    >
        <slot />
    </div>
</template>
