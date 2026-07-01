<script setup lang="ts">
import DateFilter from '@/Components/DateFilter.vue';
import { formatDate, formatTime } from '@/helpers/format';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import {
    Check,
    CreditCard,
    Eye,
    Pencil,
    Plus,
    Trash2,
    Truck,
    X,
} from 'lucide-vue-next';
import { computed, nextTick, reactive, ref, watch } from 'vue';

const props = defineProps<{
    invoices: {
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
        category_id: number | null;
        sub_category: string | null;
    }[];
    categories: {
        id: number;
        name: string;
        slug: string;
        children: { id: number; name: string; slug: string }[];
    }[];
    filters: {
        status?: string;
        supplier_id?: number;
        date_from?: string;
        date_to?: string;
        search?: string;
    };
}>();

const showForm = ref(false);
const showDetail = ref(false);
const detailInvoice = ref<any>(null);
const showReceive = ref(false);
const receiveInvoiceRef = ref<any>(null);
const receiveObservation = ref('');
const showQuickAdd = ref(false);
const isQuickAddEdit = ref(false);
const quickAddTargetIndex = ref<number | null>(null);
const showSuggestCreate = ref<number | null>(null);
const suggestedName = ref('');
const quickForm = reactive({
    category_name: '',
    subcategory_name: '',
    name: '',
    sku: '',
    barcode: '',
    unit_cost: 0,
    quantity: 1,
});
const receiveItems = ref<
    {
        id: number;
        name: string;
        sku: string;
        ordered: number;
        received: number;
        previous_cost: number;
        unit_cost: number;
        cost_pesos: number;
        is_new_product: boolean;
        expiration_date: string;
        barcode: string;
        sale_price: number;
        old_sale_price: number;
        margin: number;
    }[]
>([]);

const showEdit = ref(false);
const editInvoice = ref<any>(null);

const editForm = useForm({
    supplier_id: null as number | null,
    invoice_number: '',
    issue_date: '',
    due_date: '',
    delivery_date: '',
    notes: '',
    items: [] as any[],
});

const form = useForm({
    supplier_id: null as number | null,
    invoice_number: '',
    issue_date: '',
    due_date: '',
    delivery_date: '',
    notes: '',
    items: [] as any[],
});

// ─── Filter state ─────────────────────────────
const filterStatus = ref(props.filters?.status ?? '');
const filterSupplier = ref<number | null>(props.filters?.supplier_id ?? null);
const filterDateFrom = ref(props.filters?.date_from ?? '');
const filterDateTo = ref(props.filters?.date_to ?? '');
const filterSearch = ref(props.filters?.search ?? '');

function applyFilters() {
    router.get(
        route('admin.facturas-pendientes.index'),
        {
            status: filterStatus.value || undefined,
            supplier_id: filterSupplier.value || undefined,
            date_from: filterDateFrom.value || undefined,
            date_to: filterDateTo.value || undefined,
            search: filterSearch.value || undefined,
        },
        { preserveScroll: true, preserveState: true },
    );
}

// ─── New form ─────────────────────────────────
function addItem(productName?: string) {
    const idx = form.items.length;
    form.items.push({
        product_id: null,
        sku: '',
        product_name: productName || '',
        quantity_ordered: 1,
        unit_cost: 0,
        is_new_product: false,
        category_name: '',
        subcategory_name: '',
    });
    if (!productName) {
        nextTick(() => {
            const el = document.querySelector(
                '.product-name-input-' + idx,
            ) as HTMLInputElement;
            el?.focus();
        });
    }
    return idx;
}
function removeItem(index: number) {
    form.items.splice(index, 1);
}

const supplierSearch = ref('');
const showSupplierDropdown = ref(false);
const focusedSupplierIndex = ref(-1);
const focusedProductIndex = ref(-1);

const proveedorInputRef = ref<HTMLInputElement | null>(null);
const productoInputRefs = ref<(HTMLInputElement | null)[]>([]);
const precioInputRefs = ref<(HTMLInputElement | null)[]>([]);
const cantidadInputRefs = ref<(HTMLInputElement | null)[]>([]);
const notasInputRef = ref<HTMLTextAreaElement | null>(null);
const editProductoInputRefs = ref<(HTMLInputElement | null)[]>([]);

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

function handleSupplierBlur() {
    setTimeout(() => {
        showSupplierDropdown.value = false;
        focusedSupplierIndex.value = -1;
    }, 200);
}

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
function handleSupplierEnter() {
    nextTick(() => productoInputRefs.value[0]?.focus());
}

// ─── Name search ──────────────────────────────
const showNameDropdown = ref<number | null>(null);

function filteredByName(index: number) {
    const item = form.items[index];
    const q = (item.product_name || '').toLowerCase();
    if (!q) return [];
    return props.products.filter((p) => p.name.toLowerCase().includes(q));
}

function onNameInput(index: number) {
    const item = form.items[index];
    item.product_id = null;
    item.is_new_product = false;
    focusedProductIndex.value = -1;
    const q = (item.product_name || '').toLowerCase();
    if (q && !props.products.some((p) => p.name.toLowerCase() === q)) {
        showSuggestCreate.value = index;
        suggestedName.value = item.product_name;
    } else {
        showSuggestCreate.value = null;
    }
}

function selectByName(
    index: number,
    p: {
        id: number;
        name: string;
        sku: string;
        cost_price: number;
        category_id: number | null;
        sub_category: string | null;
    },
) {
    const item = form.items[index];
    item.product_id = p.id;
    item.product_name = p.name;
    item.sku = p.sku;
    item.unit_cost = p.cost_price / 100;
    item.is_new_product = false;
    if (p.category_id) {
        let cat: { id: number; name: string } | undefined;
        let subcat: string | undefined;
        cat = props.categories.find((c) => c.id === p.category_id);
        if (cat) {
            subcat = p.sub_category || '';
        } else {
            for (const root of props.categories) {
                const child = root.children.find((c) => c.id === p.category_id);
                if (child) {
                    cat = root;
                    subcat = child.name;
                    break;
                }
            }
        }
        if (cat) {
            item.category_name = cat.name;
            item.subcategory_name = subcat || '';
        }
    }
    showNameDropdown.value = null;
    focusedProductIndex.value = -1;
}
function markAsNew(index: number) {
    const item = form.items[index];
    if (!item.product_name) return;
    quickForm.name = item.product_name;
    quickAddTargetIndex.value = index;
    showNameDropdown.value = null;
    showQuickAdd.value = true;
}

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
function handleProductoEnter(index: number) {
    nextTick(() => precioInputRefs.value[index]?.focus());
}
function handleNameBlur(index: number) {
    setTimeout(() => {
        if (showNameDropdown.value === index) showNameDropdown.value = null;
    }, 200);
}

// ─── SKU search ───────────────────────────────
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
        selectByName(index, match);
        return;
    } else {
        item.product_id = null;
    }
}

function handlePrecioEnter(index: number) {
    nextTick(() => cantidadInputRefs.value[index]?.focus());
}
function handleCantidadEnter() {
    nextTick(() => notasInputRef.value?.focus());
}

// ─── Quick add ────────────────────────────────
const filteredSubcategories = computed(() => {
    if (!quickForm.category_name) return [];
    const cat = props.categories.find(
        (c) => c.name === quickForm.category_name,
    );
    return cat?.children ?? [];
});

const quickAddDone = ref(false);

function confirmQuickAdd() {
    if (!quickForm.name) return;
    const target = isQuickAddEdit.value ? editForm : form;
    target.items.push({
        product_id: null,
        sku: quickForm.sku,
        product_name: quickForm.name,
        quantity_ordered: quickForm.quantity,
        unit_cost: quickForm.unit_cost,
        is_new_product: true,
        category_name: quickForm.category_name,
        subcategory_name: quickForm.subcategory_name,
    });
    if (quickAddTargetIndex.value !== null) {
        target.items.splice(quickAddTargetIndex.value, 1);
        quickAddTargetIndex.value = null;
    }
    quickAddDone.value = true;
}

function addAnotherQuick() {
    quickForm.name = '';
    quickForm.sku = '';
    quickForm.barcode = '';
    quickForm.unit_cost = 0;
    quickForm.quantity = 1;
    quickAddDone.value = false;
    isQuickAddEdit.value = false;
}

function closeQuickAdd() {
    quickForm.category_name = '';
    quickForm.subcategory_name = '';
    quickForm.name = '';
    quickForm.sku = '';
    quickForm.barcode = '';
    quickForm.unit_cost = 0;
    quickForm.quantity = 1;
    isQuickAddEdit.value = false;
    quickAddDone.value = false;
    showQuickAdd.value = false;
}

// ─── Open / Close ─────────────────────────────
function openNew() {
    form.reset();
    form.issue_date = new Date().toISOString().split('T')[0];
    form.due_date = form.issue_date;
    form.delivery_date = '';
    form.items = [];
    supplierSearch.value = '';
    showSupplierDropdown.value = false;
    addItem();
    showForm.value = true;
}

