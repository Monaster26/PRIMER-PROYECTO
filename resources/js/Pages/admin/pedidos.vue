<script setup lang="ts">
import { formatDate, formatTime } from '@/helpers/format';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import {
    Check,
    Eye,
    Plus,
    ShoppingBag,
    Trash2,
    Truck,
    X,
} from 'lucide-vue-next';
import { nextTick, ref, watch } from 'vue';

const props = defineProps<{
    orders: {
        data: any[];
        links: any[];
        current_page: number;
        last_page: number;
        prev_page_url: string | null;
        next_page_url: string | null;
    };
    suppliers: { id: number; company_name: string }[];
    products: {
        id: number;
        name: string;
        sku: string;
        cost_price: number;
        stock: number;
    }[];
}>();

const showForm = ref(false);
const showDetail = ref(false);
const detailOrder = ref<any>(null);
const showReceive = ref(false);
const receiveOrderRef = ref<any>(null);
const receiveItems = ref<
    {
        id: number;
        name: string;
        sku: string;
        ordered: number;
        received: number;
    }[]
>([]);

const form = useForm({
    supplier_id: null as number | null,
    ordered_at: new Date().toISOString().split('T')[0],
    delivery_at: '',
    notes: '',
    items: [] as {
        product_id: number | null;
        sku: string;
        name: string;
        quantity: number;
        unit_cost: number;
        is_new: boolean;
        new_name: string;
        old_cost: number;
    }[],
});

function addItem() {
    form.items.push({
        product_id: null,
        sku: '',
        name: '',
        quantity: 1,
        unit_cost: 0,
        is_new: false,
        new_name: '',
        old_cost: 0,
    });
}

function removeItem(index: number) {
    form.items.splice(index, 1);
}

function openCreate() {
    form.reset();
    form.ordered_at = new Date().toISOString().split('T')[0];
    form.delivery_at = '';
    form.items = [];
    supplierSearch.value = '';
    showSupplierDropdown.value = false;
    addItem();
    showForm.value = true;
}

function handleSupplierEnter() {
    nextTick(() => productoInputRefs.value[0]?.focus());
}

function handleProductoEnter(index: number) {
    nextTick(() => precioInputRefs.value[index]?.focus());
}

function handlePrecioEnter(index: number) {
    nextTick(() => cantidadInputRefs.value[index]?.focus());
}

function handleCantidadEnter(index: number) {
    nextTick(() => fechaEntregaInputRef.value?.focus());
}

function handleFechaEntregaEnter() {
    nextTick(() => notasInputRef.value?.focus());
}

// ─── Arrow/Enter navigation for supplier dropdown ──────
function onSupplierArrowDown() {
    showSupplierDropdown.value = true;
    const list = filteredSuppliers();
    if (focusedSupplierIndex.value < list.length - 1)
        focusedSupplierIndex.value++;
}
function onSupplierArrowUp() {
    if (focusedSupplierIndex.value > 0) focusedSupplierIndex.value--;
}
function onSupplierEnter() {
    const list = filteredSuppliers();
    if (focusedSupplierIndex.value >= 0 && list[focusedSupplierIndex.value]) {
        selectSupplier(list[focusedSupplierIndex.value]);
        handleSupplierEnter();
    } else {
        handleSupplierEnter();
    }
}

// ─── Arrow/Enter navigation for product dropdown ──────
function onProductArrowDown(index: number) {
    showNameDropdown.value = index;
    const list = filteredByName(index);
    if (focusedProductIndex.value < list.length - 1)
        focusedProductIndex.value++;
}
function onProductArrowUp() {
    if (focusedProductIndex.value > 0) focusedProductIndex.value--;
}
function onProductEnter(index: number) {
    const list = filteredByName(index);
    if (focusedProductIndex.value >= 0 && list[focusedProductIndex.value]) {
        selectByName(index, list[focusedProductIndex.value]);
        handleProductoEnter(index);
    } else {
        handleProductoEnter(index);
    }
}

function handleSupplierBlur() {
    setTimeout(() => {
        showSupplierDropdown.value = false;
        focusedSupplierIndex.value = -1;
    }, 200);
}

function handleNameBlur(index: number) {
    setTimeout(() => {
        if (showNameDropdown.value === index) showNameDropdown.value = null;
    }, 200);
}

function closeForm() {
    showForm.value = false;
    form.reset();
}

