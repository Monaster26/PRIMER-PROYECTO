<script setup lang="ts">
import PosHeaderBar from '@/Components/Pos/PosHeaderBar.vue';
import PosCart from '@/Components/Pos/PosCart.vue';
import PosPaymentPanel from '@/Components/Pos/PosPaymentPanel.vue';
import PosSessionOverlay from '@/Components/Pos/PosSessionOverlay.vue';
import CashMovementModal from '@/Components/CashMovementModal.vue';
import SalesHistoryModal from '@/Components/SalesHistoryModal.vue';
import SessionCloseModal from '@/Components/SessionCloseModal.vue';
import { useBarcodeScanner } from '@/composables/useBarcodeScanner';
import { useCart } from '@/composables/useCart';
import { usePayments } from '@/composables/usePayments';
import { useSession } from '@/composables/useSession';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import type { Product } from '@/Stores/posTabsStore';
import { usePosTabsStore } from '@/Stores/posTabsStore';
import { Head, router, usePage } from '@inertiajs/vue3';
import { storeToRefs } from 'pinia';
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';
import { Search, X } from 'lucide-vue-next';

function csrfToken(): string {
    const token = (usePage().props.csrf_token as string) || '';
    const meta = document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]');
    if (meta && token) meta.content = token;
    return token;
}

interface TodaySale {
    id: number; folio: number; time: string; total: number;
    net_total: number; tax_total: number; discount_total: number;
    items: { name: string; quantity: number; price: number; total: number }[];
    payments: { method: string; amount: number }[];
    cash_amount: number; card_amount: number; transfer_amount: number;
    cashier_name: string; created_at: string;
}

const props = defineProps<{
    products: Product[];
    hasOpenSession: boolean;
    ultimaSesion: {
        cierre_desglose: Record<string, number> | null;
        total_efectivo_cierre: number; cerrado_por: string; cerrado_at: string;
    } | null;
    todaySales?: TodaySale[] | null;
}>();

const {
    sessionOpened, sessionOpening, sessionOpenError,
    denominations, bills, coins, billQtys, coinAmounts, coinErrors,
    desgloseAnterior, totalOpening, hasCoinErrors,
    showDiscrepancyModal, discrepancyData, discrepancyReason,
    formatCoin, onCoinInput, validateCoin, focusNext,
    submitOpenSession, confirmOpenWithDiscrepancy,
} = useSession(csrfToken, props.hasOpenSession, computed(() => props.ultimaSesion));

const scannerRef = ref<HTMLInputElement | null>(null);
const scannerInput = ref('');
const searchRef = ref<HTMLInputElement | null>(null);
const searchQuery = ref('');
const showSearchDropdown = ref(false);

function focusBarcodeInput() { scannerRef.value?.focus(); scannerRef.value?.select(); }
function focusScanner() { nextTick(() => scannerRef.value?.focus()); }

const filteredProducts = computed(() => {
    if (!searchQuery.value) return [];
    const q = searchQuery.value.toLowerCase();
    return props.products
        .filter((p) => p.name.toLowerCase().includes(q) || p.sku.toLowerCase().includes(q) || (p.barcode && p.barcode.toLowerCase().includes(q)))
        .slice(0, 10);
});

function handleBlur() { setTimeout(() => { showSearchDropdown.value = false; }, 200); }

const posTabStore = usePosTabsStore();
const { activeTab, tabs, activeIndex } = storeToRefs(posTabStore);
const { addTab, removeTab, switchTab } = posTabStore;

const cartRef = computed(() => activeTab.value.cart);
const { scannedProductIndex, addToCart, addProductByCode, incrementQty, decrementQty, removeItem } = useCart(cartRef, props.products);

useBarcodeScanner({
    onScan: (code) => addProductByCode(code, () => { scannerInput.value = ''; showSearchDropdown.value = false; focusScanner(); }),
    allowlist: [scannerRef],
    discardWhen: (el) => el instanceof HTMLInputElement && (el.getAttribute('step') === '100' || !sessionOpened.value || el.closest('.fixed') !== null),
});

const {
    couponCode, couponError, checkoutLoading, lastSaleId, lastDiscount,
    lastAppliedPromotions, showSuccess, total, remaining, balanceState,
    balanceClasses, canCheckout, onPaymentFocus, finalizeSale, resetCheckoutState,
} = usePayments(activeTab as any);

const cashAmount = computed(() => activeTab.value.payments.find((p) => p.method === 'cash')?.amount ?? null);
const cardAmount = computed(() => activeTab.value.payments.find((p) => p.method === 'card')?.amount ?? null);
const transferAmount = computed(() => activeTab.value.payments.find((p) => p.method === 'transfer')?.amount ?? null);
const totalUnits = computed(() => activeTab.value.cart.reduce((s, i) => s + i.quantity, 0));

function setPaymentAmount(method: string, v: number | null) { const p = activeTab.value.payments.find((p) => p.method === method); if (p) p.amount = v; }

