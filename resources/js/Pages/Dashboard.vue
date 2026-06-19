<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useCategoryStore } from '@/Stores/categoryStore';
import { Head } from '@inertiajs/vue3';
import {
    AlertCircle,
    ArrowDownRight,
    ArrowUpRight,
    ChevronDown,
    Layers,
    Package,
    TrendingUp,
} from 'lucide-vue-next';

const categoryStore = useCategoryStore();

// ─── Mock Stats Data ──────────────────────────────────────────────
const stats = [
    {
        label: 'Ventas del Mes',
        value: '$12,450.00',
        trend: '+12.5%',
        isUp: true,
        icon: TrendingUp,
        color: 'text-primary-500',
        bg: 'bg-primary-50',
    },
    {
        label: 'Total Productos',
        value: '1,248',
        trend: '+3',
        isUp: true,
        icon: Package,
        color: 'text-blue-500',
        bg: 'bg-blue-50',
    },
    {
        label: 'Categorías Activas',
        value: categoryStore.categories.length.toString(),
        trend: '0',
        isUp: true,
        icon: Layers,
        color: 'text-accent-500',
        bg: 'bg-accent-50',
    },
    {
        label: 'Stock Bajo',
        value: '12',
        trend: '-2',
        isUp: false,
        icon: AlertCircle,
        color: 'text-danger',
        bg: 'bg-red-50',
    },
];

const recentOrders = [
    {
        id: '#ORD-7842',
        customer: 'Juan Perez',
        items: 3,
        total: '$45.20',
        status: 'Entregado',
        time: 'Hace 5 min',
    },
    {
        id: '#ORD-7841',
        customer: 'Maria Lopez',
        items: 1,
        total: '$12.00',
        status: 'Pendiente',
        time: 'Hace 12 min',
    },
    {
        id: '#ORD-7840',
        customer: 'Carlos Ruiz',
        items: 5,
        total: '$89.50',
        status: 'Procesando',
        time: 'Hace 24 min',
    },
    {
        id: '#ORD-7839',
        customer: 'Ana Belén',
        items: 2,
        total: '$24.00',
        status: 'Entregado',
        time: 'Hace 1 hora',
    },
];

const lowStockProducts = [
    { name: 'Aceite de Oliva 1L', stock: 2, category: 'Abarrotes' },
    { name: 'Leche Entera 1L', stock: 4, category: 'Lácteos' },
    { name: 'Arroz Grado 1 1kg', stock: 3, category: 'Abarrotes' },
];

function getStatusClass(status: string) {
    switch (status) {
        case 'Entregado':
            return 'bg-success-light text-success-dark dark:bg-success-dark/20 dark:text-success-light';
        case 'Pendiente':
            return 'bg-warning-light text-warning-dark dark:bg-warning-dark/20 dark:text-warning-light';
        case 'Procesando':
            return 'bg-info-light text-info-dark dark:bg-info-dark/20 dark:text-info-light';
        default:
            return 'bg-gray-100 text-gray-700';
    }
}
</script>