function submitForm() {
    form.post(route('admin.pedidos.store'), { onSuccess: closeForm });
}

function openReceive(o: any) {
    receiveOrderRef.value = o;
    receiveItems.value = (o.items || []).map((item: any) => ({
        id: item.id,
        name: item.product?.name || 'Producto #' + item.product_id,
        sku: item.product?.sku || '',
        ordered: item.quantity,
        received: item.quantity,
    }));
    showReceive.value = true;
}

function submitReceive() {
    if (!receiveOrderRef.value) return;
    router.post(
        route('admin.pedidos.receive', receiveOrderRef.value.id),
        {
            items: receiveItems.value.map((i) => ({
                id: i.id,
                received_quantity: i.received,
            })),
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                showReceive.value = false;
                receiveOrderRef.value = null;
            },
        },
    );
}

function cancelOrder(id: number) {
    if (!confirm('¿Cancelar este pedido?')) return;
    router.delete(route('admin.pedidos.destroy', id), { preserveScroll: true });
}

function viewDetail(order: any) {
    detailOrder.value = order;
    showDetail.value = true;
}

const statusBadge: Record<string, string> = {
    pending: 'bg-warning/10 text-warning',
    received: 'bg-success/10 text-success',
    cancelled: 'bg-gray-100 text-gray-500',
};

const statusLabel: Record<string, string> = {
    pending: 'Pendiente',
    received: 'Recibido',
    cancelled: 'Cancelado',
};

const fmt = (v: number) =>
    '$' + (v / 100).toLocaleString('es-CO', { minimumFractionDigits: 0 });

// ─── Supplier search ──────────────────────────
const supplierSearch = ref('');
const showSupplierDropdown = ref(false);

const proveedorInputRef = ref<HTMLInputElement | null>(null);
const productoInputRefs = ref<(HTMLInputElement | null)[]>([]);
const precioInputRefs = ref<(HTMLInputElement | null)[]>([]);
const cantidadInputRefs = ref<(HTMLInputElement | null)[]>([]);
const fechaEntregaInputRef = ref<HTMLInputElement | null>(null);
const notasInputRef = ref<HTMLTextAreaElement | null>(null);

const focusedSupplierIndex = ref(-1);
const focusedProductIndex = ref(-1);

watch(showForm, (val) => {
    if (val) nextTick(() => proveedorInputRef.value?.focus());
});

function filteredSuppliers() {
    const q = supplierSearch.value.toLowerCase();
    if (!q) return props.suppliers;
    return props.suppliers.filter((s) =>
        s.company_name.toLowerCase().includes(q),
    );
}

function selectSupplier(s: { id: number; company_name: string }) {
    form.supplier_id = s.id;
    supplierSearch.value = s.company_name;
    showSupplierDropdown.value = false;
    focusedSupplierIndex.value = -1;
}

// ─── Name search (per-row dropdown) ──────────
const showNameDropdown = ref<number | null>(null);

function filteredByName(index: number) {
    const item = form.items[index];
    const q = (item.name || '').toLowerCase();
    if (!q) return [];
    return props.products.filter((p) => p.name.toLowerCase().includes(q));
}

function onNameInput(index: number) {
    const item = form.items[index];
    item.product_id = null;
    item.is_new = false;
    item.new_name = '';
    focusedProductIndex.value = -1;
}

function selectByName(
    index: number,
    p: { id: number; name: string; sku: string; cost_price: number },
) {
    const item = form.items[index];
    item.product_id = p.id;
    item.name = p.name;
    item.sku = p.sku;
    item.unit_cost = p.cost_price / 100;
    item.old_cost = p.cost_price / 100;
    item.is_new = false;
    showNameDropdown.value = null;
    focusedProductIndex.value = -1;
}

function markAsNew(index: number) {
    const item = form.items[index];
    if (!item.name) return;
    item.is_new = true;
    item.new_name = item.name;
    item.product_id = null;
    item.sku = '';
    showNameDropdown.value = null;
}

// ─── SKU search (strict match) ────────────────
function onSkuInput(index: number) {
    const item = form.items[index];
    if (!item.sku) {
        item.product_id = null;
        return;
    }
    const match = props.products.find(
        (p) => p.sku.toLowerCase() === item.sku.toLowerCase(),
    );
    if (match) {
        item.product_id = match.id;
        item.name = match.name;
        item.unit_cost = match.cost_price / 100;
        item.old_cost = match.cost_price / 100;
        item.is_new = false;
    } else {
        item.product_id = null;
    }
}