watch(total, () => {
    const card = activeTab.value.payments.find((p) => p.method === 'card');
    const cash = activeTab.value.payments.find((p) => p.method === 'cash');
    const transfer = activeTab.value.payments.find((p) => p.method === 'transfer');
    if (card) card.amount = total.value > 0 ? Math.round(total.value) : null;
    if (cash) cash.amount = null;
    if (transfer) transfer.amount = null;
});

async function handleFinalizeSale() {
    if (!(await finalizeSale(csrfToken()))) return;
    activeTab.value.cart = [];
    activeTab.value.payments = [{ method: 'cash', amount: null }, { method: 'card', amount: null }, { method: 'transfer', amount: null }];
    couponCode.value = '';
    couponError.value = '';
    setTimeout(() => resetCheckoutState(), 6000);
    focusScanner();
}

function selectProduct(product: Product) { addToCart(product); searchQuery.value = ''; showSearchDropdown.value = false; }

function confirmClearCart() {
    if (activeTab.value.cart.length) {
        const lineas = activeTab.value.cart.map((i) => `${i.product.name} (SKU: ${i.product.sku}) x${i.quantity} — $${(i.product.price * i.quantity).toLocaleString('es-CL')}`);
        fetch(route('admin.pos.observacion'), {
            method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
            body: JSON.stringify({ tipo_accion: 'limpiar_carrito', detalle: `Limpió carrito (${activeTab.value.cart.length} items): ${lineas.join(' | ')}` }),
        }).catch(() => {});
    }
    activeTab.value.cart = [];
    activeTab.value.payments = [{ method: 'cash', amount: null }, { method: 'card', amount: null }, { method: 'transfer', amount: null }];
    resetCheckoutState();
    focusScanner();
}

const showCashMovementModal = ref(false);
const cashMovementType = ref<'ingreso' | 'retiro'>('ingreso');
const showCloseSessionModal = ref(false);
const showSalesHistoryModal = ref(false);
const showCartWarning = ref(false);

function openSalesHistory() {
    (router as any).reload({ only: ['todaySales'], preserveState: true, preserveScroll: true,
        onError: () => alert('Error al cargar el historial de ventas.'),
        onSuccess: () => { showSalesHistoryModal.value = true; },
    });
}

function reprintTicket(saleId: number) { window.open(route('admin.pos.reprint', saleId), '_blank', 'width=400,height=600'); }

function handleMenuAction(label: string) {
    if (label === 'Entrada de Dinero') { cashMovementType.value = 'ingreso'; showCashMovementModal.value = true; }
    else if (label === 'Salida de Dinero') { cashMovementType.value = 'retiro'; showCashMovementModal.value = true; }
    else if (label === 'Cerrar Caja') { if (activeTab.value.cart.length > 0) showCartWarning.value = true; else showCloseSessionModal.value = true; }
}

function onCashMovementSaved() { showCashMovementModal.value = false; focusScanner(); }

onMounted(() => {
    document.addEventListener('keydown', handleKeydown);
    nextTick(() => document.getElementById('input-20k')?.focus());
});
onUnmounted(() => document.removeEventListener('keydown', handleKeydown));

function handleKeydown(e: KeyboardEvent) {
    if (e.key === 'F9') { e.preventDefault(); if (canCheckout.value) handleFinalizeSale(); }
}
</script>