<template>
    <Head title="Panel de Administración" />

    <AdminLayout>
        <template #title>
            <h1
                class="font-display text-xl font-bold text-content-primary dark:text-white"
            >
                Resumen General
            </h1>
        </template>

        <!-- ══════════════════════════════════════
             STATS GRID
        ══════════════════════════════════════ -->
        <div class="mb-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <div
                v-for="stat in stats"
                :key="stat.label"
                class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm transition-shadow hover:shadow-md dark:border-gray-800 dark:bg-surface-dark"
            >
                <div class="mb-4 flex items-center justify-between">
                    <div :class="[stat.bg, stat.color]" class="rounded-2xl p-3">
                        <component :is="stat.icon" class="h-6 w-6" />
                    </div>
                    <div
                        :class="stat.isUp ? 'text-success' : 'text-danger'"
                        class="flex items-center rounded-lg bg-gray-50 px-2 py-1 text-xs font-bold dark:bg-gray-800"
                    >
                        <component
                            :is="stat.isUp ? ArrowUpRight : ArrowDownRight"
                            class="mr-1 h-3 w-3"
                        />
                        {{ stat.trend }}
                    </div>
                </div>
                <div>
                    <p
                        class="text-sm font-semibold text-content-secondary dark:text-gray-400"
                    >
                        {{ stat.label }}
                    </p>
                    <h3
                        class="mt-1 font-display text-2xl font-extrabold text-content-primary dark:text-white"
                    >
                        {{ stat.value }}
                    </h3>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
            <!-- ══════════════════════════════════════
                 RECENT ORDERS TABLE
            ══════════════════════════════════════ -->
            <div
                class="overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-surface-dark lg:col-span-2"
            >
                <div
                    class="flex items-center justify-between border-b border-gray-100 px-6 py-5 dark:border-gray-800"
                >
                    <h3
                        class="font-display text-lg font-bold text-content-primary dark:text-white"
                    >
                        Últimos Pedidos
                    </h3>
                    <Link
                        href="/orders"
                        class="text-sm font-bold text-primary-500 transition-colors hover:text-primary-600"
                        >Ver todos</Link
                    >
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-left">
                        <thead>
                            <tr
                                class="bg-gray-50 text-xs uppercase tracking-wider text-content-muted dark:bg-gray-900/50 dark:text-gray-500"
                            >
                                <th class="px-6 py-4 font-bold">Pedido</th>
                                <th class="px-6 py-4 font-bold">Cliente</th>
                                <th class="px-6 py-4 text-center font-bold">
                                    Items
                                </th>
                                <th class="px-6 py-4 font-bold">Total</th>
                                <th class="px-6 py-4 font-bold">Estado</th>
                                <th class="px-6 py-4 font-bold"></th>
                            </tr>
                        </thead>
                        <tbody
                            class="divide-y divide-gray-100 dark:divide-gray-800"
                        >
                            <tr
                                v-for="order in recentOrders"
                                :key="order.id"
                                class="group transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/50"
                            >
                                <td
                                    class="px-6 py-4 text-sm font-bold text-content-primary dark:text-white"
                                >
                                    {{ order.id }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <div
                                            class="flex h-7 w-7 items-center justify-center rounded-full bg-gray-200 text-[10px] font-bold text-content-secondary dark:bg-gray-700 dark:text-gray-400"
                                        >
                                            {{
                                                order.customer
                                                    .split(' ')
                                                    .map((n) => n[0])
                                                    .join('')
                                            }}
                                        </div>
                                        <span
                                            class="text-sm font-medium text-content-primary dark:text-white"
                                            >{{ order.customer }}</span
                                        >
                                    </div>
                                </td>
                                <td
                                    class="px-6 py-4 text-center text-sm text-content-secondary dark:text-gray-400"
                                >
                                    {{ order.items }}
                                </td>
                                <td
                                    class="px-6 py-4 text-sm font-bold text-primary-500"
                                >
                                    {{ order.total }}
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        :class="getStatusClass(order.status)"
                                        class="rounded-full px-3 py-1 text-[10px] font-bold uppercase tracking-tight"
                                    >
                                        {{ order.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button
                                        class="rounded-lg p-2 text-content-muted opacity-0 transition-all hover:bg-primary-50 hover:text-primary-500 group-hover:opacity-100 dark:hover:bg-gray-800"
                                    >
                                        <ArrowUpRight class="h-4 w-4" />
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ══════════════════════════════════════
                 STOCK ALERTS & CATEGORIES
            ══════════════════════════════════════ -->
            <div class="space-y-8">
                <!-- Stock Alerts -->
                <div
                    class="overflow-hidden rounded-3xl border border-red-100 bg-white shadow-sm dark:border-red-900/30 dark:bg-surface-dark"
                >
                    <div
                        class="flex items-center gap-2 border-b border-red-100 bg-red-50/50 px-6 py-5 dark:border-red-900/30 dark:bg-red-900/10"
                    >
                        <AlertCircle class="h-5 w-5 text-danger" />
                        <h3 class="font-display text-lg font-bold text-danger">
                            Alertas de Stock
                        </h3>
                    </div>
                    <div class="space-y-3 p-4">
                        <div
                            v-for="item in lowStockProducts"
                            :key="item.name"
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
                                    {{ item.category }}
                                </p>
                            </div>
                            <div class="text-right">
                                <span class="text-sm font-black text-danger">{{
                                    item.stock
                                }}</span>
                                <p
                                    class="text-[10px] font-bold uppercase text-danger/70"
                                >
                                    Unidades
                                </p>
                            </div>
                        </div>
                    </div>
                    <div
                        class="border-t border-gray-100 p-4 dark:border-gray-800"
                    >
                        <Link
                            href="/products?low_stock=1"
                            class="flex w-full items-center justify-center rounded-xl py-2.5 text-xs font-bold text-danger transition-all hover:bg-red-50 dark:hover:bg-red-900/20"
                        >
                            Reponer Inventario
                        </Link>
                    </div>
                </div>

                <!-- Categories Overview -->
                <div
                    class="flex flex-col rounded-3xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-surface-dark"
                >
                    <div
                        class="border-b border-gray-100 px-6 py-5 dark:border-gray-800"
                    >
                        <h3
                            class="font-display text-lg font-bold text-content-primary dark:text-white"
                        >
                            Departamentos
                        </h3>
                    </div>
                    <div class="flex-1 space-y-3 overflow-y-auto p-4">
                        <div
                            v-for="cat in categoryStore.categories.slice(0, 6)"
                            :key="cat.id"
                            class="group flex cursor-pointer items-center gap-4 rounded-2xl p-3 transition-colors hover:bg-gray-50 dark:hover:bg-gray-800"
                        >
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-xl bg-gray-100 text-2xl transition-transform group-hover:scale-110 dark:bg-gray-900"
                            >
                                {{ cat.emoji }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <p
                                    class="truncate text-sm font-bold text-content-primary dark:text-white"
                                >
                                    {{ cat.name }}
                                </p>
                                <p class="text-xs text-content-muted">
                                    {{ cat.subcategories.length }} Subcategorías
                                </p>
                            </div>
                            <ChevronDown
                                class="h-4 w-4 -rotate-90 text-gray-300"
                            />
                        </div>
                    </div>
                    <div
                        class="border-t border-gray-100 p-4 dark:border-gray-800"
                    >
                        <Link
                            href="/categories"
                            class="flex w-full items-center justify-center gap-2 rounded-xl bg-gray-50 py-3 text-sm font-bold text-content-secondary transition-all hover:text-primary-500 dark:bg-gray-900"
                        >
                            Gestionar Categorías
                            <ArrowUpRight class="h-4 w-4" />
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
