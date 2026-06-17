<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { formatDate, formatTime } from '@/helpers/format';
import { Head, Link } from '@inertiajs/vue3';
import {
    AlertCircle,
    Layers,
    Package,
    ShoppingCart,
    TrendingUp,
    Wallet,
} from 'lucide-vue-next';

const props = defineProps<{
    role: 'admin' | 'cashier';
    stats: any;
    recent_sales: any[];
    low_stock_products?: any[];
    categories?: any[];
}>();

const fmt = (v: number) =>
    '$' + (v / 100).toLocaleString('es-CO', { minimumFractionDigits: 0 });

const methodLabel: Record<string, string> = {
    cash: 'Efectivo',
    card: 'Tarjeta',
    transfer: 'Transferencia',
    mixed: 'Mixto',
};
</script>

<template>
    <Head title="Panel de Administración" />

    <AdminLayout>
        <template #title>
            <h1
                class="font-display text-xl font-bold text-content-primary dark:text-white"
            >
                {{ role === 'admin' ? 'Resumen General' : 'Mi Panel' }}
            </h1>
        </template>

        <!-- Admin Dashboard -->
        <template v-if="role === 'admin'">
            <div
                class="mb-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4"
            >
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
                            {{ stats.sales_count }} ventas
                        </span>
                    </div>
                    <p
                        class="text-sm font-semibold text-content-secondary dark:text-gray-400"
                    >
                        Ventas del Mes
                    </p>
                    <h3
                        class="mt-1 font-display text-2xl font-extrabold text-content-primary dark:text-white"
                    >
                        {{ fmt(stats.total_sales) }}
                    </h3>
                </div>
                <div
                    class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-surface-dark"
                >
                    <div class="mb-4 flex items-center justify-between">
                        <div
                            class="rounded-2xl bg-blue-50 p-3 text-blue-500 dark:bg-blue-900/30"
                        >
                            <Package class="h-6 w-6" />
                        </div>
                    </div>
                    <p
                        class="text-sm font-semibold text-content-secondary dark:text-gray-400"
                    >
                        Total Productos
                    </p>
                    <h3
                        class="mt-1 font-display text-2xl font-extrabold text-content-primary dark:text-white"
                    >
                        {{ stats.total_products }}
                    </h3>
                </div>
                <div
                    class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-surface-dark"
                >
                    <div class="mb-4 flex items-center justify-between">
                        <div
                            class="rounded-2xl bg-accent-50 p-3 text-accent-500 dark:bg-accent-900/30"
                        >
                            <Layers class="h-6 w-6" />
                        </div>
                    </div>
                    <p
                        class="text-sm font-semibold text-content-secondary dark:text-gray-400"
                    >
                        Categorías
                    </p>
                    <h3
                        class="mt-1 font-display text-2xl font-extrabold text-content-primary dark:text-white"
                    >
                        {{ stats.active_categories }}
                    </h3>
                </div>
                <div
                    class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-surface-dark"
                >
                    <div class="mb-4 flex items-center justify-between">
                        <div
                            class="rounded-2xl bg-red-50 p-3 text-danger dark:bg-red-900/30"
                        >
                            <AlertCircle class="h-6 w-6" />
                        </div>
                        <span
                            class="rounded-lg bg-danger/10 px-2 py-1 text-xs font-bold text-danger"
                            >{{ stats.low_stock_count }}</span
                        >
                    </div>
                    <p
                        class="text-sm font-semibold text-content-secondary dark:text-gray-400"
                    >
                        Stock Bajo
                    </p>
                    <h3
                        class="mt-1 font-display text-2xl font-extrabold text-content-primary dark:text-white"
                    >
                        {{ stats.low_stock_count }} productos
                    </h3>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
                <div
                    class="overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-surface-dark lg:col-span-2"
                >
                    <div
                        class="flex items-center justify-between border-b border-gray-100 px-6 py-5 dark:border-gray-800"
                    >
                        <h3
                            class="font-display text-lg font-bold text-content-primary dark:text-white"
                        >
                            Últimas Ventas
                        </h3>
                        <Link
                            :href="route('admin.ventas.index')"
                            class="text-sm font-bold text-primary-500 transition-colors hover:text-primary-600"
                            >Ver todas</Link
                        >
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse text-left">
                            <thead>
                                <tr
                                    class="bg-gray-50 text-xs uppercase tracking-wider text-content-muted dark:bg-gray-900/50 dark:text-gray-500"
                                >
                                    <th class="px-6 py-4 font-bold">#</th>
                                    <th class="px-6 py-4 font-bold">Cajero</th>
                                    <th class="px-6 py-4 font-bold">Método</th>
                                    <th class="px-6 py-4 text-right font-bold">
                                        Total
                                    </th>
                                    <th class="px-6 py-4 text-right font-bold">
                                        Hora
                                    </th>
                                </tr>
                            </thead>
                            <tbody
                                class="divide-y divide-gray-100 dark:divide-gray-800"
                            >
                                <tr v-if="!recent_sales.length">
                                    <td
                                        colspan="5"
                                        class="px-6 py-8 text-center text-sm text-content-muted"
                                    >
                                        Sin ventas recientes
                                    </td>
                                </tr>
                                <tr
                                    v-for="sale in recent_sales"
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
                                        {{ sale.cashier?.name || '—' }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span
                                            class="rounded-lg bg-gray-100 px-2.5 py-1 text-xs font-bold capitalize text-content-secondary dark:bg-gray-800"
                                        >
                                            {{
                                                methodLabel[
                                                    sale.payment_method
                                                ] || sale.payment_method
                                            }}
                                        </span>
                                    </td>
                                    <td
                                        class="px-6 py-4 text-right text-sm font-bold text-primary-500"
                                    >
                                        {{ fmt(sale.total) }}
                                    </td>
                                    <td
                                        class="px-6 py-4 text-right text-sm text-content-secondary"
                                    >
                                        {{ formatTime(sale.created_at) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="space-y-8">
                    <div
                        v-if="low_stock_products?.length"
                        class="overflow-hidden rounded-3xl border border-red-100 bg-white shadow-sm dark:border-red-900/30 dark:bg-surface-dark"
                    >
                        <div
                            class="flex items-center gap-2 border-b border-red-100 bg-red-50/50 px-6 py-5 dark:border-red-900/30 dark:bg-red-900/10"
                        >
                            <AlertCircle class="h-5 w-5 text-danger" />
                            <h3
                                class="font-display text-lg font-bold text-danger"
                            >
                                Alertas de Stock
                            </h3>
                        </div>
                        <div class="space-y-3 p-4">
                            <div
                                v-for="item in low_stock_products"
                                :key="item.id"
                                class="flex items-center justify-between rounded-2xl bg-gray-50 p-3 dark:bg-gray-900"
                            >
                                <div>
                                    <p
                                        class="text-sm font-bold text-content-primary dark:text-white"
                                    >
                                        {{ item.name }}
                                    </p>
                                    <p
                                        class="text-[10px] uppercase tracking-wider text-content-muted"
                                    >
                                        Stock mínimo: {{ item.min_stock }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <span
                                        class="text-sm font-black text-danger"
                                        >{{ item.stock }}</span
                                    >
                                    <p
                                        class="text-[10px] font-bold uppercase text-danger/70"
                                    >
                                        uds.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div
                            class="border-t border-gray-100 p-4 dark:border-gray-800"
                        >
                            <Link
                                :href="route('admin.codigos.index')"
                                class="flex w-full items-center justify-center rounded-xl py-2.5 text-xs font-bold text-danger transition-all hover:bg-red-50 dark:hover:bg-red-900/20"
                            >
                                Ir a Productos
                            </Link>
                        </div>
                    </div>

                    <div
                        v-if="categories?.length"
                        class="flex flex-col rounded-3xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-surface-dark"
                    >
                        <div
                            class="border-b border-gray-100 px-6 py-5 dark:border-gray-800"
                        >
                            <h3
                                class="font-display text-lg font-bold text-content-primary dark:text-white"
                            >
                                Categorías
                            </h3>
                        </div>
                        <div class="flex-1 space-y-2 overflow-y-auto p-4">
                            <div
                                v-for="cat in categories"
                                :key="cat.id"
                                class="flex items-center justify-between rounded-2xl p-3 transition-colors hover:bg-gray-50 dark:hover:bg-gray-800"
                            >
                                <p
                                    class="text-sm font-bold text-content-primary dark:text-white"
                                >
                                    {{ cat.name }}
                                </p>
                                <span
                                    class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-bold text-content-muted dark:bg-gray-800"
                                    >{{ cat.product_count }}</span
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <!-- Cashier Dashboard -->
        <template v-else>
            <div
                class="mb-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3"
            >
                <div
                    class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-surface-dark"
                >
                    <div class="mb-4 flex items-center justify-between">
                        <div
                            class="rounded-2xl bg-primary-50 p-3 text-primary-500 dark:bg-primary-900/30"
                        >
                            <ShoppingCart class="h-6 w-6" />
                        </div>
                        <span
                            class="rounded-lg bg-success/10 px-2 py-1 text-xs font-bold text-success"
                            >{{ stats.today_count }} ventas</span
                        >
                    </div>
                    <p
                        class="text-sm font-semibold text-content-secondary dark:text-gray-400"
                    >
                        Ventas de Hoy
                    </p>
                    <h3
                        class="mt-1 font-display text-2xl font-extrabold text-content-primary dark:text-white"
                    >
                        {{ fmt(stats.today_sales) }}
                    </h3>
                </div>
                <div
                    class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-surface-dark"
                >
                    <div class="mb-4 flex items-center justify-between">
                        <div
                            class="rounded-2xl p-3"
                            :class="
                                stats.has_open_session
                                    ? 'bg-success/10 text-success'
                                    : 'bg-gray-100 text-content-muted dark:bg-gray-800'
                            "
                        >
                            <Wallet class="h-6 w-6" />
                        </div>
                        <span
                            class="rounded-lg px-2 py-1 text-xs font-bold"
                            :class="
                                stats.has_open_session
                                    ? 'bg-success/10 text-success'
                                    : 'bg-warning/10 text-warning'
                            "
                        >
                            {{
                                stats.has_open_session
                                    ? 'Abierta'
                                    : 'Sin sesión'
                            }}
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
                            stats.has_open_session
                                ? 'Abierta ' +
                                  formatDate(stats.session_opened_at)
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
                            <tr v-if="!recent_sales.length">
                                <td
                                    colspan="5"
                                    class="px-6 py-12 text-center text-sm text-content-muted"
                                >
                                    No has realizado ventas aún.
                                </td>
                            </tr>
                            <tr
                                v-for="sale in recent_sales"
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
                                    {{ formatDate(sale.date) }}
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
                                    {{ sale.items?.length || 0 }}
                                </td>
                                <td
                                    class="px-6 py-4 text-right text-sm font-bold text-primary-500"
                                >
                                    {{ fmt(sale.total) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </template>
    </AdminLayout>
</template>
