<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Percent, Plus, Trash2, X } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface PromotionItem {
    id: number;
    name: string;
    description: string | null;
    type: string;
    conditions: Record<string, any>;
    rewards: Record<string, any>;
    is_active: boolean;
    is_exclusive: boolean;
    priority: number;
    starts_at: string | null;
    expires_at: string | null;
    created_at: string;
}

const props = defineProps<{
    promotions: {
        data: PromotionItem[];
        links: any[];
        current_page: number;
        last_page: number;
        prev_page_url: string | null;
        next_page_url: string | null;
    };
    filters: {
        search?: string;
        type?: string;
        status?: string;
    };
}>();

const filters = ref({ ...props.filters });
const showForm = ref(false);
const editingId = ref<number | null>(null);

// — Product search refs per field (min_qty, buy_x_get_y buy, buy_x_get_y reward, bundle) —
const minQtySearchResults = ref<any[]>([]);
const minQtySkuResults = ref<any[]>([]);
const minQtyProductName = ref('');

const buyProductSearchResults = ref<any[]>([]);
const buyProductSkuResults = ref<any[]>([]);
const buyProductName = ref('');

const getProductSearchResults = ref<any[]>([]);
const getProductSkuResults = ref<any[]>([]);

const bundleProductSearchResults = ref<any[]>([]);
const bundleProductSkuResults = ref<any[]>([]);
const searchProductId = ref<number | null>(null);
const bundleSearchQuery = ref('');
const bundleSearchLoading = ref(false);
const discountMode = ref<'pct' | 'fixed'>('pct');

// ── Clear timers for blur/focus pattern ──
let minQtyClearTimer: ReturnType<typeof setTimeout> | null = null;
let buyProductClearTimer: ReturnType<typeof setTimeout> | null = null;
let getProductClearTimer: ReturnType<typeof setTimeout> | null = null;
let bundleClearTimer: ReturnType<typeof setTimeout> | null = null;

function onBlurMinQty() {
    minQtyClearTimer = setTimeout(() => { minQtySearchResults.value = []; minQtySkuResults.value = []; }, 200);
}
function onFocusMinQty() {
    if (minQtyClearTimer) { clearTimeout(minQtyClearTimer); minQtyClearTimer = null; }
}

function onBlurBuyProduct() {
    buyProductClearTimer = setTimeout(() => { buyProductSearchResults.value = []; buyProductSkuResults.value = []; }, 200);
}
function onFocusBuyProduct() {
    if (buyProductClearTimer) { clearTimeout(buyProductClearTimer); buyProductClearTimer = null; }
}

function onBlurGetProduct() {
    getProductClearTimer = setTimeout(() => { getProductSearchResults.value = []; getProductSkuResults.value = []; }, 200);
}
function onFocusGetProduct() {
    if (getProductClearTimer) { clearTimeout(getProductClearTimer); getProductClearTimer = null; }
}

function onBlurBundle() {
    bundleClearTimer = setTimeout(() => { bundleProductSearchResults.value = []; bundleProductSkuResults.value = []; }, 200);
}
function onFocusBundle() {
    if (bundleClearTimer) { clearTimeout(bundleClearTimer); bundleClearTimer = null; }
}

function handleSearchInput() {
    clearTimeout(searchTimer);
    bundleSearchLoading.value = true;
    searchTimer = setTimeout(async () => {
        try {
            const { data } = await window.axios.get(
                route('admin.codigos.search-name'),
                { params: { query: bundleSearchQuery.value } }
            );
            bundleProductSearchResults.value = data;
        } catch {
            bundleProductSearchResults.value = [];
        } finally {
            bundleSearchLoading.value = false;
        }
    }, 300);
}

const typeLabels: Record<string, string> = {
    buy_x_get_y: 'Compra X, Lleva Y',
    min_qty_discount: 'Dto. por Cantidad',
    bundle_discount: 'Combo',
};

const typeColors: Record<string, string> = {
    buy_x_get_y: 'bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-300',
    min_qty_discount: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
    bundle_discount: 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300',
};

const form = useForm({
    name: '',
    description: '',
    type: 'min_qty_discount',
    is_active: true,
    is_exclusive: false,
    priority: 0,
    starts_at: '',
    expires_at: '',
    conditions: {} as Record<string, any>,
    rewards: {} as Record<string, any>,
});

function resetForm() {
    form.reset();
    form.is_active = true;
    form.is_exclusive = false;
    form.priority = 0;
    form.conditions = {};
    form.rewards = {};
    form.type = 'min_qty_discount';
    discountMode.value = 'pct';
    searchProductId.value = null;
    minQtyProductName.value = '';
    minQtySearchResults.value = [];
    minQtySkuResults.value = [];
    buyProductName.value = '';
    buyProductSearchResults.value = [];
    buyProductSkuResults.value = [];
    bundleSearchQuery.value = '';
    bundleProductSearchResults.value = [];
    bundleProductSkuResults.value = [];
}