// ─── Total ─────────────────────────────────────
const computedTotal = () => {
    return form.items.reduce((sum, item) => {
        return sum + Math.round(item.unit_cost * 100) * item.quantity;
    }, 0);
};
</script>

<template>
    <Head title="Pedidos a Proveedores" />
    <AdminLayout>
        <template #title>
            <h1
                class="font-display text-xl font-bold text-content-primary dark:text-white"
            >
                Pedidos a Proveedores
            </h1>
        </template>

        <div
            class="overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-surface-dark"
        >
            <div
                class="flex flex-wrap items-center gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-800"
            >
                <ShoppingBag class="h-5 w-5 text-primary-500" />
                <h2
                    class="flex-1 font-bold text-content-primary dark:text-white"
                >
                    Órdenes de Compra
                </h2>
                <button
                    @click="openCreate"
                    class="flex items-center gap-2 rounded-2xl bg-primary-500 px-4 py-2 text-sm font-bold text-white shadow-sm transition-colors hover:bg-primary-600"
                >
                    <Plus class="h-4 w-4" /> Nuevo Pedido
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead
                        class="bg-gray-50 text-xs uppercase tracking-wider text-content-muted dark:bg-gray-900/50 dark:text-gray-500"
                    >
                        <tr>
                            <th class="px-6 py-3 font-bold"># Pedido</th>
                            <th class="px-6 py-3 font-bold">Proveedor</th>
                            <th class="px-6 py-3 font-bold">Fecha</th>
                            <th class="px-6 py-3 text-right font-bold">
                                Total
                            </th>
                            <th class="px-6 py-3 text-center font-bold">
                                Estado
                            </th>
                            <th class="px-6 py-3 text-right font-bold">
                                Acciones
                            </th>
                        </tr>
                    </thead>
                    <tbody
                        class="divide-y divide-gray-100 dark:divide-gray-800"
                    >
                        <tr v-if="!orders.data?.length">
                            <td
                                colspan="6"
                                class="px-6 py-12 text-center text-sm text-content-muted dark:text-gray-500"
                            >
                                No hay pedidos registrados.
                            </td>
                        </tr>
                        <tr
                            v-for="o in orders.data"
                            :key="o.id"
                            class="group transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/50"
                        >
                            <td
                                class="px-6 py-4 font-mono text-sm font-bold text-content-primary dark:text-white"
                            >
                                {{ o.order_number }}
                            </td>
                            <td
                                class="px-6 py-4 text-sm font-medium text-content-primary dark:text-white"
                            >
                                {{ o.supplier?.company_name || '—' }}
                            </td>
                            <td
                                class="px-6 py-4 text-sm text-content-secondary"
                            >
                                {{ formatDate(o.ordered_at) }}
                            </td>
                            <td
                                class="px-6 py-4 text-right text-sm font-bold text-primary-500"
                            >
                                {{ fmt(o.total_cost) }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span
                                    :class="
                                        statusBadge[o.status] ||
                                        'bg-gray-100 text-gray-500'
                                    "
                                    class="rounded-lg px-2.5 py-1 text-[10px] font-bold uppercase"
                                >
                                    {{ statusLabel[o.status] || o.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div
                                    class="flex items-center justify-end gap-2 opacity-0 transition-opacity group-hover:opacity-100"
                                >
                                    <button
                                        @click="viewDetail(o)"
                                        class="rounded-xl p-2 text-blue-500 transition-colors hover:bg-blue-50 dark:hover:bg-blue-900/20"
                                        title="Ver detalle"
                                    >
                                        <Eye class="h-4 w-4" />
                                    </button>
                                    <button
                                        v-if="o.status === 'pending'"
                                        @click="openReceive(o)"
                                        class="rounded-xl p-2 text-success transition-colors hover:bg-success/10"
                                        title="Recibir pedido"
                                    >
                                        <Truck class="h-4 w-4" />
                                    </button>
                                    <button
                                        v-if="o.status === 'pending'"
                                        @click="cancelOrder(o.id)"
                                        class="rounded-xl p-2 text-danger transition-colors hover:bg-red-50 dark:hover:bg-red-900/20"
                                        title="Cancelar"
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
                v-if="orders.last_page > 1"
                class="flex items-center justify-between border-t border-gray-100 px-6 py-4 text-sm text-content-muted dark:border-gray-800 dark:text-gray-500"
            >
                <span
                    >Página {{ orders.current_page }} de
                    {{ orders.last_page }}</span
                >
                <div class="flex gap-2">
                    <a
                        v-if="orders.prev_page_url"
                        :href="orders.prev_page_url"
                        class="rounded-lg bg-gray-50 px-3 py-1.5 font-bold transition-colors hover:bg-gray-100 dark:bg-gray-900"
                        >←</a
                    >
                    <a
                        v-if="orders.next_page_url"
                        :href="orders.next_page_url"
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
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4 backdrop-blur-sm md:p-6"
            >
                <div
                    class="relative my-auto max-h-[90vh] w-full max-w-4xl overflow-y-auto rounded-3xl bg-white p-6 shadow-2xl dark:bg-surface-dark md:ml-20 md:p-8 lg:ml-32"
                >
                    <div class="mb-6 flex items-center justify-between">
                        <h3
                            class="font-display text-lg font-bold text-content-primary dark:text-white"
                        >
                            Nuevo Pedido
                        </h3>
                        <button
                            @click="closeForm"
                            class="rounded-xl p-2 transition-colors hover:bg-gray-100 dark:hover:bg-gray-800"
                        >
                            <X class="h-5 w-5 text-content-muted" />
                        </button>
                    </div>
                    <form @submit.prevent="submitForm" class="space-y-4">
                        <!-- 3-column header -->
                        <div
                            class="mb-6 grid grid-cols-1 items-end gap-4 md:grid-cols-3"
                        >
                            <div class="relative">
                                <label
                                    class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                                    >Proveedor</label
                                >
                                <input
                                    v-model="supplierSearch"
                                    ref="proveedorInputRef"
                                    @keydown.down.prevent="onSupplierArrowDown"
                                    @keydown.up.prevent="onSupplierArrowUp"
                                    @keydown.enter.prevent="onSupplierEnter"
                                    @focus="
                                        showSupplierDropdown = true;
                                        focusedSupplierIndex = -1;
                                    "
                                    @blur="handleSupplierBlur"
                                    type="text"
                                    placeholder="Buscar proveedor..."
                                    class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                />
                                <div
                                    v-if="showSupplierDropdown"
                                    class="absolute left-0 right-0 top-full z-20 mt-1 max-h-48 overflow-y-auto rounded-xl border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-surface-dark"
                                >
                                    <button
                                        v-for="(
                                            s, sIndex
                                        ) in filteredSuppliers()"
                                        :key="s.id"
                                        @mousedown.prevent="selectSupplier(s)"
                                        :class="[
                                            'flex w-full items-center gap-2 border-b border-gray-100 px-3 py-2 text-left text-sm text-content-primary last:border-0 hover:bg-gray-50 dark:border-gray-800 dark:text-white dark:hover:bg-gray-800',
                                            {
                                                'bg-pink-50 font-medium text-pink-700':
                                                    sIndex ===
                                                    focusedSupplierIndex,
                                            },
                                        ]"
                                    >
                                        <span class="font-medium">{{
                                            s.company_name
                                        }}</span>
                                    </button>
                                    <div
                                        v-if="!filteredSuppliers().length"
                                        class="px-3 py-2 text-center text-sm text-content-muted"
                                    >
                                        Sin resultados
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label
                                    class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                                    >Fecha de Pedido</label
                                >
                                <input
                                    v-model="form.ordered_at"
                                    type="date"
                                    required
                                    class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                />
                            </div>
                            <div>
                                <label
                                    class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                                    >Fecha de Entrega</label
                                >
                                <input
                                    v-model="form.delivery_at"
                                    ref="fechaEntregaInputRef"
                                    @keydown.enter.prevent="
                                        handleFechaEntregaEnter
                                    "
                                    type="date"
                                    class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                />
                            </div>
                        </div>

                        <!-- Products section -->
                        <div>
                            <div class="mb-2 flex items-center justify-between">
                                <label
                                    class="block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                                    >Productos</label
                                >
                                <button
                                    type="button"
                                    @click="addItem"
                                    class="flex items-center gap-2 rounded-xl bg-pink-500 px-4 py-2 text-xs font-medium font-semibold text-white shadow-sm transition-all hover:bg-pink-600 active:scale-95 md:text-sm"
                                >
                                    <Plus class="h-4 w-4" /> Añadir producto
                                </button>
                            </div>
                            <div class="space-y-1">
                                <div
                                    v-for="(item, index) in form.items"
                                    :key="index"
                                >
                                    <div
                                        class="mb-3 flex w-full items-center gap-4 rounded-xl border border-gray-100 bg-white p-2 dark:bg-neutral-900"
                                    >
                                        <!-- SKU -->
                                        <div class="w-40 flex-shrink-0">
                                            <input
                                                v-model="item.sku"
                                                @input="onSkuInput(index)"
                                                type="text"
                                                placeholder="SKU"
                                                class="w-full rounded-xl border-gray-200 text-center text-sm dark:border-gray-700 dark:bg-surface-dark dark:text-white"
                                            />
                                        </div>

                                        <!-- Name + dropdown -->
                                        <div
                                            class="relative min-w-[180px] flex-1"
                                        >
                                            <input
                                                v-model="item.name"
                                                :ref="
                                                    (el) => {
                                                        productoInputRefs[
                                                            index
                                                        ] =
                                                            el as HTMLInputElement;
                                                    }
                                                "
                                                @keydown.down.prevent="
                                                    onProductArrowDown(index)
                                                "
                                                @keydown.up.prevent="
                                                    onProductArrowUp()
                                                "
                                                @keydown.enter.prevent="
                                                    onProductEnter(index)
                                                "
                                                @input="onNameInput(index)"
                                                @focus="
                                                    showNameDropdown = index;
                                                    focusedProductIndex = -1;
                                                "
                                                @blur="handleNameBlur(index)"
                                                type="text"
                                                placeholder="Buscar producto..."
                                                class="w-full rounded-xl border-gray-200 text-sm dark:border-gray-700 dark:bg-surface-dark dark:text-white"
                                            />
                                            <div
                                                v-if="
                                                    showNameDropdown ===
                                                        index &&
                                                    !item.product_id &&
                                                    item.name
                                                "
                                                class="absolute left-0 right-0 top-full z-20 mt-1 max-h-48 overflow-y-auto rounded-xl border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-surface-dark"
                                            >
                                                <button
                                                    v-for="(
                                                        p, pIndex
                                                    ) in filteredByName(index)"
                                                    :key="p.id"
                                                    @mousedown.prevent="
                                                        selectByName(index, p)
                                                    "
                                                    :class="[
                                                        'flex w-full items-center gap-2 border-b border-gray-100 px-3 py-2 text-left text-sm text-content-primary last:border-0 hover:bg-gray-50 dark:border-gray-800 dark:text-white dark:hover:bg-gray-800',
                                                        {
                                                            'bg-pink-50 font-medium text-pink-700':
                                                                pIndex ===
                                                                focusedProductIndex,
                                                        },
                                                    ]"
                                                >
                                                    <span class="font-medium">{{
                                                        p.name
                                                    }}</span>
                                                    <span
                                                        class="text-xs text-content-muted"
                                                        >({{ p.sku }})</span
                                                    >
                                                </button>
                                                <div
                                                    v-if="
                                                        !filteredByName(index)
                                                            .length
                                                    "
                                                    class="border-t border-gray-100 px-3 py-2 dark:border-gray-800"
                                                >
                                                    <button
                                                        type="button"
                                                        @mousedown.prevent="
                                                            markAsNew(index)
                                                        "
                                                        class="text-xs font-bold text-amber-600 hover:text-amber-700"
                                                    >
                                                        + Crear "{{
                                                            item.name
                                                        }}" como producto nuevo
                                                    </button>
                                                </div>
                                            </div>
                                            <span
                                                v-if="item.is_new"
                                                class="mt-1 inline-block rounded-md bg-amber-100 px-2 py-0.5 text-[10px] font-bold uppercase text-amber-700"
                                            >
                                                Nuevo
                                            </span>
                                        </div>

                                        <!-- Costo -->
                                        <div
                                            class="flex w-32 flex-shrink-0 flex-col items-center"
                                        >
                                            <span
                                                class="mb-1 text-[11px] font-medium text-gray-400"
                                            >
                                                Ref: ${{ item.old_cost || 0 }}
                                            </span>
                                            <div class="relative w-full">
                                                <span
                                                    class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-gray-400"
                                                    >$</span
                                                >
                                                <input
                                                    v-model.number="
                                                        item.unit_cost
                                                    "
                                                    :ref="
                                                        (el) => {
                                                            precioInputRefs[
                                                                index
                                                            ] =
                                                                el as HTMLInputElement;
                                                        }
                                                    "
                                                    @keydown.enter.prevent="
                                                        handlePrecioEnter(index)
                                                    "
                                                    type="number"
                                                    min="0"
                                                    step="0.01"
                                                    class="w-full rounded-xl border-gray-200 pl-7 pr-2 text-center text-sm dark:border-gray-700 dark:bg-surface-dark dark:text-white"
                                                />
                                            </div>
                                        </div>

                                        <!-- Cantidad -->
                                        <div
                                            class="flex w-20 flex-shrink-0 flex-col items-center"
                                        >
                                            <span
                                                class="mb-1 block text-center text-[11px] font-medium text-gray-400"
                                                >Cantidad</span
                                            >
                                            <input
                                                v-model.number="item.quantity"
                                                :ref="
                                                    (el) => {
                                                        cantidadInputRefs[
                                                            index
                                                        ] =
                                                            el as HTMLInputElement;
                                                    }
                                                "
                                                @keydown.enter.prevent="
                                                    handleCantidadEnter(index)
                                                "
                                                type="number"
                                                min="1"
                                                required
                                                class="w-full rounded-xl border-gray-200 text-center text-sm dark:border-gray-700 dark:bg-surface-dark dark:text-white"
                                            />
                                        </div>

                                        <!-- Subtotal -->
                                        <div
                                            class="w-24 flex-shrink-0 text-right text-sm font-bold text-gray-800 dark:text-gray-200"
                                        >
                                            ${{
                                                (
                                                    item.unit_cost *
                                                    item.quantity
                                                ).toLocaleString('es-CO', {
                                                    minimumFractionDigits: 0,
                                                })
                                            }}
                                        </div>

                                        <!-- Eliminar -->
                                        <div
                                            class="flex w-8 flex-shrink-0 justify-center"
                                        >
                                            <button
                                                @click="removeItem(index)"
                                                class="text-red-400 transition-colors hover:text-red-600"
                                            >
                                                <X class="h-4 w-4" />
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Total -->
                        <div class="flex justify-end">
                            <span
                                class="text-sm font-bold text-content-primary dark:text-white"
                            >
                                Total:
                                <span class="ml-1 text-lg text-primary-500">{{
                                    fmt(computedTotal())
                                }}</span>
                            </span>
                        </div>

                        <!-- Notas generales -->
                        <div>
                            <label
                                class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                                >Notas generales</label
                            >
                            <textarea
                                v-model="form.notes"
                                ref="notasInputRef"
                                @keydown.enter.prevent
                                rows="2"
                                maxlength="500"
                                class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                            ></textarea>
                        </div>

                        <!-- Buttons -->
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
                                Registrar Pedido
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
                v-if="showDetail && detailOrder"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4 backdrop-blur-sm"
            >
                <div
                    class="relative w-full max-w-lg rounded-3xl bg-white p-6 shadow-xl dark:bg-surface-dark"
                >
                    <div class="mb-6 flex items-center justify-between">
                        <h3
                            class="font-display text-lg font-bold text-content-primary dark:text-white"
                        >
                            Pedido {{ detailOrder.order_number }}
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
                            <span class="text-content-muted">Proveedor</span>
                            <span
                                class="font-medium text-content-primary dark:text-white"
                                >{{
                                    detailOrder.supplier?.company_name || '—'
                                }}</span
                            >
                        </div>
                        <div class="flex justify-between">
                            <span class="text-content-muted">Fecha</span>
                            <span
                                class="font-medium text-content-primary dark:text-white"
                                >{{ formatDate(detailOrder.ordered_at) }}</span
                            >
                        </div>
                        <div
                            v-if="detailOrder.received_at"
                            class="flex justify-between"
                        >
                            <span class="text-content-muted">Recibido</span>
                            <span
                                class="font-medium text-content-primary dark:text-white"
                                >{{ formatDate(detailOrder.received_at) }}
                                {{ formatTime(detailOrder.received_at) }}</span
                            >
                        </div>
                        <div class="flex justify-between">
                            <span class="text-content-muted">Estado</span>
                            <span
                                :class="statusBadge[detailOrder.status]"
                                class="rounded-lg px-2.5 py-0.5 text-[10px] font-bold uppercase"
                            >
                                {{
                                    statusLabel[detailOrder.status] ||
                                    detailOrder.status
                                }}
                            </span>
                        </div>
                        <div
                            v-if="detailOrder.notes"
                            class="flex justify-between"
                        >
                            <span class="text-content-muted">Notas</span>
                            <span
                                class="max-w-[60%] text-right font-medium text-content-primary dark:text-white"
                                >{{ detailOrder.notes }}</span
                            >
                        </div>
                        <hr class="border-gray-100 dark:border-gray-800" />
                        <div>
                            <span
                                class="text-xs font-bold uppercase tracking-wider text-content-muted"
                                >Productos</span
                            >
                            <div
                                v-for="item in detailOrder.items"
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
                                    >{{ fmt(item.subtotal) }}</span
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
                                fmt(detailOrder.total_cost)
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

        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="showReceive && receiveOrderRef"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4 backdrop-blur-sm"
            >
                <div
                    class="relative w-full max-w-xl rounded-3xl bg-white p-6 shadow-xl dark:bg-surface-dark"
                >
                    <div class="mb-6 flex items-center justify-between">
                        <h3
                            class="font-display text-lg font-bold text-content-primary dark:text-white"
                        >
                            Recibir Pedido {{ receiveOrderRef.order_number }}
                        </h3>
                        <button
                            @click="showReceive = false"
                            class="rounded-xl p-2 transition-colors hover:bg-gray-100 dark:hover:bg-gray-800"
                        >
                            <X class="h-5 w-5 text-content-muted" />
                        </button>
                    </div>

                    <div class="mb-4 space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-content-muted">Proveedor</span>
                            <span
                                class="font-medium text-content-primary dark:text-white"
                                >{{
                                    receiveOrderRef.supplier?.company_name ||
                                    '—'
                                }}</span
                            >
                        </div>
                        <div class="flex justify-between">
                            <span class="text-content-muted">Fecha Pedido</span>
                            <span
                                class="font-medium text-content-primary dark:text-white"
                                >{{
                                    formatDate(receiveOrderRef.ordered_at)
                                }}</span
                            >
                        </div>
                    </div>

                    <table class="mb-6 w-full text-left">
                        <thead
                            class="rounded-xl bg-gray-50 text-xs uppercase tracking-wider text-content-muted dark:bg-gray-900/50 dark:text-gray-500"
                        >
                            <tr>
                                <th class="px-3 py-2 font-bold">Producto</th>
                                <th class="px-3 py-2 text-center font-bold">
                                    Pedido
                                </th>
                                <th class="px-3 py-2 text-center font-bold">
                                    Recibido
                                </th>
                            </tr>
                        </thead>
                        <tbody
                            class="divide-y divide-gray-100 dark:divide-gray-800"
                        >
                            <tr
                                v-for="ri in receiveItems"
                                :key="ri.id"
                                class="hover:bg-gray-50 dark:hover:bg-gray-800/50"
                            >
                                <td
                                    class="px-3 py-3 text-sm font-medium text-content-primary dark:text-white"
                                >
                                    {{ ri.name }}
                                    <span class="text-xs text-content-muted"
                                        >({{ ri.sku }})</span
                                    >
                                </td>
                                <td
                                    class="px-3 py-3 text-center text-sm text-content-secondary"
                                >
                                    {{ ri.ordered }}
                                </td>
                                <td class="px-3 py-3 text-center">
                                    <input
                                        v-model.number="ri.received"
                                        type="number"
                                        min="0"
                                        class="w-20 rounded-xl border border-gray-200 bg-white px-3 py-1.5 text-center text-sm text-content-primary dark:border-gray-700 dark:bg-surface-dark dark:text-white"
                                    />
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="flex gap-3">
                        <button
                            @click="showReceive = false"
                            class="flex-1 rounded-2xl border border-gray-200 py-2.5 text-sm font-bold text-content-secondary transition-colors hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800"
                        >
                            Cancelar
                        </button>
                        <button
                            @click="submitReceive"
                            class="flex flex-1 items-center justify-center gap-2 rounded-2xl bg-success py-2.5 text-sm font-bold text-white shadow-sm transition-colors hover:bg-success/90"
                        >
                            <Truck class="h-4 w-4" />
                            Confirmar Recepción
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </AdminLayout>
</template>
