<script setup lang="ts">
import { ref, computed, nextTick } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import {
    ShoppingCart, Plus, Minus, Trash2, X, Search, CreditCard,
    Banknote, Landmark, Smartphone, Check, Printer
} from 'lucide-vue-next';

interface CartItem {
    product: {
        id: number;
        name: string;
        sku: string;
        barcode: string | null;
        price: number;
        stock: number;
        unit: string;
        image_path: string | null;
    };
    quantity: number;
}

interface Product {
    id: number;
    name: string;
    sku: string;
    barcode: string | null;
    price: number;
    stock: number;
    unit: string;
    image_path: string | null;
}

interface PaymentEntry {
    method: 'cash' | 'card' | 'transfer' | 'mercadopago';
    amount: number;
}

const props = defineProps<{
    products: Product[];
}>();

const scannerInput = ref('');
const scannerRef = ref<HTMLInputElement | null>(null);
const searchQuery = ref('');
const showSearchDropdown = ref(false);
const searchFocused = ref(false);
const checkoutLoading = ref(false);
const lastSaleId = ref<number | null>(null);
const showSuccess = ref(false);

const cart = ref<CartItem[]>([]);

const payments = ref<PaymentEntry[]>([{ method: 'cash', amount: 0 }]);

const total = computed(() =>
    cart.value.reduce((sum, item) => sum + (item.product.price / 100) * item.quantity, 0)
);

const totalPayments = computed(() =>
    payments.value.reduce((sum, p) => sum + (Number(p.amount) || 0), 0)
);

const remaining = computed(() => total.value - totalPayments.value);
const isBalanced = computed(() => Math.abs(remaining.value) < 0.01 && total.value > 0);

const methodIcons: Record<string, any> = {
    cash: Banknote,
    card: CreditCard,
    transfer: Landmark,
    mercadopago: Smartphone,
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
    return props.products.filter(p =>
        p.name.toLowerCase().includes(q) ||
        p.sku.toLowerCase().includes(q) ||
        (p.barcode && p.barcode.toLowerCase().includes(q))
    ).slice(0, 10);
});

function focusScanner() {
    nextTick(() => scannerRef.value?.focus());
}

function addProductByCode(code: string) {
    const trimmed = code.trim();
    if (!trimmed) return;

    const existing = cart.value.find(
        item => item.product.barcode === trimmed || item.product.sku === trimmed
    );
    if (existing) {
        existing.quantity++;
        scannerInput.value = '';
        focusScanner();
        return;
    }

    const product = props.products.find(
        p => p.barcode === trimmed || p.sku === trimmed
    );
    if (product) {
        addToCart(product);
        scannerInput.value = '';
        focusScanner();
        return;
    }

    fetch(route('admin.pos.lookup', trimmed))
        .then(res => {
            if (!res.ok) throw new Error('No encontrado');
            return res.json();
        })
        .then(data => {
            addToCart(data.product);
        })
        .catch(() => {
            alert('Producto no encontrado: ' + trimmed);
        })
        .finally(() => {
            scannerInput.value = '';
            focusScanner();
        });
}

function addToCart(product: Product) {
    const existing = cart.value.find(item => item.product.id === product.id);
    if (existing) {
        existing.quantity++;
    } else {
        cart.value.push({ product, quantity: 1 });
    }
    searchQuery.value = '';
    showSearchDropdown.value = false;
}

function incrementQty(index: number) {
    if (cart.value[index].quantity < cart.value[index].product.stock) {
        cart.value[index].quantity++;
    }
}

function decrementQty(index: number) {
    if (cart.value[index].quantity > 1) {
        cart.value[index].quantity--;
    }
}

function removeItem(index: number) {
    cart.value.splice(index, 1);
}

function selectProduct(product: Product) {
    addToCart(product);
}

function addPaymentRow() {
    payments.value.push({ method: 'cash', amount: 0 });
}

function removePaymentRow(index: number) {
    if (payments.value.length > 1) {
        payments.value.splice(index, 1);
    }
}

function clearCart() {
    cart.value = [];
    payments.value = [{ method: 'cash', amount: 0 }];
    lastSaleId.value = null;
    showSuccess.value = false;
    focusScanner();
}