function openNew() {
    resetForm();
    editingId.value = null;
    showForm.value = true;
}

function openEdit(promotion: PromotionItem) {
    resetForm();
    editingId.value = promotion.id;
    form.name = promotion.name;
    form.description = promotion.description || '';
    form.type = promotion.type;
    form.is_active = promotion.is_active;
    form.is_exclusive = promotion.is_exclusive;
    form.priority = promotion.priority;
    form.starts_at = toDatetimeLocal(promotion.starts_at);
    form.expires_at = toDatetimeLocal(promotion.expires_at);
    form.conditions = { ...promotion.conditions };
    form.rewards = { ...promotion.rewards };

    if (promotion.type === 'min_qty_discount') {
        discountMode.value = promotion.conditions.special_price ? 'fixed' : 'pct';
    }
    if (promotion.type === 'buy_x_get_y' || promotion.type === 'bundle_discount') {
        discountMode.value = promotion.conditions.special_price_total ? 'fixed' : 'pct';
    }
    if (promotion.type === 'min_qty_discount' && promotion.conditions.product_id) {
        searchProductId.value = promotion.conditions.product_id;
        window.axios.get(route('admin.codigos.search-sku'), {
            params: { query: promotion.conditions.product_id },
        }).then(r => {
            if (r.data) minQtyProductName.value = r.data.name;
        }).catch(() => {
            minQtyProductName.value = `#${promotion.conditions.product_id}`;
        });
    }
    if (promotion.type === 'buy_x_get_y' && promotion.conditions.buy_product_id) {
        searchProductId.value = promotion.conditions.buy_product_id;
        window.axios.get(route('admin.codigos.search-sku'), {
            params: { query: promotion.conditions.buy_product_id },
        }).then(r => {
            if (r.data) buyProductName.value = r.data.name;
        }).catch(() => {
            buyProductName.value = `#${promotion.conditions.buy_product_id}`;
        });
    }
    if (promotion.type === 'buy_x_get_y' && promotion.rewards?.get_product_id) {
        window.axios.get(route('admin.codigos.search-sku'), {
            params: { query: promotion.rewards.get_product_id },
        }).then(r => {
            if (r.data) form.rewards.get_product_name = r.data.name;
        }).catch(() => {
            form.rewards.get_product_name = `#${promotion.rewards.get_product_id}`;
        });
    }
    showForm.value = true;
}

function cancelForm() {
    showForm.value = false;
    editingId.value = null;
    resetForm();
}

function submitForm() {
    if (form.type === 'min_qty_discount' && !form.conditions.product_id) {
        alert('Selecciona un producto antes de guardar.');
        return;
    }

    const payload: Record<string, any> = {
        name: form.name,
        description: form.description,
        type: form.type,
        is_active: form.is_active,
        is_exclusive: form.is_exclusive,
        priority: form.priority,
        starts_at: form.starts_at || null,
        expires_at: form.expires_at || null,
    };

    if (form.type === 'min_qty_discount') {
        payload.conditions = {
            product_id: form.conditions.product_id,
            min_qty: form.conditions.min_qty,
        };
        if (discountMode.value === 'pct') {
            payload.conditions.discount_pct = form.conditions.discount_pct;
        } else {
            payload.conditions.special_price = form.conditions.special_price;
        }
    } else if (form.type === 'buy_x_get_y') {
        payload.conditions = {
            buy_product_id: form.conditions.buy_product_id,
            buy_qty: form.conditions.buy_qty,
        };
        payload.rewards = {
            get_product_id: form.rewards.get_product_id,
            get_qty: form.rewards.get_qty,
        };
        if (discountMode.value === 'pct') {
            payload.rewards.discount_pct = form.rewards.discount_pct ?? 100;
        } else {
            payload.conditions.special_price_total = form.conditions.special_price_total;
            payload.rewards.discount_pct = 100;
        }
    } else if (form.type === 'bundle_discount') {
        payload.conditions = {
            product_ids: form.conditions.product_ids,
        };
        if (discountMode.value === 'pct') {
            payload.conditions.discount_pct = form.conditions.discount_pct;
        } else {
            payload.conditions.special_price_total = form.conditions.special_price_total;
        }
    }

    const options = {
        onSuccess: () => { cancelForm(); },
        onError: (errors: Record<string, string>) => {
            const msgs = Object.values(errors);
            if (msgs.length) alert('Error:\n' + msgs.join('\n'));
        },
    };

    if (editingId.value) {
        payload._method = 'PUT';
        router.post(route('admin.promociones.update', editingId.value), payload, options);
    } else {
        router.post(route('admin.promociones.store'), payload, options);
    }
}

function deletePromotion(id: number) {
    if (!confirm('¿Eliminar esta promoción?')) return;
    router.delete(route('admin.promociones.destroy', id), { preserveScroll: true });
}