function closeForm() {
    showForm.value = false;
    form.reset();
}

function submitForm() {
    if (form.delivery_date === '') form.delivery_date = null as any;
    form.post(route('admin.facturas-pendientes.store'), {
        onSuccess: closeForm,
    });
}

// ─── Edit ─────────────────────────────────────
function openEdit(inv: any) {
    editInvoice.value = inv;
    editForm.supplier_id = inv.supplier_id;
    editForm.invoice_number = inv.invoice_number || '';
    editForm.issue_date = inv.issue_date;
    editForm.due_date = inv.due_date;
    editForm.delivery_date = inv.delivery_date ?? '';
    editForm.notes = inv.notes ?? '';
    editForm.items = (inv.items || []).map((item: any) => ({
        id: item.id,
        product_id: item.product_id,
        sku: item.product?.sku || '',
        product_name: item.product_name,
        quantity_ordered: item.quantity_ordered,
        unit_cost: item.unit_cost / 100,
        is_new_product: item.is_new_product,
        category_name: item.category_name || '',
        subcategory_name: item.subcategory_name || '',
    }));
    supplierSearchEdit.value = inv.supplier?.company_name || '';
    showEdit.value = true;
}

function closeEdit() {
    showEdit.value = false;
    editInvoice.value = null;
    editForm.reset();
    supplierSearchEdit.value = '';
}

function submitEdit() {
    if (!editInvoice.value) return;
    if (editForm.delivery_date === '') editForm.delivery_date = null as any;
    editForm.put(
        route('admin.facturas-pendientes.update', editInvoice.value.id),
        {
            preserveScroll: true,
            onSuccess: closeEdit,
        },
    );
}

// ─── Edit supplier search ─────────────────────
const supplierSearchEdit = ref('');
const showSupplierDropdownEdit = ref(false);
const focusedSupplierIndexEdit = ref(-1);

function filteredSuppliersEdit() {
    const q = supplierSearchEdit.value.toLowerCase();
    if (!q) return props.suppliers;
    return props.suppliers.filter((s) =>
        s.company_name.toLowerCase().includes(q),
    );
}
function selectSupplierEdit(s: { id: number; company_name: string }) {
    editForm.supplier_id = s.id;
    supplierSearchEdit.value = s.company_name;
    showSupplierDropdownEdit.value = false;
    focusedSupplierIndexEdit.value = -1;
    nextTick(() => editProductoInputRefs.value[0]?.focus());
}
function handleSupplierBlurEdit() {
    setTimeout(() => {
        showSupplierDropdownEdit.value = false;
        focusedSupplierIndexEdit.value = -1;
    }, 200);
}
function onSupplierArrowDownEdit() {
    showSupplierDropdownEdit.value = true;
    const list = filteredSuppliersEdit();
    if (focusedSupplierIndexEdit.value < list.length - 1)
        focusedSupplierIndexEdit.value++;
}
function onSupplierArrowUpEdit() {
    if (focusedSupplierIndexEdit.value > 0) focusedSupplierIndexEdit.value--;
}
function onSupplierEnterEdit() {
    const list = filteredSuppliersEdit();
    if (
        focusedSupplierIndexEdit.value >= 0 &&
        list[focusedSupplierIndexEdit.value]
    ) {
        selectSupplierEdit(list[focusedSupplierIndexEdit.value]);
    }
}

// ─── Edit product search ──────────────────────
const showNameDropdownEdit = ref<number | null>(null);
const focusedProductIndexEdit = ref(-1);

function filteredByNameEdit(index: number) {
    const item = editForm.items[index];
    const q = (item.product_name || '').toLowerCase();
    if (!q) return [];
    return props.products.filter((p) => p.name.toLowerCase().includes(q));
}
function onNameInputEdit(index: number) {
    const item = editForm.items[index];
    item.product_id = null;
    item.is_new_product = false;
    focusedProductIndexEdit.value = -1;
    const q = (item.product_name || '').toLowerCase();
    if (q && !props.products.some((p) => p.name.toLowerCase() === q)) {
        showSuggestCreate.value = index;
        suggestedName.value = item.product_name;
    } else {
        showSuggestCreate.value = null;
    }
}
function selectByNameEdit(
    index: number,
    p: {
        id: number;
        name: string;
        sku: string;
        cost_price: number;
        category_id: number | null;
        sub_category: string | null;
    },
) {
    const item = editForm.items[index];
    item.product_id = p.id;
    item.product_name = p.name;
    item.sku = p.sku;
    item.unit_cost = p.cost_price / 100;
    item.is_new_product = false;
    if (p.category_id) {
        let cat: { id: number; name: string } | undefined;
        let subcat: string | undefined;
        cat = props.categories.find((c) => c.id === p.category_id);
        if (cat) {
            subcat = p.sub_category || '';
        } else {
            for (const root of props.categories) {
                const child = root.children.find((c) => c.id === p.category_id);
                if (child) {
                    cat = root;
                    subcat = child.name;
                    break;
                }
            }
        }
        if (cat) {
            item.category_name = cat.name;
            item.subcategory_name = subcat || '';
        }
    }
    showNameDropdownEdit.value = null;
    focusedProductIndexEdit.value = -1;
}
function markAsNewEdit(index: number) {
    const item = editForm.items[index];
    if (!item.product_name) return;
    quickForm.name = item.product_name;
    quickAddTargetIndex.value = index;
    showNameDropdownEdit.value = null;
    isQuickAddEdit.value = true;
    showQuickAdd.value = true;
}
function onSkuInputEdit(index: number) {
    const item = editForm.items[index];
    if (!item.sku) {
        item.product_id = null;
        return;
    }
    const match = props.products.find(
        (p) => p.sku.toLowerCase() === item.sku.toLowerCase(),
    );
    if (match) {
        selectByNameEdit(index, match);
        return;
    } else {
        item.product_id = null;
    }
}

function onProductArrowDownEdit(index: number) {
    showNameDropdownEdit.value = index;
    const list = filteredByNameEdit(index);
    if (focusedProductIndexEdit.value < list.length - 1)
        focusedProductIndexEdit.value++;
}
function onProductArrowUpEdit() {
    if (focusedProductIndexEdit.value > 0) focusedProductIndexEdit.value--;
}
function onProductEnterEdit(index: number) {
    const list = filteredByNameEdit(index);
    if (
        focusedProductIndexEdit.value >= 0 &&
        list[focusedProductIndexEdit.value]
    ) {
        selectByNameEdit(index, list[focusedProductIndexEdit.value]);
    } else {
        handleProductoEnterEdit(index);
    }
}
function handleNameBlurEdit(index: number) {
    setTimeout(() => {
        if (showNameDropdownEdit.value === index)
            showNameDropdownEdit.value = null;
    }, 200);
}
function handleProductoEnterEdit(index: number) {
    nextTick(() => {
        const editPrecioRefs = document.querySelectorAll('.edit-precio-input');
        (editPrecioRefs[index] as HTMLInputElement)?.focus();
    });
}

// ─── Detail ───────────────────────────────────
function viewDetail(inv: any) {
    detailInvoice.value = inv;
    showDetail.value = true;
}

const detailTotal = computed(() => {
    if (!detailInvoice.value) return 0;
    return detailInvoice.value.total_amount;
});

const receiveTotal = computed(() =>
    receiveItems.value.reduce((sum, ri) => sum + ri.unit_cost * ri.received, 0),
);

// ─── Receive ──────────────────────────────────
function openReceive(inv: any) {
    receiveInvoiceRef.value = inv;
    receiveObservation.value = '';
    receiveItems.value = (inv.items || []).map((item: any) => ({
        id: item.id,
        name: item.product_name,
        sku: item.product?.sku || '',
        ordered: item.quantity_ordered,
        received: item.quantity_ordered,
        previous_cost: item.previous_cost,
        unit_cost: item.unit_cost,
        cost_pesos: item.unit_cost / 100,
        is_new_product: item.is_new_product,
        expiration_date: '',
        barcode: item.product?.barcode || '',
        old_sale_price: item.product?.price ? item.product.price / 100 : 0,
        sale_price: Math.round(item.unit_cost / 100 / 0.7) || 0,
        margin: 30,
    }));
    showReceive.value = true;
}

function onMarginChange(ri: any) {
    if (ri.margin >= 100) ri.margin = 99;
    if (ri.margin < 0) ri.margin = 0;
    const costPesos = ri.unit_cost / 100;
    if (costPesos > 0) {
        ri.sale_price = Math.round(costPesos / (1 - ri.margin / 100));
    }
}
function onSalePriceChange(ri: any) {
    const costPesos = ri.unit_cost / 100;
    if (ri.sale_price > 0 && costPesos > 0) {
        ri.margin = Math.round((1 - costPesos / ri.sale_price) * 100);
        if (ri.margin >= 100) ri.margin = 99;
        if (ri.margin < 0) ri.margin = 0;
    }
}
function onCostChange(ri: any) {
    ri.unit_cost = Math.round(ri.cost_pesos * 100);
    onMarginChange(ri);
}

