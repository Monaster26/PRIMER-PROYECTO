<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    BarChart2,
    CreditCard,
    GitCompareArrows,
    Layers,
    LayoutDashboard,
    Package,
    ReceiptText,
    ShoppingCart,
    Store,
    Tag,
    Truck,
    Users,
} from 'lucide-vue-next';

defineProps<{ isOpen: boolean }>();

// Navigation groups with sections
const navGroups = [
    {
        label: 'General',
        items: [
            { label: 'Dashboard', name: 'dashboard', icon: LayoutDashboard },
            {
                label: 'Analítica',
                name: 'analytics.dashboard',
                icon: BarChart2,
            },
            { label: 'POS Terminal', name: 'pos.index', icon: Store },
        ],
    },
    {
        label: 'Inventario',
        items: [
            { label: 'Productos', name: 'products.index', icon: Package },
            { label: 'Categorías', name: 'categories.index', icon: Layers },
        ],
    },
    {
        label: 'Ventas',
        items: [
            { label: 'Pedidos', name: 'orders.index', icon: ShoppingCart },
            { label: 'Clientes', name: 'customers.index', icon: Users },
            { label: 'Cupones', name: 'coupons.index', icon: Tag },
        ],
    },
    {
        label: 'Finanzas',
        items: [
            { label: 'Registro de Egresos', name: 'expenses.index', icon: ReceiptText },
            {
                label: 'Créditos',
                name: 'credit-invoices.index',
                icon: CreditCard,
            },
        ],
    },
    {
        label: 'Proveedores',
        items: [
            { label: 'Proveedores', name: 'providers.index', icon: Truck },
            {
                label: 'Matriz de Precios',
                name: 'providers.price-matrix',
                icon: GitCompareArrows,
            },
        ],
    },
];
</script>

<template>
    <aside
        class="fixed inset-y-0 left-0 z-30 flex flex-col border-r border-gray-100 bg-white shadow-sm transition-all duration-300 ease-in-out dark:border-gray-800 dark:bg-surface-dark lg:relative"
        :class="isOpen ? 'w-64' : 'w-20'"
    >
        <!-- Brand -->
        <div
            class="flex h-16 flex-shrink-0 items-center bg-gradient-to-r from-primary-500 to-secondary-400 px-6"
        >
            <Link :href="route('dashboard')" class="flex items-center gap-3">
                <img src="/images/logo.png" class="h-8 w-8 object-contain" />
                <span
                    v-if="isOpen"
                    class="font-display text-lg font-bold text-white"
                    >Monasterios</span
                >
            </Link>
        </div>

        <!-- Nav Groups -->
        <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
            <template v-for="group in navGroups" :key="group.label">
                <!-- Group label -->
                <p
                    v-if="isOpen"
                    class="select-none px-4 pb-1 pt-4 text-[10px] font-extrabold uppercase tracking-widest text-content-muted dark:text-gray-500"
                >
                    {{ group.label }}
                </p>
                <div
                    v-else
                    class="my-2 border-t border-gray-100 dark:border-gray-800"
                />

                <!-- Nav items -->
                <Link
                    v-for="item in group.items"
                    :key="item.name"
                    :href="route(item.name)"
                    class="group pointer-events-auto relative z-[100] flex cursor-pointer items-center gap-3 rounded-2xl px-4 py-2.5 text-sm font-bold transition-all"
                    :class="
                        route().current(item.name)
                            ? 'bg-primary-50 text-primary-500 shadow-sm dark:bg-primary-900/30'
                            : 'text-content-secondary hover:bg-gray-50 hover:text-content-primary dark:text-gray-400 dark:hover:bg-gray-800'
                    "
                >
                    <component :is="item.icon" class="h-5 w-5 flex-shrink-0" />
                    <span v-if="isOpen" class="select-none truncate">{{
                        item.label
                    }}</span>
                    <div
                        v-if="route().current(item.name)"
                        class="absolute right-0 h-6 w-1.5 rounded-l-full bg-primary-500"
                    />
                </Link>
            </template>
        </nav>
    </aside>
</template>
