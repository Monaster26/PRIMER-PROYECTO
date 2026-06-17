<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
import {
    Banknote,
    Check,
    CreditCard,
    Landmark,
    Minus,
    Plus,
    Search,
    ShoppingCart,
    Smartphone,
    Trash2,
    X,
} from 'lucide-vue-next';
import { computed, nextTick, ref } from 'vue';
import { useBarcodeScanner } from '@/composables/useBarcodeScanner';
import { usePosTabsStore } from '@/Stores/posTabsStore';
import { storeToRefs } from 'pinia';
import type { CartItem, Product, PaymentEntry } from '@/Stores/posTabsStore';

const props = defineProps<{
    products: Product[];
}>();

const scannerInput = ref('');
const scannerRef = ref<HTMLInputElement | null>(null);
const searchRef = ref<HTMLInputElement | null>(null);
const searchQuery = ref('');
const showSearchDropdown = ref(false);
const searchFocused = ref(false);
const checkoutLoading = ref(false);
const lastSaleId = ref<number | null>(null);
const showSuccess = ref(false);
const scannedProductName = ref<string | null>(null);
const scannedProductIndex = ref<number | null>(null);

const posTabStore = usePosTabsStore()
const { activeTab, tabs, activeIndex } = storeToRefs(posTabStore)
const { addTab, removeTab, switchTab } = posTabStore

// ─── Escáner global: captura ráfagas del lector de código de barras ──
useBarcodeScanner({
    onScan: addProductByCode,
    allowlist: [scannerRef, searchRef],
    discardWhen: (el) =>
        el instanceof HTMLInputElement && el.getAttribute('step') === '100',
});

const total = computed(() =>
    activeTab.value.cart.reduce(
        (sum, item) => sum + (item.product.price / 100) * item.quantity,
        0,
    ),
);

const totalPayments = computed(() =>
    activeTab.value.payments.reduce((sum, p) => sum + (Number(p.amount) || 0), 0),
);

const remaining = computed(() => total.value - totalPayments.value);
const isBalanced = computed(
    () => Math.abs(remaining.value) < 0.01 && total.value > 0,
);

const methodIcons: Record<string, any> = {
    cash: Banknote,
    card: CreditCard,
    transfer: Landmark,
    mercadopago: Smartphone,
};

const methodColors: Record<string, string> = {
    cash: 'text-emerald-500',
    card: 'text-amber-500',
    transfer: 'text-content-muted',
    mercadopago: 'text-sky-500',
};

const methodLabels: Record<string, string> = {
    cash: 'Efectivo',
    card: 'Tarjeta',
    transfer: 'Transferencia',
    mercadopago: 'Mercado Pago',
};

const filteredProducts = computed(() => {
    if (!searchQuery.value) return [];
    const q = searchQuery.value.toLowerCase();
    return props.products
        .filter(
            (p) =>
                p.name.toLowerCase().includes(q) ||
                p.sku.toLowerCase().includes(q) ||
                (p.barcode && p.barcode.toLowerCase().includes(q)),
        )
        .slice(0, 10);
});

function focusScanner() {
    nextTick(() => scannerRef.value?.focus());
}

function flashScanFeedback(productName: string, index: number) {
    scannedProductName.value = productName;
    scannedProductIndex.value = index;
    setTimeout(() => {
        scannedProductName.value = null;
        scannedProductIndex.value = null;
    }, 600);
}

