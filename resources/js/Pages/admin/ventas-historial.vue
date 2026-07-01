<script setup lang="ts">
import DateFilter from '@/Components/DateFilter.vue';
import SalesHistoryModal from '@/Components/SalesHistoryModal.vue';
import TextInput from '@/Components/TextInput.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import {
    Clock,
    Download,
    Eye,
    Printer,
    Search,
    ShoppingCart,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface SaleItem {
    id: number;
    product: { name: string } | null;
    quantity: number;
    price: number;
    total_line: number;
}

interface SalePayment {
    id: number;
    method: string;
    amount: number;
}

interface SaleRow {
    id: number;
    total: number;
    net_total: number;
    tax_total: number;
    discount_total: number;
    cash_amount: number;
    card_amount: number;
    transfer_amount: number;
    created_at: string;
    cashier: { id: number; name: string } | null;
    items: SaleItem[];
    payments: SalePayment[];
}

const props = defineProps<{
    sales: {
        data: SaleRow[];
        links: any[];
        current_page: number;
        last_page: number;
        prev_page_url: string | null;
        next_page_url: string | null;
    };
    cashiers: { id: number; name: string }[];
    filters: {
        from: string | null;
        to: string | null;
        cashier_id: string | null;
        payment_method: string | null;
        search: string | null;
    };
}>();

const filter = {
    search: props.filters.search ?? '',
    from: props.filters.from ?? '',
    to: props.filters.to ?? '',
    cashier_id: props.filters.cashier_id ?? '',
    payment_method: props.filters.payment_method ?? '',
};

const paymentOptions = [
    { value: '', label: 'Todos' },
    { value: 'cash', label: 'Efectivo' },
    { value: 'card', label: 'Tarjeta' },
    { value: 'transfer', label: 'Transferencia' },
];

function cleanParams() {
    const p: Record<string, string> = {};
    if (filter.search) p.search = filter.search;
    if (filter.from) p.from = filter.from;
    if (filter.to) p.to = filter.to;
    if (filter.cashier_id) p.cashier_id = filter.cashier_id;
    if (filter.payment_method) p.payment_method = filter.payment_method;
    return p;
}

function loadFilters() {
    router.get(route('admin.ventas.index'), cleanParams(), {
        preserveState: true,
        preserveScroll: true,
    });
}

function clearFilters() {
    filter.search = '';
    filter.from = '';
    filter.to = '';
    filter.cashier_id = '';
    filter.payment_method = '';
    loadFilters();
}

function setQuickRange(label: string) {
    const now = new Date();
    const today = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`;

    if (label === 'Hoy') {
        filter.from = today;
        filter.to = today;
    } else if (label === 'Esta semana') {
        const mon = new Date(now);
        mon.setDate(
            now.getDate() - (now.getDay() === 0 ? 6 : now.getDay() - 1),
        );
        filter.from = `${mon.getFullYear()}-${String(mon.getMonth() + 1).padStart(2, '0')}-${String(mon.getDate()).padStart(2, '0')}`;
        filter.to = today;
    } else if (label === 'Este mes') {
        filter.from = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-01`;
        filter.to = today;
    }
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

function formatDate(d: string) {
    if (!d) return '—';
    return new Date(d).toLocaleDateString('es-CL', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
    });
}

function formatTime(d: string) {
    if (!d) return '';
    return new Date(d).toLocaleTimeString('es-CL', {
        hour: '2-digit',
        minute: '2-digit',
        hour12: false,
    });
}

function formatCLP(cents: number): string {
    return '$' + Math.round(cents / 100).toLocaleString('es-CL');
}

function paymentLabel(s: SaleRow): string {
    const methods: string[] = [];
    if (s.cash_amount > 0) methods.push('Efectivo');
    if (s.card_amount > 0) methods.push('Tarjeta');
    if (s.transfer_amount > 0) methods.push('Transferencia');
    return methods.join(' + ') || '—';
}

function paymentBadgeClass(s: SaleRow): string {
    if (s.card_amount > 0 && s.cash_amount === 0 && s.transfer_amount === 0)
        return 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400';
    if (s.cash_amount > 0 && s.card_amount === 0 && s.transfer_amount === 0)
        return 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400';
    if (s.transfer_amount > 0 && s.cash_amount === 0 && s.card_amount === 0)
        return 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400';
    return 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400';
}

function productsPreview(s: SaleRow): string {
    return s.items.map((i) => i.product?.name ?? '—').join(', ');
}

// ─── Modal Detail ────────────────────────────────────────

const showDetailModal = ref(false);
const detailSale = ref<any>(null);

interface TodaySaleItem {
    name: string;
    quantity: number;
    price: number;
    total: number;
}

interface TodaySalePayment {
    method: string;
    amount: number;
}

interface TodaySale {
    id: number;
    folio: number;
    time: string;
    total: number;
    net_total: number;
    tax_total: number;
    discount_total: number;
    items: TodaySaleItem[];
    payments: TodaySalePayment[];
    cash_amount: number;
    card_amount: number;
    transfer_amount: number;
    cashier_name: string;
    created_at: string;
}

