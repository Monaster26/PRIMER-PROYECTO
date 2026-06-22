<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { Check, Loader2, Trash2 } from 'lucide-vue-next';
import { computed, nextTick, onMounted, ref, watch } from 'vue';

const STORAGE_KEY = 'monasterios_inventory_draft';

interface ScannedItem {
    product_id: number;
    name: string;
    sku: string;
    system_stock: number;
    counted_stock: number;
    cost_price: number;
}

const barcodeInput = ref<HTMLInputElement | null>(null);
const searchQuery = ref('');
const items = ref<ScannedItem[]>([]);
const loading = ref(false);
const submitting = ref(false);
const error = ref('');
const highlightedId = ref<number | null>(null);

watch(items, (val) => {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(val));
}, { deep: true });

onMounted(() => {
    const saved = localStorage.getItem(STORAGE_KEY);
    if (saved) {
        try { items.value = JSON.parse(saved); } catch { /* ignore corrupt data */ }
    }
    barcodeInput.value?.focus();
});

function fmt(cents: number): string {
    return '$' + Math.round(cents / 100).toLocaleString('es-CL');
}

function diffClass(item: { system_stock: number; counted_stock: number }): string {
    const d = item.counted_stock - item.system_stock;
    if (d < 0) return 'text-red-600 font-bold';
    if (d > 0) return 'text-green-600 font-bold';
    return '';
}

function formatDiff(item: { system_stock: number; counted_stock: number }): string {
    const d = item.counted_stock - item.system_stock;
    return d > 0 ? '+' + d : String(d);
}

async function scanProduct() {
    const query = searchQuery.value.trim();
    if (!query) return;
    loading.value = true;
    error.value = '';
    try {
        const res = await window.axios.post(route('admin.inventory-adjustments.scan'), { sku: query, barcode: query });
        const product = res.data;
        const existing = items.value.find(i => i.product_id === product.id);
        if (existing) {
            highlightedId.value = product.id;
            setTimeout(() => highlightedId.value = null, 1500);
        } else {
            items.value.push({
                product_id: product.id,
                name: product.name,
                sku: product.sku,
                system_stock: product.system_stock,
                counted_stock: product.system_stock,
                cost_price: product.cost_price || 0,
            });
        }
        searchQuery.value = '';
        nextTick(() => {
            const input = document.getElementById(`conteo-${product.id}`) as HTMLInputElement | null;
            input?.focus();
            input?.select();
        });
    } catch (err: any) {
        error.value = err.response?.status === 404 ? 'Producto no encontrado' : 'Error al buscar producto';
        nextTick(() => barcodeInput.value?.focus());
    } finally {
        loading.value = false;
    }
}

function regresarAlBuscador() {
    searchQuery.value = '';
    barcodeInput.value?.focus();
    barcodeInput.value?.select();
}

function removeItem(index: number) {
    items.value.splice(index, 1);
}

const totalCostDiff = computed(() =>
    items.value.reduce((sum, i) => sum + ((i.counted_stock - i.system_stock) * i.cost_price), 0),
);

const totalDiffUnits = computed(() =>
    items.value.reduce((sum, i) => sum + (i.counted_stock - i.system_stock), 0),
);

function finalizeInventory() {
    if (submitting.value || items.value.length === 0) return;
    submitting.value = true;
    router.post(route('admin.inventory-adjustments.store'), {
        items: items.value.map(item => ({
            product_id: item.product_id,
            counted_stock: item.counted_stock,
        })),
    }, {
        preserveState: true,
        onSuccess: () => {
            items.value = [];
            localStorage.removeItem(STORAGE_KEY);
            submitting.value = false;
            barcodeInput.value?.focus();
        },
        onError: () => { submitting.value = false; },
    });
}
</script>

