<script setup lang="ts">
import DateFilter from '@/Components/DateFilter.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { BarChart3 } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps<{
    registros: any[];
    filters: { mes?: string; anio?: string };
}>();

const todayStr = new Date().toISOString().slice(0, 10);
const filterDate = ref<string>(
    props.filters?.mes && props.filters?.anio
        ? `${props.filters.anio}-${String(props.filters.mes).padStart(2, '0')}-01`
        : todayStr,
);

function onDatePicked(payload: { dia: number; mes: number; anio: number }) {
    router.get(
        route('admin.z-mensual.index'),
        {
            mes: payload.mes,
            anio: payload.anio,
        },
        { preserveState: true, preserveScroll: true },
    );
}

function fmtFecha(dt: string) {
    const d = new Date(dt);
    return d.toLocaleDateString('es-CL', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        hour12: true,
    });
}

const fmt = (v: number | null) =>
    v !== null && v !== undefined ? v.toLocaleString('es-CL') : '—';

function saveRow(row: any) {
    const body: Record<string, any> = {};
    const pct = (row.red_compra_total ?? 0) - (row.red_compra_neto ?? 0);
    if (row.red_compra_neto !== undefined)
        body.red_compra_neto = row.red_compra_neto;
    if (pct !== (row.porcentaje_banco ?? 0)) body.porcentaje_banco = pct;
    if (row.observaciones !== undefined)
        body.observaciones = row.observaciones || null;

    fetch(route('admin.z-mensual.update', row.id), {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN':
                (
                    document.querySelector(
                        'meta[name="csrf-token"]',
                    ) as HTMLMetaElement
                )?.content || '',
        },
        body: JSON.stringify(body),
    }).catch(() => {});
}

const totales = computed(() => {
    const t = {
        esperado: 0,
        efectivo: 0,
        redNeto: 0,
        transfer: 0,
        redTotal: 0,
        pctBanco: 0,
        sobrante: 0,
        faltante: 0,
    };
    for (const r of props.registros) {
        t.esperado += r.esperado_caja ?? 0;
        t.efectivo += r.efectivo_neto ?? 0;
        t.redNeto += r.red_compra_neto ?? 0;
        t.transfer += r.transferencia ?? 0;
        t.redTotal += r.red_compra_total ?? 0;
        t.sobrante += r.sobrante ?? 0;
        t.faltante += r.faltante ?? 0;
    }
    t.pctBanco = t.redTotal - t.redNeto;
    return t;
});

function onRedNetoInput(e: Event, row: any) {
    const raw = (e.target as HTMLInputElement).value.replace(/\D/g, '');
    const num = raw ? parseInt(raw, 10) : null;
    row.red_compra_neto = num;
    (e.target as HTMLInputElement).value = num
        ? num.toLocaleString('es-CL')
        : '';
}
</script>