function submitReceive() {
    if (!receiveInvoiceRef.value) return;
    const missingExp = receiveItems.value.find(
        (i) => i.received > 0 && !i.expiration_date,
    );
    if (missingExp) {
        alert(`"${missingExp.name}" requiere fecha de vencimiento.`);
        return;
    }
    router.post(
        route('admin.facturas-pendientes.receive', receiveInvoiceRef.value.id),
        {
            items: receiveItems.value.map((i) => ({
                id: i.id,
                quantity_received: i.received,
                expiration_date: i.expiration_date || null,
                barcode: i.barcode || null,
                sale_price: i.sale_price || null,
                cost_price: Math.round(i.cost_pesos * 100) || null,
            })),
            notes: receiveObservation.value || null,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                showReceive.value = false;
                receiveInvoiceRef.value = null;
            },
        },
    );
}

// ─── Mark paid ────────────────────────────────
function markPaid(inv: any) {
    if (!confirm(`¿Marcar factura #${inv.id} como pagada?`)) return;
    router.patch(
        route('admin.facturas-pendientes.mark-paid', inv.id),
        {},
        { preserveScroll: true },
    );
}

// ─── Delete ───────────────────────────────────
function deleteInvoice(id: number) {
    if (!confirm('¿Eliminar esta factura?')) return;
    router.delete(route('admin.facturas-pendientes.destroy', id), {
        preserveScroll: true,
    });
}

// ─── Computed total ───────────────────────────
const computedTotal = () => {
    return form.items.reduce((sum, item) => {
        const cents = Math.round(item.unit_cost * 100) || 0;
        return sum + cents * (item.quantity_ordered || 0);
    }, 0);
};

const computedEditTotal = () => {
    return editForm.items.reduce((sum, item) => {
        const cents = Math.round(item.unit_cost * 100) || 0;
        return sum + cents * (item.quantity_ordered || 0);
    }, 0);
};

// ─── Format helpers ───────────────────────────
const fmt = (v: number) =>
    '$' + (v / 100).toLocaleString('es-CO', { minimumFractionDigits: 0 });

const statusLabel: Record<string, string> = {
    pending: 'Pendiente',
    received: 'Recibida',
    paid: 'Pagada',
    overdue: 'Vencida',
};

const statusColor: Record<string, string> = {
    pending: 'bg-warning/10 text-warning',
    received: 'bg-success/10 text-success',
    paid: 'bg-primary/10 text-primary',
    overdue: 'bg-danger/10 text-danger',
};

function priceVariation(item: any) {
    if (item.is_new_product) return { label: 'Nuevo', cls: 'text-blue-500' };
    if (!item.previous_cost) return { label: 'Nuevo', cls: 'text-blue-500' };
    const diff = item.unit_cost - item.previous_cost;
    const pct =
        item.previous_cost > 0
            ? ((diff / item.previous_cost) * 100).toFixed(1)
            : '0';
    if (diff > 0)
        return {
            label: `▲ +$${(diff / 100).toLocaleString('es-CO', { minimumFractionDigits: 0 })} (${pct}%)`,
            cls: 'text-danger',
        };
    if (diff < 0)
        return {
            label: `▼ $${(Math.abs(diff) / 100).toLocaleString('es-CO', { minimumFractionDigits: 0 })} (${Math.abs(Number(pct))}%)`,
            cls: 'text-success',
        };
    return { label: '= Sin cambio', cls: 'text-gray-400' };
}
</script>