<template>
    <AdminLayout>
        <Head title="Auditoria de Inventario" />
        <div class="mx-auto max-w-6xl space-y-6 p-6">
            <!-- Header -->
            <div>
                <h1 class="text-2xl font-bold text-content-primary dark:text-white">Auditoria de inventario</h1>
                <p class="mt-1 text-sm text-content-muted">Escanea productos y registra el conteo real del stock.</p>
            </div>

            <!-- Scanner Bar -->
            <div class="relative">
                <div class="flex items-center gap-3">
                    <div class="relative flex-1">
                        <input
                            ref="barcodeInput"
                            v-model="searchQuery"
                            @keyup.enter.prevent="scanProduct"
                            type="text"
                            autofocus
                            placeholder="🔎 Escanea o ingresa SKU / código de barras..."
                            class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-5 py-3.5 pr-12 text-base font-mono text-content-primary outline-none transition-shadow focus:border-primary-400 focus:ring-4 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                        />
                        <span
                            v-if="loading"
                            class="absolute right-4 top-1/2 -translate-y-1/2 text-sm font-semibold text-primary-500"
                        >Buscando...</span>
                    </div>
                    <div class="shrink-0 rounded-xl bg-gray-100 px-4 py-3 dark:bg-gray-800">
                        <p class="text-xs text-content-muted">Diferencia total</p>
                        <p class="text-lg font-bold tabular-nums" :class="totalCostDiff < 0 ? 'text-red-600' : totalCostDiff > 0 ? 'text-green-600' : ''">
                            {{ fmt(totalCostDiff) }}
                        </p>
                    </div>
                </div>
                <p
                    v-if="error"
                    class="mt-2 rounded-lg bg-red-50 px-4 py-2 text-sm font-medium text-red-700 dark:bg-red-900/30 dark:text-red-400"
                >{{ error }}</p>
            </div>

            <!-- Items Table -->
            <div v-if="items.length === 0" class="rounded-2xl border-2 border-dashed border-gray-200 p-16 text-center dark:border-gray-700">
                <p class="text-lg font-medium text-content-muted">Ningún producto escaneado aún</p>
                <p class="mt-1 text-sm text-content-muted">Usa el buscador de arriba para comenzar la auditoria de inventario.</p>
            </div>
            <div v-else class="overflow-hidden rounded-2xl border border-gray-200 dark:border-gray-700">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-left text-xs font-bold uppercase tracking-wider text-content-muted dark:bg-gray-800/50">
                            <th class="px-4 py-3">SKU</th>
                            <th class="px-4 py-3">Producto</th>
                            <th class="px-4 py-3 text-center">Stock Sistema</th>
                            <th class="px-4 py-3 text-center">Conteo Real</th>
                            <th class="px-4 py-3 text-center">Diferencia</th>
                            <th class="px-4 py-3 text-right">Costo Und.</th>
                            <th class="px-4 py-3 text-right">Total Dif.</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <tr
                            v-for="(item, i) in items"
                            :key="item.product_id"
                            class="transition-colors"
                            :class="highlightedId === item.product_id ? 'bg-yellow-50 dark:bg-yellow-900/20' : 'hover:bg-gray-50 dark:hover:bg-gray-800/30'"
                        >
                            <td class="px-4 py-3 font-mono text-xs text-content-muted">{{ item.sku }}</td>
                            <td class="px-4 py-3 font-medium text-content-primary dark:text-white">{{ item.name }}</td>
                            <td class="px-4 py-3 text-center font-mono tabular-nums">{{ item.system_stock }}</td>
                            <td class="px-4 py-3 text-center">
                                <input
                                    :id="`conteo-${item.product_id}`"
                                    v-model.number="item.counted_stock"
                                    @keyup.enter="regresarAlBuscador"
                                    type="number"
                                    min="0"
                                    class="w-20 rounded-lg border border-gray-200 px-2 py-1.5 text-center font-mono text-sm font-bold text-content-primary outline-none transition-shadow focus:border-primary-400 focus:ring-2 focus:ring-primary-200 [appearance:textfield] dark:border-gray-700 dark:bg-gray-800 dark:text-white [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none"
                                />
                            </td>
                            <td class="px-4 py-3 text-center font-mono tabular-nums" :class="diffClass(item)">{{ formatDiff(item) }}</td>
                            <td class="px-4 py-3 text-right font-mono tabular-nums text-content-muted">{{ fmt(item.cost_price) }}</td>
                            <td class="px-4 py-3 text-right font-mono tabular-nums" :class="diffClass(item)">
                                {{ fmt((item.counted_stock - item.system_stock) * item.cost_price) }}
                            </td>
                            <td class="px-4 py-3">
                                <button @click="removeItem(i)" class="rounded-lg p-1 text-content-muted transition-colors hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/30">
                                    <Trash2 class="h-4 w-4" />
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Summary + Finalize -->
            <div v-if="items.length > 0" class="flex items-center justify-between rounded-2xl bg-gray-50 p-4 dark:bg-gray-800/50">
                <div class="space-y-1">
                    <p class="text-sm text-content-muted">
                        <span class="font-medium">{{ items.length }}</span> producto(s) · 
                        <span :class="totalDiffUnits < 0 ? 'text-red-600' : totalDiffUnits > 0 ? 'text-green-600' : ''">
                            {{ totalDiffUnits > 0 ? '+' : '' }}{{ totalDiffUnits }} unidades
                        </span>
                    </p>
                    <p class="text-2xl font-bold" :class="totalCostDiff < 0 ? 'text-red-600' : totalCostDiff > 0 ? 'text-green-600' : ''">
                        {{ fmt(Math.abs(totalCostDiff)) }}
                        <span class="text-sm font-normal text-content-muted">{{ totalCostDiff < 0 ? 'en pérdidas' : totalCostDiff > 0 ? 'en sobrantes' : 'sin diferencias' }}</span>
                    </p>
                </div>
                <button
                    @click="finalizeInventory"
                    :disabled="submitting"
                    class="flex items-center gap-2 rounded-2xl bg-emerald-600 px-6 py-3.5 text-sm font-bold text-white shadow-sm transition-all hover:bg-emerald-700 disabled:cursor-not-allowed disabled:bg-gray-300 dark:disabled:bg-gray-700"
                >
                    <template v-if="submitting">
                        <Loader2 class="h-5 w-5 animate-spin" />
                        Procesando...
                    </template>
                    <template v-else>
                        <Check class="h-5 w-5" />
                        Finalizar Inventario
                    </template>
                </button>
            </div>
        </div>
    </AdminLayout>
</template>
