<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    BarChart3,
    ClipboardList,
    CreditCard,
    Eye,
    GitCompareArrows,
    LayoutDashboard,
    Package,
    Percent,
    ReceiptText,
    ShoppingBag,
    ShoppingCart,
    TrendingDown,
    Truck,
    UserCog,
    Users,
    Wallet,
    X,
} from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';

defineProps<{ isOpen: boolean }>();
const emit = defineEmits(['close']);

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

const unreadObservaciones = ref(0);

onMounted(async () => {
    try {
        const res = await fetch(route('admin.observaciones.unread-count'));
        const json = await res.json();
        unreadObservaciones.value = json.count;
    } catch {
        /* silent */
    }
});

const navGroups = computed(() => {
    const groups: {
        label: string;
        items: { label: string; name: string; icon: any }[];
    }[] = [];

    // Dashboard — admin only
    if (isAtLeastAdmin.value) {
        groups.push({
            label: 'Panel',
            items: [
                {
                    label: 'Dashboard',
                    name: 'admin.dashboard',
                    icon: LayoutDashboard,
                },
            ],
        });
    }

    // Caja
    if (isAtLeastAdmin.value) {
        groups.push({
            label: 'Caja',
            items: [
                {
                    label: 'Caja Rápida (POS)',
                    name: 'admin.pos',
                    icon: ShoppingCart,
                },
                {
                    label: 'Arqueo de Caja',
                    name: 'admin.arqueo-caja.index',
                    icon: Wallet,
                },
                {
                    label: 'Corte Diario',
                    name: 'admin.reporte-diario',
                    icon: BarChart3,
                },
            ],
        });
    } else if (isCashier.value) {
        groups.push({
            label: 'Caja',
            items: [
                {
                    label: 'Caja Rápida (POS)',
                    name: 'admin.pos',
                    icon: ShoppingCart,
                },
            ],
        });
    }

    // Admin-only sections
    if (isAtLeastAdmin.value) {
        groups.push(
            {
                label: 'Inventario',
                items: [
                    {
                        label: 'Catálogo de Productos',
                        name: 'admin.codigos.index',
                        icon: Package,
                    },
                    {
                        label: 'Pérdidas',
                        name: 'admin.perdida.index',
                        icon: TrendingDown,
                    },
                    {
                        label: 'Auditoria de inventario',
                        name: 'admin.inventory-adjustments.index',
                        icon: ClipboardList,
                    },
                ],
            },
            {
                label: 'Ventas',
                items: [
                    {
                        label: 'Ventas',
                        name: 'admin.ventas.index',
                        icon: ShoppingCart,
                    },
                    {
                        label: 'Observaciones',
                        name: 'admin.observaciones.index',
                        icon: Eye,
                    },
                ],
            },
            {
                label: 'Finanzas',
                items: [
                    {
                        label: 'Z Mensual',
                        name: 'admin.z-mensual.index',
                        icon: BarChart3,
                    },
                    {
                        label: 'Gastos',
                        name: 'admin.gastos.index',
                        icon: ReceiptText,
                    },
                    {
                        label: 'Facturas Pendientes',
                        name: 'admin.facturas-pendientes.index',
                        icon: CreditCard,
                    },
                ],
            },
            {
                label: 'Proveedores',
                items: [
                    {
                        label: 'Proveedores',
                        name: 'admin.proveedores.index',
                        icon: Truck,
                    },
                    {
                        label: 'Comparativa de Precios',
                        name: 'admin.comparativa-precios.index',
                        icon: GitCompareArrows,
                    },
                ],
            },
            {
                label: 'Marketing',
                items: [
                    {
                        label: 'Promociones',
                        name: 'admin.promociones.index',
                        icon: Percent,
                    },
                ],
            },
            {
                label: 'Compras',
                items: [
                    {
                        label: 'Pedidos',
                        name: 'admin.pedidos.index',
                        icon: ShoppingBag,
                    },
                ],
            },
            {
                label: 'Usuarios',
                items: [
                    { label: 'Cajeros', name: 'admin.cajeros', icon: UserCog },
                    { label: 'Clientes', name: 'admin.clientes', icon: Users },
                ],
            },
        );
    }

    return groups;
});
</script>

<template>
    <aside
        class="fixed inset-y-0 left-0 z-50 flex flex-col border-r border-gray-100 bg-white shadow-sm transition-all duration-300 ease-in-out dark:border-gray-800 dark:bg-surface-dark lg:relative"
        :class="
            isOpen
                ? 'w-64 translate-x-0 opacity-100'
                : '-translate-x-full lg:w-0 lg:translate-x-0 lg:overflow-hidden lg:border-none lg:opacity-0'
        "
    >
        <div
            class="flex h-16 flex-shrink-0 items-center bg-gradient-to-r from-primary-500 to-secondary-400 px-6"
        >
            <Link
                :href="route('admin.dashboard')"
                class="flex items-center gap-3"
            >
                <img src="/images/logo.png" class="h-8 w-8 object-contain" />
                <span
                    v-if="isOpen"
                    class="font-display text-lg font-bold text-white"
                    >Admin</span
                >
            </Link>
            <button
                v-if="isOpen"
                @click="emit('close')"
                class="ml-auto rounded-xl p-1.5 text-white/80 transition-colors hover:bg-white/10 lg:hidden"
            >
                <X class="h-5 w-5" />
            </button>
        </div>

        <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
            <template v-for="group in navGroups" :key="group.label">
                <p
                    class="select-none px-4 pb-1 pt-4 text-[10px] font-extrabold uppercase tracking-widest text-content-muted dark:text-gray-500"
                >
                    {{ group.label }}
                </p>

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
                    <span
                        v-if="
                            unreadObservaciones &&
                            item.name === 'admin.observaciones.index'
                        "
                        class="absolute -right-1 -top-1 flex h-5 w-5 items-center justify-center rounded-full bg-rose-500 text-[10px] font-bold text-white shadow-sm"
                    >
                        {{ unreadObservaciones }}
                    </span>
                    <span class="select-none truncate">{{ item.label }}</span>
                    <div
                        v-if="route().current(item.name)"
                        class="absolute right-0 h-6 w-1.5 rounded-l-full bg-primary-500"
                    />
                </Link>
            </template>
        </nav>
    </aside>
</template>
