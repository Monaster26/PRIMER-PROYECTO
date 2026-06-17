<script setup lang="ts">
import { formatDate, formatTime } from '@/helpers/format';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Users } from 'lucide-vue-next';

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

const fmt = (v: number) =>
    '$' + v.toLocaleString('es-CO', { minimumFractionDigits: 0 });
</script>

<template>
    <Head title="Clientes" />
    <AdminLayout>
        <template #title>
            <h1
                class="font-display text-xl font-bold text-content-primary dark:text-white"
            >
                Clientes
            </h1>
        </template>

        <div
            class="overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-surface-dark"
        >
            <div
                class="flex flex-wrap items-center gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-800"
            >
                <Users class="h-5 w-5 text-primary-500" />
                <h2
                    class="flex-1 font-bold text-content-primary dark:text-white"
                >
                    Clientes Registrados
                </h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead
                        class="bg-gray-50 text-xs uppercase tracking-wider text-content-muted dark:bg-gray-900/50 dark:text-gray-500"
                    >
                        <tr>
                            <th class="px-6 py-3 font-bold">Nombre</th>
                            <th class="px-6 py-3 font-bold">RUT</th>
                            <th class="px-6 py-3 font-bold">Teléfono</th>
                            <th class="px-6 py-3 font-bold">Dirección</th>
                            <th class="px-6 py-3 text-right font-bold">
                                Total Compras
                            </th>
                            <th class="px-6 py-3 font-bold">
                                Última Compra (Fecha)
                            </th>
                            <th class="px-6 py-3 font-bold">Hora</th>
                        </tr>
                    </thead>
                    <tbody
                        class="divide-y divide-gray-100 dark:divide-gray-800"
                    >
                        <tr v-if="!clients?.length">
                            <td
                                colspan="7"
                                class="px-6 py-12 text-center text-sm text-content-muted dark:text-gray-500"
                            >
                                No hay clientes registrados.
                            </td>
                        </tr>
                        <tr
                            v-for="c in clients"
                            :key="c.id"
                            class="group transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/50"
                        >
                            <td
                                class="px-6 py-4 text-sm font-bold text-content-primary dark:text-white"
                            >
                                {{ c.name }}
                            </td>
                            <td
                                class="px-6 py-4 font-mono text-sm text-content-muted"
                            >
                                {{ c.rut }}
                            </td>
                            <td
                                class="px-6 py-4 text-sm text-content-secondary"
                            >
                                {{ c.phone }}
                            </td>
                            <td
                                class="max-w-[200px] truncate px-6 py-4 text-sm text-content-secondary"
                            >
                                {{ c.address }}
                            </td>
                            <td
                                class="px-6 py-4 text-right text-sm font-medium text-content-primary"
                            >
                                {{ fmt(c.total_purchases) }}
                            </td>
                            <td class="px-6 py-4 text-sm text-content-muted">
                                {{ formatDate(c.last_purchase) }}
                            </td>
                            <td class="px-6 py-4 text-sm text-content-muted">
                                {{ formatTime(c.last_purchase) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>
