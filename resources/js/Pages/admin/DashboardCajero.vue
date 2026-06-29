<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { formatCLP, formatDate } from '@/helpers/format';
import { Head, Link } from '@inertiajs/vue3';
import { ShoppingCart, TrendingUp, Wallet } from 'lucide-vue-next';

defineProps<{
    todaySales: number;
    todaySalesCount: number;
    hasOpenSession: boolean;
    sessionOpenedAt: string | null;
    recentSales: {
        id: number;
        payment_method: string;
        total: number;
        items_count: number;
        created_at: string;
    }[];
}>();

const methodLabel: Record<string, string> = {
    cash: 'Efectivo',
    card: 'Tarjeta',
    transfer: 'Transferencia',
    mixed: 'Mixto',
};
</script>

<template>
    <Head title="Mi Panel" />

    <AdminLayout>
        <template #title>
            <h1
                class="font-display text-xl font-bold text-content-primary dark:text-white"
            >
                Mi Panel
            </h1>
        </template>

        <div class="mb-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <div
                class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-surface-dark"
            >
                <div class="mb-4 flex items-center justify-between">
                    <div
                        class="rounded-2xl bg-primary-50 p-3 text-primary-500 dark:bg-primary-900/30"
                    >
                        <TrendingUp class="h-6 w-6" />
                    </div>
                    <span
                        class="rounded-lg bg-success/10 px-2 py-1 text-xs font-bold text-success"
                    >
                        {{ todaySalesCount }} ventas
                    </span>
                </div>
                <p
                    class="text-sm font-semibold text-content-secondary dark:text-gray-400"
                >
                    Ventas de Hoy
                </p>
                <h3
                    class="mt-1 font-display text-2xl font-extrabold text-content-primary dark:text-white"
                >
                    {{ formatCLP(todaySales) }}
                </h3>
            </div>

            <div
                class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-surface-dark"
            >
                <div class="mb-4 flex items-center justify-between">
                    <div
                        class="rounded-2xl p-3"
                        :class="
                            hasOpenSession
                                ? 'bg-success/10 text-success'
                                : 'bg-gray-100 text-content-muted dark:bg-gray-800'
                        "
                    >
                        <Wallet class="h-6 w-6" />
                    </div>
                    <span
                        class="rounded-lg px-2 py-1 text-xs font-bold"
                        :class="
                            hasOpenSession
                                ? 'bg-success/10 text-success'
                                : 'bg-warning/10 text-warning'
                        "
                    >
                        {{ hasOpenSession ? 'Abierta' : 'Sin sesión' }}
                    </span>
                </div>
                <p
                    class="text-sm font-semibold text-content-secondary dark:text-gray-400"
                >
                    Sesión de Caja
                </p>
                <p
                    class="mt-1 text-sm font-medium text-content-primary dark:text-white"
                >
                    {{
                        hasOpenSession
                            ? 'Abierta ' + formatDate(sessionOpenedAt ?? '')
                            : 'No has abierto sesión hoy'
                    }}
                </p>
            </div>

            <div
                class="flex flex-col items-center justify-center rounded-3xl border border-gray-100 bg-white p-6 text-center shadow-sm dark:border-gray-800 dark:bg-surface-dark"
            >
                <p
                    class="mb-3 text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                >
                    Acceso Rápido
                </p>
                <div class="flex gap-3">
                    <Link
                        :href="route('admin.pos')"
                        class="flex items-center gap-2 rounded-2xl bg-primary-500 px-5 py-3 text-sm font-bold text-white shadow-sm transition-colors hover:bg-primary-600"
                    >
                        <ShoppingCart class="h-5 w-5" /> POS
                    </Link>
                    <Link
                        :href="route('admin.arqueo-caja.index')"
                        class="flex items-center gap-2 rounded-2xl border border-gray-200 px-5 py-3 text-sm font-bold text-content-secondary transition-colors hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800"
                    >
                        <Wallet class="h-5 w-5" /> Arqueo
                    </Link>
                </div>
            </div>
        </div>

        <div
            class="overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-surface-dark"
        >
            <div
                class="border-b border-gray-100 px-6 py-5 dark:border-gray-800"
            >
                <h3
                    class="font-display text-lg font-bold text-content-primary dark:text-white"
                >
                    Mis Últimas Ventas
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left">
                    <thead>
                        <tr
                            class="bg-gray-50 text-xs uppercase tracking-wider text-content-muted dark:bg-gray-900/50 dark:text-gray-500"
                        >
                            <th class="px-6 py-4 font-bold">#</th>
                            <th class="px-6 py-4 font-bold">Fecha</th>
                            <th class="px-6 py-4 font-bold">Método</th>
                            <th class="px-6 py-4 text-right font-bold">
                                Items
                            </th>
                            <th class="px-6 py-4 text-right font-bold">
                                Total
                            </th>
                        </tr>
                    </thead>
                    <tbody
                        class="divide-y divide-gray-100 dark:divide-gray-800"
                    >
                        <tr v-if="!recentSales.length">
                            <td
                                colspan="5"
                                class="px-6 py-12 text-center text-sm text-content-muted"
                            >
                                No has realizado ventas aún.
                            </td>
                        </tr>
                        <tr
                            v-for="sale in recentSales"
                            :key="sale.id"
                            class="transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/50"
                        >
                            <td
                                class="px-6 py-4 font-mono text-sm font-bold text-content-primary dark:text-white"
                            >
                                #{{ sale.id }}
                            </td>
                            <td
                                class="px-6 py-4 text-sm text-content-primary dark:text-white"
                            >
                                {{ formatDate(sale.created_at) }}
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    class="rounded-lg bg-gray-100 px-2.5 py-1 text-xs font-bold capitalize dark:bg-gray-800"
                                >
                                    {{
                                        methodLabel[sale.payment_method] ||
                                        sale.payment_method
                                    }}
                                </span>
                            </td>
                            <td
                                class="px-6 py-4 text-right text-sm text-content-secondary"
                            >
                                {{ sale.items_count || 0 }}
                            </td>
                            <td
                                class="px-6 py-4 text-right text-sm font-bold text-primary-500"
                            >
                                {{ formatCLP(sale.total) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>