function validateStock(): boolean {
    for (const item of cart.value) {
        if (item.quantity > item.product.stock) {
            alert(`Stock insuficiente para "${item.product.name}".\nDisponible: ${item.product.stock} — Solicitaste: ${item.quantity}`);
            return false;
        }
    }
    return true;
}

function finalizeSale() {
    if (cart.value.length === 0) {
        alert('Agrega productos al carrito.');
        return;
    }
    if (!validateStock()) return;
    if (!isBalanced.value) {
        alert('Los pagos no cubren el total. Faltan: $' + remaining.value.toFixed(0));
        return;
    }

    checkoutLoading.value = true;

    const payload = {
        items: cart.value.map(item => ({
            product_id: item.product.id,
            quantity: item.quantity,
        })),
        payments: payments.value.map(p => ({
            method: p.method,
            amount: Number(p.amount),
        })),
    };

    fetch(route('admin.pos.checkout'), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '' },
        body: JSON.stringify(payload),
    })
        .then(res => {
            if (!res.ok) return res.json().then(err => { throw new Error(err.error || 'Error al procesar venta'); });
            return res.json();
        })
        .then(data => {
            lastSaleId.value = data.sale_id;
            showSuccess.value = true;
            cart.value = [];
            payments.value = [{ method: 'cash', amount: 0 }];
            setTimeout(() => { showSuccess.value = false; lastSaleId.value = null; }, 6000);
            focusScanner();
        })
        .catch(err => {
            alert(err.message);
        })
        .finally(() => {
            checkoutLoading.value = false;
        });
}

function handleScannerEnter() {
    if (scannerInput.value.trim()) {
        addProductByCode(scannerInput.value.trim());
    }
}

function handleBlur() {
    setTimeout(() => { searchFocused.value = false; showSearchDropdown.value = false }, 200);
}

