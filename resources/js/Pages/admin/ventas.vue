<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { CalendarRange, Download, FileSpreadsheet, Search, TrendingDown, TrendingUp } from 'lucide-vue-next';
import DateFilter from '@/Components/DateFilter.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps<{
    movements: {
        data: any[];
        links: any[];
        current_page: number;
        last_page: number;
        prev_page_url: string | null;
        next_page_url: string | null;
    };
    summary: { total_entries: number; total_exits: number };
    filters: { search: string | null; type: string | null; from: string | null; to: string | null };
}>();

const filter = { ...props.filters };

const typeOptions = [
    { value: '', label: 'Todos' },
    { value: 'purchase', label: 'Compra' },
    { value: 'sale', label: 'Venta' },
    { value: 'return_in', label: 'Devolución entrada' },
    { value: 'return_out', label: 'Devolución salida' },
    { value: 'adjustment', label: 'Ajuste manual' },
    { value: 'damage', label: 'Merma' },
    { value: 'loss', label: 'Pérdida' },
    { value: 'transfer', label: 'Transferencia' },
];

const badgeColors: Record<string, string> = {
    blue: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
    red: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
    green: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
    orange: 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
    yellow: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
    purple: 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
    gray: 'bg-gray-100 text-gray-700 dark:bg-gray-900/30 dark:text-gray-400',
    indigo: 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400',
};

function cleanParams() {
    const p: Record<string, string> = {};
    if (filter.search) p.search = filter.search;
    if (filter.type) p.type = filter.type;
    if (filter.from) p.from = filter.from;
    if (filter.to) p.to = filter.to;
    return p;
}

function loadFilters() {
    router.get(route('admin.ventas.index'), cleanParams(), { preserveState: true, preserveScroll: true });
}

function clearFilters() {
    filter.search = null;
    filter.type = null;
    filter.from = null;
    filter.to = null;
    loadFilters();
}

function onFromPicked(payload: { dia: number; mes: number; anio: number }) {
    filter.from = `${payload.anio}-${String(payload.mes).padStart(2, '0')}-${String(payload.dia).padStart(2, '0')}`;
    loadFilters();
}

function onToPicked(payload: { dia: number; mes: number; anio: number }) {
    filter.to = `${payload.anio}-${String(payload.mes).padStart(2, '0')}-${String(payload.dia).padStart(2, '0')}`;
    loadFilters();
}

function referenceUrl(m: any): string | null {
    if (!m.reference_type || !m.reference_id) return null;
    if (m.reference_type.includes('\\Sale')) return route('admin.ventas.index') + '?search=' + m.reference_id;
    if (m.reference_type.includes('\\Purchase')) return route('admin.compras.index') + '?search=' + m.reference_id;
    if (m.reference_type.includes('\\Loss')) return route('admin.perdida.index') + '?search=' + m.reference_id;
    return null;
}

function referenceLabel(m: any): string {
    if (!m.reference_type || !m.reference_id) return '—';
    const base = m.reference_type.includes('\\Sale') ? 'Venta' :
        m.reference_type.includes('\\Purchase') ? 'Compra' :
        m.reference_type.includes('\\Loss') ? 'Pérdida' : 'Doc.';
    return `${base} #${m.reference_id}`;
}

function formatDate(d: string) {
    if (!d) return '—';
    const date = new Date(d);
    return date.toLocaleDateString('es-CO', { year: 'numeric', month: '2-digit', day: '2-digit' });
}

function formatTime(d: string) {
    if (!d) return '';
    return new Date(d).toLocaleTimeString('es-CO', { hour: '2-digit', minute: '2-digit' });
}
</script>

