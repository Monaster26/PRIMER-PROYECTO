<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    LayoutDashboard, Package, ShoppingCart, Users,
    BarChart3, ReceiptText, Truck, GitCompareArrows,
    CreditCard, ClipboardList, TrendingDown, FileText,
    DollarSign, UserCog, Wallet, CalendarRange, ShoppingBag
} from 'lucide-vue-next';
import { computed } from 'vue';

defineProps<{ isOpen: boolean }>();

const roles = computed<string[]>(() => {
    const auth = (usePage().props as any).auth;
    if (!auth) return [];
    const r = auth.roles;
    if (Array.isArray(r)) return r;
    if (typeof r === 'string') return r.split(',');
    return [];
});

const isAtLeastAdmin = computed(() => roles.value.includes('admin'));
const isCashier = computed(() => roles.value.includes('cashier'));

const navGroups = computed(() => {
    const groups: { label: string; items: { label: string; name: string; icon: any }[] }[] = [];

    // Dashboard — first for everyone
    if (isCashier.value || isAtLeastAdmin.value) {
        groups.push({
            label: 'Panel',
            items: [
                { label: 'Dashboard', name: 'admin.dashboard', icon: LayoutDashboard },
            ],
        });
    }

    // POS — visible to admin and cashier
    if (isCashier.value || isAtLeastAdmin.value) {
        groups.push({
            label: 'Caja',
            items: [
                { label: 'Caja Rápida (POS)', name: 'admin.pos', icon: ShoppingCart },
                { label: 'Arqueo de Caja', name: 'admin.arqueo-caja.index', icon: Wallet },
            ],
        });
    }

    // Admin-only sections
    if (isAtLeastAdmin.value) {
        groups.push(
            {
                label: 'Inventario',
                items: [
                    { label: 'Catálogo de Productos', name: 'admin.codigos.index', icon: Package },
                    { label: 'Pérdidas', name: 'admin.perdida.index', icon: TrendingDown },
                ],
            },
            {
                label: 'Ventas',
                items: [
                    { label: 'Ventas', name: 'admin.ventas.index', icon: ShoppingCart },
                    { label: 'Reportes Zeta', name: 'admin.zeta.index', icon: FileText },
                ],
            },
            {
                label: 'Finanzas',
                items: [
                    { label: 'Gastos', name: 'admin.gastos.index', icon: ReceiptText },
                    { label: 'Facturas Pendientes', name: 'admin.facturas-pendientes.index', icon: CreditCard },
                    { label: 'Control Diario', name: 'admin.control-diario.index', icon: ClipboardList },
                    { label: 'Resumen Mensual', name: 'admin.resumen-mensual.index', icon: CalendarRange },
                ],
            },
            {
                label: 'Proveedores',
                items: [
                    { label: 'Proveedores', name: 'admin.proveedores.index', icon: Truck },
                    { label: 'Comparativa de Precios', name: 'admin.comparativa-precios.index', icon: GitCompareArrows },
                ],
            },
            {
                label: 'Compras',
                items: [
                    { label: 'Pedidos', name: 'admin.pedidos.index', icon: ShoppingBag },
                ],
            },
            {
                label: 'Usuarios',
                items: [
                    { label: 'Cajeros', name: 'admin.cajeros', icon: UserCog },
                    { label: 'Clientes', name: 'admin.clientes', icon: Users },
                ],
            }
        );
    }

    return groups;
});
</script>

<template>
    <aside
        class="fixed lg:relative inset-y-0 left-0 z-30 flex flex-col transition-all duration-300 ease-in-out
               bg-white dark:bg-surface-dark border-r border-gray-100 dark:border-gray-800 shadow-sm"
        :class="isOpen ? 'w-64' : 'w-20'"
    >
        <div class="flex items-center h-16 px-6 bg-gradient-to-r from-primary-500 to-secondary-400 flex-shrink-0">
            <Link :href="route('admin.dashboard')" class="flex items-center gap-3">
                <img src="/images/logo.png" class="h-8 w-8 object-contain" />
                <span v-if="isOpen" class="text-white font-display font-bold text-lg">Admin</span>
            </Link>
        </div>

        <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
            <template v-for="group in navGroups" :key="group.label">
                <p
                    v-if="isOpen"
                    class="px-4 pt-4 pb-1 text-[10px] font-extrabold uppercase tracking-widest text-content-muted dark:text-gray-500 select-none"
                >
                    {{ group.label }}
                </p>
                <div v-else class="my-2 border-t border-gray-100 dark:border-gray-800" />

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
