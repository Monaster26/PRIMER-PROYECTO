<script setup lang="ts">
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import {
    LayoutDashboard, Package, Layers, ShoppingCart, Users,
    Tag, BarChart2, Store, ReceiptText, Truck, GitCompareArrows,
    CreditCard, ChevronDown
} from 'lucide-vue-next';

defineProps<{ isOpen: boolean }>();

// Navigation groups with sections
const navGroups = [
    {
        label: 'General',
        items: [
            { label: 'Dashboard',    name: 'dashboard',          icon: LayoutDashboard },
            { label: 'Analítica',    name: 'analytics.dashboard', icon: BarChart2 },
            { label: 'POS Terminal', name: 'pos.index',           icon: Store },
        ],
    },
    {
        label: 'Inventario',
        items: [
            { label: 'Productos',   name: 'products.index',   icon: Package },
            { label: 'Categorías',  name: 'categories.index', icon: Layers },
        ],
    },
    {
        label: 'Ventas',
        items: [
            { label: 'Pedidos',   name: 'orders.index',    icon: ShoppingCart },
            { label: 'Clientes',  name: 'customers.index', icon: Users },
            { label: 'Cupones',   name: 'coupons.index',   icon: Tag },
        ],
    },
    {
        label: 'Finanzas',
        items: [
            { label: 'Gastos',          name: 'expenses.index',           icon: ReceiptText },
            { label: 'Créditos',        name: 'credit-invoices.index',    icon: CreditCard },
        ],
    },
    {
        label: 'Proveedores',
        items: [
            { label: 'Proveedores',     name: 'providers.index',          icon: Truck },
            { label: 'Matriz de Precios', name: 'providers.price-matrix', icon: GitCompareArrows },
        ],
    },
];
</script>

<template>
    <aside
        class="fixed lg:relative inset-y-0 left-0 z-30 flex flex-col transition-all duration-300 ease-in-out
               bg-white dark:bg-surface-dark border-r border-gray-100 dark:border-gray-800 shadow-sm"
        :class="isOpen ? 'w-64' : 'w-20'"
    >
        <!-- Brand -->
        <div class="flex items-center h-16 px-6 bg-gradient-to-r from-primary-500 to-secondary-400 flex-shrink-0">
            <Link :href="route('dashboard')" class="flex items-center gap-3">
                <img src="/images/logo.png" class="h-8 w-8 object-contain" />
                <span v-if="isOpen" class="text-white font-display font-bold text-lg">Monasterios</span>
            </Link>
        </div>

        <!-- Nav Groups -->
        <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
            <template v-for="group in navGroups" :key="group.label">
                <!-- Group label -->
                <p
                    v-if="isOpen"
                    class="px-4 pt-4 pb-1 text-[10px] font-extrabold uppercase tracking-widest text-content-muted dark:text-gray-500 select-none"
                >
                    {{ group.label }}
                </p>
                <div v-else class="my-2 border-t border-gray-100 dark:border-gray-800" />

                <!-- Nav items -->
                <Link
                    v-for="item in group.items"
                    :key="item.name"
                    :href="route(item.name)"
                    class="flex items-center gap-3 px-4 py-2.5 rounded-2xl text-sm font-bold transition-all group relative z-[100] pointer-events-auto cursor-pointer"
                    :class="route().current(item.name)
                        ? 'bg-primary-50 dark:bg-primary-900/30 text-primary-500 shadow-sm'
                        : 'text-content-secondary dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-content-primary'"
                >
                    <component :is="item.icon" class="w-5 h-5 flex-shrink-0" />
                    <span v-if="isOpen" class="truncate select-none">{{ item.label }}</span>
                    <div v-if="route().current(item.name)" class="absolute right-0 w-1.5 h-6 bg-primary-500 rounded-l-full" />
                </Link>
            </template>
        </nav>
    </aside>
</template>