<template>
    <Head title="Historial de Movimientos de Inventario" />
    <AdminLayout>
        <template #title>
            <h1 class="font-display text-xl font-bold text-content-primary dark:text-white">
                Historial de Movimientos de Inventario
            </h1>
        </template>

        <div class="mb-6 overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-surface-dark">
            <div class="flex flex-wrap items-center gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                <Search class="h-5 w-5 text-primary-500" />
                <h2 class="font-bold text-content-primary dark:text-white">Filtros</h2>
                <span class="h-6 w-px bg-gray-200 dark:bg-gray-700"></span>

                <TextInput v-model="filter.search" placeholder="Buscar producto, SKU o notas..." class="min-w-[200px] flex-1" @keyup.enter="loadFilters" />
                <select v-model="filter.type" @change="loadFilters"
                    class="rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    <option v-for="opt in typeOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                </select>
                <DateFilter :model-value="filter.from" label="Desde" @select="onFromPicked" />
                <DateFilter :model-value="filter.to" label="Hasta" @select="onToPicked" />
                <button @click="clearFilters"
                    class="rounded-xl border border-gray-200 px-3 py-1.5 text-xs font-bold text-content-muted transition-colors hover:bg-gray-100 dark:border-gray-700 dark:hover:bg-gray-800">
                    Limpiar
                </button>
                <a :href="route('admin.ventas.export', cleanParams())"
                    class="ml-auto flex items-center gap-2 rounded-2xl bg-primary-500 px-4 py-2 text-sm font-bold text-white shadow-sm transition-colors hover:bg-primary-600">
                    <Download class="h-4 w-4" /> Exportar Excel
                </a>
            </div>
        </div>

        <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="flex items-center gap-4 rounded-3xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-surface-dark">
                <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-2xl bg-success/10">
                    <TrendingUp class="h-6 w-6 text-success" />
                </div>
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400">Entradas totales</p>
                    <p class="text-xl font-bold text-content-primary dark:text-white">{{ summary.total_entries.toLocaleString() }}</p>
                    <p class="text-[11px] text-content-muted">unidades</p>
                </div>
            </div>
            <div class="flex items-center gap-4 rounded-3xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-surface-dark">
                <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-2xl bg-danger/10">
                    <TrendingDown class="h-6 w-6 text-danger" />
                </div>
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400">Salidas totales</p>
                    <p class="text-xl font-bold text-content-primary dark:text-white">{{ Math.abs(summary.total_exits).toLocaleString() }}</p>
                    <p class="text-[11px] text-content-muted">unidades</p>
                </div>
            </div>
        </div>

        <div class="overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-surface-dark">
            <div class="flex flex-wrap items-center gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                <FileSpreadsheet class="h-5 w-5 text-primary-500" />
                <h2 class="flex-1 font-bold text-content-primary dark:text-white">Movimientos</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 text-xs uppercase tracking-wider text-content-muted dark:bg-gray-900/50 dark:text-gray-500">
                        <tr>
                            <th class="px-6 py-3 font-bold">#</th>
                            <th class="px-6 py-3 font-bold">Producto</th>
                            <th class="px-6 py-3 font-bold">SKU</th>
                            <th class="px-6 py-3 font-bold">Tipo</th>
                            <th class="px-6 py-3 text-right font-bold">Cantidad</th>
                            <th class="px-6 py-3 text-right font-bold">Stock Antes</th>
                            <th class="px-6 py-3 text-right font-bold">Stock Después</th>
                            <th class="px-6 py-3 font-bold">Documento</th>
                            <th class="px-6 py-3 font-bold">Notas</th>
                            <th class="px-6 py-3 font-bold">Fecha</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <tr v-if="!movements.data?.length">
                            <td colspan="10" class="px-6 py-12 text-center text-sm text-content-muted dark:text-gray-500">
                                No hay movimientos registrados.
                            </td>
                        </tr>
                        <tr v-for="m in movements.data" :key="m.id"
                            class="group transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="px-6 py-4 font-mono text-sm font-bold text-content-primary dark:text-white">#{{ m.id }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-content-primary dark:text-white">{{ m.product?.name || '—' }}</td>
                            <td class="px-6 py-4 text-sm text-content-secondary">{{ m.product?.sku || '—' }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-block rounded-full px-2.5 py-0.5 text-xs font-bold" :class="badgeColors[m.type_color] || badgeColors.gray">
                                    {{ m.type_name }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right text-sm font-bold" :class="m.quantity_change >= 0 ? 'text-success' : 'text-danger'">
                                {{ m.quantity_change_formatted }}
                            </td>
                            <td class="px-6 py-4 text-right text-sm text-content-secondary">{{ m.stock_before?.toLocaleString() }}</td>
                            <td class="px-6 py-4 text-right text-sm text-content-secondary">{{ m.stock_after?.toLocaleString() }}</td>
                            <td class="px-6 py-4 text-sm">
                                <a v-if="referenceUrl(m)" :href="referenceUrl(m)" class="font-medium text-primary-500 hover:text-primary-600 hover:underline">
                                    {{ referenceLabel(m) }}
                                </a>
                                <span v-else class="text-content-muted">{{ referenceLabel(m) }}</span>
                            </td>
                            <td class="max-w-[200px] truncate px-6 py-4 text-sm text-content-secondary" :title="m.notes || ''">{{ m.notes || '—' }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-content-secondary">
                                <div>{{ formatDate(m.created_at) }}</div>
                                <div class="text-[11px] text-content-muted">{{ formatTime(m.created_at) }}</div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="movements.last_page > 1"
                class="flex items-center justify-between border-t border-gray-100 px-6 py-4 text-sm text-content-muted dark:border-gray-800 dark:text-gray-500">
                <span>Página {{ movements.current_page }} de {{ movements.last_page }}</span>
                <div class="flex gap-2">
                    <a v-if="movements.prev_page_url" :href="movements.prev_page_url"
                        class="rounded-lg bg-gray-50 px-3 py-1.5 font-bold transition-colors hover:bg-gray-100 dark:bg-gray-900">←</a>
                    <a v-if="movements.next_page_url" :href="movements.next_page_url"
                        class="rounded-lg bg-gray-50 px-3 py-1.5 font-bold transition-colors hover:bg-gray-100 dark:bg-gray-900">→</a>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
