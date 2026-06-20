<script setup lang="ts">
import { formatDate, formatTime } from '@/helpers/format';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DateFilter from '@/Components/DateFilter.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import {
    CalendarRange,
    Check,
    DollarSign,
    Eye,
    Plus,
    ShoppingCart,
    Trash2,
    X,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

const props = defineProps<{
    sales: {
        data: any[];
        links: any[];
        current_page: number;
        last_page: number;
        prev_page_url: string | null;
        next_page_url: string | null;
    };
    products: any[];
    cashiers: any[];
    from: string | null;
    to: string | null;
    summary: { total: number; count: number };
}>();

const showForm = ref(false);
const items = ref<{ product_id: number | null; quantity: number }[]>([
    { product_id: null, quantity: 1 },
]);
const rowFilters = ref<string[]>(['']);
const activeDropdown = ref<number | null>(null);

const payments = ref<{ method: string; amount: number }[]>([
    { method: 'cash', amount: 0 },
]);
const showDetail = ref(false);
const detailSale = ref<any>(null);
const filterFrom = ref(props.from ?? '');
const filterTo = ref(props.to ?? '');

function loadMonth() {
    router.get(
        route('admin.ventas.index'),
        {
            from: filterFrom.value || null,
            to: filterTo.value || null,
        },
        { preserveState: true, preserveScroll: true },
    );
}

function onFromPicked(payload: { dia: number; mes: number; anio: number }) {
    const m = String(payload.mes).padStart(2, '0');
    const d = String(payload.dia).padStart(2, '0');
    filterFrom.value = `${payload.anio}-${m}-${d}`;
    loadMonth();
}

function onToPicked(payload: { dia: number; mes: number; anio: number }) {
    const m = String(payload.mes).padStart(2, '0');
    const d = String(payload.dia).padStart(2, '0');
    filterTo.value = `${payload.anio}-${m}-${d}`;
    loadMonth();
}

function viewDetail(sale: any) {
    detailSale.value = sale;
    showDetail.value = true;
}

const form = useForm({
    date: new Date().toISOString().split('T')[0],
    cashier_id: null as number | null,
    payment_method: 'cash',
});

const total = computed(() =>
    items.value.reduce((sum, item) => {
        const p = props.products.find((p) => p.id === item.product_id);
        return sum + (p ? (p.price / 100) * item.quantity : 0);
    }, 0),
);

const totalPayments = computed(() =>
    payments.value.reduce((sum, p) => sum + (Number(p.amount) || 0), 0),
);

const remaining = computed(() => total.value - totalPayments.value);
const isBalanced = computed(
    () => Math.abs(remaining.value) < 0.01 && total.value > 0,
);

const methodLabels: Record<string, string> = {
    cash: 'Efectivo',
    card: 'Tarjeta',
    transfer: 'Transferencia',
};

function addPaymentRow() {
    payments.value.push({ method: 'cash', amount: 0 });
}

function removePaymentRow(index: number) {
    if (payments.value.length > 1) {
        payments.value.splice(index, 1);
    }
}

function resetPayments() {
    if (form.payment_method === 'mixed') {
        payments.value = [{ method: 'cash', amount: 0 }];
    } else {
        payments.value = [{ method: form.payment_method, amount: 0 }];
    }
}

watch(() => form.payment_method, resetPayments);

watch(
    () => items.value.length,
    (len) => {
        while (rowFilters.value.length < len) rowFilters.value.push('');
        if (rowFilters.value.length > len) rowFilters.value.splice(len);
    },
    { immediate: true },
);

function openNew() {
    form.reset();
    form.date = new Date().toISOString().split('T')[0];
    items.value = [{ product_id: null, quantity: 1 }];
    rowFilters.value = [''];
    payments.value = [{ method: 'cash', amount: 0 }];
    showForm.value = true;
}

function closeForm() {
    showForm.value = false;
    form.reset();
}

function addItem() {
    items.value.push({ product_id: null, quantity: 1 });
    rowFilters.value.push('');
}