async function toggleActive(id: number) {
    try {
        const res = await window.axios.patch(route('admin.promociones.toggle', id));
        const idx = props.promotions.data.findIndex(p => p.id === id);
        if (idx !== -1) props.promotions.data[idx].is_active = res.data.is_active;
    } catch { /* silent */ }
}

function applyFilters() {
    router.get(
        route('admin.promociones.index'),
        { search: filters.value.search || '', type: filters.value.type || '', status: filters.value.status || '' },
        { preserveState: true, replace: true },
    );
}

function clearFilters() {
    filters.value = { search: '', type: '', status: '' };
    applyFilters();
}

const statusText = (p: PromotionItem) => {
    if (!p.is_active) return 'Inactiva';
    if (p.expires_at && new Date(p.expires_at) < new Date()) return 'Vencida';
    if (p.starts_at && new Date(p.starts_at) > new Date()) return 'Programada';
    return 'Activa';
};

const statusColor = (p: PromotionItem) => {
    if (!p.is_active || (p.expires_at && new Date(p.expires_at) < new Date())) return 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300';
    if (p.starts_at && new Date(p.starts_at) > new Date()) return 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300';
    return 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300';
};

const fmtDate = (d: string | null) => d ? new Date(d).toLocaleDateString('es-CL') : '—';

const discountText = (p: PromotionItem) => {
    if (p.type === 'buy_x_get_y') {
        if (p.conditions?.special_price_total) return `$${p.conditions.special_price_total}`;
        return `${p.rewards?.discount_pct ?? 100}%`;
    }
    if (p.type === 'min_qty_discount') {
        if (p.conditions?.special_price) return `$${p.conditions.special_price}`;
        return `${p.conditions?.discount_pct}%`;
    }
    if (p.type === 'bundle_discount') {
        if (p.conditions?.special_price_total) return `$${p.conditions.special_price_total}`;
        return `${p.conditions?.discount_pct}%`;
    }
    return '—';
};

let searchTimer: ReturnType<typeof setTimeout>;

// ── min_qty product search ──
let minQtySearchTimer: ReturnType<typeof setTimeout>;
function onSearchMinQtyName(query: string) {
    clearTimeout(minQtySearchTimer);
    if (!query.trim()) { minQtySearchResults.value = []; return; }
    minQtySearchTimer = setTimeout(async () => {
        try {
            const res = await window.axios.get(route('admin.codigos.search-name'), { params: { query } });
            minQtySearchResults.value = res.data;
        } catch { minQtySearchResults.value = []; }
    }, 300);
}

let minQtySkuTimer: ReturnType<typeof setTimeout>;
function onSearchMinQtySku(query: string) {
    clearTimeout(minQtySkuTimer);
    if (!query.trim()) { minQtySkuResults.value = []; return; }
    minQtySkuTimer = setTimeout(async () => {
        try {
            const res = await window.axios.get(route('admin.codigos.search-sku'), { params: { query } });
            minQtySkuResults.value = res.data ? [res.data] : [];
        } catch { minQtySkuResults.value = []; }
    }, 300);
}

function selectMinQtyProduct(product: any) {
    minQtyProductName.value = product.name;
    form.conditions.product_id = product.id;
    minQtySearchResults.value = [];
    minQtySkuResults.value = [];
}

// ── buy_x_get_y "buy product" search ──
let buyProductSearchTimer: ReturnType<typeof setTimeout>;
function onSearchBuyName(query: string) {
    clearTimeout(buyProductSearchTimer);
    if (!query.trim()) { buyProductSearchResults.value = []; return; }
    buyProductSearchTimer = setTimeout(async () => {
        try {
            const res = await window.axios.get(route('admin.codigos.search-name'), { params: { query } });
            buyProductSearchResults.value = res.data;
        } catch { buyProductSearchResults.value = []; }
    }, 300);
}

let buyProductSkuTimer: ReturnType<typeof setTimeout>;
function onSearchBuySku(query: string) {
    clearTimeout(buyProductSkuTimer);
    if (!query.trim()) { buyProductSkuResults.value = []; return; }
    buyProductSkuTimer = setTimeout(async () => {
        try {
            const res = await window.axios.get(route('admin.codigos.search-sku'), { params: { query } });
            buyProductSkuResults.value = res.data ? [res.data] : [];
        } catch { buyProductSkuResults.value = []; }
    }, 300);
}

function selectBuyProduct(product: any) {
    buyProductName.value = product.name;
    form.conditions.buy_product_id = product.id;
    form.conditions.buy_qty = form.conditions.buy_qty || 2;
    buyProductSearchResults.value = [];
    buyProductSkuResults.value = [];
}

// ── buy_x_get_y "get product" search ──
let getProductSearchTimer: ReturnType<typeof setTimeout>;
function onSearchGetName(query: string) {
    clearTimeout(getProductSearchTimer);
    if (!query.trim()) { getProductSearchResults.value = []; return; }
    getProductSearchTimer = setTimeout(async () => {
        try {
            const res = await window.axios.get(route('admin.codigos.search-name'), { params: { query } });
            getProductSearchResults.value = res.data;
        } catch { getProductSearchResults.value = []; }
    }, 300);
}

