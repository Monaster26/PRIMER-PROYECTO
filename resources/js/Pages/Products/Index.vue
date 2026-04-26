<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ProductTable from '@/Components/Product/ProductTable.vue';

defineProps<{
    products: any;
    categories: any[];
    filters: any;
}>();
</script>

<template>
    <Head title="Gestión de Productos" />

    <AdminLayout>
        <template #title>
            <h1 class="text-xl font-display font-bold text-content-primary dark:text-white">Gestión de Inventario</h1>
        </template>

        <div class="space-y-6">
            <ProductTable 
                :products="products" 
                :categories="categories" 
                :filters="filters" 
            />
        </div>
    </AdminLayout>
</template>