<template>
    <Head title="Z Mensual" />
    <AdminLayout>
        <div class="mx-auto max-w-7xl space-y-6 p-4 sm:p-6">
            <!-- Header -->
            <div class="flex items-center gap-3">
                <div
                    class="rounded-xl bg-primary-50 p-2.5 dark:bg-primary-900/20"
                >
                    <BarChart3
                        class="h-5 w-5 text-primary-600 dark:text-primary-400"
                    />
                </div>
                <div>
                    <h1
                        class="text-xl font-bold text-content-primary dark:text-white"
                    >
                        Z Mensual
                    </h1>
                    <p class="text-xs text-content-muted">
                        Control mensual de cierre Z
                    </p>
                </div>
            </div>

            <div
                class="flex flex-wrap items-end gap-3 rounded-xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-surface-dark"
            >
                <DateFilter
                    v-model="filterDate"
                    label="Mes / Año"
                    @select="onDatePicked"
                />
            </div>

            <!-- Table -->
            <div
                class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-surface-dark"
            >
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead
                            class="bg-gray-50 text-xs uppercase tracking-wider text-content-muted dark:bg-gray-900/50 dark:text-gray-500"
                        >
                            <tr>
                                <th
                                    class="whitespace-nowrap px-3 py-3 font-bold"
                                >
                                    Fecha
                                </th>
                                <th
                                    class="whitespace-nowrap px-3 py-3 font-bold"
                                >
                                    Cajero
                                </th>
                                <th
                                    class="whitespace-nowrap px-3 py-3 text-right font-bold"
                                >
                                    Esperado Caja
                                </th>
                                <th
                                    class="whitespace-nowrap px-3 py-3 text-right font-bold"
                                >
                                    Efectivo Neto
                                </th>
                                <th
                                    class="whitespace-nowrap px-3 py-3 text-right font-bold"
                                >
                                    Redcompra Neto
                                </th>
                                <th
                                    class="whitespace-nowrap px-3 py-3 text-right font-bold"
                                >
                                    Transferencia
                                </th>
                                <th
                                    class="whitespace-nowrap px-3 py-3 text-right font-bold"
                                >
                                    Redcompra Total
                                </th>
                                <th
                                    class="whitespace-nowrap px-3 py-3 text-right font-bold"
                                >
                                    % Banco
                                </th>
                                <th
                                    class="whitespace-nowrap px-3 py-3 text-right font-bold"
                                >
                                    Sobrante
                                </th>
                                <th
                                    class="whitespace-nowrap px-3 py-3 text-right font-bold"
                                >
                                    Faltante
                                </th>
                                <th
                                    class="whitespace-nowrap px-3 py-3 font-bold"
                                >
                                    Observaciones
                                </th>
                            </tr>
                        </thead>
                        <tbody
                            class="divide-y divide-gray-100 dark:divide-gray-800"
                        >
                            <tr
                                v-for="(row, i) in registros"
                                :key="row.id"
                                :class="
                                    i % 2 === 0
                                        ? 'bg-white dark:bg-transparent'
                                        : 'bg-gray-50/50 dark:bg-gray-900/20'
                                "
                            >
                                <!-- Fecha -->
                                <td
                                    class="whitespace-nowrap px-3 py-3 text-content-primary"
                                >
                                    {{ fmtFecha(row.fecha_apertura) }}
                                </td>
                                <!-- Cajero -->
                                <td
                                    class="whitespace-nowrap px-3 py-3 font-medium text-content-primary"
                                >
                                    {{ row.cajero }}
                                </td>
                                <!-- Esperado Caja -->
                                <td
                                    class="whitespace-nowrap px-3 py-3 text-right tabular-nums text-content-primary"
                                >
                                    {{ fmt(row.esperado_caja) }}
                                </td>
                                <!-- Efectivo Neto -->
                                <td
                                    class="whitespace-nowrap px-3 py-3 text-right tabular-nums text-content-primary"
                                >
                                    {{ fmt(row.efectivo_neto) }}
                                </td>
                                <!-- Redcompra Neto (editable) -->
                                <td
                                    class="whitespace-nowrap px-3 py-3 text-right"
                                >
                                    <input
                                        type="text"
                                        inputmode="numeric"
                                        :value="fmt(row.red_compra_neto)"
                                        @input="onRedNetoInput($event, row)"
                                        @blur="saveRow(row)"
                                        @keydown.enter.prevent="saveRow(row)"
                                        class="w-28 rounded-lg border border-gray-200 bg-gray-50 px-2 py-1.5 text-right text-sm tabular-nums text-content-primary transition-shadow focus:border-primary-500 focus:ring-1 focus:ring-primary-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                    />
                                </td>
                                <!-- Transferencia -->
                                <td
                                    class="whitespace-nowrap px-3 py-3 text-right tabular-nums text-content-primary"
                                >
                                    {{ fmt(row.transferencia) }}
                                </td>
                                <!-- Redcompra Total -->
                                <td
                                    class="whitespace-nowrap px-3 py-3 text-right tabular-nums text-content-primary"
                                >
                                    {{ fmt(row.red_compra_total) }}
                                </td>
                                <!-- % Banco (reactivo) -->
                                <td
                                    class="whitespace-nowrap px-3 py-3 text-right tabular-nums text-content-primary"
                                >
                                    {{
                                        fmt(
                                            (row.red_compra_total ?? 0) -
                                                (row.red_compra_neto ?? 0),
                                        )
                                    }}
                                </td>
                                <!-- Sobrante -->
                                <td
                                    class="whitespace-nowrap px-3 py-3 text-right tabular-nums"
                                    :class="
                                        (row.sobrante ?? 0) > 0
                                            ? 'text-emerald-600'
                                            : 'text-content-primary'
                                    "
                                >
                                    {{ fmt(row.sobrante) }}
                                </td>
                                <!-- Faltante -->
                                <td
                                    class="whitespace-nowrap px-3 py-3 text-right tabular-nums"
                                    :class="
                                        (row.faltante ?? 0) > 0
                                            ? 'text-red-600'
                                            : 'text-content-primary'
                                    "
                                >
                                    {{ fmt(row.faltante) }}
                                </td>
                                <!-- Observaciones (editable) -->
                                <td class="px-3 py-3">
                                    <input
                                        type="text"
                                        v-model="row.observaciones"
                                        @blur="saveRow(row)"
                                        @keydown.enter.prevent="saveRow(row)"
                                        class="w-36 rounded-lg border border-gray-200 bg-gray-50 px-2 py-1.5 text-sm text-content-primary transition-shadow focus:border-primary-500 focus:ring-1 focus:ring-primary-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                    />
                                </td>
                            </tr>
                            <tr v-if="!registros.length">
                                <td
                                    colspan="11"
                                    class="px-4 py-12 text-center text-sm text-content-muted"
                                >
                                    No hay registros para los filtros
                                    seleccionados.
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr
                                class="bg-primary-50 font-bold dark:bg-primary-900/10"
                            >
                                <td
                                    colspan="2"
                                    class="whitespace-nowrap px-3 py-3 text-content-primary dark:text-white"
                                >
                                    Totales
                                </td>
                                <td
                                    class="whitespace-nowrap px-3 py-3 text-right tabular-nums text-content-primary dark:text-white"
                                >
                                    {{ fmt(totales.esperado) }}
                                </td>
                                <td
                                    class="whitespace-nowrap px-3 py-3 text-right tabular-nums text-content-primary dark:text-white"
                                >
                                    {{ fmt(totales.efectivo) }}
                                </td>
                                <td
                                    class="whitespace-nowrap px-3 py-3 text-right tabular-nums text-content-primary dark:text-white"
                                >
                                    {{ fmt(totales.redNeto) }}
                                </td>
                                <td
                                    class="whitespace-nowrap px-3 py-3 text-right tabular-nums text-content-primary dark:text-white"
                                >
                                    {{ fmt(totales.transfer) }}
                                </td>
                                <td
                                    class="whitespace-nowrap px-3 py-3 text-right tabular-nums text-content-primary dark:text-white"
                                >
                                    {{ fmt(totales.redTotal) }}
                                </td>
                                <td
                                    class="whitespace-nowrap px-3 py-3 text-right tabular-nums text-content-primary dark:text-white"
                                >
                                    {{ fmt(totales.pctBanco) }}
                                </td>
                                <td
                                    class="whitespace-nowrap px-3 py-3 text-right tabular-nums text-emerald-600"
                                >
                                    {{ fmt(totales.sobrante) }}
                                </td>
                                <td
                                    class="whitespace-nowrap px-3 py-3 text-right tabular-nums text-red-600"
                                >
                                    {{ fmt(totales.faltante) }}
                                </td>
                                <td class="px-3 py-3"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