let getProductSkuTimer: ReturnType<typeof setTimeout>;
function onSearchGetSku(query: string) {
    clearTimeout(getProductSkuTimer);
    if (!query.trim()) { getProductSkuResults.value = []; return; }
    getProductSkuTimer = setTimeout(async () => {
        try {
            const res = await window.axios.get(route('admin.codigos.search-sku'), { params: { query } });
            getProductSkuResults.value = res.data ? [res.data] : [];
        } catch { getProductSkuResults.value = []; }
    }, 300);
}

function selectGetProduct(product: any) {
    form.rewards.get_product_id = product.id;
    form.rewards.get_product_name = product.name;
    getProductSearchResults.value = [];
    getProductSkuResults.value = [];
}

// ── bundle product search ──
let bundleSkuSearchTimer: ReturnType<typeof setTimeout>;
function onSearchBundleSku(query: string) {
    clearTimeout(bundleSkuSearchTimer);
    if (!query.trim()) { bundleProductSkuResults.value = []; return; }
    bundleSkuSearchTimer = setTimeout(async () => {
        try {
            const res = await window.axios.get(route('admin.codigos.search-sku'), { params: { query } });
            bundleProductSkuResults.value = res.data ? [res.data] : [];
        } catch { bundleProductSkuResults.value = []; }
    }, 300);
}

function selectBundleProduct(product: any) {
    const ids = form.conditions.product_ids || [];
    if (!ids.includes(product.id)) {
        form.conditions.product_ids = [...ids, product.id];
    }
    bundleSearchQuery.value = '';
    bundleProductSearchResults.value = [];
    bundleProductSkuResults.value = [];
}

function removeBundleProduct(productId: number) {
    form.conditions.product_ids = (form.conditions.product_ids || []).filter((id: number) => id !== productId);
}

function toDatetimeLocal(iso: string | null): string {
    if (!iso) return '';
    const d = new Date(iso);
    const pad = (n: number) => n.toString().padStart(2, '0');
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
}

const bundleProductNames = computed(() => {
    const ids = form.conditions.product_ids || [];
    if (!ids.length) return [];
    return bundleProductSearchResults.value.filter(p => ids.includes(p.id));
});
</script>

