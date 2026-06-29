<script setup lang="ts">
import SalesTrendChart from '@/Components/Dashboard/SalesTrendChart.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { formatCLP, formatDate, formatTime } from '@/helpers/format';
import { Head, Link } from '@inertiajs/vue3';
import {
    AlertCircle,
    ArrowRight,
    Banknote,
    Clock,
    CreditCard,
    DollarSign,
    Package,
    ShoppingCart,
    Tag,
    Trash2,
    TrendingUp,
    Wallet,
} from 'lucide-vue-next';

const props = defineProps<{
    role: string;
    todaySales: number;
    todaySalesCount: number;
    todayProfit: number;
    todayMargin: number;
    thisMonthSales: number;
    lastMonthSales: number;
    salesGrowth: number;
    salesTrend: { date: string; total: number; count: number }[];
    topProducts: { name: string; qty: number; total: number }[];
    paymentMethods: { cash: number; card: number; transfer: number };
    openSession: {
        id: number;
        user: { id: number; name: string };
        opened_at: string;
    } | null;
    lowStockProducts: {
        id: number;
        name: string;
        stock: number;
        min_stock: number;
        sku: string | null;
    }[];
    expiringBatches: {
        id: number;
        product: { id: number; name: string };
        expiration_date: string;
        quantity: number;
    }[];
    monthlyExpenses: number;
    monthlyLosses: number;
    activePromotions: number;
}>();

const paymentTotal =
    props.paymentMethods.cash +
    props.paymentMethods.card +
    props.paymentMethods.transfer;

const methodPct = (v: number) =>
    paymentTotal > 0 ? Math.round((v / paymentTotal) * 100) : 0;

const methodColor: Record<string, string> = {
    cash: 'bg-success',
    card: 'bg-primary-500',
    transfer: 'bg-accent-500',
};
</script>

