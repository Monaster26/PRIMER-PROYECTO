<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { formatDate, formatTime } from '@/helpers/format';
import {
    TrendingUp, Package, Layers, AlertCircle,
    ShoppingCart, Wallet, Clock, CheckCircle2,
    ArrowRight, ChevronDown
} from 'lucide-vue-next';

const props = defineProps<{
    role: 'admin' | 'cashier';
    stats: any;
    recent_sales: any[];
    low_stock_products?: any[];
    categories?: any[];
}>();

const fmt = (v: number) => '$' + (v / 100).toLocaleString('es-CO', { minimumFractionDigits: 0 });

const methodLabel: Record<string, string> = {
    cash: 'Efectivo',
    card: 'Tarjeta',
    transfer: 'Transferencia',
    mixed: 'Mixto',
    mercadopago: 'Mercado Pago',
};
</script>

<template>
    <Head title="Panel de Administración" />

    <AdminLayout>
        <template #title>
            <h1 class="text-xl font-display font-bold text-content-primary dark:text-white">
                {{ role === 'admin' ? 'Resumen General' : 'Mi Panel' }}
            </h1>
        </template>

        <!-- Admin Dashboard -->
        <template v-if="role === 'admin'">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white dark:bg-surface-dark p-6 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-800">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 rounded-2xl bg-primary-50 dark:bg-primary-900/30 text-primary-500">
                            <TrendingUp class="w-6 h-6" />
                        </div>
                        <span class="text-xs font-bold text-success bg-success/10 px-2 py-1 rounded-lg">
                            {{ stats.sales_count }} ventas
                        </span>
                    </div>
                    <p class="text-sm font-semibold text-content-secondary dark:text-gray-400">Ventas del Mes</p>
                    <h3 class="text-2xl font-display font-extrabold text-content-primary dark:text-white mt-1">{{ fmt(stats.total_sales) }}</h3>
                </div>
                <div class="bg-white dark:bg-surface-dark p-6 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-800">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 rounded-2xl bg-blue-50 dark:bg-blue-900/30 text-blue-500">
                            <Package class="w-6 h-6" />
                        </div>
                    </div>
                    <p class="text-sm font-semibold text-content-secondary dark:text-gray-400">Total Productos</p>
                    <h3 class="text-2xl font-display font-extrabold text-content-primary dark:text-white mt-1">{{ stats.total_products }}</h3>
                </div>
                <div class="bg-white dark:bg-surface-dark p-6 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-800">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 rounded-2xl bg-accent-50 dark:bg-accent-900/30 text-accent-500">
                            <Layers class="w-6 h-6" />
                        </div>
                    </div>
                    <p class="text-sm font-semibold text-content-secondary dark:text-gray-400">Categorías</p>
                    <h3 class="text-2xl font-display font-extrabold text-content-primary dark:text-white mt-1">{{ stats.active_categories }}</h3>
                </div>
                <div class="bg-white dark:bg-surface-dark p-6 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-800">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 rounded-2xl bg-red-50 dark:bg-red-900/30 text-danger">
                            <AlertCircle class="w-6 h-6" />
                        </div>
                        <span class="text-xs font-bold text-danger bg-danger/10 px-2 py-1 rounded-lg">{{ stats.low_stock_count }}</span>
                    </div>
                    <p class="text-sm font-semibold text-content-secondary dark:text-gray-400">Stock Bajo</p>
                    <h3 class="text-2xl font-display font-extrabold text-content-primary dark:text-white mt-1">{{ stats.low_stock_count }} productos</h3>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 bg-white dark:bg-surface-dark rounded-3xl shadow-sm border border-gray-100 dark:border-gray-800 overflow-hidden">
                    <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                        <h3 class="font-display font-bold text-lg text-content-primary dark:text-white">Últimas Ventas</h3>
                        <Link :href="route('admin.ventas.index')" class="text-sm font-bold text-primary-500 hover:text-primary-600 transition-colors">Ver todas</Link>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50 dark:bg-gray-900/50 text-content-muted dark:text-gray-500 text-xs uppercase tracking-wider">
                                    <th class="px-6 py-4 font-bold">#</th>
                                    <th class="px-6 py-4 font-bold">Cajero</th>
                                    <th class="px-6 py-4 font-bold">Método</th>
                                    <th class="px-6 py-4 font-bold text-right">Total</th>
                                    <th class="px-6 py-4 font-bold text-right">Hora</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                <tr v-if="!recent_sales.length">
                                    <td colspan="5" class="px-6 py-8 text-center text-sm text-content-muted">Sin ventas recientes</td>
                                </tr>
                                <tr v-for="sale in recent_sales" :key="sale.id"
                                    class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                    <td class="px-6 py-4 text-sm font-mono font-bold text-content-primary dark:text-white">#{{ sale.id }}</td>
                                    <td class="px-6 py-4 text-sm text-content-primary dark:text-white">{{ sale.cashier?.name || '—' }}</td>
                                    <td class="px-6 py-4">
                                        <span class="px-2.5 py-1 bg-gray-100 dark:bg-gray-800 rounded-lg text-xs font-bold text-content-secondary capitalize">
                                            {{ methodLabel[sale.payment_method] || sale.payment_method }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-right font-bold text-primary-500">{{ fmt(sale.total) }}</td>
                                    <td class="px-6 py-4 text-sm text-right text-content-secondary">{{ formatTime(sale.created_at) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="space-y-8">
                    <div v-if="low_stock_products?.length" class="bg-white dark:bg-surface-dark rounded-3xl shadow-sm border border-red-100 dark:border-red-900/30 overflow-hidden">
                        <div class="px-6 py-5 bg-red-50/50 dark:bg-red-900/10 border-b border-red-100 dark:border-red-900/30 flex items-center gap-2">
                            <AlertCircle class="w-5 h-5 text-danger" />
                            <h3 class="font-display font-bold text-lg text-danger">Alertas de Stock</h3>
                        </div>
                        <div class="p-4 space-y-3">
                            <div v-for="item in low_stock_products" :key="item.id"
                                class="flex items-center justify-between p-3 rounded-2xl bg-gray-50 dark:bg-gray-900">
                                <div>
                                    <p class="text-sm font-bold text-content-primary dark:text-white">{{ item.name }}</p>
                                    <p class="text-[10px] text-content-muted uppercase tracking-wider">Stock mínimo: {{ item.min_stock }}</p>
                                </div>
                                <div class="text-right">
                                    <span class="text-sm font-black text-danger">{{ item.stock }}</span>
                                    <p class="text-[10px] text-danger/70 font-bold uppercase">uds.</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-4 border-t border-gray-100 dark:border-gray-800">
                            <Link :href="route('admin.codigos.index')"
                                class="w-full py-2.5 flex items-center justify-center text-xs font-bold text-danger hover:bg-red-50 dark:hover:bg-red-900/20 rounded-xl transition-all">
                                Ir a Productos
                            </Link>
                        </div>
                    </div>

                    <div v-if="categories?.length" class="bg-white dark:bg-surface-dark rounded-3xl shadow-sm border border-gray-100 dark:border-gray-800 flex flex-col">
                        <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-800">
                            <h3 class="font-display font-bold text-lg text-content-primary dark:text-white">Categorías</h3>
                        </div>
                        <div class="flex-1 overflow-y-auto p-4 space-y-2">
                            <div v-for="cat in categories" :key="cat.id"
                                class="flex items-center justify-between p-3 rounded-2xl hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                <p class="text-sm font-bold text-content-primary dark:text-white">{{ cat.name }}</p>
                                <span class="text-xs font-bold text-content-muted bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded-full">{{ cat.product_count }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <!-- Cashier Dashboard -->
        <template v-else>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <div class="bg-white dark:bg-surface-dark p-6 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-800">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 rounded-2xl bg-primary-50 dark:bg-primary-900/30 text-primary-500">
                            <ShoppingCart class="w-6 h-6" />
                        </div>
                        <span class="text-xs font-bold text-success bg-success/10 px-2 py-1 rounded-lg">{{ stats.today_count }} ventas</span>
                    </div>
                    <p class="text-sm font-semibold text-content-secondary dark:text-gray-400">Ventas de Hoy</p>
                    <h3 class="text-2xl font-display font-extrabold text-content-primary dark:text-white mt-1">{{ fmt(stats.today_sales) }}</h3>
                </div>
                <div class="bg-white dark:bg-surface-dark p-6 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-800">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 rounded-2xl" :class="stats.has_open_session ? 'bg-success/10 text-success' : 'bg-gray-100 dark:bg-gray-800 text-content-muted'">
                            <Wallet class="w-6 h-6" />
                        </div>
                        <span class="text-xs font-bold px-2 py-1 rounded-lg"
                            :class="stats.has_open_session ? 'bg-success/10 text-success' : 'bg-warning/10 text-warning'">
                            {{ stats.has_open_session ? 'Abierta' : 'Sin sesión' }}
                        </span>
                    </div>
                    <p class="text-sm font-semibold text-content-secondary dark:text-gray-400">Sesión de Caja</p>
                    <p class="text-sm font-medium text-content-primary dark:text-white mt-1">
                        {{ stats.has_open_session ? 'Abierta ' + formatDate(stats.session_opened_at) : 'No has abierto sesión hoy' }}
                    </p>
                </div>
                <div class="bg-white dark:bg-surface-dark p-6 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-800 flex flex-col items-center justify-center text-center">
                    <p class="text-xs font-bold text-content-muted dark:text-gray-400 uppercase tracking-wider mb-3">Acceso Rápido</p>
                    <div class="flex gap-3">
                        <Link :href="route('admin.pos')"
                            class="flex items-center gap-2 bg-primary-500 hover:bg-primary-600 text-white text-sm font-bold px-5 py-3 rounded-2xl transition-colors shadow-sm">
                            <ShoppingCart class="w-5 h-5" /> POS
                        </Link>
                        <Link :href="route('admin.arqueo-caja.index')"
                            class="flex items-center gap-2 border border-gray-200 dark:border-gray-700 text-content-secondary hover:bg-gray-50 dark:hover:bg-gray-800 text-sm font-bold px-5 py-3 rounded-2xl transition-colors">
                            <Wallet class="w-5 h-5" /> Arqueo
                        </Link>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-surface-dark rounded-3xl shadow-sm border border-gray-100 dark:border-gray-800 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-800">
                    <h3 class="font-display font-bold text-lg text-content-primary dark:text-white">Mis Últimas Ventas</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-gray-900/50 text-content-muted dark:text-gray-500 text-xs uppercase tracking-wider">
                                <th class="px-6 py-4 font-bold">#</th>
                                <th class="px-6 py-4 font-bold">Fecha</th>
                                <th class="px-6 py-4 font-bold">Método</th>
                                <th class="px-6 py-4 font-bold text-right">Items</th>
                                <th class="px-6 py-4 font-bold text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            <tr v-if="!recent_sales.length">
                                <td colspan="5" class="px-6 py-12 text-center text-sm text-content-muted">No has realizado ventas aún.</td>
                            </tr>
                            <tr v-for="sale in recent_sales" :key="sale.id"
                                class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                <td class="px-6 py-4 text-sm font-mono font-bold text-content-primary dark:text-white">#{{ sale.id }}</td>
                                <td class="px-6 py-4 text-sm text-content-primary dark:text-white">{{ formatDate(sale.date) }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 bg-gray-100 dark:bg-gray-800 rounded-lg text-xs font-bold capitalize">
                                        {{ methodLabel[sale.payment_method] || sale.payment_method }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-right text-content-secondary">{{ sale.items?.length || 0 }}</td>
                                <td class="px-6 py-4 text-sm text-right font-bold text-primary-500">{{ fmt(sale.total) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </template>
    </AdminLayout>
</template>