<template>
    <Head title="Facturas Pendientes" />
    <AdminLayout>
        <template #title>
            <h1
                class="font-display text-xl font-bold text-content-primary dark:text-white"
            >
                Cuentas por Pagar
            </h1>
        </template>

        <!-- Filters -->
        <div
            class="mb-4 rounded-3xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-surface-dark"
        >
            <div
                class="flex flex-wrap items-end gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-800"
            >
                <div class="min-w-[140px]">
                    <label
                        class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                        >Estado</label
                    >
                    <select
                        v-model="filterStatus"
                        @change="applyFilters"
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                    >
                        <option value="">Todos</option>
                        <option value="pending">Pendiente</option>
                        <option value="received">Recibida</option>
                        <option value="paid">Pagada</option>
                        <option value="overdue">Vencida</option>
                    </select>
                </div>
                <div class="min-w-[160px]">
                    <label
                        class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                        >Proveedor</label
                    >
                    <select
                        v-model="filterSupplier"
                        @change="applyFilters"
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                    >
                        <option :value="null">Todos</option>
                        <option
                            v-for="s in suppliers"
                            :key="s.id"
                            :value="s.id"
                        >
                            {{ s.company_name }}
                        </option>
                    </select>
                </div>
                <DateFilter
                    v-model="filterDateFrom"
                    label="Desde"
                    @select="applyFilters"
                />
                <DateFilter
                    v-model="filterDateTo"
                    label="Hasta"
                    @select="applyFilters"
                />
                <div class="min-w-[180px] flex-1">
                    <label
                        class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                        >Buscar</label
                    >
                    <input
                        v-model="filterSearch"
                        @keyup.enter="applyFilters"
                        type="text"
                        placeholder="Factura o proveedor..."
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                    />
                </div>
                <button
                    @click="applyFilters"
                    class="rounded-xl bg-primary-500 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition-colors hover:bg-primary-600"
                >
                    Filtrar
                </button>
            </div>
            <div class="flex items-center justify-between px-6 py-3">
                <span class="text-sm text-content-muted dark:text-gray-500">
                    {{ invoices.data?.length ?? 0 }} facturas
                </span>
                <button
                    @click="openNew"
                    class="flex items-center gap-2 rounded-2xl bg-primary-500 px-4 py-2 text-sm font-bold text-white shadow-sm transition-colors hover:bg-primary-600"
                >
                    <Plus class="h-4 w-4" /> Nueva Factura
                </button>
            </div>
        </div>

        <!-- Invoices Table -->
        <div
            class="rounded-3xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-surface-dark"
        >
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead
                        class="bg-gray-50 text-xs uppercase tracking-wider text-content-muted dark:bg-gray-900/50 dark:text-gray-500"
                    >
                        <tr>
                            <th class="px-6 py-3 font-bold"># Factura</th>
                            <th class="px-6 py-3 font-bold">Proveedor</th>
                            <th class="px-6 py-3 font-bold">Emisión</th>
                            <th class="px-6 py-3 font-bold">Vencimiento</th>
                            <th class="px-6 py-3 font-bold">Entrega</th>
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
                        <tr v-if="!invoices.data?.length">
                            <td
                                colspan="8"
                                class="px-6 py-12 text-center text-sm text-content-muted dark:text-gray-500"
                            >
                                No hay facturas registradas.
                            </td>
                        </tr>
                        <tr
                            v-for="inv in invoices.data"
                            :key="inv.id"
                            class="group transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/50"
                        >
                            <td
                                class="px-6 py-4 font-mono text-sm font-bold text-content-primary dark:text-white"
                            >
                                {{ inv.invoice_number || '#' + inv.id }}
                            </td>
                            <td
                                class="px-6 py-4 text-sm font-medium text-content-primary dark:text-white"
                            >
                                {{ inv.supplier?.company_name || '—' }}
                            </td>
                            <td
                                class="px-6 py-4 text-sm text-content-secondary"
                            >
                                {{ formatDate(inv.issue_date) }}
                            </td>
                            <td
                                class="px-6 py-4 text-sm text-content-secondary"
                            >
                                {{ formatDate(inv.due_date) }}
                                <span
                                    v-if="inv.is_overdue"
                                    class="ml-1 text-[10px] font-bold text-danger"
                                    >VENCIDA</span
                                >
                            </td>
                            <td
                                class="px-6 py-4 text-sm text-content-secondary"
                            >
                                {{
                                    inv.delivery_date
                                        ? formatDate(inv.delivery_date)
                                        : '—'
                                }}
                            </td>
                            <td
                                class="px-6 py-4 text-right text-sm font-bold text-primary-500"
                            >
                                {{ fmt(inv.total_amount) }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span
                                    :class="
                                        statusColor[inv.status] ||
                                        'bg-gray-100 text-gray-500'
                                    "
                                    class="rounded-lg px-2.5 py-1 text-[10px] font-bold uppercase"
                                >
                                    {{ statusLabel[inv.status] || inv.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div
                                    class="flex items-center justify-end gap-2 opacity-0 transition-opacity group-hover:opacity-100"
                                >
                                    <button
                                        @click="viewDetail(inv)"
                                        class="rounded-xl p-2 text-blue-500 transition-colors hover:bg-blue-50 dark:hover:bg-blue-900/20"
                                        title="Ver detalle"
                                    >
                                        <Eye class="h-4 w-4" />
                                    </button>
                                    <button
                                        v-if="inv.status === 'pending'"
                                        @click="openEdit(inv)"
                                        class="rounded-xl p-2 text-amber-500 transition-colors hover:bg-amber-50 dark:hover:bg-amber-900/20"
                                        title="Editar"
                                    >
                                        <Pencil class="h-4 w-4" />
                                    </button>
                                    <button
                                        v-if="inv.status === 'pending'"
                                        @click="openReceive(inv)"
                                        class="rounded-xl p-2 text-success transition-colors hover:bg-success/10"
                                        title="Recibir"
                                    >
                                        <Truck class="h-4 w-4" />
                                    </button>
                                    <button
                                        v-if="inv.status === 'received'"
                                        @click="markPaid(inv)"
                                        class="rounded-xl p-2 text-primary-500 transition-colors hover:bg-primary-50 dark:hover:bg-primary-900/20"
                                        title="Marcar pagada"
                                    >
                                        <CreditCard class="h-4 w-4" />
                                    </button>
                                    <button
                                        v-if="inv.status === 'pending'"
                                        @click="deleteInvoice(inv.id)"
                                        class="rounded-xl p-2 text-danger transition-colors hover:bg-red-50 dark:hover:bg-red-900/20"
                                        title="Eliminar"
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
                v-if="invoices.last_page > 1"
                class="flex items-center justify-between border-t border-gray-100 px-6 py-4 text-sm text-content-muted dark:border-gray-800 dark:text-gray-500"
            >
                <span
                    >Página {{ invoices.current_page }} de
                    {{ invoices.last_page }}</span
                >
                <div class="flex gap-2">
                    <a
                        v-if="invoices.prev_page_url"
                        :href="invoices.prev_page_url"
                        class="rounded-lg bg-gray-50 px-3 py-1.5 font-bold transition-colors hover:bg-gray-100 dark:bg-gray-900"
                        >←</a
                    >
                    <a
                        v-if="invoices.next_page_url"
                        :href="invoices.next_page_url"
                        class="rounded-lg bg-gray-50 px-3 py-1.5 font-bold transition-colors hover:bg-gray-100 dark:bg-gray-900"
                        >→</a
                    >
                </div>
            </div>
        </div>

        <!-- New Invoice Modal -->
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
                    class="relative my-auto max-h-[90vh] w-[95%] max-w-7xl overflow-y-auto rounded-3xl bg-white p-4 shadow-2xl dark:bg-surface-dark md:p-6 lg:p-8"
                >
                    <div class="mb-6 flex items-center justify-between">
                        <h3
                            class="font-display text-lg font-bold text-content-primary dark:text-white"
                        >
                            Nueva Factura
                        </h3>
                        <button
                            @click="closeForm"
                            class="rounded-xl p-2 transition-colors hover:bg-gray-100 dark:hover:bg-gray-800"
                        >
                            <X class="h-5 w-5 text-content-muted" />
                        </button>
                    </div>
                    <form @submit.prevent="submitForm" class="space-y-4">
                        <!-- Proveedor (full width) -->
                        <div>
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
                        </div>

                        <hr
                            class="border-t border-gray-200 dark:border-gray-700"
                        />

                        <!-- Fechas (2-column) -->
                        <div
                            class="grid grid-cols-1 items-end gap-4 md:grid-cols-2"
                        >
                            <DateFilter
                                v-model="form.issue_date"
                                label="Fecha de Emisión"
                            />
                            <DateFilter
                                v-model="form.due_date"
                                label="Fecha de Vencimiento"
                            />
                        </div>

                        <hr
                            class="border-t border-gray-200 dark:border-gray-700"
                        />

                        <!-- Products section -->
                        <div>
                            <h4
                                class="mb-3 text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                            >
                                PRODUCTOS
                            </h4>
                            <div class="mb-3 flex gap-2">
                                <button
                                    type="button"
                                    @click="addItem()"
                                    class="flex items-center justify-center gap-2 rounded-xl bg-pink-500 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-all hover:bg-pink-600 active:scale-95"
                                >
                                    <Plus class="h-4 w-4" /> Añadir Producto
                                </button>
                                <button
                                    type="button"
                                    @click="
                                        isQuickAddEdit = false;
                                        showQuickAdd = true;
                                    "
                                    class="flex items-center justify-center gap-2 rounded-xl border-2 border-dashed border-amber-400 px-5 py-2.5 text-sm font-semibold text-amber-600 transition-all hover:bg-amber-50 active:scale-95"
                                >
                                    <Plus class="h-4 w-4" /> Producto rápido
                                </button>
                            </div>
                            <div>
                                <table class="w-full text-left text-sm">
                                    <thead
                                        class="text-[10px] uppercase tracking-wider text-content-muted"
                                    >
                                        <tr
                                            class="border-b border-gray-100 dark:border-gray-800"
                                        >
                                            <th class="px-2 py-1.5 font-bold">
                                                SKU
                                            </th>
                                            <th class="px-2 py-1.5 font-bold">
                                                Producto
                                            </th>
                                            <th class="px-2 py-1.5 font-bold">
                                                Categoría
                                            </th>
                                            <th class="px-2 py-1.5 font-bold">
                                                Subcategoría
                                            </th>
                                            <th
                                                class="px-2 py-1.5 text-right font-bold"
                                            >
                                                Costo $
                                            </th>
                                            <th
                                                class="px-2 py-1.5 text-right font-bold"
                                            >
                                                Cant.
                                            </th>
                                            <th
                                                class="px-2 py-1.5 text-right font-bold"
                                            >
                                                Subtotal
                                            </th>
                                            <th class="px-2 py-1.5"></th>
                                        </tr>
                                    </thead>
                                    <tbody
                                        class="divide-y divide-gray-50 dark:divide-gray-800"
                                    >
                                        <tr
                                            v-for="(item, index) in form.items"
                                            :key="index"
                                        >
                                            <td class="px-2 py-1.5">
                                                <input
                                                    v-model="item.sku"
                                                    @input="onSkuInput(index)"
                                                    type="text"
                                                    placeholder="SKU"
                                                    class="w-28 rounded-lg border-gray-200 px-2 py-1.5 text-xs dark:border-gray-700 dark:bg-surface-dark dark:text-white"
                                                />
                                            </td>
                                            <td class="relative px-2 py-1.5">
                                                <input
                                                    v-model="item.product_name"
                                                    @keydown.down.prevent="
                                                        onProductArrowDown(
                                                            index,
                                                        )
                                                    "
                                                    @keydown.up.prevent="
                                                        onProductArrowUp()
                                                    "
                                                    @keydown.enter.prevent="
                                                        onProductEnter(index)
                                                    "
                                                    @input="onNameInput(index)"
                                                    @focus="
                                                        showNameDropdown =
                                                            index;
                                                        focusedProductIndex =
                                                            -1;
                                                    "
                                                    @blur="
                                                        handleNameBlur(index)
                                                    "
                                                    type="text"
                                                    placeholder="Buscar..."
                                                    :class="[
                                                        'product-name-input-' +
                                                            index,
                                                        'w-48 rounded-lg border-gray-200 px-2 py-1.5 text-xs dark:border-gray-700 dark:bg-surface-dark dark:text-white',
                                                    ]"
                                                />
                                                <div
                                                    v-if="
                                                        showNameDropdown ===
                                                            index &&
                                                        !item.product_id &&
                                                        item.product_name
                                                    "
                                                    class="absolute left-2 right-2 top-full z-20 mt-0.5 max-h-32 overflow-y-auto rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-surface-dark"
                                                >
                                                    <button
                                                        v-for="(
                                                            p, pIndex
                                                        ) in filteredByName(
                                                            index,
                                                        )"
                                                        :key="p.id"
                                                        @mousedown.prevent="
                                                            selectByName(
                                                                index,
                                                                p,
                                                            )
                                                        "
                                                        :class="[
                                                            'flex w-full items-center gap-1 border-b border-gray-100 px-2 py-1.5 text-left text-xs last:border-0 hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-800',
                                                            {
                                                                'bg-pink-50 font-medium text-pink-700':
                                                                    pIndex ===
                                                                    focusedProductIndex,
                                                            },
                                                        ]"
                                                    >
                                                        <span
                                                            class="font-medium"
                                                            >{{ p.name }}</span
                                                        >
                                                        <span
                                                            class="text-[10px] text-content-muted"
                                                            >({{ p.sku }})</span
                                                        >
                                                    </button>
                                                    <div
                                                        v-if="
                                                            !filteredByName(
                                                                index,
                                                            ).length
                                                        "
                                                        class="border-t border-gray-100 px-2 py-1.5 dark:border-gray-800"
                                                    >
                                                        <button
                                                            type="button"
                                                            @mousedown.prevent="
                                                                markAsNew(index)
                                                            "
                                                            class="text-[10px] font-bold text-amber-600 hover:text-amber-700"
                                                        >
                                                            + Crear "{{
                                                                item.product_name
                                                            }}"
                                                        </button>
                                                    </div>
                                                </div>
                                                <span
                                                    v-if="item.is_new_product"
                                                    class="ml-1 rounded bg-amber-100 px-1 text-[10px] font-bold text-amber-700"
                                                    >Nuevo</span
                                                >
                                            </td>
                                            <td class="px-2 py-1.5">
                                                <select
                                                    v-model="item.category_name"
                                                    class="w-32 rounded-lg border-gray-200 px-1 py-1.5 text-xs dark:border-gray-700 dark:bg-surface-dark dark:text-white"
                                                >
                                                    <option value="">
                                                        Sin categoría
                                                    </option>
                                                    <option
                                                        v-for="cat in categories"
                                                        :key="cat.id"
                                                        :value="cat.name"
                                                    >
                                                        {{ cat.name }}
                                                    </option>
                                                </select>
                                            </td>
                                            <td class="px-2 py-1.5">
                                                <select
                                                    v-model="
                                                        item.subcategory_name
                                                    "
                                                    :disabled="
                                                        !item.category_name
                                                    "
                                                    class="w-32 rounded-lg border-gray-200 px-1 py-1.5 text-xs dark:border-gray-700 dark:bg-surface-dark dark:text-white"
                                                >
                                                    <option value="">—</option>
                                                    <option
                                                        v-for="sub in categories.find(
                                                            (c) =>
                                                                c.name ===
                                                                item.category_name,
                                                        )?.children || []"
                                                        :key="sub.id"
                                                        :value="sub.name"
                                                    >
                                                        {{ sub.name }}
                                                    </option>
                                                </select>
                                            </td>
                                            <td class="px-2 py-1.5">
                                                <div class="relative">
                                                    <span
                                                        class="absolute left-2 top-1/2 -translate-y-1/2 text-[10px] text-gray-400"
                                                        >$</span
                                                    >
                                                    <input
                                                        v-model.number="
                                                            item.unit_cost
                                                        "
                                                        @keydown.enter.prevent="
                                                            handlePrecioEnter(
                                                                index,
                                                            )
                                                        "
                                                        type="number"
                                                        min="0"
                                                        step="1"
                                                        class="w-24 rounded-lg border-gray-200 py-1.5 pl-4 pr-1 text-right text-xs dark:border-gray-700 dark:bg-surface-dark dark:text-white"
                                                    />
                                                </div>
                                            </td>
                                            <td class="px-2 py-1.5 text-right">
                                                <input
                                                    v-model.number="
                                                        item.quantity_ordered
                                                    "
                                                    @keydown.enter.prevent="
                                                        handleCantidadEnter()
                                                    "
                                                    type="number"
                                                    min="1"
                                                    required
                                                    class="w-16 rounded-lg border-gray-200 px-1 py-1.5 text-center text-xs dark:border-gray-700 dark:bg-surface-dark dark:text-white"
                                                />
                                            </td>
                                            <td
                                                class="px-2 py-1.5 text-right text-xs font-bold text-gray-800 dark:text-gray-200"
                                            >
                                                ${{
                                                    (
                                                        (Math.round(
                                                            item.unit_cost,
                                                        ) || 0) *
                                                        (item.quantity_ordered ||
                                                            0)
                                                    ).toLocaleString('es-CO', {
                                                        minimumFractionDigits: 0,
                                                    })
                                                }}
                                            </td>
                                            <td class="px-2 py-1.5 text-right">
                                                <button
                                                    @click="removeItem(index)"
                                                    class="text-red-400 transition-colors hover:text-red-600"
                                                >
                                                    <X class="h-3.5 w-3.5" />
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Summary -->
                        <div
                            class="flex flex-wrap items-center justify-end gap-4 text-sm text-content-secondary"
                        >
                            <span
                                >Productos:
                                <strong
                                    class="text-content-primary dark:text-white"
                                    >{{ form.items.length }}</strong
                                ></span
                            >
                            <span
                                >Unidades:
                                <strong
                                    class="text-content-primary dark:text-white"
                                    >{{
                                        form.items.reduce(
                                            (s, i) =>
                                                s + (i.quantity_ordered || 0),
                                            0,
                                        )
                                    }}</strong
                                ></span
                            >
                            <span
                                class="text-base font-bold text-primary-500"
                                >{{ fmt(computedTotal()) }}</span
                            >
                        </div>

                        <hr
                            class="border-t border-gray-200 dark:border-gray-700"
                        />

                        <!-- Delivery date & Notes -->
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <DateFilter
                                v-model="form.delivery_date"
                                label="Fecha de Entrega (opcional)"
                            />
                            <div>
                                <label
                                    class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                                    >Notas</label
                                >
                                <textarea
                                    v-model="form.notes"
                                    ref="notasInputRef"
                                    rows="2"
                                    maxlength="500"
                                    placeholder="Notas opcionales..."
                                    class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                ></textarea>
                            </div>
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
                                <Check class="h-4 w-4" /> Registrar Factura
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>

        <!-- Edit Invoice Modal -->
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="showEdit"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4 backdrop-blur-sm md:p-6"
            >
                <div
                    class="relative my-auto max-h-[90vh] w-[95%] max-w-7xl overflow-y-auto rounded-3xl bg-white p-4 shadow-2xl dark:bg-surface-dark md:p-6 lg:p-8"
                >
                    <div class="mb-6 flex items-center justify-between">
                        <h3
                            class="font-display text-lg font-bold text-content-primary dark:text-white"
                        >
                            Editar Factura
                        </h3>
                        <button
                            @click="closeEdit"
                            class="rounded-xl p-2 transition-colors hover:bg-gray-100 dark:hover:bg-gray-800"
                        >
                            <X class="h-5 w-5 text-content-muted" />
                        </button>
                    </div>
                    <form @submit.prevent="submitEdit" class="space-y-4">
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
                                    v-model="supplierSearchEdit"
                                    @focus="showSupplierDropdownEdit = true"
                                    @blur="handleSupplierBlurEdit"
                                    @keydown.down.prevent="
                                        onSupplierArrowDownEdit
                                    "
                                    @keydown.up.prevent="onSupplierArrowUpEdit"
                                    @keydown.enter.prevent="onSupplierEnterEdit"
                                    type="text"
                                    placeholder="Buscar proveedor..."
                                    class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                />
                                <div
                                    v-if="showSupplierDropdownEdit"
                                    class="absolute left-0 right-0 top-full z-20 mt-1 max-h-48 overflow-y-auto rounded-xl border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-surface-dark"
                                >
                                    <button
                                        v-for="(
                                            s, idx
                                        ) in filteredSuppliersEdit()"
                                        :key="s.id"
                                        @mousedown.prevent="
                                            selectSupplierEdit(s)
                                        "
                                        :class="[
                                            'flex w-full items-center gap-2 border-b border-gray-100 px-3 py-2 text-left text-sm text-content-primary last:border-0 hover:bg-gray-50 dark:border-gray-800 dark:text-white dark:hover:bg-gray-800',
                                            idx === focusedSupplierIndexEdit
                                                ? 'bg-gray-100 dark:bg-gray-800'
                                                : '',
                                        ]"
                                    >
                                        <span class="font-medium">{{
                                            s.company_name
                                        }}</span>
                                    </button>
                                </div>
                            </div>
                            <DateFilter
                                v-model="editForm.issue_date"
                                label="Fecha de Emisión"
                            />
                            <DateFilter
                                v-model="editForm.due_date"
                                label="Fecha de Vencimiento"
                            />
                        </div>

                        <!-- Items -->
                        <div>
                            <div class="mb-2 flex items-center justify-between">
                                <label
                                    class="block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                                    >Productos</label
                                >
                                <button
                                    type="button"
                                    @click="
                                        editForm.items.push({
                                            product_id: null,
                                            sku: '',
                                            product_name: '',
                                            quantity_ordered: 1,
                                            unit_cost: 0,
                                            is_new_product: false,
                                            category_name: '',
                                            subcategory_name: '',
                                        })
                                    "
                                    class="flex items-center justify-center gap-2 rounded-xl bg-pink-500 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-all hover:bg-pink-600 active:scale-95"
                                >
                                    <Plus class="h-4 w-4" /> Agregar Producto
                                </button>
                            </div>
                            <div>
                                <table class="w-full text-left text-sm">
                                    <thead
                                        class="text-[10px] uppercase tracking-wider text-content-muted"
                                    >
                                        <tr
                                            class="border-b border-gray-100 dark:border-gray-800"
                                        >
                                            <th class="px-2 py-1.5 font-bold">
                                                SKU
                                            </th>
                                            <th class="px-2 py-1.5 font-bold">
                                                Producto
                                            </th>
                                            <th class="px-2 py-1.5 font-bold">
                                                Categoría
                                            </th>
                                            <th class="px-2 py-1.5 font-bold">
                                                Subcategoría
                                            </th>
                                            <th
                                                class="px-2 py-1.5 text-right font-bold"
                                            >
                                                Costo $
                                            </th>
                                            <th
                                                class="px-2 py-1.5 text-right font-bold"
                                            >
                                                Cant.
                                            </th>
                                            <th
                                                class="px-2 py-1.5 text-right font-bold"
                                            >
                                                Subtotal
                                            </th>
                                            <th class="px-2 py-1.5"></th>
                                        </tr>
                                    </thead>
                                    <tbody
                                        class="divide-y divide-gray-50 dark:divide-gray-800"
                                    >
                                        <tr
                                            v-for="(
                                                item, index
                                            ) in editForm.items"
                                            :key="index"
                                        >
                                            <td class="px-2 py-1.5">
                                                <input
                                                    v-model="item.sku"
                                                    @input="
                                                        onSkuInputEdit(index)
                                                    "
                                                    type="text"
                                                    placeholder="SKU"
                                                    class="w-28 rounded-lg border-gray-200 px-2 py-1.5 text-xs dark:border-gray-700 dark:bg-surface-dark dark:text-white"
                                                />
                                            </td>
                                            <td class="relative px-2 py-1.5">
                                                <input
                                                    v-model="item.product_name"
                                                    :ref="
                                                        (el: any) =>
                                                            (editProductoInputRefs[
                                                                index
                                                            ] = el)
                                                    "
                                                    @keydown.down.prevent="
                                                        onProductArrowDownEdit(
                                                            index,
                                                        )
                                                    "
                                                    @keydown.up.prevent="
                                                        onProductArrowUpEdit()
                                                    "
                                                    @keydown.enter.prevent="
                                                        onProductEnterEdit(
                                                            index,
                                                        )
                                                    "
                                                    @input="
                                                        onNameInputEdit(index)
                                                    "
                                                    @focus="
                                                        showNameDropdownEdit =
                                                            index;
                                                        focusedProductIndexEdit =
                                                            -1;
                                                    "
                                                    @blur="
                                                        handleNameBlurEdit(
                                                            index,
                                                        )
                                                    "
                                                    type="text"
                                                    placeholder="Buscar..."
                                                    class="w-48 rounded-lg border-gray-200 px-2 py-1.5 text-xs dark:border-gray-700 dark:bg-surface-dark dark:text-white"
                                                />
                                                <div
                                                    v-if="
                                                        showNameDropdownEdit ===
                                                            index &&
                                                        !item.product_id &&
                                                        item.product_name
                                                    "
                                                    class="absolute left-2 right-2 top-full z-20 mt-0.5 max-h-32 overflow-y-auto rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-surface-dark"
                                                >
                                                    <button
                                                        v-for="(
                                                            p, pIndex
                                                        ) in filteredByNameEdit(
                                                            index,
                                                        )"
                                                        :key="p.id"
                                                        @mousedown.prevent="
                                                            selectByNameEdit(
                                                                index,
                                                                p,
                                                            )
                                                        "
                                                        :class="[
                                                            'flex w-full items-center gap-1 border-b border-gray-100 px-2 py-1.5 text-left text-xs last:border-0 hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-800',
                                                            {
                                                                'bg-pink-50 font-medium text-pink-700':
                                                                    pIndex ===
                                                                    focusedProductIndexEdit,
                                                            },
                                                        ]"
                                                    >
                                                        <span
                                                            class="font-medium"
                                                            >{{ p.name }}</span
                                                        >
                                                        <span
                                                            class="text-[10px] text-content-muted"
                                                            >({{ p.sku }})</span
                                                        >
                                                    </button>
                                                    <div
                                                        v-if="
                                                            !filteredByNameEdit(
                                                                index,
                                                            ).length
                                                        "
                                                        class="border-t border-gray-100 px-2 py-1.5 dark:border-gray-800"
                                                    >
                                                        <button
                                                            type="button"
                                                            @mousedown.prevent="
                                                                markAsNewEdit(
                                                                    index,
                                                                )
                                                            "
                                                            class="text-[10px] font-bold text-amber-600 hover:text-amber-700"
                                                        >
                                                            + Crear "{{
                                                                item.product_name
                                                            }}"
                                                        </button>
                                                    </div>
                                                </div>
                                                <span
                                                    v-if="item.is_new_product"
                                                    class="ml-1 rounded bg-amber-100 px-1 text-[10px] font-bold text-amber-700"
                                                    >Nuevo</span
                                                >
                                            </td>
                                            <td class="px-2 py-1.5">
                                                <select
                                                    v-model="item.category_name"
                                                    class="w-32 rounded-lg border-gray-200 px-1 py-1.5 text-xs dark:border-gray-700 dark:bg-surface-dark dark:text-white"
                                                >
                                                    <option value="">
                                                        Sin categoría
                                                    </option>
                                                    <option
                                                        v-for="cat in categories"
                                                        :key="cat.id"
                                                        :value="cat.name"
                                                    >
                                                        {{ cat.name }}
                                                    </option>
                                                </select>
                                            </td>
                                            <td class="px-2 py-1.5">
                                                <select
                                                    v-model="
                                                        item.subcategory_name
                                                    "
                                                    :disabled="
                                                        !item.category_name
                                                    "
                                                    class="w-32 rounded-lg border-gray-200 px-1 py-1.5 text-xs dark:border-gray-700 dark:bg-surface-dark dark:text-white"
                                                >
                                                    <option value="">—</option>
                                                    <option
                                                        v-for="sub in categories.find(
                                                            (c) =>
                                                                c.name ===
                                                                item.category_name,
                                                        )?.children || []"
                                                        :key="sub.id"
                                                        :value="sub.name"
                                                    >
                                                        {{ sub.name }}
                                                    </option>
                                                </select>
                                            </td>
                                            <td class="px-2 py-1.5">
                                                <div class="relative">
                                                    <span
                                                        class="absolute left-2 top-1/2 -translate-y-1/2 text-[10px] text-gray-400"
                                                        >$</span
                                                    >
                                                    <input
                                                        v-model.number="
                                                            item.unit_cost
                                                        "
                                                        type="number"
                                                        min="0"
                                                        step="1"
                                                        class="w-24 rounded-lg border-gray-200 py-1.5 pl-4 pr-1 text-right text-xs dark:border-gray-700 dark:bg-surface-dark dark:text-white"
                                                    />
                                                </div>
                                            </td>
                                            <td class="px-2 py-1.5 text-right">
                                                <input
                                                    v-model.number="
                                                        item.quantity_ordered
                                                    "
                                                    type="number"
                                                    min="1"
                                                    required
                                                    class="w-16 rounded-lg border-gray-200 px-1 py-1.5 text-center text-xs dark:border-gray-700 dark:bg-surface-dark dark:text-white"
                                                />
                                            </td>
                                            <td
                                                class="px-2 py-1.5 text-right text-xs font-bold text-gray-800 dark:text-gray-200"
                                            >
                                                ${{
                                                    (
                                                        (Math.round(
                                                            item.unit_cost,
                                                        ) || 0) *
                                                        (item.quantity_ordered ||
                                                            0)
                                                    ).toLocaleString('es-CO', {
                                                        minimumFractionDigits: 0,
                                                    })
                                                }}
                                            </td>
                                            <td class="px-2 py-1.5 text-right">
                                                <button
                                                    @click="
                                                        editForm.items.splice(
                                                            index,
                                                            1,
                                                        )
                                                    "
                                                    class="text-red-400 transition-colors hover:text-red-600"
                                                >
                                                    <X class="h-3.5 w-3.5" />
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div
                            class="flex flex-wrap items-center justify-end gap-4 text-sm text-content-secondary"
                        >
                            <span
                                >Productos:
                                <strong
                                    class="text-content-primary dark:text-white"
                                    >{{ editForm.items.length }}</strong
                                ></span
                            >
                            <span
                                >Unidades:
                                <strong
                                    class="text-content-primary dark:text-white"
                                    >{{
                                        editForm.items.reduce(
                                            (s, i) =>
                                                s + (i.quantity_ordered || 0),
                                            0,
                                        )
                                    }}</strong
                                ></span
                            >
                            <span
                                class="text-base font-bold text-primary-500"
                                >{{ fmt(computedEditTotal()) }}</span
                            >
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <DateFilter
                                v-model="editForm.delivery_date"
                                label="Fecha de Entrega (opcional)"
                            />
                            <div>
                                <label
                                    class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                                    >Notas</label
                                >
                                <textarea
                                    v-model="editForm.notes"
                                    rows="2"
                                    maxlength="500"
                                    class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                ></textarea>
                            </div>
                        </div>

                        <div class="flex gap-3 pt-2">
                            <button
                                type="button"
                                @click="closeEdit"
                                class="flex-1 rounded-2xl border border-gray-200 py-2.5 text-sm font-bold text-content-secondary transition-colors hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800"
                            >
                                Cancelar
                            </button>
                            <button
                                type="submit"
                                :disabled="editForm.processing"
                                class="flex flex-1 items-center justify-center gap-2 rounded-2xl bg-primary-500 py-2.5 text-sm font-bold text-white shadow-sm transition-colors hover:bg-primary-600"
                            >
                                <Check class="h-4 w-4" /> Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>

        <!-- Quick Add Modal -->
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="showQuickAdd"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4 backdrop-blur-sm"
            >
                <div
                    class="relative w-[95%] max-w-xl rounded-3xl bg-white p-4 shadow-xl dark:bg-surface-dark md:p-6"
                >
                    <div class="mb-6 flex items-center justify-between">
                        <h3
                            class="font-display text-lg font-bold text-content-primary dark:text-white"
                        >
                            Producto rápido
                        </h3>
                        <button
                            @click="closeQuickAdd"
                            class="rounded-xl p-2 transition-colors hover:bg-gray-100 dark:hover:bg-gray-800"
                        >
                            <X class="h-5 w-5 text-content-muted" />
                        </button>
                    </div>
                    <form @submit.prevent="confirmQuickAdd" class="space-y-4">
                        <template v-if="!quickAddDone">
                            <div>
                                <label
                                    class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                                    >Categoría</label
                                >
                                <select
                                    v-model="quickForm.category_name"
                                    required
                                    class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                >
                                    <option value="" disabled>
                                        Seleccionar categoría
                                    </option>
                                    <option
                                        v-for="cat in categories"
                                        :key="cat.id"
                                        :value="cat.name"
                                    >
                                        {{ cat.name }}
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label
                                    class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                                    >Subcategoría</label
                                >
                                <select
                                    v-model="quickForm.subcategory_name"
                                    :disabled="!quickForm.category_name"
                                    class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                >
                                    <option value="">Sin subcategoría</option>
                                    <option
                                        v-for="sub in filteredSubcategories"
                                        :key="sub.id"
                                        :value="sub.name"
                                    >
                                        {{ sub.name }}
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label
                                    class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                                    >Nombre del producto</label
                                >
                                <input
                                    v-model="quickForm.name"
                                    type="text"
                                    required
                                    placeholder="Ej: Arroz Diana x 1kg"
                                    class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                />
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label
                                        class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                                        >SKU (opcional)</label
                                    >
                                    <input
                                        v-model="quickForm.sku"
                                        type="text"
                                        placeholder="Código interno"
                                        class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                    />
                                </div>
                                <div>
                                    <label
                                        class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                                        >Código barras (opcional)</label
                                    >
                                    <input
                                        v-model="quickForm.barcode"
                                        type="text"
                                        placeholder="Barcode"
                                        class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                    />
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label
                                        class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                                        >Costo unitario</label
                                    >
                                    <div class="relative">
                                        <span
                                            class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-gray-400"
                                            >$</span
                                        >
                                        <input
                                            v-model.number="quickForm.unit_cost"
                                            type="number"
                                            min="0"
                                            step="1"
                                            required
                                            class="w-full rounded-2xl border border-gray-200 bg-gray-50 py-2.5 pl-7 pr-4 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                        />
                                    </div>
                                </div>
                                <div>
                                    <label
                                        class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                                        >Cantidad</label
                                    >
                                    <input
                                        v-model.number="quickForm.quantity"
                                        type="number"
                                        min="1"
                                        required
                                        class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                    />
                                </div>
                            </div>
                            <div class="flex gap-3 pt-2">
                                <button
                                    type="button"
                                    @click="closeQuickAdd"
                                    class="flex-1 rounded-2xl border border-gray-200 py-2.5 text-sm font-bold text-content-secondary transition-colors hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800"
                                >
                                    Cancelar
                                </button>
                                <button
                                    type="submit"
                                    class="flex flex-1 items-center justify-center gap-2 rounded-2xl bg-amber-500 py-2.5 text-sm font-bold text-white shadow-sm transition-colors hover:bg-amber-600"
                                >
                                    <Plus class="h-4 w-4" /> Añadir
                                </button>
                            </div>
                        </template>
                        <template v-else>
                            <div class="py-6 text-center">
                                <p class="mb-4 text-lg font-bold text-success">
                                    ✔ Producto agregado exitosamente
                                </p>
                                <div class="flex gap-3">
                                    <button
                                        type="button"
                                        @click="addAnotherQuick"
                                        class="flex-1 rounded-2xl border-2 border-dashed border-amber-400 py-2.5 text-sm font-bold text-amber-600 transition-colors hover:bg-amber-50"
                                    >
                                        + Agregar otro
                                    </button>
                                    <button
                                        type="button"
                                        @click="closeQuickAdd"
                                        class="flex-1 rounded-2xl bg-primary-500 py-2.5 text-sm font-bold text-white shadow-sm transition-colors hover:bg-primary-600"
                                    >
                                        Volver a factura
                                    </button>
                                </div>
                            </div>
                        </template>
                    </form>
                </div>
            </div>
        </Transition>

        <!-- Detail Modal -->
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="showDetail && detailInvoice"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4 backdrop-blur-sm"
            >
                <div
                    class="relative w-[95%] max-w-lg rounded-3xl bg-white p-4 shadow-xl dark:bg-surface-dark md:p-6"
                >
                    <div class="mb-6 flex items-center justify-between">
                        <h3
                            class="font-display text-lg font-bold text-content-primary dark:text-white"
                        >
                            Factura
                            {{
                                detailInvoice.invoice_number ||
                                '#' + detailInvoice.id
                            }}
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
                                    detailInvoice.supplier?.company_name || '—'
                                }}</span
                            >
                        </div>
                        <div class="flex justify-between">
                            <span class="text-content-muted">Emisión</span>
                            <span
                                class="font-medium text-content-primary dark:text-white"
                                >{{
                                    formatDate(detailInvoice.issue_date)
                                }}</span
                            >
                        </div>
                        <div class="flex justify-between">
                            <span class="text-content-muted">Vencimiento</span>
                            <span
                                class="font-medium text-content-primary dark:text-white"
                                >{{ formatDate(detailInvoice.due_date) }}</span
                            >
                        </div>
                        <div
                            v-if="detailInvoice.delivery_date"
                            class="flex justify-between"
                        >
                            <span class="text-content-muted">Entrega</span>
                            <span
                                class="font-medium text-content-primary dark:text-white"
                                >{{
                                    formatDate(detailInvoice.delivery_date)
                                }}</span
                            >
                        </div>
                        <div
                            v-if="detailInvoice.received_at"
                            class="flex justify-between"
                        >
                            <span class="text-content-muted">Recibida</span>
                            <span
                                class="font-medium text-content-primary dark:text-white"
                                >{{ formatDate(detailInvoice.received_at) }}
                                {{
                                    formatTime(detailInvoice.received_at)
                                }}</span
                            >
                        </div>
                        <div class="flex justify-between">
                            <span class="text-content-muted">Estado</span>
                            <span
                                :class="statusColor[detailInvoice.status]"
                                class="rounded-lg px-2.5 py-0.5 text-[10px] font-bold uppercase"
                            >
                                {{
                                    statusLabel[detailInvoice.status] ||
                                    detailInvoice.status
                                }}
                            </span>
                        </div>
                        <div
                            v-if="detailInvoice.notes"
                            class="flex justify-between"
                        >
                            <span class="text-content-muted">Notas</span>
                            <span
                                class="max-w-[60%] text-right font-medium text-content-primary dark:text-white"
                                >{{ detailInvoice.notes }}</span
                            >
                        </div>
                        <hr class="border-gray-100 dark:border-gray-800" />
                        <div>
                            <span
                                class="text-xs font-bold uppercase tracking-wider text-content-muted"
                                >Productos</span
                            >
                            <div
                                v-for="item in detailInvoice.items"
                                :key="item.id"
                                class="mt-2 flex justify-between"
                            >
                                <div class="flex-1">
                                    <span class="text-content-secondary">{{
                                        item.product_name
                                    }}</span>
                                    <span
                                        v-if="item.product?.sku"
                                        class="ml-1 text-xs text-content-muted"
                                        >({{ item.product.sku }})</span
                                    >
                                    <div
                                        class="flex gap-2 text-[11px] text-content-muted"
                                    >
                                        <span
                                            >{{ item.quantity_ordered }} ×
                                            {{ fmt(item.unit_cost) }}</span
                                        >
                                        <span
                                            v-if="
                                                item.quantity_received != null
                                            "
                                            >· Recibido:
                                            {{ item.quantity_received }}</span
                                        >
                                        <span
                                            :class="priceVariation(item).cls"
                                            class="font-medium"
                                            >{{
                                                priceVariation(item).label
                                            }}</span
                                        >
                                    </div>
                                </div>
                                <span
                                    class="font-medium text-content-primary dark:text-white"
                                    >{{
                                        fmt(
                                            item.unit_cost *
                                                item.quantity_ordered,
                                        )
                                    }}</span
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
                                fmt(detailTotal)
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

        <!-- Receive Modal -->
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="showReceive && receiveInvoiceRef"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4 backdrop-blur-sm"
            >
                <div
                    class="relative w-[95%] max-w-7xl rounded-3xl bg-white p-6 shadow-xl dark:bg-surface-dark md:p-8"
                >
                    <div class="mb-6 flex items-center justify-between">
                        <h3
                            class="font-display text-xl font-bold text-primary-500"
                        >
                            Recibir Factura
                            {{
                                receiveInvoiceRef.invoice_number ||
                                '#' + receiveInvoiceRef.id
                            }}
                        </h3>
                        <button
                            @click="showReceive = false"
                            class="rounded-xl p-2 transition-colors hover:bg-gray-100 dark:hover:bg-gray-800"
                        >
                            <X class="h-5 w-5 text-content-muted" />
                        </button>
                    </div>
                    <div class="mb-6 grid grid-cols-2 gap-6 text-sm">
                        <div>
                            <span class="font-medium text-content-muted"
                                >Proveedor</span
                            >
                            <p
                                class="mt-0.5 font-semibold text-content-primary dark:text-white"
                            >
                                {{
                                    receiveInvoiceRef.supplier?.company_name ||
                                    '—'
                                }}
                            </p>
                        </div>
                        <div>
                            <span class="font-medium text-content-muted"
                                >Emisión</span
                            >
                            <p
                                class="mt-0.5 font-semibold text-content-primary dark:text-white"
                            >
                                {{ formatDate(receiveInvoiceRef.issue_date) }}
                            </p>
                        </div>
                    </div>
                    <div>
                        <table class="mb-6 w-full text-left text-sm">
                            <thead
                                class="rounded-xl bg-gray-50 uppercase tracking-wider text-content-muted dark:bg-gray-900/50 dark:text-gray-500"
                            >
                                <tr>
                                    <th class="px-3 py-3 font-bold">
                                        Producto
                                    </th>
                                    <th class="px-3 py-3 text-center font-bold">
                                        Lote
                                    </th>
                                    <th class="px-3 py-3 text-center font-bold">
                                        Vence <span class="text-danger">*</span>
                                    </th>
                                    <th class="px-3 py-3 text-center font-bold">
                                        Cód. Barras
                                    </th>
                                    <th class="px-3 py-3 text-center font-bold">
                                        Costo $
                                    </th>
                                    <th class="px-3 py-3 text-center font-bold">
                                        Margen %
                                    </th>
                                    <th class="px-3 py-3 text-center font-bold">
                                        Precio Venta $
                                    </th>
                                    <th class="px-3 py-3 text-center font-bold">
                                        Pedido
                                    </th>
                                    <th class="px-3 py-3 text-center font-bold">
                                        Recibido
                                    </th>
                                    <th class="px-3 py-3 text-center font-bold">
                                        Total $
                                    </th>
                                </tr>
                            </thead>
                            <tbody
                                class="divide-y divide-gray-100 dark:divide-gray-800"
                            >
                                <tr
                                    v-for="(ri, idx) in receiveItems"
                                    :key="ri.id"
                                >
                                    <td
                                        class="px-3 py-3 text-sm font-medium text-content-primary dark:text-white"
                                    >
                                        {{ ri.name }}
                                        <span
                                            v-if="ri.sku"
                                            class="ml-1 text-[11px] text-content-muted"
                                            >({{ ri.sku }})</span
                                        >
                                        <span
                                            v-if="ri.is_new_product"
                                            class="ml-1 rounded bg-amber-100 px-1.5 text-[11px] font-bold text-amber-700"
                                            >Nuevo</span
                                        >
                                    </td>
                                    <td
                                        class="px-3 py-3 text-center font-mono text-[11px] text-content-muted"
                                    >
                                        LOTE-{{
                                            new Date()
                                                .toISOString()
                                                .slice(0, 10)
                                                .replace(/-/g, '')
                                        }}-{{
                                            String(idx + 1).padStart(3, '0')
                                        }}
                                    </td>
                                    <td class="px-3 py-3 text-center">
                                        <input
                                            v-model="ri.expiration_date"
                                            type="date"
                                            class="w-40 rounded-lg border-gray-200 px-2 py-2 text-sm dark:border-gray-700 dark:bg-surface-dark dark:text-white"
                                        />
                                    </td>
                                    <td class="px-3 py-3 text-center">
                                        <input
                                            v-model="ri.barcode"
                                            type="text"
                                            placeholder="Opcional"
                                            class="w-36 rounded-lg border-gray-200 px-2 py-2 text-sm dark:border-gray-700 dark:bg-surface-dark dark:text-white"
                                        />
                                    </td>
                                    <td class="px-3 py-3 text-center">
                                        <div class="relative inline-block">
                                            <span
                                                class="absolute left-2 top-1/2 -translate-y-1/2 text-xs text-gray-400"
                                                >$</span
                                            >
                                            <input
                                                v-model.number="ri.cost_pesos"
                                                type="number"
                                                min="0"
                                                step="1"
                                                @input="onCostChange(ri)"
                                                class="w-28 rounded-lg border-gray-200 py-2 pl-4 pr-1 text-right text-sm dark:border-gray-700 dark:bg-surface-dark dark:text-white"
                                            />
                                        </div>
                                    </td>
                                    <td class="px-3 py-3 text-center">
                                        <div class="relative inline-block">
                                            <input
                                                v-model.number="ri.margin"
                                                type="number"
                                                min="0"
                                                max="99"
                                                step="1"
                                                @input="onMarginChange(ri)"
                                                class="w-20 rounded-lg border-gray-200 py-2 text-center text-sm dark:border-gray-700 dark:bg-surface-dark dark:text-white"
                                            />
                                            <span
                                                class="absolute right-2 top-1/2 -translate-y-1/2 text-xs text-gray-400"
                                                >%</span
                                            >
                                        </div>
                                    </td>
                                    <td class="px-3 py-3 text-center">
                                        <div
                                            v-if="ri.old_sale_price"
                                            class="mb-0.5 text-[10px] text-content-muted"
                                        >
                                            Antes: ${{ ri.old_sale_price }}
                                        </div>
                                        <div class="relative inline-block">
                                            <span
                                                class="absolute left-2 top-1/2 -translate-y-1/2 text-xs text-gray-400"
                                                >$</span
                                            >
                                            <input
                                                v-model.number="ri.sale_price"
                                                type="number"
                                                min="0"
                                                step="1"
                                                @input="onSalePriceChange(ri)"
                                                class="w-28 rounded-lg border-gray-200 py-2 pl-4 pr-1 text-right text-sm dark:border-gray-700 dark:bg-surface-dark dark:text-white"
                                            />
                                        </div>
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
                                            :max="ri.ordered"
                                            class="w-20 rounded-lg border-gray-200 py-2 text-center text-sm dark:border-gray-700 dark:bg-surface-dark dark:text-white"
                                        />
                                    </td>
                                    <td
                                        class="px-3 py-3 text-center text-sm font-semibold text-content-primary dark:text-white"
                                    >
                                        {{ fmt(ri.unit_cost * ri.received) }}
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot
                                class="border-t-2 border-gray-200 dark:border-gray-700"
                            >
                                <tr>
                                    <td
                                        colspan="9"
                                        class="px-3 py-3 text-right text-sm font-bold text-content-primary dark:text-white"
                                    >
                                        Total Factura
                                    </td>
                                    <td
                                        class="px-3 py-3 text-center text-sm font-bold text-primary-500"
                                    >
                                        {{ fmt(receiveTotal) }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                        <div class="mb-6">
                            <label
                                class="mb-1 block text-xs font-medium text-content-muted"
                                >Observaciones</label
                            >
                            <textarea
                                v-model="receiveObservation"
                                rows="2"
                                placeholder="Ej: El pedido no llegó completo, faltaron algunos artículos..."
                                class="w-full rounded-xl border-gray-200 px-3 py-2 text-sm dark:border-gray-700 dark:bg-surface-dark dark:text-white dark:placeholder:text-gray-500"
                            ></textarea>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <button
                            @click="showReceive = false"
                            class="flex-1 rounded-2xl border border-gray-200 py-2.5 text-sm font-bold text-content-secondary transition-colors hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800"
                        >
                            Cancelar
                        </button>
                        <button
                            @click="submitReceive"
                            class="flex flex-1 items-center justify-center gap-2 rounded-2xl bg-success py-2.5 text-sm font-bold text-white shadow-sm transition-colors hover:bg-success/80"
                        >
                            <Check class="h-4 w-4" /> Confirmar Recepción
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </AdminLayout>
</template>