<template>
    <Head title="Promociones" />
    <AdminLayout>
        <template #title>
            <h1 class="font-display text-xl font-bold text-content-primary dark:text-white">Promociones</h1>
        </template>

        <!-- ─── LIST VIEW ─────────────────────────────────── -->
        <template v-if="!showForm">
            <div class="overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-surface-dark">
                <!-- Toolbar -->
                <div class="flex flex-wrap items-center gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                    <Percent class="h-5 w-5 text-primary-500" />
                    <h2 class="flex-1 text-sm font-bold text-content-primary dark:text-white">Todas las promociones</h2>

                    <select v-model="filters.type" @change="applyFilters"
                        class="rounded-2xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs font-bold text-content-secondary dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400">
                        <option value="">Todos los tipos</option>
                        <option value="buy_x_get_y">Compra X, Lleva Y</option>
                        <option value="min_qty_discount">Dto. por Cantidad</option>
                        <option value="bundle_discount">Combo</option>
                    </select>

                    <select v-model="filters.status" @change="applyFilters"
                        class="rounded-2xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs font-bold text-content-secondary dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400">
                        <option value="">Todos los estados</option>
                        <option value="active">Activas</option>
                        <option value="expired">Vencidas / Inactivas</option>
                        <option value="scheduled">Programadas</option>
                    </select>

                    <input v-model="filters.search" @keydown.enter="applyFilters"
                        placeholder="Buscar por nombre..."
                        class="max-w-xs rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2 text-xs font-bold text-content-primary placeholder:text-content-muted dark:border-gray-700 dark:bg-gray-900 dark:text-white" />

                    <button @click="clearFilters" v-if="filters.search || filters.type || filters.status"
                        class="rounded-2xl border border-gray-200 px-3 py-2 text-xs font-bold text-content-secondary hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800">
                        Limpiar
                    </button>

                    <button @click="openNew"
                        class="ml-auto flex items-center gap-2 rounded-2xl bg-primary-500 px-4 py-2 text-sm font-bold text-white shadow-sm transition-colors hover:bg-primary-600">
                        <Plus class="h-4 w-4" /> Nueva Promoción
                    </button>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 text-xs uppercase tracking-wider text-content-muted dark:bg-gray-900/50 dark:text-gray-500">
                            <tr>
                                <th class="px-6 py-3 font-bold">Nombre</th>
                                <th class="px-6 py-3 font-bold">Tipo</th>
                                <th class="px-6 py-3 text-right font-bold">Dto.</th>
                                <th class="px-6 py-3 font-bold">Vigencia</th>
                                <th class="px-6 py-3 text-center font-bold">Prioridad</th>
                                <th class="px-6 py-3 text-center font-bold">Estado</th>
                                <th class="px-6 py-3 text-right font-bold">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            <tr v-if="!promotions.data?.length">
                                <td colspan="7" class="px-6 py-12 text-center text-sm text-content-muted dark:text-gray-500">
                                    No hay promociones registradas.
                                </td>
                            </tr>
                            <tr v-for="p in promotions.data" :key="p.id"
                                class="group transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                <td class="px-6 py-4">
                                    <span class="text-sm font-bold text-content-primary dark:text-white">{{ p.name }}</span>
                                    <p v-if="p.description" class="mt-0.5 text-xs text-content-muted line-clamp-1">{{ p.description }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="rounded-lg px-2.5 py-1 text-xs font-bold" :class="typeColors[p.type] || ''">
                                        {{ typeLabels[p.type] || p.type }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-bold text-primary-500">{{ discountText(p) }}</td>
                                <td class="px-6 py-4 text-xs text-content-secondary">
                                    {{ fmtDate(p.starts_at) }} → {{ fmtDate(p.expires_at) }}
                                </td>
                                <td class="px-6 py-4 text-center text-sm font-bold text-content-primary dark:text-white">{{ p.priority }}</td>
                                <td class="px-6 py-4 text-center">
                                    <span class="rounded-full px-2.5 py-1 text-[11px] font-bold uppercase" :class="statusColor(p)">{{ statusText(p) }}</span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2 opacity-0 transition-opacity group-hover:opacity-100">
                                        <button @click="toggleActive(p.id)"
                                            class="rounded-xl p-2 transition-colors"
                                            :class="p.is_active ? 'text-amber-500 hover:bg-amber-50 dark:hover:bg-amber-900/20' : 'text-green-500 hover:bg-green-50 dark:hover:bg-green-900/20'"
                                            :title="p.is_active ? 'Desactivar' : 'Activar'">
                                            <span class="text-sm font-bold">{{ p.is_active ? '🔴' : '🟢' }}</span>
                                        </button>
                                        <button @click="openEdit(p)"
                                            class="rounded-xl p-2 text-blue-500 transition-colors hover:bg-blue-50 dark:hover:bg-blue-900/20">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </button>
                                        <button @click="deletePromotion(p.id)"
                                            class="rounded-xl p-2 text-danger transition-colors hover:bg-red-50 dark:hover:bg-red-900/20">
                                            <Trash2 class="h-4 w-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="promotions.last_page > 1"
                    class="flex items-center justify-between border-t border-gray-100 px-6 py-4 text-sm text-content-muted dark:border-gray-800 dark:text-gray-500">
                    <span>Página {{ promotions.current_page }} de {{ promotions.last_page }}</span>
                    <div class="flex gap-2">
                        <a v-if="promotions.prev_page_url" :href="promotions.prev_page_url"
                            class="rounded-lg bg-gray-50 px-3 py-1.5 font-bold transition-colors hover:bg-gray-100 dark:bg-gray-900">←</a>
                        <a v-if="promotions.next_page_url" :href="promotions.next_page_url"
                            class="rounded-lg bg-gray-50 px-3 py-1.5 font-bold transition-colors hover:bg-gray-100 dark:bg-gray-900">→</a>
                    </div>
                </div>
            </div>
        </template>

        <!-- ─── FORM VIEW ─────────────────────────────────── -->
        <template v-else>
            <div class="overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-surface-dark">
                <div class="flex items-center gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                    <Percent class="h-5 w-5 text-primary-500" />
                    <h2 class="flex-1 text-sm font-bold text-content-primary dark:text-white">
                        {{ editingId ? 'Editar Promoción' : 'Nueva Promoción' }}
                    </h2>
                </div>

                <form @submit.prevent="submitForm" class="space-y-6 p-6">
                    <!-- Common fields -->
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted">Nombre</label>
                            <input v-model="form.name" type="text" required
                                class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted">Tipo</label>
                            <select v-model="form.type"
                                class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                <option value="min_qty_discount">Dto. por Cantidad</option>
                                <option value="buy_x_get_y">Compra X, Lleva Y</option>
                                <option value="bundle_discount">Combo</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted">Descripción</label>
                            <textarea v-model="form.description" rows="2"
                                class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"></textarea>
                        </div>
                    </div>

                    <!-- ─── min_qty_discount fields ─────────── -->
                    <template v-if="form.type === 'min_qty_discount'">
                        <div class="rounded-2xl bg-gray-50 p-4 dark:bg-gray-800/50">
                            <h3 class="mb-3 text-xs font-bold uppercase tracking-wider text-content-muted">Condiciones — Dto. por Cantidad</h3>
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                <div class="relative md:col-span-2">
                                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted">Producto</label>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div class="relative">
                                            <input :value="minQtyProductName"
                                                @input="onSearchMinQtyName(($event.target as HTMLInputElement).value)"
                                                @blur="onBlurMinQty" @focus="onFocusMinQty"
                                                type="text" placeholder="Buscar por nombre..."
                                                class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                                            <input v-model="form.conditions.product_id" type="hidden" />
                                            <div v-if="minQtySearchResults.length"
                                                class="absolute left-0 right-0 top-full z-50 mt-1 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-900">
                                                <button v-for="pr in minQtySearchResults" :key="pr.id" type="button"
                                                    @mousedown.prevent="selectMinQtyProduct(pr)"
                                                    class="flex w-full items-center gap-3 px-4 py-3 text-left text-sm transition-colors hover:bg-gray-50 dark:hover:bg-gray-800">
                                                    <span class="flex-1 font-medium text-content-primary dark:text-white">{{ pr.name }}</span>
                                                    <span class="text-xs text-content-muted">Stock: {{ pr.stock }}</span>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="relative">
                                            <input
                                                @input="onSearchMinQtySku(($event.target as HTMLInputElement).value)"
                                                @blur="onBlurMinQty" @focus="onFocusMinQty"
                                                type="text" placeholder="SKU / Código de barras..."
                                                class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                                            <div v-if="minQtySkuResults.length"
                                                class="absolute left-0 right-0 top-full z-50 mt-1 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-900">
                                                <button v-for="pr in minQtySkuResults" :key="pr.id" type="button"
                                                    @mousedown.prevent="selectMinQtyProduct(pr)"
                                                    class="flex w-full items-center gap-3 px-4 py-3 text-left text-sm transition-colors hover:bg-gray-50 dark:hover:bg-gray-800">
                                                    <span class="flex-1 font-medium text-content-primary dark:text-white">{{ pr.name }}</span>
                                                    <span class="text-xs text-content-muted">Stock: {{ pr.stock }}</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted">Cantidad mínima</label>
                                    <input v-model.number="form.conditions.min_qty" type="number" min="1" required
                                        class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted">Modo de descuento</label>
                                    <div class="flex gap-4 pt-1">
                                        <label class="flex cursor-pointer items-center gap-2 text-sm text-content-primary dark:text-white">
                                            <input type="radio" v-model="discountMode" value="pct"
                                                class="text-primary-500 focus:ring-primary-500" />
                                            Porcentaje (%)
                                        </label>
                                        <label class="flex cursor-pointer items-center gap-2 text-sm text-content-primary dark:text-white">
                                            <input type="radio" v-model="discountMode" value="fixed"
                                                class="text-primary-500 focus:ring-primary-500" />
                                            Precio fijo total ($)
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div v-if="discountMode === 'pct'" class="mt-4 md:w-1/3">
                                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted">% Descuento</label>
                                <input v-model.number="form.conditions.discount_pct" type="number" min="1" max="100" required
                                    class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                            </div>
                            <div v-if="discountMode === 'fixed'" class="mt-4 md:w-1/3">
                                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted">Precio especial total ($)</label>
                                <input v-model.number="form.conditions.special_price" type="number" min="1" required
                                    class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                            </div>
                        </div>
                    </template>

                    <!-- ─── buy_x_get_y fields ─────────────── -->
                    <template v-if="form.type === 'buy_x_get_y'">
                        <div class="rounded-2xl bg-gray-50 p-4 dark:bg-gray-800/50">
                            <h3 class="mb-3 text-xs font-bold uppercase tracking-wider text-content-muted">Condiciones — Compra X, Lleva Y</h3>
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div class="relative">
                                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted">Producto a comprar</label>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div class="relative">
                                            <input :value="buyProductName"
                                                @input="onSearchBuyName(($event.target as HTMLInputElement).value)"
                                                @blur="onBlurBuyProduct" @focus="onFocusBuyProduct"
                                                type="text" placeholder="Buscar por nombre..."
                                                class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                                            <input v-model="form.conditions.buy_product_id" type="hidden" />
                                            <div v-if="buyProductSearchResults.length"
                                                class="absolute left-0 right-0 top-full z-50 mt-1 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-900">
                                                <button v-for="pr in buyProductSearchResults" :key="pr.id" type="button"
                                                    @mousedown.prevent="selectBuyProduct(pr)"
                                                    class="flex w-full items-center gap-3 px-4 py-3 text-left text-sm transition-colors hover:bg-gray-50 dark:hover:bg-gray-800">
                                                    <span class="flex-1 font-medium text-content-primary dark:text-white">{{ pr.name }}</span>
                                                    <span class="text-xs text-content-muted">Stock: {{ pr.stock }}</span>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="relative">
                                            <input
                                                @input="onSearchBuySku(($event.target as HTMLInputElement).value)"
                                                @blur="onBlurBuyProduct" @focus="onFocusBuyProduct"
                                                type="text" placeholder="SKU / Código de barras..."
                                                class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                                            <div v-if="buyProductSkuResults.length"
                                                class="absolute left-0 right-0 top-full z-50 mt-1 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-900">
                                                <button v-for="pr in buyProductSkuResults" :key="pr.id" type="button"
                                                    @mousedown.prevent="selectBuyProduct(pr)"
                                                    class="flex w-full items-center gap-3 px-4 py-3 text-left text-sm transition-colors hover:bg-gray-50 dark:hover:bg-gray-800">
                                                    <span class="flex-1 font-medium text-content-primary dark:text-white">{{ pr.name }}</span>
                                                    <span class="text-xs text-content-muted">Stock: {{ pr.stock }}</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted">Cantidad a comprar</label>
                                    <input v-model.number="form.conditions.buy_qty" type="number" min="1" required
                                        class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                                </div>
                            </div>
                            <h4 class="mb-2 mt-4 text-xs font-bold uppercase tracking-wider text-content-muted">Recompensa</h4>
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                <div class="relative">
                                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted">Producto a regalar</label>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div class="relative">
                                            <input :value="form.rewards?.get_product_name || ''"
                                                @input="onSearchGetName(($event.target as HTMLInputElement).value); form.rewards.get_product_name = ($event.target as HTMLInputElement).value"
                                                @blur="onBlurGetProduct" @focus="onFocusGetProduct"
                                                type="text" placeholder="Buscar por nombre..."
                                                class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                                            <input v-model="form.rewards.get_product_id" type="hidden" />
                                            <div v-if="getProductSearchResults.length"
                                                class="absolute left-0 right-0 top-full z-50 mt-1 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-900">
                                                <button v-for="pr in getProductSearchResults" :key="pr.id" type="button"
                                                    @mousedown.prevent="selectGetProduct(pr)"
                                                    class="flex w-full items-center gap-3 px-4 py-3 text-left text-sm transition-colors hover:bg-gray-50 dark:hover:bg-gray-800">
                                                    <span class="flex-1 font-medium text-content-primary dark:text-white">{{ pr.name }}</span>
                                                    <span class="text-xs text-content-muted">Stock: {{ pr.stock }}</span>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="relative">
                                            <input
                                                @input="onSearchGetSku(($event.target as HTMLInputElement).value)"
                                                @blur="onBlurGetProduct" @focus="onFocusGetProduct"
                                                type="text" placeholder="SKU / Código de barras..."
                                                class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                                            <div v-if="getProductSkuResults.length"
                                                class="absolute left-0 right-0 top-full z-50 mt-1 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-900">
                                                <button v-for="pr in getProductSkuResults" :key="pr.id" type="button"
                                                    @mousedown.prevent="selectGetProduct(pr)"
                                                    class="flex w-full items-center gap-3 px-4 py-3 text-left text-sm transition-colors hover:bg-gray-50 dark:hover:bg-gray-800">
                                                    <span class="flex-1 font-medium text-content-primary dark:text-white">{{ pr.name }}</span>
                                                    <span class="text-xs text-content-muted">Stock: {{ pr.stock }}</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted">Cantidad a regalar</label>
                                    <input v-model.number="form.rewards.get_qty" type="number" min="1" required
                                        class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                                </div>
                                <div v-if="discountMode === 'pct'">
                                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted">% Descuento</label>
                                    <input v-model.number="form.rewards.discount_pct" type="number" min="0" max="100"
                                        class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                                </div>
                                <div v-else>
                                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted">Precio fijo total ($)</label>
                                    <input v-model.number="form.conditions.special_price_total" type="number" min="1" required
                                        class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                                </div>
                            </div>
                            <div class="mt-4">
                                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted">Modo de descuento</label>
                                <div class="flex gap-4 pt-1">
                                    <label class="flex cursor-pointer items-center gap-2 text-sm text-content-primary dark:text-white">
                                        <input type="radio" v-model="discountMode" value="pct"
                                            class="text-primary-500 focus:ring-primary-500" />
                                        Porcentaje (%)
                                    </label>
                                    <label class="flex cursor-pointer items-center gap-2 text-sm text-content-primary dark:text-white">
                                        <input type="radio" v-model="discountMode" value="fixed"
                                            class="text-primary-500 focus:ring-primary-500" />
                                        Precio fijo total ($)
                                    </label>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- ─── bundle_discount fields ─────────── -->
                    <template v-if="form.type === 'bundle_discount'">
                        <div class="rounded-2xl bg-gray-50 p-4 dark:bg-gray-800/50">
                            <h3 class="mb-3 text-xs font-bold uppercase tracking-wider text-content-muted">Condiciones — Combo</h3>
                            <div class="mb-4">
                                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted">Productos del combo</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <div class="relative">
                                        <input v-model="bundleSearchQuery"
                                            @input="handleSearchInput"
                                            @blur="onBlurBundle" @focus="onFocusBundle"
                                            type="text" placeholder="Buscar por nombre..."
                                            class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                                        <div v-if="bundleProductSearchResults.length"
                                            class="absolute left-0 right-0 top-full z-50 mt-1 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-900">
                                            <button v-for="pr in bundleProductSearchResults" :key="pr.id" type="button"
                                                @mousedown.prevent="selectBundleProduct(pr)"
                                                class="flex w-full items-center gap-3 px-4 py-3 text-left text-sm transition-colors hover:bg-gray-50 dark:hover:bg-gray-800">
                                                <span class="flex-1 font-medium text-content-primary dark:text-white">{{ pr.name }}</span>
                                                <span class="text-xs text-content-muted">Stock: {{ pr.stock }}</span>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="relative">
                                        <input
                                            @input="onSearchBundleSku(($event.target as HTMLInputElement).value)"
                                            @blur="onBlurBundle" @focus="onFocusBundle"
                                            type="text" placeholder="SKU / Código de barras..."
                                            class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                                        <div v-if="bundleProductSkuResults.length"
                                            class="absolute left-0 right-0 top-full z-50 mt-1 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-900">
                                            <button v-for="pr in bundleProductSkuResults" :key="pr.id" type="button"
                                                @mousedown.prevent="selectBundleProduct(pr)"
                                                class="flex w-full items-center gap-3 px-4 py-3 text-left text-sm transition-colors hover:bg-gray-50 dark:hover:bg-gray-800">
                                                <span class="flex-1 font-medium text-content-primary dark:text-white">{{ pr.name }}</span>
                                                <span class="text-xs text-content-muted">Stock: {{ pr.stock }}</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div v-if="(form.conditions.product_ids || []).length" class="mt-2 flex flex-wrap gap-2">
                                    <span v-for="pid in form.conditions.product_ids" :key="pid"
                                        class="flex items-center gap-1 rounded-xl bg-primary-100 px-3 py-1.5 text-xs font-bold text-primary-700 dark:bg-primary-900/30 dark:text-primary-300">
                                        #{{ pid }}
                                        <button type="button" @click="removeBundleProduct(pid)" class="ml-0.5 hover:text-danger">&times;</button>
                                    </span>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div>
                                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted">Modo de descuento</label>
                                    <div class="flex gap-4 pt-1">
                                        <label class="flex cursor-pointer items-center gap-2 text-sm text-content-primary dark:text-white">
                                            <input type="radio" v-model="discountMode" value="pct"
                                                class="text-primary-500 focus:ring-primary-500" />
                                            Porcentaje (%)
                                        </label>
                                        <label class="flex cursor-pointer items-center gap-2 text-sm text-content-primary dark:text-white">
                                            <input type="radio" v-model="discountMode" value="fixed"
                                                class="text-primary-500 focus:ring-primary-500" />
                                            Precio fijo total ($)
                                        </label>
                                    </div>
                                </div>
                                <div v-if="discountMode === 'pct'">
                                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted">% Descuento</label>
                                    <input v-model.number="form.conditions.discount_pct" type="number" min="1" max="100" required
                                        class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                                </div>
                                <div v-else>
                                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted">Precio fijo total ($)</label>
                                    <input v-model.number="form.conditions.special_price_total" type="number" min="1" required
                                        class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- ─── Schedule & options ──────────────── -->
                    <div class="rounded-2xl bg-gray-50 p-4 dark:bg-gray-800/50">
                        <h3 class="mb-3 text-xs font-bold uppercase tracking-wider text-content-muted">Programación y opciones</h3>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                            <div>
                                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted">Fecha inicio</label>
                                <input v-model="form.starts_at" type="datetime-local"
                                    class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted">Fecha fin</label>
                                <input v-model="form.expires_at" type="datetime-local"
                                    class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted">Prioridad</label>
                                <input v-model.number="form.priority" type="number" min="0"
                                    class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                            </div>
                            <div class="flex items-end gap-4 pb-2.5">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input v-model="form.is_active" type="checkbox" class="rounded border-gray-300 text-primary-500 focus:ring-primary-500" />
                                    <span class="text-xs font-bold uppercase tracking-wider text-content-muted">Activa</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input v-model="form.is_exclusive" type="checkbox" class="rounded border-gray-300 text-primary-500 focus:ring-primary-500" />
                                    <span class="text-xs font-bold uppercase tracking-wider text-content-muted">Exclusiva</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- ─── Buttons ─────────────────────────── -->
                    <div class="flex gap-3 pt-2">
                        <button type="button" @click="cancelForm"
                            class="flex-1 rounded-2xl border border-gray-200 py-2.5 text-sm font-bold text-content-secondary transition-colors hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800">
                            Cancelar
                        </button>
                        <button type="submit" :disabled="form.processing"
                            class="flex flex-1 items-center justify-center gap-2 rounded-2xl bg-primary-500 py-2.5 text-sm font-bold text-white shadow-sm transition-colors hover:bg-primary-600 disabled:cursor-not-allowed disabled:opacity-50">
                            {{ form.processing ? 'Guardando...' : editingId ? 'Actualizar' : 'Crear Promoción' }}
                        </button>
                    </div>
                </form>
            </div>
        </template>
    </AdminLayout>
</template>
