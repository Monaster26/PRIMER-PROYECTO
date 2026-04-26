<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { 
    TrendingUp, Package, Layers, AlertCircle, 
    ArrowUpRight, ArrowDownRight, Clock, CheckCircle2
} from 'lucide-vue-next';
import { useCategoryStore } from '@/Stores/categoryStore';

const categoryStore = useCategoryStore();

// ─── Mock Stats Data ──────────────────────────────────────────────
const stats = [
    { label: 'Ventas del Mes', value: '$12,450.00', trend: '+12.5%', isUp: true, icon: TrendingUp, color: 'text-primary-500', bg: 'bg-primary-50' },
    { label: 'Total Productos', value: '1,248', trend: '+3', isUp: true, icon: Package, color: 'text-blue-500', bg: 'bg-blue-50' },
    { label: 'Categorías Activas', value: categoryStore.categories.length.toString(), trend: '0', isUp: true, icon: Layers, color: 'text-accent-500', bg: 'bg-accent-50' },
    { label: 'Stock Bajo', value: '12', trend: '-2', isUp: false, icon: AlertCircle, color: 'text-danger', bg: 'bg-red-50' },
];

const recentOrders = [
    { id: '#ORD-7842', customer: 'Juan Perez', items: 3, total: '$45.20', status: 'Entregado', time: 'Hace 5 min' },
    { id: '#ORD-7841', customer: 'Maria Lopez', items: 1, total: '$12.00', status: 'Pendiente', time: 'Hace 12 min' },
    { id: '#ORD-7840', customer: 'Carlos Ruiz', items: 5, total: '$89.50', status: 'Procesando', time: 'Hace 24 min' },
    { id: '#ORD-7839', customer: 'Ana Belén', items: 2, total: '$24.00', status: 'Entregado', time: 'Hace 1 hora' },
];

const lowStockProducts = [
    { name: 'Aceite de Oliva 1L', stock: 2, category: 'Abarrotes' },
    { name: 'Leche Entera 1L', stock: 4, category: 'Lácteos' },
    { name: 'Arroz Grado 1 1kg', stock: 3, category: 'Abarrotes' },
];

function getStatusClass(status: string) {
    switch (status) {
        case 'Entregado': return 'bg-success-light text-success-dark dark:bg-success-dark/20 dark:text-success-light';
        case 'Pendiente': return 'bg-warning-light text-warning-dark dark:bg-warning-dark/20 dark:text-warning-light';
        case 'Procesando': return 'bg-info-light text-info-dark dark:bg-info-dark/20 dark:text-info-light';
        default: return 'bg-gray-100 text-gray-700';
    }
}
</script>