const fmt = (v: number) => '$' + v.toLocaleString('es-CO', { minimumFractionDigits: 0 });
const fmtDec = (v: number) => '$' + Number(v).toLocaleString('es-CO', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
</script>

<template>
    <Head title="Caja Rápida" />
    <AdminLayout>
        <template #title>
            <h1 class="text-xl font-display font-bold text-content-primary dark:text-white">Caja Rápida — POS</h1>
        </template>

        <div v-if="showSuccess && lastSaleId" class="mb-4 bg-success/10 border border-success/30 rounded-3xl p-4 flex items-center gap-3 text-success">
            <Check class="w-6 h-6 flex-shrink-0" />
            <div>
                <p class="font-bold">Venta #{{ lastSaleId }} registrada correctamente.</p>
                <p class="text-sm text-success/80">Puedes seguir agregando productos para una nueva venta.</p>
            </div>
            <button @click="showSuccess = false; lastSaleId = null" class="ml-auto p-1.5 rounded-full hover:bg-success/20 transition-colors">
                <X class="w-4 h-4" />
            </button>
        </div>

        <div class="flex gap-4 h-[calc(100vh-10rem)]">
            <!-- Left Column: Cart -->
            <div class="flex-1 flex flex-col bg-white dark:bg-surface-dark rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm overflow-hidden">
                <!-- Scanner & Search Bar -->
                <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-800 flex gap-3">
                    <div class="relative flex-1">
                        <input ref="scannerRef"
                            v-model="scannerInput"
                            @keydown.enter.prevent="handleScannerEnter"
                            type="text" autofocus
                            placeholder="Escanea o tipea código de barras / SKU y presiona Enter"
                            class="w-full border border-gray-200 dark:border-gray-700 rounded-2xl px-4 py-3 bg-gray-50 dark:bg-gray-900 text-content-primary dark:text-white text-sm font-mono" />
                    </div>
                    <div class="relative w-72">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-content-muted" />
                        <input v-model="searchQuery"
                            @focus="searchFocused = true"
                            @blur="handleBlur()"
                            @input="showSearchDropdown = searchQuery.length > 0"
                            type="text"
                            placeholder="Buscar por nombre, SKU o código..."
                            class="w-full pl-10 pr-4 py-3 rounded-2xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-content-primary dark:text-white text-sm" />
                        <div v-if="showSearchDropdown && filteredProducts.length" class="absolute top-full left-0 right-0 mt-1 bg-white dark:bg-surface-dark border border-gray-200 dark:border-gray-700 rounded-2xl shadow-lg z-50 max-h-60 overflow-y-auto">
                            <button v-for="p in filteredProducts" :key="p.id" @mousedown="selectProduct(p)"
                                class="w-full text-left px-4 py-2.5 hover:bg-gray-50 dark:hover:bg-gray-800 text-sm flex items-center gap-3 border-b border-gray-100 dark:border-gray-800 last:border-0">
                                <span class="font-medium text-content-primary dark:text-white flex-1 truncate">{{ p.name }}</span>
                                <span class="text-xs font-mono text-content-muted">{{ p.sku }}</span>
                                <span class="font-bold text-primary-500">{{ fmt(p.price / 100) }}</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Cart Header -->
                <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-800 flex items-center gap-3">
                    <ShoppingCart class="w-5 h-5 text-primary-500" />
                    <h2 class="font-bold text-content-primary dark:text-white flex-1">Venta Actual</h2>
                    <span class="text-xs font-bold text-content-muted bg-gray-100 dark:bg-gray-800 px-2.5 py-1 rounded-full">
                        {{ cart.length }} {{ cart.length === 1 ? 'ítem' : 'ítems' }}
                    </span>
                    <button v-if="cart.length" @click="clearCart" class="text-xs font-bold text-danger hover:text-danger/80 transition-colors">
                        Limpiar
                    </button>
                </div>

                <!-- Cart Table -->
                <div class="flex-1 overflow-y-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 dark:bg-gray-900/50 text-xs uppercase tracking-wider text-content-muted dark:text-gray-500 sticky top-0">
                            <tr>
                                <th class="px-4 py-2.5 font-bold w-24">Código</th>
                                <th class="px-4 py-2.5 font-bold">Descripción</th>
                                <th class="px-4 py-2.5 font-bold text-center w-36">Cantidad</th>
                                <th class="px-4 py-2.5 font-bold text-right w-28">P.Unitario</th>
                                <th class="px-4 py-2.5 font-bold text-right w-28">Total</th>
                                <th class="px-4 py-2.5 w-12"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            <tr v-if="!cart.length">
                                <td colspan="6" class="px-4 py-16 text-center text-sm text-content-muted dark:text-gray-500">
                                    <ShoppingCart class="w-10 h-10 mx-auto mb-3 opacity-30" />
                                    Escanea o busca productos para comenzar la venta
                                </td>
                            </tr>
                            <tr v-for="(item, i) in cart" :key="item.product.id"
                                class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors">
                                <td class="px-4 py-3 text-xs font-mono text-content-muted">{{ item.product.sku }}</td>
                                <td class="px-4 py-3 text-sm font-medium text-content-primary dark:text-white max-w-[240px] truncate">
                                    {{ item.product.name }}
                                    <span class="text-[10px] text-content-muted ml-1">/{{ item.product.unit }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-center gap-1">
                                        <button @click="decrementQty(i)"
                                            class="w-7 h-7 rounded-lg bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 flex items-center justify-center transition-colors">
                                            <Minus class="w-3.5 h-3.5 text-content-secondary" />
                                        </button>
                                        <input v-model.number="item.quantity" type="number" min="1" :max="item.product.stock"
                                            class="w-14 text-center font-bold text-content-primary dark:text-white text-sm bg-transparent border border-gray-200 dark:border-gray-700 rounded-lg px-1 py-1.5 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none" />
                                        <button @click="incrementQty(i)"
                                            class="w-7 h-7 rounded-lg bg-primary-100 dark:bg-primary-900/30 hover:bg-primary-200 dark:hover:bg-primary-800/40 flex items-center justify-center transition-colors"
                                            :class="{ 'opacity-30 cursor-not-allowed': item.quantity >= item.product.stock }">
                                            <Plus class="w-3.5 h-3.5 text-primary-500" />
                                        </button>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm text-right font-medium text-content-primary dark:text-white">
                                    {{ fmt(item.product.price / 100) }}
                                </td>
                                <td class="px-4 py-3 text-sm text-right font-bold text-content-primary dark:text-white">
                                    {{ fmt(Math.round((item.product.price / 100) * item.quantity)) }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <button @click="removeItem(i)"
                                        class="p-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 text-danger/60 hover:text-danger transition-colors">
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Right Column: Payment Panel -->
            <div class="w-96 flex-shrink-0 flex flex-col bg-slate-50 dark:bg-gray-900/50 rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm p-5">
                <h3 class="text-xs font-bold text-content-muted dark:text-gray-400 uppercase tracking-wider mb-3">Resumen</h3>

                <!-- Total -->
                <div class="mb-6">
                    <p class="text-xs font-bold text-content-muted dark:text-gray-400 uppercase tracking-wider mb-1">Total a Pagar</p>
                    <p class="text-4xl font-black text-content-primary dark:text-white tracking-tight">{{ fmtDec(total) }}</p>
                </div>

                <!-- Items Count -->
                <div class="flex items-center gap-2 mb-6 text-sm text-content-muted">
                    <ShoppingCart class="w-4 h-4" />
                    <span>{{ cart.reduce((s, i) => s + i.quantity, 0) }} unidades / {{ cart.length }} productos</span>
                </div>

                <hr class="border-gray-200 dark:border-gray-700 mb-4" />

                <!-- Payment Methods -->
                <h4 class="text-xs font-bold text-content-muted dark:text-gray-400 uppercase tracking-wider mb-3">Métodos de Pago</h4>

                <div class="space-y-3 flex-1 overflow-y-auto">
                    <div v-for="(payment, i) in payments" :key="i"
                        class="flex items-center gap-2">
                        <select v-model="payment.method"
                            class="border border-gray-200 dark:border-gray-700 rounded-xl px-2.5 py-2 bg-white dark:bg-gray-800 text-sm text-content-primary dark:text-white flex-1">
                            <option value="cash">Efectivo</option>
                            <option value="card">Tarjeta</option>
                            <option value="transfer">Transferencia</option>
                            <option value="mercadopago">Mercado Pago</option>
                        </select>
                        <div class="relative flex-1">
                            <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-xs font-bold text-content-muted">$</span>
                            <input v-model.number="payment.amount" type="number" min="0" step="100"
                                class="w-full border border-gray-200 dark:border-gray-700 rounded-xl pl-7 pr-3 py-2 bg-white dark:bg-gray-800 text-sm text-content-primary dark:text-white text-right font-bold" />
                        </div>
                        <button v-if="payments.length > 1" @click="removePaymentRow(i)"
                            class="p-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 text-danger/60 hover:text-danger transition-colors flex-shrink-0">
                            <X class="w-4 h-4" />
                        </button>
                    </div>

                    <button @click="addPaymentRow"
                        class="flex items-center gap-1.5 text-xs font-bold text-primary-500 hover:text-primary-600 transition-colors">
                        <Plus class="w-3.5 h-3.5" /> Agregar otro método de pago
                    </button>
                </div>

                <!-- Balance Indicator -->
                <div v-if="total > 0" class="mt-4 px-3 py-2.5 rounded-2xl text-sm font-bold text-center"
                    :class="isBalanced ? 'bg-success/10 text-success' : remaining > 0 ? 'bg-warning/10 text-warning' : 'bg-danger/10 text-danger'">
                    <template v-if="isBalanced">✓ Pagos completos</template>
                    <template v-else-if="remaining > 0">Faltan {{ fmtDec(remaining) }}</template>
                    <template v-else>Sobran {{ fmtDec(Math.abs(remaining)) }}</template>
                </div>

                <!-- Checkout Button -->
                <button @click="finalizeSale"
                    :disabled="checkoutLoading || cart.length === 0 || !isBalanced"
                    class="mt-4 w-full py-3.5 rounded-2xl bg-emerald-600 hover:bg-emerald-700 disabled:bg-gray-300 dark:disabled:bg-gray-700 disabled:cursor-not-allowed text-white text-sm font-bold flex items-center justify-center gap-2 transition-colors shadow-sm">
                    <template v-if="checkoutLoading">
                        <span class="inline-block w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                        Procesando...
                    </template>
                    <template v-else>
                        <Check class="w-5 h-5" />
                        Finalizar Venta
                    </template>
                </button>
            </div>
        </div>
    </AdminLayout>
</template>