function toTodaySale(s: SaleRow): TodaySale {
    return {
        id: s.id,
        folio: s.id,
        time: formatTime(s.created_at),
        total: s.total,
        net_total: s.net_total,
        tax_total: s.tax_total,
        discount_total: s.discount_total,
        items: s.items.map((i) => ({
            name: i.product?.name ?? '—',
            quantity: i.quantity,
            price: i.price,
            total: i.total_line,
        })),
        payments: s.payments.map((p) => ({
            method: p.method,
            amount: p.amount,
        })),
        cash_amount: s.cash_amount,
        card_amount: s.card_amount,
        transfer_amount: s.transfer_amount,
        cashier_name: s.cashier?.name ?? '—',
        created_at: s.created_at,
    };
}

function openDetail(s: SaleRow) {
    detailSale.value = toTodaySale(s);
    showDetailModal.value = true;
}

function closeDetail() {
    showDetailModal.value = false;
    detailSale.value = null;
}

function reprintTicket(saleId: number) {
    window.open(
        route('admin.pos.reprint', saleId),
        '_blank',
        'width=400,height=600',
    );
}

const totalSum = computed(() =>
    props.sales.data.reduce((sum, s) => sum + s.total, 0),
);
</script>

<template>
    <Head title="Historial de Ventas" />
    <AdminLayout>
        <template #title>
            <h1
                class="font-display text-xl font-bold text-content-primary dark:text-white"
            >
                Historial de Ventas
            </h1>
        </template>

        <!-- Filtros -->
        <div
            class="mb-6 overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-surface-dark"
        >
            <div
                class="flex flex-wrap items-center gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-800"
            >
                <Search class="h-5 w-5 text-primary-500" />
                <h2 class="font-bold text-content-primary dark:text-white">
                    Filtros
                </h2>
                <span class="h-6 w-px bg-gray-200 dark:bg-gray-700"></span>

                <TextInput
                    v-model="filter.search"
                    placeholder="Buscar por Folio..."
                    class="w-36 min-w-[120px]"
                    @keyup.enter="loadFilters"
                />

                <DateFilter
                    :model-value="filter.from"
                    label="Desde"
                    @select="onFromPicked"
                />
                <DateFilter
                    :model-value="filter.to"
                    label="Hasta"
                    @select="onToPicked"
                />

                <div class="flex gap-1">
                    <button
                        v-for="label in ['Hoy', 'Esta semana', 'Este mes']"
                        :key="label"
                        @click="setQuickRange(label)"
                        class="rounded-lg border border-gray-200 px-2.5 py-1.5 text-[11px] font-bold text-content-muted transition-colors hover:bg-gray-100 dark:border-gray-700 dark:hover:bg-gray-800"
                    >
                        {{ label }}
                    </button>
                </div>

                <select
                    v-model="filter.cashier_id"
                    @change="loadFilters"
                    class="rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                >
                    <option value="">Todos los cajeros</option>
                    <option v-for="c in cashiers" :key="c.id" :value="c.id">
                        {{ c.name }}
                    </option>
                </select>

                <select
                    v-model="filter.payment_method"
                    @change="loadFilters"
                    class="rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                >
                    <option
                        v-for="opt in paymentOptions"
                        :key="opt.value"
                        :value="opt.value"
                    >
                        {{ opt.label }}
                    </option>
                </select>

                <button
                    @click="clearFilters"
                    class="rounded-xl border border-gray-200 px-3 py-1.5 text-xs font-bold text-content-muted transition-colors hover:bg-gray-100 dark:border-gray-700 dark:hover:bg-gray-800"
                >
                    Limpiar
                </button>
            </div>
        </div>

        <!-- Summary -->
        <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div
                class="flex items-center gap-4 rounded-3xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-surface-dark"
            >
                <div
                    class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-2xl bg-primary-500/10"
                >
                    <ShoppingCart class="h-6 w-6 text-primary-500" />
                </div>
                <div>
                    <p
                        class="text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                    >
                        Ventas
                    </p>
                    <p
                        class="text-xl font-bold text-content-primary dark:text-white"
                    >
                        {{ sales.data.length }}
                    </p>
                    <p class="text-[11px] text-content-muted">en esta página</p>
                </div>
            </div>
            <div
                class="flex items-center gap-4 rounded-3xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-surface-dark"
            >
                <div
                    class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-2xl bg-emerald-500/10"
                >
                    <Download class="h-6 w-6 text-emerald-500" />
                </div>
                <div>
                    <p
                        class="text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                    >
                        Total filtrado
                    </p>
                    <p
                        class="text-xl font-bold text-content-primary dark:text-white"
                    >
                        {{ formatCLP(totalSum) }}
                    </p>
                    <p class="text-[11px] text-content-muted">
                        suma de esta página
                    </p>
                </div>
            </div>
        </div>

        <!-- Tabla -->
        <div
            class="overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-surface-dark"
        >
            <div
                class="flex items-center gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-800"
            >
                <Clock class="h-5 w-5 text-primary-500" />
                <h2
                    class="flex-1 font-bold text-content-primary dark:text-white"
                >
                    Ventas realizadas
                </h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead
                        class="bg-gray-50 text-xs uppercase tracking-wider text-content-muted dark:bg-gray-900/50 dark:text-gray-500"
                    >
                        <tr>
                            <th class="px-6 py-3 font-bold">Folio</th>
                            <th class="px-6 py-3 font-bold">Fecha / Hora</th>
                            <th class="px-6 py-3 font-bold">Cajero</th>
                            <th class="px-6 py-3 font-bold">Productos</th>
                            <th class="px-6 py-3 font-bold">Método de Pago</th>
                            <th class="px-6 py-3 text-right font-bold">
                                Total
                            </th>
                            <th class="px-6 py-3 text-right font-bold">
                                Descuento
                            </th>
                            <th class="px-6 py-3 text-center font-bold">
                                Acciones
                            </th>
                        </tr>
                    </thead>
                    <tbody
                        class="divide-y divide-gray-100 dark:divide-gray-800"
                    >
                        <tr v-if="!sales.data?.length">
                            <td
                                colspan="8"
                                class="px-6 py-12 text-center text-sm text-content-muted dark:text-gray-500"
                            >
                                No hay ventas registradas.
                            </td>
                        </tr>
                        <tr
                            v-for="s in sales.data"
                            :key="s.id"
                            class="group cursor-pointer transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/50"
                            @click="openDetail(s)"
                        >
                            <td
                                class="px-6 py-4 font-mono text-sm font-bold text-content-primary dark:text-white"
                            >
                                #{{ s.id }}
                            </td>
                            <td
                                class="whitespace-nowrap px-6 py-4 text-sm text-content-secondary"
                            >
                                <div>{{ formatDate(s.created_at) }}</div>
                                <div class="text-[11px] text-content-muted">
                                    {{ formatTime(s.created_at) }}
                                </div>
                            </td>
                            <td
                                class="px-6 py-4 text-sm text-content-primary dark:text-white"
                            >
                                {{ s.cashier?.name || '—' }}
                            </td>
                            <td
                                class="max-w-[220px] truncate px-6 py-4 text-sm text-content-secondary"
                                :title="productsPreview(s)"
                            >
                                {{ productsPreview(s) }}
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    class="inline-block rounded-full px-2.5 py-0.5 text-[10px] font-bold"
                                    :class="paymentBadgeClass(s)"
                                >
                                    {{ paymentLabel(s) }}
                                </span>
                            </td>
                            <td
                                class="px-6 py-4 text-right text-sm font-bold tabular-nums text-content-primary dark:text-white"
                            >
                                {{ formatCLP(s.total) }}
                            </td>
                            <td
                                class="px-6 py-4 text-right text-sm font-bold tabular-nums"
                                :class="
                                    s.discount_total > 0
                                        ? 'text-red-500'
                                        : 'text-content-muted'
                                "
                            >
                                {{
                                    s.discount_total > 0
                                        ? formatCLP(s.discount_total)
                                        : '—'
                                }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div
                                    class="flex items-center justify-center gap-2"
                                    @click.stop
                                >
                                    <button
                                        @click="openDetail(s)"
                                        class="rounded-lg p-1.5 text-content-muted transition-colors hover:bg-gray-100 hover:text-primary-500 dark:hover:bg-gray-800"
                                        title="Ver detalle"
                                    >
                                        <Eye class="h-4 w-4" />
                                    </button>
                                    <button
                                        @click="reprintTicket(s.id)"
                                        class="rounded-lg p-1.5 text-content-muted transition-colors hover:bg-gray-100 hover:text-emerald-500 dark:hover:bg-gray-800"
                                        title="Reimprimir ticket"
                                    >
                                        <Printer class="h-4 w-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div
                v-if="sales.last_page > 1"
                class="flex items-center justify-between border-t border-gray-100 px-6 py-4 text-sm text-content-muted dark:border-gray-800 dark:text-gray-500"
            >
                <span
                    >Página {{ sales.current_page }} de
                    {{ sales.last_page }}</span
                >
                <div class="flex gap-2">
                    <a
                        v-if="sales.prev_page_url"
                        :href="sales.prev_page_url"
                        class="rounded-lg bg-gray-50 px-3 py-1.5 font-bold transition-colors hover:bg-gray-100 dark:bg-gray-900"
                        >←</a
                    >
                    <a
                        v-if="sales.next_page_url"
                        :href="sales.next_page_url"
                        class="rounded-lg bg-gray-50 px-3 py-1.5 font-bold transition-colors hover:bg-gray-100 dark:bg-gray-900"
                        >→</a
                    >
                </div>
            </div>
        </div>

        <!-- Modal de detalle -->
        <SalesHistoryModal
            :show="showDetailModal"
            :sales="detailSale ? [detailSale] : null"
            hide-list
            @close="closeDetail"
            @reprint="reprintTicket"
        />
    </AdminLayout>
</template>