function removeItem(index: number) {
    items.value.splice(index, 1);
    rowFilters.value.splice(index, 1);
}

function rowFiltered(index: number) {
    const q = (rowFilters.value[index] || '').toLowerCase();
    if (!q) return props.products;
    return props.products.filter(
        (p) =>
            p.name.toLowerCase().includes(q) || p.sku.toLowerCase().includes(q),
    );
}

function selectProduct(index: number, product: any) {
    items.value[index].product_id = product.id;
    rowFilters.value[index] = product.name;
    activeDropdown.value = null;
}

function onRowInput(index: number) {
    if (items.value[index].product_id !== null) {
        items.value[index].product_id = null;
    }
    activeDropdown.value = index;
}

function handleBlur(index: number) {
    setTimeout(() => {
        if (activeDropdown.value === index) activeDropdown.value = null;
    }, 200);
}

function closeDropdown() {
    activeDropdown.value = null;
}

function submitForm() {
    const finalPayments =
        form.payment_method === 'mixed'
            ? payments.value
            : [
                  {
                      method: form.payment_method,
                      amount: Math.round(total.value * 100) / 100,
                  },
              ];

    if (form.payment_method === 'mixed' && !isBalanced.value) {
        const falta =
            remaining.value > 0
                ? `Faltan $${remaining.value.toFixed(0)}`
                : `Sobran $${Math.abs(remaining.value).toFixed(0)}`;
        alert(`Los pagos no cubren el total. ${falta}`);
        return;
    }

    router.post(
        route('admin.ventas.store'),
        {
            date: form.date,
            cashier_id: form.cashier_id,
            payment_method: form.payment_method,
            items: items.value,
            payments: finalPayments,
        },
        { onSuccess: closeForm },
    );
}

function deleteSale(id: number) {
    if (!confirm('¿Eliminar esta venta?')) return;
    router.delete(route('admin.ventas.destroy', id), { preserveScroll: true });
}

const fmt = (v: number) =>
    '$' + (v / 100).toLocaleString('es-CO', { minimumFractionDigits: 0 });
</script>

