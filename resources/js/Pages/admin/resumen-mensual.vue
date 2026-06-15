<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import {
    CalendarRange,
    DollarSign,
    TrendingDown,
    TrendingUp,
} from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    resumen: {
        year: number;
        month: number;
        month_name: string;
        total_efectivo: number;
        total_tarjeta: number;
        total_mercado_pago: number;
        ingreso_bruto: number;
        total_gastos: number;
        total_perdidas: number;
        utilidad_operativa: number;
    };
    available_years: number[];
}>();

const selectedYear = ref(props.resumen.year);
const selectedMonth = ref(props.resumen.month);

function loadSummary() {
    router.get(
        route('admin.resumen-mensual.index'),
        {
            year: selectedYear.value,
            month: selectedMonth.value,
        },
        { preserveState: true, preserveScroll: true },
    );
}

const months = [
    'Enero',
    'Febrero',
    'Marzo',
    'Abril',
    'Mayo',
    'Junio',
    'Julio',
    'Agosto',
    'Septiembre',
    'Octubre',
    'Noviembre',
    'Diciembre',
];

const fmt = (v: number) =>
    '$' + v.toLocaleString('es-CO', { minimumFractionDigits: 0 });
</script>

<template>
    <Head title="Resumen Mensual" />
    <AdminLayout>
        <template #title>
            <h1
                class="font-display text-xl font-bold text-content-primary dark:text-white"
            >
                Resumen Mensual
            </h1>
        </template>

        <div
            class="mb-6 overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-surface-dark"
        >
            <div
                class="flex flex-wrap items-center gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-800"
            >
                <CalendarRange class="h-5 w-5 text-primary-500" />
                <h2 class="font-bold text-content-primary dark:text-white">
                    Período
                </h2>
                <select
                    v-model="selectedMonth"
                    @change="loadSummary"
                    class="rounded-xl border border-gray-200 bg-gray-50 py-2 pl-3 pr-8 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                >
                    <option v-for="(m, i) in months" :key="i" :value="i + 1">
                        {{ m }}
                    </option>
                </select>
                <select
                    v-model="selectedYear"
                    @change="loadSummary"
                    class="rounded-xl border border-gray-200 bg-gray-50 py-2 pl-3 pr-8 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                >
                    <option v-for="y in available_years" :key="y" :value="y">
                        {{ y }}
                    </option>
                </select>
                <span
                    class="ml-2 text-sm font-bold text-content-primary dark:text-white"
                    >{{ resumen.month_name }} {{ resumen.year }}</span
                >
            </div>
        </div>

        <div class="mb-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <div
                class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-surface-dark"
            >
                <div class="mb-4 flex items-center gap-3">
                    <div
                        class="rounded-2xl bg-green-50 p-3 text-success dark:bg-green-900/20"
                    >
                        <TrendingUp class="h-6 w-6" />
                    </div>
                </div>
                <p
                    class="text-sm font-semibold text-content-secondary dark:text-gray-400"
                >
                    Ingreso Bruto
                </p>
                <h3
                    class="mt-1 font-display text-2xl font-extrabold text-content-primary dark:text-white"
                >
                    {{ fmt(resumen.ingreso_bruto) }}
                </h3>
            </div>
            <div
                class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-surface-dark"
            >
                <div class="mb-4 flex items-center gap-3">
                    <div
                        class="rounded-2xl bg-red-50 p-3 text-danger dark:bg-red-900/20"
                    >
                        <TrendingDown class="h-6 w-6" />
                    </div>
                </div>
                <p
                    class="text-sm font-semibold text-content-secondary dark:text-gray-400"
                >
                    Total Gastos
                </p>
                <h3
                    class="mt-1 font-display text-2xl font-extrabold text-content-primary dark:text-white"
                >
                    {{ fmt(resumen.total_gastos) }}
                </h3>
            </div>
            <div
                class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-surface-dark"
            >
                <div class="mb-4 flex items-center gap-3">
                    <div
                        class="rounded-2xl bg-amber-50 p-3 text-amber-500 dark:bg-amber-900/20"
                    >
                        <TrendingDown class="h-6 w-6" />
                    </div>
                </div>
                <p
                    class="text-sm font-semibold text-content-secondary dark:text-gray-400"
                >
                    Total Pérdidas
                </p>
                <h3
                    class="mt-1 font-display text-2xl font-extrabold text-content-primary dark:text-white"
                >
                    {{ fmt(resumen.total_perdidas) }}
                </h3>
            </div>
            <div
                class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-surface-dark"
            >
                <div class="mb-4 flex items-center gap-3">
                    <div
                        class="rounded-2xl bg-primary-50 p-3 text-primary-500 dark:bg-primary-900/20"
                    >
                        <DollarSign class="h-6 w-6" />
                    </div>
                </div>
                <p
                    class="text-sm font-semibold text-content-secondary dark:text-gray-400"
                >
                    Utilidad Operativa
                </p>
                <h3
                    class="mt-1 font-display text-2xl font-extrabold"
                    :class="
                        resumen.utilidad_operativa >= 0
                            ? 'text-success'
                            : 'text-danger'
                    "
                >
                    {{ fmt(resumen.utilidad_operativa) }}
                </h3>
            </div>
        </div>

        <div
            class="overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-surface-dark"
        >
            <div
                class="border-b border-gray-100 px-6 py-4 dark:border-gray-800"
            >
                <h3 class="font-bold text-content-primary dark:text-white">
                    Desglose de Ingresos
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead
                        class="bg-gray-50 text-xs uppercase tracking-wider text-content-muted dark:bg-gray-900/50 dark:text-gray-500"
                    >
                        <tr>
                            <th class="px-6 py-3 font-bold">Fuente</th>
                            <th class="px-6 py-3 text-right font-bold">
                                Monto
                            </th>
                            <th class="px-6 py-3 text-right font-bold">
                                % del Total
                            </th>
                        </tr>
                    </thead>
                    <tbody
                        class="divide-y divide-gray-100 dark:divide-gray-800"
                    >
                        <tr
                            class="transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/50"
                        >
                            <td
                                class="px-6 py-4 text-sm font-medium text-content-primary dark:text-white"
                            >
                                Efectivo
                            </td>
                            <td
                                class="px-6 py-4 text-right text-sm font-medium text-amber-600 dark:text-amber-400"
                            >
                                {{ fmt(resumen.total_efectivo) }}
                            </td>
                            <td
                                class="px-6 py-4 text-right text-sm text-content-secondary"
                            >
                                {{
                                    resumen.ingreso_bruto > 0
                                        ? (
                                              (resumen.total_efectivo /
                                                  resumen.ingreso_bruto) *
                                              100
                                          ).toFixed(1)
                                        : 0
                                }}%
                            </td>
                        </tr>
                        <tr
                            class="transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/50"
                        >
                            <td
                                class="px-6 py-4 text-sm font-medium text-content-primary dark:text-white"
                            >
                                Tarjeta (Z)
                            </td>
                            <td
                                class="px-6 py-4 text-right text-sm font-medium text-blue-600 dark:text-blue-400"
                            >
                                {{ fmt(resumen.total_tarjeta) }}
                            </td>
                            <td
                                class="px-6 py-4 text-right text-sm text-content-secondary"
                            >
                                {{
                                    resumen.ingreso_bruto > 0
                                        ? (
                                              (resumen.total_tarjeta /
                                                  resumen.ingreso_bruto) *
                                              100
                                          ).toFixed(1)
                                        : 0
                                }}%
                            </td>
                        </tr>
                        <tr
                            class="transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/50"
                        >
                            <td
                                class="px-6 py-4 text-sm font-medium text-content-primary dark:text-white"
                            >
                                Mercado Pago
                            </td>
                            <td
                                class="px-6 py-4 text-right text-sm font-medium text-accent-600 dark:text-accent-400"
                            >
                                {{ fmt(resumen.total_mercado_pago) }}
                            </td>
                            <td
                                class="px-6 py-4 text-right text-sm text-content-secondary"
                            >
                                {{
                                    resumen.ingreso_bruto > 0
                                        ? (
                                              (resumen.total_mercado_pago /
                                                  resumen.ingreso_bruto) *
                                              100
                                          ).toFixed(1)
                                        : 0
                                }}%
                            </td>
                        </tr>
                    </tbody>
                    <tfoot
                        class="bg-gray-50 text-xs uppercase tracking-wider dark:bg-gray-900/50"
                    >
                        <tr>
                            <th
                                class="px-6 py-4 font-bold text-content-primary dark:text-white"
                            >
                                Total
                            </th>
                            <th
                                class="px-6 py-4 text-right font-bold text-primary-500"
                            >
                                {{ fmt(resumen.ingreso_bruto) }}
                            </th>
                            <th
                                class="px-6 py-4 text-right font-bold text-content-muted"
                            >
                                100%
                            </th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>