<template>
    <Head title="Caja Rápida" />
    <AdminLayout>
        <template #title>
            <h1 class="font-display text-xl font-bold text-content-primary dark:text-white">Caja Rápida — POS</h1>
        </template>

        <PosSessionOverlay
            v-if="!sessionOpened"
            :session-opening="sessionOpening"
            :session-open-error="sessionOpenError"
            :denominations="denominations"
            :bills="bills"
            :coins="coins"
            :bill-qtys="billQtys"
            :coin-amounts="coinAmounts"
            :coin-errors="coinErrors"
            :desglose-anterior="desgloseAnterior"
            :ultima-sesion="props.ultimaSesion"
            :total-opening="totalOpening"
            :has-coin-errors="hasCoinErrors"
            :show-discrepancy-modal="showDiscrepancyModal"
            :discrepancy-data="discrepancyData"
            :discrepancy-reason="discrepancyReason"
            @update:bill-qtys="(k, v: number | null) => billQtys[k] = v"
            @update:coin-amounts="(k, v: number | null) => coinAmounts[k] = v"
            @update:discrepancy-reason="(v: string) => discrepancyReason = v"
            @submit-open-session="submitOpenSession(focusScanner)"
            @coin-input="(k: string, e: Event) => onCoinInput(e, k)"
            @validate-coin="(k: string) => validateCoin(k)"
            @focus-next="(e: Event) => focusNext(e)"
            @confirm-discrepancy="confirmOpenWithDiscrepancy(focusScanner)"
            @cancel-discrepancy="showDiscrepancyModal = false"
        />

        <div v-if="sessionOpened">
            <PosHeaderBar
                :tabs="tabs"
                :active-index="activeIndex"
                :show-success="showSuccess"
                :last-sale-id="lastSaleId"
                :last-discount="lastDiscount"
                :last-applied-promotions="lastAppliedPromotions"
                @switch-tab="switchTab"
                @remove-tab="removeTab"
                @add-tab="addTab"
                @dismiss-success="resetCheckoutState"
            />

            <div class="flex h-[calc(100vh-10rem)] gap-4">
                <div class="flex flex-1 flex-col overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-surface-dark">
                    <div class="flex gap-3 border-b border-gray-100 px-4 py-3 dark:border-gray-800">
                        <div class="relative flex-1">
                            <input ref="scannerRef" v-model="scannerInput" type="text" autofocus placeholder="🔎 Escanea el código de barras o SKU" class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 font-mono text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                            <span v-if="scannerInput" class="absolute right-3 top-1/2 -translate-y-1/2 text-[10px] font-bold uppercase tracking-wider text-emerald-500">Escaneando...</span>
                        </div>
                        <div class="relative w-72">
                            <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-content-muted" />
                            <input ref="searchRef" v-model="searchQuery" @blur="handleBlur()" @input="showSearchDropdown = searchQuery.length > 0" @keydown.enter="filteredProducts.length && selectProduct(filteredProducts[0])" type="text" placeholder="Buscar por nombre" class="w-full rounded-2xl border border-gray-200 bg-gray-50 py-3 pl-10 pr-4 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                            <div v-if="showSearchDropdown && filteredProducts.length" class="absolute left-0 right-0 top-full z-50 mt-1 max-h-60 overflow-y-auto rounded-2xl border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-surface-dark">
                                <button v-for="p in filteredProducts" :key="p.id" @mousedown="selectProduct(p)" class="flex w-full items-center gap-2 border-b border-gray-100 px-4 py-2.5 text-left text-sm last:border-0 hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-800">
                                    <span class="min-w-0 flex-1 whitespace-normal break-words font-medium text-content-primary dark:text-white">{{ p.name }}</span>
                                    <span class="shrink-0 font-bold text-primary-500">${{ Math.round(p.price / 100).toLocaleString('es-CL', { minimumFractionDigits: 0 }) }}</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <PosCart
                        :cart="activeTab.cart"
                        :scanned-product-index="scannedProductIndex"
                        :item-count="activeTab.cart.length"
                        @increment-qty="incrementQty"
                        @decrement-qty="decrementQty"
                        @remove-item="(i: number) => removeItem(i, csrfToken())"
                        @confirm-clear-cart="confirmClearCart"
                        @focus-barcode="focusBarcodeInput"
                    />
                </div>

                <PosPaymentPanel
                    :total="total"
                    :remaining="remaining"
                    :balance-state="balanceState"
                    :balance-classes="balanceClasses"
                    :cart-length="activeTab.cart.length"
                    :total-units="totalUnits"
                    :checkout-loading="checkoutLoading"
                    :can-checkout="canCheckout"
                    :coupon-code="couponCode"
                    :coupon-error="couponError"
                    :cash-amount="cashAmount"
                    :card-amount="cardAmount"
                    :transfer-amount="transferAmount"
                    @update:coupon-code="(v: string) => couponCode = v"
                    @payment-focus="onPaymentFocus"
                    @update:cash-amount="(v: number | null) => setPaymentAmount('cash', v)"
                    @update:card-amount="(v: number | null) => setPaymentAmount('card', v)"
                    @update:transfer-amount="(v: number | null) => setPaymentAmount('transfer', v)"
                    @finalize-sale="handleFinalizeSale"
                    @open-sales-history="openSalesHistory"
                    @menu-action="handleMenuAction"
                />
            </div>
        </div>

        <SalesHistoryModal :show="showSalesHistoryModal" :sales="props.todaySales ?? null" @close="showSalesHistoryModal = false" @reprint="reprintTicket" />
        <CashMovementModal :show="showCashMovementModal" :type="cashMovementType" @close="showCashMovementModal = false; focusScanner()" @saved="onCashMovementSaved" />
        <SessionCloseModal v-if="showCloseSessionModal" @close="showCloseSessionModal = false; focusScanner()" />

        <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="showCartWarning" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4 backdrop-blur-sm" @click.self="showCartWarning = false">
                <div class="w-full max-w-sm rounded-2xl bg-white shadow-xl dark:bg-surface-dark">
                    <div class="flex items-center justify-between border-b border-gray-100 px-5 py-3 dark:border-gray-800">
                        <h3 class="font-display text-sm font-bold text-content-primary dark:text-white">Carrito con Productos</h3>
                        <button @click="showCartWarning = false" class="rounded-lg p-1 transition-colors hover:bg-gray-100 dark:hover:bg-gray-800"><X class="h-4 w-4 text-content-muted" /></button>
                    </div>
                    <div class="space-y-4 p-5">
                        <p class="text-sm leading-relaxed text-content-secondary">Termina la Venta para Cerrar Caja</p>
                        <button @click="showCartWarning = false" class="w-full rounded-xl bg-rose-500 py-2.5 text-xs font-bold text-white shadow-sm transition-colors hover:bg-rose-600">Entendido</button>
                    </div>
                </div>
            </div>
        </Transition>
    </AdminLayout>
</template>