<template>
    <Head title="Panel de Administración" />

    <AdminLayout>
        <template #title>
            <h1 class="text-xl font-display font-bold text-content-primary dark:text-white">Resumen General</h1>
        </template>

        <!-- ══════════════════════════════════════
             STATS GRID
        ══════════════════════════════════════ -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div v-for="stat in stats" :key="stat.label" 
                 class="bg-white dark:bg-surface-dark p-6 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-800 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-4">
                    <div :class="[stat.bg, stat.color]" class="p-3 rounded-2xl">
                        <component :is="stat.icon" class="w-6 h-6" />
                    </div>
                    <div :class="stat.isUp ? 'text-success' : 'text-danger'" class="flex items-center text-xs font-bold bg-gray-50 dark:bg-gray-800 px-2 py-1 rounded-lg">
                        <component :is="stat.isUp ? ArrowUpRight : ArrowDownRight" class="w-3 h-3 mr-1" />
                        {{ stat.trend }}
                    </div>
                </div>
                <div>
                    <p class="text-sm font-semibold text-content-secondary dark:text-gray-400">{{ stat.label }}</p>
                    <h3 class="text-2xl font-display font-extrabold text-content-primary dark:text-white mt-1">{{ stat.value }}</h3>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- ══════════════════════════════════════
                 RECENT ORDERS TABLE
            ══════════════════════════════════════ -->
            <div class="lg:col-span-2 bg-white dark:bg-surface-dark rounded-3xl shadow-sm border border-gray-100 dark:border-gray-800 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                    <h3 class="font-display font-bold text-lg text-content-primary dark:text-white">Últimos Pedidos</h3>
                    <Link href="/orders" class="text-sm font-bold text-primary-500 hover:text-primary-600 transition-colors">Ver todos</Link>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-gray-900/50 text-content-muted dark:text-gray-500 text-xs uppercase tracking-wider">
                                <th class="px-6 py-4 font-bold">Pedido</th>
                                <th class="px-6 py-4 font-bold">Cliente</th>
                                <th class="px-6 py-4 font-bold text-center">Items</th>
                                <th class="px-6 py-4 font-bold">Total</th>
                                <th class="px-6 py-4 font-bold">Estado</th>
                                <th class="px-6 py-4 font-bold"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            <tr v-for="order in recentOrders" :key="order.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors group">
                                <td class="px-6 py-4 text-sm font-bold text-content-primary dark:text-white">{{ order.id }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-[10px] font-bold text-content-secondary dark:text-gray-400">
                                            {{ order.customer.split(' ').map(n => n[0]).join('') }}
                                        </div>
                                        <span class="text-sm font-medium text-content-primary dark:text-white">{{ order.customer }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-center text-content-secondary dark:text-gray-400">{{ order.items }}</td>
                                <td class="px-6 py-4 text-sm font-bold text-primary-500">{{ order.total }}</td>
                                <td class="px-6 py-4">
                                    <span :class="getStatusClass(order.status)" class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-tight">
                                        {{ order.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button class="p-2 rounded-lg text-content-muted hover:text-primary-500 hover:bg-primary-50 dark:hover:bg-gray-800 transition-all opacity-0 group-hover:opacity-100">
                                        <ArrowUpRight class="w-4 h-4" />
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
                <div class="bg-white dark:bg-surface-dark rounded-3xl shadow-sm border border-red-100 dark:border-red-900/30 overflow-hidden">
                    <div class="px-6 py-5 bg-red-50/50 dark:bg-red-900/10 border-b border-red-100 dark:border-red-900/30 flex items-center gap-2">
                        <AlertCircle class="w-5 h-5 text-danger" />
                        <h3 class="font-display font-bold text-lg text-danger">Alertas de Stock</h3>
                    </div>
                    <div class="p-4 space-y-3">
                        <div v-for="item in lowStockProducts" :key="item.name" class="flex items-center justify-between p-3 rounded-2xl bg-gray-50 dark:bg-gray-900">
                            <div>
                                <p class="text-sm font-bold text-content-primary dark:text-white">{{ item.name }}</p>
                                <p class="text-[10px] text-content-muted uppercase tracking-wider">{{ item.category }}</p>
                            </div>
                            <div class="text-right">
                                <span class="text-sm font-black text-danger">{{ item.stock }}</span>
                                <p class="text-[10px] text-danger/70 font-bold uppercase">Unidades</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-4 border-t border-gray-100 dark:border-gray-800">
                        <Link href="/products?low_stock=1" class="w-full py-2.5 flex items-center justify-center text-xs font-bold text-danger hover:bg-red-50 dark:hover:bg-red-900/20 rounded-xl transition-all">
                            Reponer Inventario
                        </Link>
                    </div>
                </div>

                <!-- Categories Overview -->
                <div class="bg-white dark:bg-surface-dark rounded-3xl shadow-sm border border-gray-100 dark:border-gray-800 flex flex-col">
                    <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-800">
                        <h3 class="font-display font-bold text-lg text-content-primary dark:text-white">Departamentos</h3>
                    </div>
                    <div class="flex-1 overflow-y-auto p-4 space-y-3">
                        <div v-for="cat in categoryStore.categories.slice(0, 6)" :key="cat.id" 
                             class="flex items-center gap-4 p-3 rounded-2xl hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors group cursor-pointer">
                            <div class="text-2xl w-12 h-12 bg-gray-100 dark:bg-gray-900 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                                {{ cat.emoji }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold text-content-primary dark:text-white truncate">{{ cat.name }}</p>
                                <p class="text-xs text-content-muted">{{ cat.subcategories.length }} Subcategorías</p>
                            </div>
                            <ChevronDown class="w-4 h-4 text-gray-300 -rotate-90" />
                        </div>
                    </div>
                    <div class="p-4 border-t border-gray-100 dark:border-gray-800">
                        <Link href="/categories" class="w-full py-3 flex items-center justify-center gap-2 text-sm font-bold text-content-secondary hover:text-primary-500 bg-gray-50 dark:bg-gray-900 rounded-xl transition-all">
                            Gestionar Categorías
                            <ArrowUpRight class="w-4 h-4" />
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