function addProductByCode(code: string) {
    const trimmed = code.trim();
    if (!trimmed) return;

    // 1. Buscar en el carrito (coincide por barcode o sku)
    const cartIdx = activeTab.value.cart.findIndex(
        (item) =>
            item.product.barcode === trimmed || item.product.sku === trimmed,
    );
    if (cartIdx !== -1) {
        activeTab.value.cart[cartIdx].quantity++;
        flashScanFeedback(activeTab.value.cart[cartIdx].product.name, cartIdx);
        scannerInput.value = '';
        focusScanner();
        return;
    }

    // 2. Buscar en productos precargados (props)
    const product = props.products.find(
        (p) => p.barcode === trimmed || p.sku === trimmed,
    );
    if (product) {
        const existingIdx = activeTab.value.cart.findIndex(
            (item) => item.product.id === product.id,
        );
        if (existingIdx !== -1) {
            activeTab.value.cart[existingIdx].quantity++;
            flashScanFeedback(product.name, existingIdx);
        } else {
            activeTab.value.cart.push({ product, quantity: 1 });
            flashScanFeedback(product.name, activeTab.value.cart.length - 1);
        }
        scannerInput.value = '';
        focusScanner();
        return;
    }

    // 3. Fallback: buscar por API (producto no cargado)
    fetch(route('admin.pos.lookup', trimmed))
        .then((res) => {
            if (!res.ok) throw new Error('No encontrado');
            return res.json();
        })
        .then((data) => {
            const p = data.product as Product;
            const existingIdx = activeTab.value.cart.findIndex(
                (item) => item.product.id === p.id,
            );
            if (existingIdx !== -1) {
                activeTab.value.cart[existingIdx].quantity++;
                flashScanFeedback(p.name, existingIdx);
            } else {
                activeTab.value.cart.push({ product: p, quantity: 1 });
                flashScanFeedback(p.name, activeTab.value.cart.length - 1);
            }
            searchQuery.value = '';
            showSearchDropdown.value = false;
        })
        .catch(() => {
            alert('❌ Producto no encontrado: ' + trimmed);
        })
        .finally(() => {
            scannerInput.value = '';
            focusScanner();
        });
}

function addToCart(product: Product) {
    const existing = activeTab.value.cart.find((item) => item.product.id === product.id);
    if (existing) {
        existing.quantity++;
    } else {
        activeTab.value.cart.push({ product, quantity: 1 });
    }
    searchQuery.value = '';
    showSearchDropdown.value = false;
}

function incrementQty(index: number) {
    if (activeTab.value.cart[index].quantity < activeTab.value.cart[index].product.stock) {
        activeTab.value.cart[index].quantity++;
    }
}

function decrementQty(index: number) {
    if (activeTab.value.cart[index].quantity > 1) {
        activeTab.value.cart[index].quantity--;
    }
}

function removeItem(index: number) {
    activeTab.value.cart.splice(index, 1);
}

function selectProduct(product: Product) {
    addToCart(product);
}

function clearCart() {
    activeTab.value.cart = [];
    activeTab.value.payments = [
        { method: 'cash', amount: null },
        { method: 'card', amount: null },
        { method: 'transfer', amount: null },
    ];
    lastSaleId.value = null;
    showSuccess.value = false;
    focusScanner();
}

function validateStock(): boolean {
    for (const item of activeTab.value.cart) {
        if (item.quantity > item.product.stock) {
            alert(
                `Stock insuficiente para "${item.product.name}".\nDisponible: ${item.product.stock} — Solicitaste: ${item.quantity}`,
            );
            return false;
        }
    }
    return true;
}

function finalizeSale() {
    if (activeTab.value.cart.length === 0) {
        alert('Agrega productos al carrito.');
        return;
    }
    if (!validateStock()) return;
    if (!isBalanced.value) {
        alert(
            'Los pagos no cubren el total. Faltan: $' +
                remaining.value.toFixed(0),
        );
        return;
    }

    checkoutLoading.value = true;

    const payload = {
        items: activeTab.value.cart.map((item) => ({
            product_id: item.product.id,
            quantity: item.quantity,
        })),
        payments: activeTab.value.payments.map((p) => ({
            method: p.method,
            amount: Number(p.amount),
        })),
    };

    fetch(route('admin.pos.checkout'), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN':
                document
                    .querySelector('meta[name="csrf-token"]')
                    ?.getAttribute('content') || '',
        },
        body: JSON.stringify(payload),
    })
        .then((res) => {
            if (!res.ok)
                return res.json().then((err) => {
                    throw new Error(err.error || 'Error al procesar venta');
                });
            return res.json();
        })
        .then((data) => {
            lastSaleId.value = data.sale_id;
            showSuccess.value = true;
            activeTab.value.cart = [];
            activeTab.value.payments = [
                { method: 'cash', amount: null },
                { method: 'card', amount: null },
                { method: 'transfer', amount: null },
            ];
            setTimeout(() => {
                showSuccess.value = false;
                lastSaleId.value = null;
            }, 6000);
            focusScanner();
        })
        .catch((err) => {
            alert(err.message);
        })
        .finally(() => {
            checkoutLoading.value = false;
        });
}

function handleBlur() {
    setTimeout(() => {
        searchFocused.value = false;
        showSearchDropdown.value = false;
    }, 200);
}

const fmt = (v: number) =>
    '$' + v.toLocaleString('es-CO', { minimumFractionDigits: 0 });
