<script setup lang="ts">
import DateFilter from '@/Components/DateFilter.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import {
    Banknote,
    CreditCard,
    Percent,
    TrendingUp,
    Wallet,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps<{
    date: string;
    userId: number | null;
    users: { id: number; name: string }[];
    cashBalance: {
        opening: number;
        cashSales: number;
        ingresos: number;
        withdrawals: number;
        expected: number;
        efectivoCierre: number;
        diferencia: number;
    };
    digitalSales: {
        card: number;
        transfer: number;
        total: number;
    };
    byCategory: { category: string; qty: number; total: number }[];
    summary: {
        grossSales: number;
        totalGeneral: number;
        netProfit: number;
    };
}>();

const filterDate = ref(props.date);
const selectedUser = ref<number | ''>(props.userId ?? '');

function consultar() {
    const params: Record<string, any> = { date: filterDate.value };
    if (selectedUser.value !== '') params.user_id = selectedUser.value;
    router.get(route('admin.reporte-diario', params));
}

function onDatePicked(payload: { dia: number; mes: number; anio: number }) {
    const m = String(payload.mes).padStart(2, '0');
    const d = String(payload.dia).padStart(2, '0');
    filterDate.value = `${payload.anio}-${m}-${d}`;
    consultar();
}

const fmt = (v: number) =>
    '$' + Number(v).toLocaleString('es-CO', { minimumFractionDigits: 0 });

const profitColor = computed(() =>
    props.summary.netProfit >= 0
        ? 'text-emerald-600 dark:text-emerald-400'
        : 'text-red-600 dark:text-red-400',
);

const profitMargin = computed(() => {
    if (props.summary.grossSales === 0) return 0;
    return (props.summary.netProfit / props.summary.grossSales) * 100;
});

const diferenciaLabel = computed(() => {
    const d = props.cashBalance.diferencia;
    if (d < 0) return `Faltante: ${fmt(Math.abs(d))}`;
    if (d > 0) return `Sobrante: +${fmt(d)}`;
    return 'Caja Cuadrada';
});

const diferenciaTitle = computed(() => {
    const d = props.cashBalance.diferencia;
    if (d < 0) return 'Diferencia (Faltante)';
    if (d > 0) return 'Diferencia (Sobrante)';
    return 'Resultado';
});

const diferenciaColor = computed(() => {
    const d = props.cashBalance.diferencia;
    if (d < 0)
        return 'bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400';
    if (d > 0)
        return 'bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400';
    return 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400';
});
</script>

