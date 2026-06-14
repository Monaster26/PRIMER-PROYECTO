<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Users } from 'lucide-vue-next';
import { formatDate, formatTime } from '@/helpers/format';

defineProps<{
    clients: Array<{
        id: number;
        name: string;
        rut: string;
        phone: string;
        address: string;
        total_purchases: number;
        last_purchase: string;
    }>;
}>();

const fmt = (v: number) => '$' + v.toLocaleString('es-CO', { minimumFractionDigits: 0 });
</script>

<template>
    <Head title="Clientes" />
    <AdminLayout>
        <template #title>
            <h1 class="text-xl font-display font-bold text-content-primary dark:text-white">Clientes</h1>
        </template>

        <div class="bg-white dark:bg-surface-dark rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 flex flex-wrap items-center gap-3">
                <Users class="w-5 h-5 text-primary-500" />
                <h2 class="font-bold text-content-primary dark:text-white flex-1">Clientes Registrados</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 dark:bg-gray-900/50 text-xs uppercase tracking-wider text-content-muted dark:text-gray-500">
                        <tr>
                            <th class="px-6 py-3 font-bold">Nombre</th>
                            <th class="px-6 py-3 font-bold">RUT</th>
                            <th class="px-6 py-3 font-bold">Teléfono</th>
                            <th class="px-6 py-3 font-bold">Dirección</th>
                            <th class="px-6 py-3 font-bold text-right">Total Compras</th>
                            <th class="px-6 py-3 font-bold">Última Compra (Fecha)</th>
                            <th class="px-6 py-3 font-bold">Hora</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <tr v-if="!clients?.length">
                            <td colspan="7" class="px-6 py-12 text-center text-sm text-content-muted dark:text-gray-500">No hay clientes registrados.</td>
                        </tr>
                        <tr v-for="c in clients" :key="c.id"
                            class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors group">
                            <td class="px-6 py-4 text-sm font-bold text-content-primary dark:text-white">{{ c.name }}</td>
                            <td class="px-6 py-4 text-sm text-content-muted font-mono">{{ c.rut }}</td>
                            <td class="px-6 py-4 text-sm text-content-secondary">{{ c.phone }}</td>
                            <td class="px-6 py-4 text-sm text-content-secondary max-w-[200px] truncate">{{ c.address }}</td>
                            <td class="px-6 py-4 text-sm text-right font-medium text-content-primary">{{ fmt(c.total_purchases) }}</td>
                            <td class="px-6 py-4 text-sm text-content-muted">{{ formatDate(c.last_purchase) }}</td>
                            <td class="px-6 py-4 text-sm text-content-muted">{{ formatTime(c.last_purchase) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>