const fmtDec = (v: number) =>
    '$' +
    Number(v).toLocaleString('es-CO', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    });
</script>

<template>
    <Head title="Caja Rápida" />
    <AdminLayout>
        <template #title>
            <h1
                class="font-display text-xl font-bold text-content-primary dark:text-white"
            >
                Caja Rápida — POS
            </h1>
        </template>

        <!-- Tab Bar -->
        <div class="mb-4 flex items-center gap-2 overflow-x-auto">
            <button
                v-for="(tab, i) in tabs"
                :key="tab.id"
                @click="switchTab(i)"
                class="flex items-center gap-2 rounded-full px-4 py-1.5 text-sm font-bold whitespace-nowrap transition-colors"
                :class="
                    activeIndex === i
                        ? 'bg-primary-500 text-white shadow-sm'
                        : 'bg-gray-100 text-content-muted hover:bg-gray-200 hover:text-content-primary dark:bg-gray-800 dark:hover:bg-gray-700 dark:hover:text-white'
                "
            >
                {{ tab.name }}
                <button
                    v-if="tabs.length > 1"
                    @click.stop="removeTab(i)"
                    class="flex h-4 w-4 items-center justify-center rounded-full text-[10px] font-bold transition-colors hover:bg-white/20"
                >
                    <X class="h-3 w-3" />
                </button>
            </button>
            <button
                @click="addTab"
                class="flex items-center gap-1 rounded-full px-3 py-1.5 text-sm font-bold text-content-muted transition-colors hover:bg-gray-100 hover:text-content-primary dark:hover:bg-gray-800 dark:hover:text-white"
            >
                <Plus class="h-4 w-4" />
            </button>
        </div>

        <div
            v-if="showSuccess && lastSaleId"
            class="mb-4 flex items-center gap-3 rounded-3xl border border-success/30 bg-success/10 p-4 text-success"
        >
            <Check class="h-6 w-6 flex-shrink-0" />
            <div>
                <p class="font-bold">
                    Venta #{{ lastSaleId }} registrada correctamente.
                </p>
                <p class="text-sm text-success/80">
                    Puedes seguir agregando productos para una nueva venta.
                </p>
            </div>
            <button
                @click="
                    showSuccess = false;
                    lastSaleId = null;
                "
                class="ml-auto rounded-full p-1.5 transition-colors hover:bg-success/20"
            >
                <X class="h-4 w-4" />
            </button>
        </div>

        <div class="flex h-[calc(100vh-10rem)] gap-4">
            <!-- Left Column: Cart -->
            <div
                class="flex flex-1 flex-col overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-surface-dark"
            >
                <!-- Scanner & Search Bar -->
                <div
                    class="flex gap-3 border-b border-gray-100 px-4 py-3 dark:border-gray-800"
                >
                    <div class="relative flex-1">
                        <input
                            ref="scannerRef"
                            v-model="scannerInput"
                            type="text"
                            autofocus
                            placeholder="🔎 Escanea el código de barras o SKU"
                            class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 font-mono text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                        />
                        <span
                            v-if="scannerInput"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-[10px] font-bold uppercase tracking-wider text-emerald-500"
                        >
                            Escaneando...
                        </span>
                    </div>
                    <div class="relative w-72">
                        <Search
                            class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-content-muted"
                        />
                        <input
                            ref="searchRef"
                            v-model="searchQuery"
                            @focus="searchFocused = true"
                            @blur="handleBlur()"
                            @input="showSearchDropdown = searchQuery.length > 0"
                            type="text"
                            placeholder="Buscar por nombre, SKU o código..."
                            class="w-full rounded-2xl border border-gray-200 bg-gray-50 py-3 pl-10 pr-4 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                        />
                        <div
                            v-if="showSearchDropdown && filteredProducts.length"
                            class="absolute left-0 right-0 top-full z-50 mt-1 max-h-60 overflow-y-auto rounded-2xl border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-surface-dark"
                        >
                            <button
                                v-for="p in filteredProducts"
                                :key="p.id"
                                @mousedown="selectProduct(p)"
                                class="flex w-full items-center gap-3 border-b border-gray-100 px-4 py-2.5 text-left text-sm last:border-0 hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-800"
                            >
                                <span
                                    class="flex-1 truncate font-medium text-content-primary dark:text-white"
                                    >{{ p.name }}</span
                                >
                                <span
                                    class="font-mono text-xs text-content-muted"
                                    >{{ p.sku }}</span
                                >
                                <span class="font-bold text-primary-500">{{
                                    fmt(p.price / 100)
                                }}</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Cart Header -->
                <div
                    class="flex items-center gap-3 border-b border-gray-100 px-4 py-3 dark:border-gray-800"
                >
                    <ShoppingCart class="h-5 w-5 text-primary-500" />
                    <h2
                        class="flex-1 font-bold text-content-primary dark:text-white"
                    >
                        Venta Actual
                    </h2>
                    <span
                        class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-bold text-content-muted dark:bg-gray-800"
                    >
                        {{ activeTab.cart.length }}
                        {{ activeTab.cart.length === 1 ? 'ítem' : 'ítems' }}
                    </span>
                    <button
                        v-if="activeTab.cart.length"
                        @click="clearCart"
                        class="text-xs font-bold text-danger transition-colors hover:text-danger/80"
                    >
                        Limpiar
                    </button>
                </div>

                <!-- Cart Table -->
                <div class="flex-1 overflow-y-auto">
                    <table class="w-full text-left">
                        <thead
                            class="sticky top-0 bg-gray-50 text-xs uppercase tracking-wider text-content-muted dark:bg-gray-900/50 dark:text-gray-500"
                        >
                            <tr>
                                <th class="w-24 px-4 py-2.5 font-bold">
                                    Código
                                </th>
                                <th class="px-4 py-2.5 font-bold">
                                    Descripción
                                </th>
                                <th
                                    class="w-36 px-4 py-2.5 text-center font-bold"
                                >
                                    Cantidad
                                </th>
                                <th
                                    class="w-28 px-4 py-2.5 text-right font-bold"
                                >
                                    P.Unitario
                                </th>
                                <th
                                    class="w-28 px-4 py-2.5 text-right font-bold"
                                >
                                    Total
                                </th>
                                <th class="w-12 px-4 py-2.5"></th>
                            </tr>
                        </thead>
                        <tbody
                            class="divide-y divide-gray-100 dark:divide-gray-800"
                        >
                            <tr v-if="!activeTab.cart.length">
                                <td
                                    colspan="6"
                                    class="px-4 py-16 text-center text-sm text-content-muted dark:text-gray-500"
                                >
                                    <ShoppingCart
                                        class="mx-auto mb-3 h-10 w-10 opacity-30"
                                    />
                                    Escanea o busca productos para comenzar la
                                    venta
                                </td>
                            </tr>
                            <tr
                                v-for="(item, i) in activeTab.cart"
                                :key="item.product.id"
                                class="transition-all duration-300 hover:bg-gray-50 dark:hover:bg-gray-800/30"
                                :class="{
                                    'animate-pulse bg-emerald-50 dark:bg-emerald-900/20':
                                        scannedProductIndex === i,
                                }"
                            >
                                <td
                                    class="px-4 py-3 font-mono text-xs text-content-muted"
                                >
                                    {{ item.product.sku }}
                                </td>
                                <td
                                    class="max-w-[240px] truncate px-4 py-3 text-sm font-medium text-content-primary dark:text-white"
                                >
                                    {{ item.product.name }}
                                    <span
                                        class="ml-1 text-[10px] text-content-muted"
                                        >/{{ item.product.unit }}</span
                                    >
                                </td>
                                <td class="px-4 py-3">
                                    <div
                                        class="flex items-center justify-center gap-1"
                                    >
                                        <button
                                            @click="decrementQty(i)"
                                            class="flex h-7 w-7 items-center justify-center rounded-lg bg-gray-100 transition-colors hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700"
                                        >
                                            <Minus
                                                class="h-3.5 w-3.5 text-content-secondary"
                                            />
                                        </button>
                                        <input
                                            v-model.number="item.quantity"
                                            type="number"
                                            min="1"
                                            :max="item.product.stock"
                                            class="w-14 rounded-lg border border-gray-200 bg-transparent px-1 py-1.5 text-center text-sm font-bold text-content-primary [appearance:textfield] dark:border-gray-700 dark:text-white [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none"
                                        />
                                        <button
                                            @click="incrementQty(i)"
                                            class="flex h-7 w-7 items-center justify-center rounded-lg bg-primary-100 transition-colors hover:bg-primary-200 dark:bg-primary-900/30 dark:hover:bg-primary-800/40"
                                            :class="{
                                                'cursor-not-allowed opacity-30':
                                                    item.quantity >=
                                                    item.product.stock,
                                            }"
                                        >
                                            <Plus
                                                class="h-3.5 w-3.5 text-primary-500"
                                            />
                                        </button>
                                    </div>
                                </td>
                                <td
                                    class="px-4 py-3 text-right text-sm font-medium text-content-primary dark:text-white"
                                >
                                    {{ fmt(item.product.price / 100) }}
                                </td>
                                <td
                                    class="px-4 py-3 text-right text-sm font-bold text-content-primary dark:text-white"
                                >
                                    {{
                                        fmt(
                                            Math.round(
                                                (item.product.price / 100) *
                                                    item.quantity,
                                            ),
                                        )
                                    }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <button
                                        @click="removeItem(i)"
                                        class="rounded-lg p-1.5 text-danger/60 transition-colors hover:bg-red-50 hover:text-danger dark:hover:bg-red-900/20"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Right Column: Payment Panel -->
            <div
                class="flex w-96 flex-shrink-0 flex-col rounded-3xl border border-gray-100 bg-slate-50 p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900/50"
            >
                <h3
                    class="mb-2 text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                >
                    Resumen
                </h3>

                <!-- Total -->
                <div class="mb-4">
                    <p
                        class="mb-1 text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                    >
                        Total a Pagar
                    </p>
                    <p
                        class="text-4xl font-black tracking-tight text-content-primary dark:text-white"
                    >
                        {{ fmtDec(total) }}
                    </p>
                </div>

                <!-- Items Count -->
                <div
                    class="mb-4 flex items-center gap-2 text-sm text-content-muted"
                >
                    <ShoppingCart class="h-4 w-4" />
                    <span
                        >{{ activeTab.cart.reduce((s, i) => s + i.quantity, 0) }} unidades
                        / {{ activeTab.cart.length }} productos</span
                    >
                </div>

                <hr class="mb-3 border-gray-200 dark:border-gray-700" />

                <!-- Payment Methods -->
                <h4
                    class="mb-2 text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                >
                    Métodos de Pago
                </h4>

                <div class="space-y-2">
                    <div
                        v-for="(payment, i) in activeTab.payments"
                        :key="i"
                        class="flex items-center gap-2"
                    >
                        <div class="flex w-36 items-center gap-2 text-content-primary dark:text-white">
                            <component :is="methodIcons[payment.method]" :class="['h-5 w-5', methodColors[payment.method]]" />
                            <span class="text-sm font-medium">{{ methodLabels[payment.method] }}</span>
                        </div>
                        <div class="relative flex-[2]">
                            <span
                                class="absolute left-2.5 top-1/2 -translate-y-1/2 text-xs font-bold text-content-muted"
                                >$</span
                            >
                            <input
                                v-model.number="payment.amount"
                                type="number"
                                min="0"
                                step="100"
                                class="w-full rounded-xl border border-gray-200 bg-white py-2 pl-7 pr-3 text-right text-sm font-bold text-content-primary dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            />
                        </div>
                    </div>
                </div>

                <!-- Balance Indicator -->
                <div
                    v-if="total > 0"
                    class="mt-4 rounded-2xl px-3 py-2.5 text-center text-sm font-bold"
                    :class="
                        isBalanced
                            ? 'bg-success/10 text-success'
                            : remaining > 0
                              ? 'bg-warning/10 text-warning'
                              : 'bg-danger/10 text-danger'
                    "
                >
                    <template v-if="isBalanced">✓ Pagos completos</template>
                    <template v-else-if="remaining > 0"
                        >Faltan {{ fmtDec(remaining) }}</template
                    >
                    <template v-else
                        >Sobran {{ fmtDec(Math.abs(remaining)) }}</template
                    >
                </div>

                <!-- Checkout Button -->
                <button
                    @click="finalizeSale"
                    :disabled="
                        checkoutLoading || activeTab.cart.length === 0 || !isBalanced
                    "
                    class="mt-4 flex w-full items-center justify-center gap-2 rounded-2xl bg-emerald-600 py-3.5 text-sm font-bold text-white shadow-sm transition-colors hover:bg-emerald-700 disabled:cursor-not-allowed disabled:bg-gray-300 dark:disabled:bg-gray-700"
                >
                    <template v-if="checkoutLoading">
                        <span
                            class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-white/30 border-t-white"
                        ></span>
                        Procesando...
                    </template>
                    <template v-else>
                        <Check class="h-5 w-5" />
                        Finalizar Venta
                    </template>
                </button>
            </div>
        </div>
    </AdminLayout>
</template>
