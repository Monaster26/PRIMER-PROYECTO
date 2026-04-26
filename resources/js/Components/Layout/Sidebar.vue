<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    LayoutDashboard, Package, Layers, ShoppingCart, Users,
    Tag, BarChart2, Store
} from 'lucide-vue-next';

defineProps<{
    isOpen: boolean;
}>();

const navItems = [
    { label: 'Dashboard',    name: 'dashboard',           icon: LayoutDashboard },
    { label: 'Productos',    name: 'products.index',      icon: Package },
    { label: 'Categorías',   name: 'categories.index',     icon: Layers },
    { label: 'Pedidos',      name: 'orders.index',         icon: ShoppingCart },
    { label: 'Clientes',     name: 'customers.index',      icon: Users },
    { label: 'Cupones',      name: 'coupons.index',        icon: Tag },
    { label: 'Analítica',    name: 'analytics.dashboard',  icon: BarChart2 },
    { label: 'POS Terminal', name: 'pos.index',            icon: Store },
];
</script>

<template>
    <aside
        class="fixed lg:relative inset-y-0 left-0 z-30 flex flex-col transition-all duration-300 ease-in-out
               bg-white dark:bg-surface-dark border-r border-gray-100 dark:border-gray-800 shadow-sm"
        :class="isOpen ? 'w-64' : 'w-20'"
    >
        <div class="flex items-center h-16 px-6 bg-gradient-to-r from-primary-500 to-secondary-400">
            <Link :href="route('dashboard')" class="flex items-center gap-3">
                <img src="/images/logo.png" class="h-8 w-8 object-contain" />
                <span v-if="isOpen" class="text-white font-display font-bold text-lg">Monasterios</span>
            </Link>
        </div>

        <nav class="flex-1 overflow-y-auto py-6 px-3 space-y-2">
            <Link
                v-for="item in navItems"
                :key="item.name"
                :href="route(item.name)"
                class="flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-bold transition-all group relative z-[100] pointer-events-auto cursor-pointer"
                :class="route().current(item.name)
                    ? 'bg-primary-50 dark:bg-primary-900/30 text-primary-500 shadow-sm'
                    : 'text-content-secondary dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-content-primary'"
            >
                <component :is="item.icon" class="w-5 h-5 flex-shrink-0" />
                <span v-if="isOpen" class="truncate select-none">{{ item.label }}</span>
                <div v-if="route().current(item.name)" class="absolute right-0 w-1.5 h-6 bg-primary-500 rounded-l-full"></div>
            </Link>
        </nav>
    </aside>
</template>