<template>
    <Head title="Corte Diario" />
    <AdminLayout>
        <template #title>
            <h1
                class="font-display text-xl font-bold text-content-primary dark:text-white"
            >
                Corte Diario
            </h1>
        </template>

        <div class="mx-auto max-w-5xl space-y-6 py-6">
            <!-- Filters -->
            <div
                class="rounded-xl border border-gray-100 bg-white p-4 dark:border-gray-800 dark:bg-surface-dark"
            >
                <div class="flex flex-wrap items-end gap-3">
                    <DateFilter
                        v-model="filterDate"
                        label="Fecha"
                        @select="onDatePicked"
                    />
                    <div class="min-w-0 flex-1">
                        <label
                            class="mb-1 block text-xs font-semibold uppercase tracking-wider text-gray-400"
                            >Cajero</label
                        >
                        <select
                            v-model="selectedUser"
                            @change="consultar"
                            class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-800 transition-colors focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        >
                            <option value="">Todos los cajeros</option>
                            <option
                                v-for="u in users"
                                :key="u.id"
                                :value="u.id"
                            >
                                {{ u.name }}
                            </option>
                        </select>
                    </div>
                    <button
                        @click="consultar"
                        class="flex h-10 items-center gap-1.5 rounded-lg bg-primary-500 px-4 text-sm font-bold text-white transition-colors hover:bg-primary-600"
                    >
                        Consultar
                    </button>
                </div>
            </div>

            <!-- Section 1: Cash Balance -->
            <div
                class="rounded-2xl border border-gray-100 bg-white p-5 dark:border-gray-800 dark:bg-surface-dark"
            >
                <h2
                    class="mb-4 flex items-center gap-2 text-lg font-semibold text-gray-800 dark:text-white"
                >
                    <Wallet class="h-5 w-5 text-primary-500" />
                    Balance de Efectivo
                </h2>
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                    <div class="rounded-xl bg-gray-50 p-3 dark:bg-gray-800/50">
                        <p class="text-xs text-gray-400">Monto Apertura</p>
                        <p
                            class="text-lg font-bold text-gray-800 dark:text-white"
                        >
                            {{ fmt(cashBalance.opening) }}
                        </p>
                    </div>
                    <div
                        class="rounded-xl bg-emerald-50 p-3 dark:bg-emerald-900/20"
                    >
                        <p
                            class="text-xs text-emerald-600 dark:text-emerald-400"
                        >
                            + Ventas Efectivo
                        </p>
                        <p
                            class="text-lg font-bold text-emerald-700 dark:text-emerald-300"
                        >
                            {{ fmt(cashBalance.cashSales) }}
                        </p>
                    </div>
                    <div class="rounded-xl bg-sky-50 p-3 dark:bg-sky-900/20">
                        <p class="text-xs text-sky-600 dark:text-sky-400">
                            + Ingresos
                        </p>
                        <p
                            class="text-lg font-bold text-sky-700 dark:text-sky-300"
                        >
                            {{ fmt(cashBalance.ingresos) }}
                        </p>
                    </div>
                    <div class="rounded-xl bg-red-50 p-3 dark:bg-red-900/20">
                        <p class="text-xs text-red-600 dark:text-red-400">
                            - Retiros
                        </p>
                        <p
                            class="text-lg font-bold text-red-700 dark:text-red-300"
                        >
                            {{ fmt(cashBalance.withdrawals) }}
                        </p>
                    </div>
                    <div
                        class="rounded-xl bg-primary-50 p-3 dark:bg-primary-900/20"
                    >
                        <p
                            class="text-xs text-primary-600 dark:text-primary-400"
                        >
                            = Total Esperado
                        </p>
                        <p
                            class="text-lg font-bold text-primary-700 dark:text-primary-300"
                        >
                            {{ fmt(cashBalance.expected) }}
                        </p>
                    </div>
                </div>
                <!-- Descuadre -->
                <div class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-2">
                    <div class="rounded-xl bg-gray-50 p-3 dark:bg-gray-800/50">
                        <p class="text-xs text-gray-400">
                            Efectivo Real (Cierre)
                        </p>
                        <p
                            class="text-lg font-bold text-gray-800 dark:text-white"
                        >
                            {{ fmt(cashBalance.efectivoCierre) }}
                        </p>
                    </div>
                    <div class="rounded-xl p-3" :class="diferenciaColor">
                        <p class="text-xs font-semibold uppercase opacity-70">
                            {{ diferenciaTitle }}
                        </p>
                        <p class="text-lg font-bold">{{ diferenciaLabel }}</p>
                    </div>
                </div>
            </div>

            <!-- Section 2: Digital Sales -->
            <div
                class="rounded-2xl border border-gray-100 bg-white p-5 dark:border-gray-800 dark:bg-surface-dark"
            >
                <h2
                    class="mb-4 flex items-center gap-2 text-lg font-semibold text-gray-800 dark:text-white"
                >
                    <CreditCard class="h-5 w-5 text-blue-500" />
                    Ventas Digitales
                </h2>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div class="rounded-xl bg-blue-50 p-3 dark:bg-blue-900/20">
                        <p class="text-xs text-blue-600 dark:text-blue-400">
                            Tarjeta
                        </p>
                        <p
                            class="text-lg font-bold text-blue-700 dark:text-blue-300"
                        >
                            {{ fmt(digitalSales.card) }}
                        </p>
                    </div>
                    <div
                        class="rounded-xl bg-purple-50 p-3 dark:bg-purple-900/20"
                    >
                        <p class="text-xs text-purple-600 dark:text-purple-400">
                            Transferencia
                        </p>
                        <p
                            class="text-lg font-bold text-purple-700 dark:text-purple-300"
                        >
                            {{ fmt(digitalSales.transfer) }}
                        </p>
                    </div>
                    <div class="rounded-xl bg-gray-50 p-3 dark:bg-gray-800/50">
                        <p class="text-xs text-gray-400">Total Digital</p>
                        <p
                            class="text-lg font-bold text-gray-800 dark:text-white"
                        >
                            {{ fmt(digitalSales.total) }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Section 3: Sales by Department -->
            <div
                class="rounded-2xl border border-gray-100 bg-white p-5 dark:border-gray-800 dark:bg-surface-dark"
            >
                <h2
                    class="mb-4 flex items-center gap-2 text-lg font-semibold text-gray-800 dark:text-white"
                >
                    <Banknote class="h-5 w-5 text-amber-500" />
                    Ventas por Departamento
                </h2>
                <div
                    v-if="byCategory.length === 0"
                    class="py-6 text-center text-sm text-gray-400"
                >
                    No hay ventas registradas en esta fecha.
                </div>
                <div v-else class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr
                                class="border-b border-gray-100 text-left text-xs uppercase text-gray-400 dark:border-gray-800"
                            >
                                <th class="pb-2 pr-4 font-medium">
                                    Departamento
                                </th>
                                <th class="pb-2 pr-4 text-right font-medium">
                                    Unidades
                                </th>
                                <th class="pb-2 text-right font-medium">
                                    Total
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(item, i) in byCategory"
                                :key="item.category"
                                class="border-b border-gray-50 last:border-0 dark:border-gray-800/50"
                            >
                                <td
                                    class="py-2 pr-4 font-medium text-gray-700 dark:text-gray-300"
                                >
                                    <span
                                        class="mr-2 inline-block h-2 w-2 rounded-full"
                                        :style="{
                                            backgroundColor: [
                                                '#FF2E7A',
                                                '#3B82F6',
                                                '#F59E0B',
                                                '#10B981',
                                                '#8B5CF6',
                                                '#EF4444',
                                                '#EC4899',
                                                '#14B8A6',
                                            ][i % 8],
                                        }"
                                    />
                                    {{ item.category }}
                                </td>
                                <td class="py-2 pr-4 text-right text-gray-500">
                                    {{ item.qty }}
                                </td>
                                <td
                                    class="py-2 text-right font-semibold text-gray-800 dark:text-white"
                                >
                                    {{ fmt(item.total) }}
                                </td>
                            </tr>
                            <tr
                                class="border-t-2 border-gray-200 font-bold dark:border-gray-700"
                            >
                                <td
                                    class="py-2 pr-4 text-gray-800 dark:text-white"
                                >
                                    Total
                                </td>
                                <td
                                    class="py-2 pr-4 text-right text-gray-800 dark:text-white"
                                >
                                    {{
                                        byCategory.reduce(
                                            (a, c) => a + c.qty,
                                            0,
                                        )
                                    }}
                                </td>
                                <td
                                    class="py-2 text-right text-gray-800 dark:text-white"
                                >
                                    {{
                                        fmt(
                                            byCategory.reduce(
                                                (a, c) => a + c.total,
                                                0,
                                            ),
                                        )
                                    }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Section 4: Performance Summary -->
            <div
                class="rounded-2xl border border-gray-100 bg-white p-5 dark:border-gray-800 dark:bg-surface-dark"
            >
                <h2
                    class="mb-4 flex items-center gap-2 text-lg font-semibold text-gray-800 dark:text-white"
                >
                    <TrendingUp class="h-5 w-5 text-emerald-500" />
                    Resumen de Rendimiento
                </h2>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
                    <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-800/50">
                        <p class="text-xs text-gray-400">
                            Ventas Totales (Bruto)
                        </p>
                        <p
                            class="text-2xl font-bold text-gray-800 dark:text-white"
                        >
                            {{ fmt(summary.grossSales) }}
                        </p>
                    </div>
                    <div
                        class="rounded-xl bg-primary-50 p-4 dark:bg-primary-900/20"
                    >
                        <p
                            class="text-xs text-primary-600 dark:text-primary-400"
                        >
                            Total General (Apertura + Ventas)
                        </p>
                        <p
                            class="text-2xl font-bold text-primary-700 dark:text-primary-300"
                        >
                            {{ fmt(summary.totalGeneral) }}
                        </p>
                    </div>
                    <div
                        class="rounded-xl bg-emerald-50 p-4 dark:bg-emerald-900/20"
                    >
                        <p
                            class="text-xs text-emerald-600 dark:text-emerald-400"
                        >
                            Ganancia del Día (Neta)
                        </p>
                        <p class="text-2xl font-bold" :class="profitColor">
                            {{ fmt(summary.netProfit) }}
                        </p>
                    </div>
                    <div
                        class="rounded-xl bg-amber-50 p-4 dark:bg-amber-900/20"
                    >
                        <div class="mb-1 flex items-center gap-1.5">
                            <Percent class="h-4 w-4 text-amber-500" />
                            <p
                                class="text-xs text-amber-600 dark:text-amber-400"
                            >
                                Margen de Utilidad
                            </p>
                        </div>
                        <p
                            class="text-2xl font-bold text-amber-700 dark:text-amber-300"
                        >
                            {{ profitMargin.toFixed(1) }}%
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