<template>
    <Head title="Ventas" />
    <AdminLayout>
        <template #title>
            <h1
                class="font-display text-xl font-bold text-content-primary dark:text-white"
            >
                Ventas
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

                <span
                    class="h-6 w-px bg-gray-200 dark:bg-gray-700"
                ></span>

                <DateFilter
                    v-model="filterFrom"
                    label="Desde"
                    @select="onFromPicked"
                />
                <DateFilter
                    v-model="filterTo"
                    label="Hasta"
                    @select="onToPicked"
                />
                <button
                    @click="filterFrom = ''; filterTo = ''; loadMonth()"
                    class="rounded-xl border border-gray-200 px-3 py-1.5 text-xs font-bold text-content-muted transition-colors hover:bg-gray-100 dark:border-gray-700 dark:hover:bg-gray-800"
                >
                    Limpiar
                </button>
            </div>
        </div>

        <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div
                class="flex items-center gap-4 rounded-3xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-surface-dark"
            >
                <div
                    class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-2xl bg-primary-500/10"
                >
                    <DollarSign class="h-6 w-6 text-primary-500" />
                </div>
                <div>
                    <p
                        class="text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                    >
                        Total Ventas
                    </p>
                    <p
                        class="text-xl font-bold text-content-primary dark:text-white"
                    >
                        {{ fmt(summary.total) }}
                    </p>
                    <p class="text-[11px] text-content-muted">
                        {{ summary.count }}
                        {{ summary.count === 1 ? 'venta' : 'ventas' }}
                    </p>
                </div>
            </div>
            <div
                class="flex items-center gap-4 rounded-3xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-surface-dark"
            >
                <div
                    class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-2xl bg-success/10"
                >
                    <ShoppingCart class="h-6 w-6 text-success" />
                </div>
                <div>
                    <p
                        class="text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                    >
                        Productos Vendidos
                    </p>
                    <p
                        class="text-xl font-bold text-content-primary dark:text-white"
                    >
                        {{
                            sales.data.reduce(
                                (s: number, sale: any) =>
                                    s +
                                    (sale.items?.reduce(
                                        (si: number, item: any) =>
                                            si + item.quantity,
                                        0,
                                    ) || 0),
                                0,
                            )
                        }}
                    </p>
                </div>
            </div>
        </div>

        <div
            class="overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-surface-dark"
        >
            <div
                class="flex flex-wrap items-center gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-800"
            >
                <ShoppingCart class="h-5 w-5 text-primary-500" />
                <h2
                    class="flex-1 font-bold text-content-primary dark:text-white"
                >
                    Historial de Ventas
                </h2>
                <button
                    @click="openNew"
                    class="flex items-center gap-2 rounded-2xl bg-primary-500 px-4 py-2 text-sm font-bold text-white shadow-sm transition-colors hover:bg-primary-600"
                >
                    <Plus class="h-4 w-4" /> Registrar Venta Manual
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead
                        class="bg-gray-50 text-xs uppercase tracking-wider text-content-muted dark:bg-gray-900/50 dark:text-gray-500"
                    >
                        <tr>
                            <th class="px-6 py-3 font-bold">#</th>
                            <th class="px-6 py-3 font-bold">Fecha</th>
                            <th class="px-6 py-3 font-bold">Hora</th>
                            <th class="px-6 py-3 font-bold">Cajero</th>
                            <th class="px-6 py-3 font-bold">Método Pago</th>
                            <th class="px-6 py-3 text-right font-bold">
                                Items
                            </th>
                            <th class="px-6 py-3 text-right font-bold">
                                Total
                            </th>
                            <th class="px-6 py-3 text-right font-bold">
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
                            v-for="sale in sales.data"
                            :key="sale.id"
                            class="group transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/50"
                        >
                            <td
                                class="px-6 py-4 font-mono text-sm font-bold text-content-primary dark:text-white"
                            >
                                #{{ sale.id }}
                            </td>
                            <td
                                class="px-6 py-4 text-sm text-content-primary dark:text-white"
                            >
                                {{ formatDate(sale.date || sale.created_at) }}
                            </td>
                            <td
                                class="px-6 py-4 text-sm text-content-secondary"
                            >
                                {{ formatTime(sale.created_at) }}
                            </td>
                            <td
                                class="px-6 py-4 text-sm font-medium text-content-primary dark:text-white"
                            >
                                {{ sale.cashier?.name || '—' }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1">
                                    <span
                                        v-for="p in sale.payments"
                                        :key="p.id"
                                        class="rounded-lg bg-gray-100 px-2.5 py-1 text-xs font-bold capitalize text-content-secondary dark:bg-gray-800"
                                    >
                                        {{ methodLabels[p.method] || p.method }}
                                    </span>
                                    <span
                                        v-if="!sale.payments?.length"
                                        class="rounded-lg bg-gray-100 px-2.5 py-1 text-xs font-bold text-content-secondary dark:bg-gray-800"
                                    >
                                        —
                                    </span>
                                </div>
                            </td>
                            <td
                                class="px-6 py-4 text-right text-sm text-content-secondary"
                            >
                                {{ sale.items?.length || 0 }}
                            </td>
                            <td
                                class="px-6 py-4 text-right text-sm font-bold text-primary-500"
                            >
                                {{ fmt(sale.total || 0) }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div
                                    class="flex items-center justify-end gap-2 opacity-0 transition-opacity group-hover:opacity-100"
                                >
                                    <button
                                        @click="viewDetail(sale)"
                                        class="rounded-xl p-2 text-blue-500 transition-colors hover:bg-blue-50 dark:hover:bg-blue-900/20"
                                        title="Ver detalle"
                                    >
                                        <Eye class="h-4 w-4" />
                                    </button>
                                    <button
                                        @click="deleteSale(sale.id)"
                                        class="rounded-xl p-2 text-danger transition-colors hover:bg-red-50 dark:hover:bg-red-900/20"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

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

        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="showForm"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4 backdrop-blur-sm"
            >
                <div
                    class="relative max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-3xl bg-white p-6 shadow-xl dark:bg-surface-dark"
                >
                    <div class="mb-6 flex items-center justify-between">
                        <h3
                            class="font-display text-lg font-bold text-content-primary dark:text-white"
                        >
                            Registrar Venta Manual
                        </h3>
                        <button
                            @click="closeForm"
                            class="rounded-xl p-2 transition-colors hover:bg-gray-100 dark:hover:bg-gray-800"
                        >
                            <X class="h-5 w-5 text-content-muted" />
                        </button>
                    </div>
                    <form @submit.prevent="submitForm" class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                                    >Fecha</label
                                >
                                <input
                                    v-model="form.date"
                                    type="date"
                                    required
                                    class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                />
                            </div>
                            <div>
                                <label
                                    class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                                    >Método Pago</label
                                >
                                <select
                                    v-model="form.payment_method"
                                    required
                                    class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm capitalize text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                >
                                    <option value="cash">Efectivo</option>
                                    <option value="card">Tarjeta</option>
                                    <option value="transfer">
                                        Transferencia
                                    </option>
                                    <option value="mixed">Mixto</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label
                                class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                                >Cajero</label
                            >
                            <select
                                v-model="form.cashier_id"
                                required
                                class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                            >
                                <option :value="null" disabled>
                                    Seleccionar cajero...
                                </option>
                                <option
                                    v-for="c in cashiers"
                                    :key="c.id"
                                    :value="c.id"
                                >
                                    {{ c.name }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <div class="mb-2 flex items-center justify-between">
                                <label
                                    class="block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                                    >Productos</label
                                >
                                <button
                                    type="button"
                                    @click="addItem"
                                    class="text-xs font-bold text-primary-500 hover:text-primary-600"
                                >
                                    + Agregar
                                </button>
                            </div>
                            <div
                                v-for="(item, idx) in items"
                                :key="idx"
                                class="mb-2 flex items-center gap-2 rounded-2xl bg-gray-50 p-3 dark:bg-gray-900"
                            >
                                <div class="relative min-w-[200px] flex-1">
                                    <input
                                        v-model="rowFilters[idx]"
                                        @input="onRowInput(idx)"
                                        @focus="activeDropdown = idx"
                                        @blur="handleBlur(idx)"
                                        type="text"
                                        placeholder="Buscar producto..."
                                        class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-content-primary dark:border-gray-700 dark:bg-surface-dark dark:text-white"
                                    />
                                    <div
                                        v-if="activeDropdown === idx"
                                        class="absolute left-0 right-0 top-full z-20 mt-1 max-h-48 overflow-y-auto rounded-xl border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-surface-dark"
                                    >
                                        <button
                                            v-for="p in rowFiltered(idx)"
                                            :key="p.id"
                                            @mousedown.prevent="
                                                selectProduct(idx, p)
                                            "
                                            class="flex w-full items-center gap-2 border-b border-gray-100 px-3 py-2 text-left text-sm text-content-primary last:border-0 hover:bg-gray-50 dark:border-gray-800 dark:text-white dark:hover:bg-gray-800"
                                        >
                                            <span class="font-medium">{{
                                                p.name
                                            }}</span>
                                            <span
                                                class="text-xs text-content-muted"
                                                >({{ p.sku }})</span
                                            >
                                            <span
                                                class="ml-auto text-xs"
                                                :class="
                                                    p.stock < 5
                                                        ? 'text-danger'
                                                        : 'text-content-muted'
                                                "
                                                >Stock: {{ p.stock }}</span
                                            >
                                        </button>
                                        <div
                                            v-if="!rowFiltered(idx).length"
                                            class="px-3 py-2 text-center text-sm text-content-muted"
                                        >
                                            Sin resultados
                                        </div>
                                    </div>
                                </div>
                                <input
                                    v-model.number="item.quantity"
                                    type="number"
                                    min="1"
                                    required
                                    class="w-20 rounded-xl border border-gray-200 bg-white px-3 py-2 text-center text-sm text-content-primary dark:border-gray-700 dark:bg-surface-dark dark:text-white"
                                />
                                <button
                                    type="button"
                                    @click="removeItem(idx)"
                                    v-if="items.length > 1"
                                    class="rounded-lg p-1.5 text-danger transition-colors hover:bg-red-50 dark:hover:bg-red-900/20"
                                >
                                    <X class="h-4 w-4" />
                                </button>
                            </div>
                        </div>

                        <div v-if="form.payment_method === 'mixed'">
                            <hr class="border-gray-200 dark:border-gray-700" />
                            <div class="pt-3">
                                <div
                                    class="mb-2 flex items-center justify-between"
                                >
                                    <label
                                        class="block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                                        >Desglose de Pagos</label
                                    >
                                    <button
                                        type="button"
                                        @click="addPaymentRow"
                                        class="text-xs font-bold text-primary-500 hover:text-primary-600"
                                    >
                                        + Agregar método
                                    </button>
                                </div>
                                <div class="space-y-2">
                                    <div
                                        v-for="(payment, i) in payments"
                                        :key="i"
                                        class="flex items-center gap-2"
                                    >
                                        <select
                                            v-model="payment.method"
                                            class="flex-1 rounded-xl border border-gray-200 bg-white px-2.5 py-2 text-sm text-content-primary dark:border-gray-700 dark:bg-surface-dark dark:text-white"
                                        >
                                            <option value="cash">
                                                Efectivo
                                            </option>
                                            <option value="card">
                                                Tarjeta
                                            </option>
                                            <option value="transfer">
                                                Transferencia
                                            </option>
                                        </select>
                                        <div class="relative flex-1">
                                            <span
                                                class="absolute left-2.5 top-1/2 -translate-y-1/2 text-xs font-bold text-content-muted"
                                                >$</span
                                            >
                                            <input
                                                v-model.number="payment.amount"
                                                type="number"
                                                min="0"
                                                step="100"
                                                class="w-full rounded-xl border border-gray-200 bg-white py-2 pl-7 pr-3 text-right text-sm font-bold text-content-primary dark:border-gray-700 dark:bg-surface-dark dark:text-white"
                                                placeholder="0"
                                            />
                                        </div>
                                        <button
                                            type="button"
                                            v-if="payments.length > 1"
                                            @click="removePaymentRow(i)"
                                            class="flex-shrink-0 rounded-lg p-1.5 text-danger transition-colors hover:bg-red-50 dark:hover:bg-red-900/20"
                                        >
                                            <X class="h-4 w-4" />
                                        </button>
                                    </div>
                                </div>
                                <div
                                    v-if="total > 0"
                                    class="mt-2 rounded-2xl px-3 py-2 text-center text-xs font-bold"
                                    :class="
                                        isBalanced
                                            ? 'bg-success/10 text-success'
                                            : remaining > 0
                                              ? 'bg-warning/10 text-warning'
                                              : 'bg-danger/10 text-danger'
                                    "
                                >
                                    <template v-if="isBalanced"
                                        >✓ Total:
                                        {{ fmt(Math.round(total * 100)) }} —
                                        Pagos completos</template
                                    >
                                    <template v-else-if="remaining > 0"
                                        >Total:
                                        {{ fmt(Math.round(total * 100)) }} —
                                        Faltan
                                        {{
                                            fmt(Math.round(remaining * 100))
                                        }}</template
                                    >
                                    <template v-else
                                        >Total:
                                        {{ fmt(Math.round(total * 100)) }} —
                                        Sobran
                                        {{
                                            fmt(
                                                Math.round(
                                                    Math.abs(remaining) * 100,
                                                ),
                                            )
                                        }}</template
                                    >
                                </div>
                            </div>
                        </div>

                        <div class="flex gap-3 pt-2">
                            <button
                                type="button"
                                @click="closeForm"
                                class="flex-1 rounded-2xl border border-gray-200 py-2.5 text-sm font-bold text-content-secondary transition-colors hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800"
                            >
                                Cancelar
                            </button>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="flex flex-1 items-center justify-center gap-2 rounded-2xl bg-primary-500 py-2.5 text-sm font-bold text-white shadow-sm transition-colors hover:bg-primary-600"
                            >
                                <Check class="h-4 w-4" />
                                Registrar Venta
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>

        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="showDetail && detailSale"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4 backdrop-blur-sm"
            >
                <div
                    class="relative w-full max-w-lg rounded-3xl bg-white p-6 shadow-xl dark:bg-surface-dark"
                >
                    <div class="mb-6 flex items-center justify-between">
                        <h3
                            class="font-display text-lg font-bold text-content-primary dark:text-white"
                        >
                            Venta #{{ detailSale.id }}
                        </h3>
                        <button
                            @click="showDetail = false"
                            class="rounded-xl p-2 transition-colors hover:bg-gray-100 dark:hover:bg-gray-800"
                        >
                            <X class="h-5 w-5 text-content-muted" />
                        </button>
                    </div>

                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-content-muted">Fecha</span>
                            <span
                                class="font-medium text-content-primary dark:text-white"
                                >{{
                                    formatDate(
                                        detailSale.date ||
                                            detailSale.created_at,
                                    )
                                }}</span
                            >
                        </div>
                        <div class="flex justify-between">
                            <span class="text-content-muted">Cajero</span>
                            <span
                                class="font-medium text-content-primary dark:text-white"
                                >{{ detailSale.cashier?.name || '—' }}</span
                            >
                        </div>
                        <div class="flex justify-between">
                            <span class="text-content-muted">Método Pago</span>
                            <div class="flex flex-wrap gap-1">
                                <span
                                    v-for="p in detailSale.payments"
                                    :key="p.id"
                                    class="rounded-lg bg-gray-100 px-2 py-0.5 text-xs font-bold capitalize dark:bg-gray-800"
                                >
                                    {{ methodLabels[p.method] || p.method }}
                                </span>
                                <span
                                    v-if="!detailSale.payments?.length"
                                    class="font-medium text-content-primary dark:text-white"
                                >
                                    —
                                </span>
                            </div>
                        </div>

                        <hr class="border-gray-100 dark:border-gray-800" />

                        <div>
                            <span
                                class="text-xs font-bold uppercase tracking-wider text-content-muted"
                                >Productos</span
                            >
                            <div
                                v-for="item in detailSale.items"
                                :key="item.id"
                                class="mt-2 flex justify-between"
                            >
                                <span class="text-content-secondary"
                                    >{{
                                        item.product?.name ||
                                        'Producto #' + item.product_id
                                    }}
                                    × {{ item.quantity }}</span
                                >
                                <span
                                    class="font-medium text-content-primary dark:text-white"
                                    >{{
                                        fmt(item.total_line)
                                    }}</span
                                >
                            </div>
                        </div>

                        <hr class="border-gray-100 dark:border-gray-800" />

                        <div
                            v-if="detailSale.payments?.length"
                            class="space-y-1"
                        >
                            <span
                                class="text-xs font-bold uppercase tracking-wider text-content-muted"
                                >Pagos</span
                            >
                            <div
                                v-for="p in detailSale.payments"
                                :key="p.id"
                                class="flex justify-between"
                            >
                                <span class="capitalize text-content-secondary">
                                    {{ methodLabels[p.method] || p.method }}
                                </span>
                                <span
                                    class="font-medium text-content-primary dark:text-white"
                                    >{{ fmt(Math.round(p.amount * 100)) }}</span
                                >
                            </div>
                        </div>

                        <hr class="border-gray-100 dark:border-gray-800" />

                        <div class="flex justify-between text-base">
                            <span
                                class="font-bold text-content-primary dark:text-white"
                                >Total</span
                            >
                            <span class="font-bold text-primary-500">{{
                                fmt(detailSale.total)
                            }}</span>
                        </div>
                    </div>

                    <div class="mt-6">
                        <button
                            @click="showDetail = false"
                            class="w-full rounded-2xl bg-gray-100 py-2.5 text-sm font-bold text-content-secondary transition-colors hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700"
                        >
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </AdminLayout>
</template>