<template>
    <Head title="Panel de Administración" />

    <AdminLayout>
        <template #title>
            <h1
                class="font-display text-xl font-bold text-content-primary dark:text-white"
            >
                Panel de Administración
            </h1>
        </template>

        <!-- Sección 1 — Estado de Caja -->
        <div
            class="mb-6 flex flex-wrap items-center justify-between gap-4 rounded-3xl border px-6 py-4 shadow-sm"
            :class="
                openSession
                    ? 'border-success/30 bg-success/5'
                    : 'border-gray-200 bg-white dark:border-gray-800 dark:bg-surface-dark'
            "
        >
            <div class="flex items-center gap-3">
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-2xl"
                    :class="
                        openSession
                            ? 'bg-success/20 text-success'
                            : 'bg-gray-100 text-content-muted dark:bg-gray-800'
                    "
                >
                    <Wallet class="h-5 w-5" />
                </div>
                <div>
                    <p
                        class="text-sm font-bold text-content-primary dark:text-white"
                    >
                        <span
                            v-if="openSession"
                            class="inline-block h-2 w-2 rounded-full bg-success align-middle"
                        ></span>
                        <span
                            v-else
                            class="inline-block h-2 w-2 rounded-full bg-danger align-middle"
                        ></span>
                        {{ openSession ? 'Caja abierta' : 'Caja cerrada' }}
                    </p>
                    <p class="text-xs text-content-muted">
                        <template v-if="openSession">
                            {{ openSession.user.name }} — desde las
                            {{ formatTime(openSession.opened_at) }}
                        </template>
                        <template v-else> Última sesión: ayer </template>
                    </p>
                </div>
            </div>
            <Link
                v-if="!openSession"
                :href="route('admin.pos')"
                class="flex items-center gap-2 rounded-xl bg-primary-500 px-5 py-2.5 text-sm font-bold text-white transition-colors hover:bg-primary-600"
            >
                <ShoppingCart class="h-4 w-4" /> Abrir Caja
            </Link>
            <Link
                v-else
                :href="route('admin.pos')"
                class="flex items-center gap-2 rounded-xl bg-primary-500/10 px-5 py-2.5 text-sm font-bold text-primary-500 transition-colors hover:bg-primary-500/20"
            >
                Ir al POS <ArrowRight class="h-4 w-4" />
            </Link>
        </div>

        <!-- Sección 2 — KPIs de Hoy -->
        <div class="mb-8 grid grid-cols-2 gap-4 lg:grid-cols-4">
            <div
                class="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-surface-dark"
            >
                <div class="mb-3 flex items-center justify-between">
                    <div
                        class="rounded-2xl bg-primary-50 p-2.5 text-primary-500 dark:bg-primary-900/30"
                    >
                        <TrendingUp class="h-5 w-5" />
                    </div>
                    <span
                        class="rounded-lg bg-primary-50 px-2 py-0.5 text-[11px] font-bold text-primary-500 dark:bg-primary-900/30"
                    >
                        {{ todaySalesCount }} ventas
                    </span>
                </div>
                <p
                    class="text-[11px] font-semibold uppercase tracking-wider text-content-muted"
                >
                    Ventas Hoy
                </p>
                <h3
                    class="mt-0.5 font-display text-2xl font-extrabold text-content-primary dark:text-white"
                >
                    {{ formatCLP(todaySales) }}
                </h3>
            </div>
            <div
                class="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-surface-dark"
            >
                <div class="mb-3 flex items-center justify-between">
                    <div
                        class="rounded-2xl bg-blue-50 p-2.5 text-blue-500 dark:bg-blue-900/30"
                    >
                        <ShoppingCart class="h-5 w-5" />
                    </div>
                </div>
                <p
                    class="text-[11px] font-semibold uppercase tracking-wider text-content-muted"
                >
                    Transacciones
                </p>
                <h3
                    class="mt-0.5 font-display text-2xl font-extrabold text-content-primary dark:text-white"
                >
                    {{ todaySalesCount }}
                </h3>
            </div>
            <div
                class="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-surface-dark"
            >
                <div class="mb-3 flex items-center justify-between">
                    <div class="rounded-2xl bg-success/10 p-2.5 text-success">
                        <DollarSign class="h-5 w-5" />
                    </div>
                    <span
                        class="rounded-lg bg-success/10 px-2 py-0.5 text-[11px] font-bold text-success"
                    >
                        Margen: {{ todayMargin }}%
                    </span>
                </div>
                <p
                    class="text-[11px] font-semibold uppercase tracking-wider text-content-muted"
                >
                    Ganancia Hoy
                </p>
                <h3
                    class="mt-0.5 font-display text-2xl font-extrabold text-content-primary dark:text-white"
                >
                    {{ formatCLP(todayProfit) }}
                </h3>
            </div>
            <div
                class="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-surface-dark"
            >
                <div class="mb-3 flex items-center justify-between">
                    <div
                        class="rounded-2xl bg-accent-50 p-2.5 text-accent-500 dark:bg-accent-900/30"
                    >
                        <Wallet class="h-5 w-5" />
                    </div>
                    <span
                        class="rounded-lg px-2 py-0.5 text-[11px] font-bold"
                        :class="
                            salesGrowth >= 0
                                ? 'bg-success/10 text-success'
                                : 'bg-danger/10 text-danger'
                        "
                    >
                        {{ salesGrowth >= 0 ? '+' : '' }}{{ salesGrowth }}%
                    </span>
                </div>
                <p
                    class="text-[11px] font-semibold uppercase tracking-wider text-content-muted"
                >
                    Mes Actual
                </p>
                <h3
                    class="mt-0.5 font-display text-2xl font-extrabold text-content-primary dark:text-white"
                >
                    {{ formatCLP(thisMonthSales) }}
                </h3>
            </div>
        </div>

        <!-- Sección 3 — Gráfico de Tendencia -->
        <div
            class="mb-8 overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-surface-dark"
        >
            <div
                class="border-b border-gray-100 px-6 py-5 dark:border-gray-800"
            >
                <h3
                    class="font-display text-lg font-bold text-content-primary dark:text-white"
                >
                    Tendencia de Ventas — Últimos 30 Días
                </h3>
            </div>
            <div class="p-6">
                <SalesTrendChart :data="salesTrend" />
            </div>
        </div>

        <!-- Sección 4 — Dos Columnas -->
        <div class="mb-8 grid grid-cols-1 gap-8 lg:grid-cols-2">
            <!-- Columna izquierda: Top productos + Métodos de pago -->
            <div class="space-y-8">
                <div
                    class="overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-surface-dark"
                >
                    <div
                        class="border-b border-gray-100 px-6 py-5 dark:border-gray-800"
                    >
                        <h3
                            class="font-display text-lg font-bold text-content-primary dark:text-white"
                        >
                            Top 5 Productos Hoy
                        </h3>
                    </div>
                    <div
                        v-if="topProducts.length"
                        class="divide-y divide-gray-100 dark:divide-gray-800"
                    >
                        <div
                            v-for="(p, i) in topProducts"
                            :key="i"
                            class="flex items-center justify-between px-6 py-3.5 transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/50"
                        >
                            <div class="flex items-center gap-3">
                                <span
                                    class="flex h-7 w-7 items-center justify-center rounded-lg bg-primary-50 text-xs font-black text-primary-500 dark:bg-primary-900/30"
                                >
                                    {{ i + 1 }}
                                </span>
                                <span
                                    class="text-sm font-bold text-content-primary dark:text-white"
                                    >{{ p.name }}</span
                                >
                            </div>
                            <div class="text-right">
                                <span
                                    class="text-sm font-bold text-content-primary dark:text-white"
                                    >{{ p.qty }} uds.</span
                                >
                                <p
                                    class="text-[11px] font-bold text-content-muted"
                                >
                                    {{ formatCLP(p.total) }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div
                        v-else
                        class="px-6 py-8 text-center text-sm text-content-muted"
                    >
                        Sin ventas hoy
                    </div>
                </div>

                <div
                    class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-surface-dark"
                >
                    <h3
                        class="mb-5 font-display text-lg font-bold text-content-primary dark:text-white"
                    >
                        Métodos de Pago Hoy
                    </h3>
                    <div class="space-y-4">
                        <div>
                            <div
                                class="mb-1.5 flex items-center justify-between text-sm"
                            >
                                <span
                                    class="flex items-center gap-2 font-bold text-content-primary dark:text-white"
                                >
                                    <Banknote class="h-4 w-4 text-success" />
                                    Efectivo
                                </span>
                                <span
                                    class="font-bold text-content-primary dark:text-white"
                                    >{{ methodPct(paymentMethods.cash) }}%</span
                                >
                            </div>
                            <div
                                class="h-2.5 rounded-full bg-gray-100 dark:bg-gray-800"
                            >
                                <div
                                    class="h-2.5 rounded-full bg-success transition-all"
                                    :style="{
                                        width:
                                            methodPct(paymentMethods.cash) +
                                            '%',
                                    }"
                                ></div>
                            </div>
                        </div>
                        <div>
                            <div
                                class="mb-1.5 flex items-center justify-between text-sm"
                            >
                                <span
                                    class="flex items-center gap-2 font-bold text-content-primary dark:text-white"
                                >
                                    <CreditCard
                                        class="h-4 w-4 text-primary-500"
                                    />
                                    Tarjeta
                                </span>
                                <span
                                    class="font-bold text-content-primary dark:text-white"
                                    >{{ methodPct(paymentMethods.card) }}%</span
                                >
                            </div>
                            <div
                                class="h-2.5 rounded-full bg-gray-100 dark:bg-gray-800"
                            >
                                <div
                                    class="h-2.5 rounded-full bg-primary-500 transition-all"
                                    :style="{
                                        width:
                                            methodPct(paymentMethods.card) +
                                            '%',
                                    }"
                                ></div>
                            </div>
                        </div>
                        <div>
                            <div
                                class="mb-1.5 flex items-center justify-between text-sm"
                            >
                                <span
                                    class="flex items-center gap-2 font-bold text-content-primary dark:text-white"
                                >
                                    <ArrowRight
                                        class="h-4 w-4 text-accent-500"
                                    />
                                    Transferencia
                                </span>
                                <span
                                    class="font-bold text-content-primary dark:text-white"
                                    >{{
                                        methodPct(paymentMethods.transfer)
                                    }}%</span
                                >
                            </div>
                            <div
                                class="h-2.5 rounded-full bg-gray-100 dark:bg-gray-800"
                            >
                                <div
                                    class="h-2.5 rounded-full bg-accent-500 transition-all"
                                    :style="{
                                        width:
                                            methodPct(paymentMethods.transfer) +
                                            '%',
                                    }"
                                ></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Columna derecha: Alertas de stock + Lotes por vencer -->
            <div class="space-y-8">
                <div
                    class="overflow-hidden rounded-3xl border shadow-sm"
                    :class="
                        lowStockProducts.length
                            ? 'border-red-100 dark:border-red-900/30'
                            : 'border-gray-100 dark:border-gray-800'
                    "
                >
                    <div
                        class="flex items-center gap-2 border-b px-6 py-5"
                        :class="
                            lowStockProducts.length
                                ? 'border-red-100 bg-red-50/50 dark:border-red-900/30 dark:bg-red-900/10'
                                : 'border-gray-100 dark:border-gray-800'
                        "
                    >
                        <AlertCircle
                            class="h-5 w-5"
                            :class="
                                lowStockProducts.length
                                    ? 'text-danger'
                                    : 'text-content-muted'
                            "
                        />
                        <h3
                            class="font-display text-lg font-bold"
                            :class="
                                lowStockProducts.length
                                    ? 'text-danger'
                                    : 'text-content-primary dark:text-white'
                            "
                        >
                            Alertas de Stock
                        </h3>
                        <span
                            v-if="lowStockProducts.length"
                            class="ml-auto rounded-full bg-danger/10 px-2.5 py-0.5 text-xs font-bold text-danger"
                        >
                            {{ lowStockProducts.length }}
                        </span>
                    </div>
                    <div
                        v-if="lowStockProducts.length"
                        class="divide-y divide-gray-100 dark:divide-gray-800"
                    >
                        <div
                            v-for="item in lowStockProducts"
                            :key="item.id"
                            class="flex items-center justify-between px-6 py-3.5 transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/50"
                        >
                            <div>
                                <Link
                                    :href="
                                        route('admin.codigos.index') +
                                        '?search=' +
                                        item.sku
                                    "
                                    class="text-sm font-bold text-content-primary underline-offset-2 hover:underline dark:text-white"
                                >
                                    {{ item.name }}
                                </Link>
                                <p
                                    class="text-[10px] uppercase tracking-wider text-content-muted"
                                >
                                    SKU: {{ item.sku || '—' }} · Mín:
                                    {{ item.min_stock }}
                                </p>
                            </div>
                            <div class="text-right">
                                <span class="text-sm font-black text-danger">{{
                                    item.stock
                                }}</span>
                                <p
                                    class="text-[10px] font-bold uppercase text-danger/70"
                                >
                                    uds.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div
                        v-else
                        class="px-6 py-8 text-center text-sm text-content-muted"
                    >
                        Sin alertas de stock
                    </div>
                    <div
                        class="border-t border-gray-100 px-6 py-3 dark:border-gray-800"
                    >
                        <Link
                            :href="route('admin.codigos.index')"
                            class="text-xs font-bold text-primary-500 transition-colors hover:text-primary-600"
                        >
                            Ir a Productos →
                        </Link>
                    </div>
                </div>

                <div
                    class="overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-surface-dark"
                >
                    <div
                        class="flex items-center gap-2 border-b border-gray-100 px-6 py-5 dark:border-gray-800"
                    >
                        <Clock class="h-5 w-5 text-warning" />
                        <h3
                            class="font-display text-lg font-bold text-content-primary dark:text-white"
                        >
                            Próximos a Vencer (7 días)
                        </h3>
                    </div>
                    <div
                        v-if="expiringBatches.length"
                        class="divide-y divide-gray-100 dark:divide-gray-800"
                    >
                        <div
                            v-for="batch in expiringBatches"
                            :key="batch.id"
                            class="flex items-center justify-between px-6 py-3.5 transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/50"
                        >
                            <div>
                                <p
                                    class="text-sm font-bold text-content-primary dark:text-white"
                                >
                                    {{ batch.product.name }}
                                </p>
                                <p
                                    class="text-[10px] uppercase tracking-wider text-content-muted"
                                >
                                    Vence:
                                    {{ formatDate(batch.expiration_date) }}
                                </p>
                            </div>
                            <span
                                class="rounded-lg bg-warning/10 px-2.5 py-1 text-xs font-bold text-warning"
                            >
                                {{ batch.quantity }} uds.
                            </span>
                        </div>
                    </div>
                    <div
                        v-else
                        class="px-6 py-8 text-center text-sm text-content-muted"
                    >
                        Sin productos próximos a vencer
                    </div>
                </div>
            </div>
        </div>

        <!-- Sección 5 — Resumen Financiero del Mes -->
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            <div
                class="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-surface-dark"
            >
                <div class="mb-3 flex items-center gap-2">
                    <div
                        class="rounded-2xl bg-primary-50 p-2.5 text-primary-500 dark:bg-primary-900/30"
                    >
                        <TrendingUp class="h-5 w-5" />
                    </div>
                </div>
                <p
                    class="text-[11px] font-semibold uppercase tracking-wider text-content-muted"
                >
                    Ventas del Mes
                </p>
                <h3
                    class="mt-0.5 font-display text-xl font-extrabold text-content-primary dark:text-white"
                >
                    {{ formatCLP(thisMonthSales) }}
                </h3>
            </div>
            <div
                class="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-surface-dark"
            >
                <div class="mb-3 flex items-center gap-2">
                    <div
                        class="rounded-2xl bg-orange-50 p-2.5 text-orange-500 dark:bg-orange-900/30"
                    >
                        <Package class="h-5 w-5" />
                    </div>
                </div>
                <p
                    class="text-[11px] font-semibold uppercase tracking-wider text-content-muted"
                >
                    Gastos del Mes
                </p>
                <h3
                    class="mt-0.5 font-display text-xl font-extrabold text-danger"
                >
                    {{ formatCLP(monthlyExpenses) }}
                </h3>
            </div>
            <div
                class="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-surface-dark"
            >
                <div class="mb-3 flex items-center gap-2">
                    <div
                        class="rounded-2xl bg-red-50 p-2.5 text-red-500 dark:bg-red-900/30"
                    >
                        <Trash2 class="h-5 w-5" />
                    </div>
                </div>
                <p
                    class="text-[11px] font-semibold uppercase tracking-wider text-content-muted"
                >
                    Pérdidas del Mes
                </p>
                <h3
                    class="mt-0.5 font-display text-xl font-extrabold text-danger"
                >
                    {{ formatCLP(monthlyLosses) }}
                </h3>
            </div>
            <div
                class="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-surface-dark"
            >
                <div class="mb-3 flex items-center gap-2">
                    <div
                        class="rounded-2xl bg-accent-50 p-2.5 text-accent-500 dark:bg-accent-900/30"
                    >
                        <Tag class="h-5 w-5" />
                    </div>
                </div>
                <p
                    class="text-[11px] font-semibold uppercase tracking-wider text-content-muted"
                >
                    Promociones Activas
                </p>
                <h3
                    class="mt-0.5 font-display text-xl font-extrabold text-content-primary dark:text-white"
                >
                    {{ activePromotions }}
                </h3>
            </div>
        </div>
    </AdminLayout>
</template>
