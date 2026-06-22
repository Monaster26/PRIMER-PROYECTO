<script setup lang="ts">
import CashMovementModal from '@/Components/CashMovementModal.vue';
import SessionCloseModal from '@/Components/SessionCloseModal.vue';
import { useBarcodeScanner } from '@/composables/useBarcodeScanner';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import type { Product } from '@/Stores/posTabsStore';
import { usePosTabsStore } from '@/Stores/posTabsStore';
import { Head, usePage } from '@inertiajs/vue3';
import {
    AlertTriangle,
    ArrowDownLeft,
    ArrowUpRight,
    Banknote,
    Check,
    CreditCard,
    FileText,
    Landmark,
    Menu,
    Minus,
    Plus,
    Search,
    ShoppingCart,
    Trash2,
    Wallet,
    X,
} from 'lucide-vue-next';
import { storeToRefs } from 'pinia';
import { computed, nextTick, onMounted, onUnmounted, reactive, ref, watch } from 'vue';

function csrfToken(): string {
    // ponytail: Inertia-shared token from server, always current; meta tag can be stale after login
    const token = (usePage().props.csrf_token as string) || '';
    const meta = document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]');
    if (meta && token) meta.content = token;
    return token;
}

const props = defineProps<{
    products: Product[];
    hasOpenSession: boolean;
    ultimaSesion: {
        cierre_desglose: Record<string, number> | null;
        total_efectivo_cierre: number;
        cerrado_por: string;
        cerrado_at: string;
    } | null;
}>();

const scannerInput = ref('');
const scannerRef = ref<HTMLInputElement | null>(null);
const searchRef = ref<HTMLInputElement | null>(null);
const searchQuery = ref('');
const showSearchDropdown = ref(false);
const searchFocused = ref(false);
const checkoutLoading = ref(false);
const lastSaleId = ref<number | null>(null);

// Apertura de caja obligatoria — persiste en localStorage (no en sessionStorage) para
// sobrevivir cierres accidentales de pestaña/navegador. Se limpia solo al cerrar caja o logout.
const storedOpen = localStorage.getItem('pos_session_opened');
if (storedOpen === 'true' && !props.hasOpenSession) {
    // La sesión fue cerrada externamente (cierre de caja, otro cajero, etc.)
    localStorage.removeItem('pos_session_opened');
}
const sessionOpened = ref(localStorage.getItem('pos_session_opened') === 'true' || props.hasOpenSession);
const sessionOpening = ref(false);
const sessionOpenError = ref('');

const denominations = [
    { key: '20k', label: '$20.000', value: 20000, directInput: false },
    { key: '10k', label: '$10.000', value: 10000, directInput: false },
    { key: '5k', label: '$5.000', value: 5000, directInput: false },
    { key: '2k', label: '$2.000', value: 2000, directInput: false },
    { key: '1k', label: '$1.000', value: 1000, directInput: false },
    { key: '500', label: '$500', value: 500, directInput: true },
    { key: '100', label: '$100', value: 100, directInput: true },
    { key: '50', label: '$50', value: 50, directInput: true },
    { key: '10', label: '$10', value: 10, directInput: true },
] as const;

const bills = denominations.filter((d) => !d.directInput);
const coins = denominations.filter((d) => d.directInput);

const billQtys = reactive<Record<string, number | null>>({
    '20k': null, '10k': null, '5k': null, '2k': null, '1k': null,
});

const coinAmounts = reactive<Record<string, number | null>>({
    '500': null, '100': null, '50': null, '10': null,
});

const coinErrors = reactive<Record<string, string | null>>({});

const desgloseAnterior = computed(() =>
    discrepancyData.value?.ultimo_cierre_desglose ?? props.ultimaSesion?.cierre_desglose ?? null,
);

const totalOpening = computed(() => {
    let t = 0;
    for (const d of denominations) {
        if (d.directInput) {
            t += Number(coinAmounts[d.key] || 0);
        } else {
            t += (Number(billQtys[d.key]) || 0) * d.value;
        }
    }
    return t;
});
const showSuccess = ref(false);
const hasCoinErrors = computed(() =>
    Object.values(coinErrors).some((v) => v !== null),
);
const scannedProductName = ref<string | null>(null);
const scannedProductIndex = ref<number | null>(null);

// Cash In / Out
const showCashMovementModal = ref(false);
const cashMovementType = ref<'ingreso' | 'retiro'>('ingreso');
const showCloseSessionModal = ref(false);
const showCartWarning = ref(false);
const showClearCartModal = ref(false)
const showDiscrepancyModal = ref(false)
const discrepancyData = ref<{ requiere_justificacion: boolean; diferencia: number; ultimo_cierre_monto: number; ultimo_cierre_desglose: Record<string, number> | null; nuevo_apertura_monto: number } | null>(null)
const discrepancyReason = ref('');
const cancelClearBtnRef = ref<HTMLButtonElement | null>(null);

watch(showClearCartModal, (val) => {
    if (val) nextTick(() => cancelClearBtnRef.value?.focus());
});

// Dropdown menu acciones administrativas
const showMenu = ref(false);
const menuRef = ref<HTMLElement | null>(null);
const menuOptions = [
    {
        label: 'Entrada de Dinero',
        action: openCashIngreso,
        icon: ArrowDownLeft,
    },
    { label: 'Salida de Dinero', action: openCashRetiro, icon: ArrowUpRight },
    { label: 'Cerrar Caja', action: openCloseSession, icon: FileText },
];

function toggleMenu() {
    showMenu.value = !showMenu.value;
}

function handleClickOutside(e: MouseEvent) {
    if (menuRef.value && !menuRef.value.contains(e.target as Node)) {
        showMenu.value = false;
    }
}

function handleKeydown(e: KeyboardEvent) {
    if (e.key === 'F9') {
        e.preventDefault();
        if (canCheckout.value) {
            finalizeSale();
        }
    }
}

const canCheckout = computed(
    () =>
        !checkoutLoading.value &&
        activeTab.value.cart.length > 0 &&
        remaining.value <= 0,
);

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
    document.addEventListener('keydown', handleKeydown);
    nextTick(() => document.getElementById('input-20k')?.focus());
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
    document.removeEventListener('keydown', handleKeydown);
});

const posTabStore = usePosTabsStore();
const { activeTab, tabs, activeIndex } = storeToRefs(posTabStore);
const { addTab, removeTab, switchTab } = posTabStore;

// ─── Escáner global: captura ráfagas del lector de código de barras ──
useBarcodeScanner({
    onScan: addProductByCode,
    allowlist: [scannerRef], // ponytail: searchRef no incluido — Enter en buscador usa filteredProducts, no barcode/SKU
    discardWhen: (el) =>
        el instanceof HTMLInputElement &&
        (el.getAttribute('step') === '100' || !sessionOpened.value
         || el.closest('.fixed') !== null),
});

const total = computed(() =>
    activeTab.value.cart.reduce(
        (sum, item) => sum + (item.product.price / 100) * item.quantity,
        0,
    ),
);

const totalPayments = computed(() =>
    activeTab.value.payments.reduce(
        (sum, p) => sum + (Number(p.amount) || 0),
        0,
    ),
);

const remaining = computed(() => total.value - totalPayments.value);
const isBalanced = computed(
    () => Math.abs(remaining.value) < 0.01 && total.value > 0,
);

type BalanceState = 'exacto' | 'faltante' | 'exceso';

const balanceState = computed<BalanceState | null>(() => {
    if (total.value <= 0) return null;
    if (isBalanced.value) return 'exacto';
    if (remaining.value > 0) return 'faltante';
    return 'exceso';
});

const balanceClasses = computed(() => {
    switch (balanceState.value) {
        case 'exacto':
            return 'bg-success/10 text-emerald-700 font-bold';
        case 'faltante':
            return 'bg-red-50 border border-red-200 text-red-700 font-medium';
        case 'exceso':
            return 'bg-blue-50 border border-blue-200 text-blue-800 font-bold';
        default:
            return '';
    }
});

const methodIcons: Record<string, any> = {
    cash: Banknote,
    card: CreditCard,
    transfer: Landmark,
};

const methodColors: Record<string, string> = {
    cash: 'text-emerald-500',
    card: 'text-amber-500',
    transfer: 'text-content-muted',
};

const methodLabels: Record<string, string> = {
    cash: 'Efectivo',
    card: 'Tarjeta',
    transfer: 'Transferencia',
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

    // 2. Buscar en productos precargados (props) — primero por barcode/SKU exacto
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

    // 2b. Si no fue código de barras/SKU, buscar por nombre entre productos precargados
    const nameMatch = props.products.find(
        (p) => p.name.toLowerCase() === trimmed.toLowerCase(),
    );
    if (nameMatch) {
        const existingIdx = activeTab.value.cart.findIndex(
            (item) => item.product.id === nameMatch.id,
        );
        if (existingIdx !== -1) {
            activeTab.value.cart[existingIdx].quantity++;
            flashScanFeedback(nameMatch.name, existingIdx);
        } else {
            activeTab.value.cart.push({ product: nameMatch, quantity: 1 });
            flashScanFeedback(nameMatch.name, activeTab.value.cart.length - 1);
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
    const existing = activeTab.value.cart.find(
        (item) => item.product.id === product.id,
    );
    if (existing) {
        existing.quantity++;
    } else {
        activeTab.value.cart.push({ product, quantity: 1 });
    }
    searchQuery.value = '';
    showSearchDropdown.value = false;
}

function incrementQty(index: number) {
    if (
        activeTab.value.cart[index].quantity <
        activeTab.value.cart[index].product.stock
    ) {
        activeTab.value.cart[index].quantity++;
    }
}

function decrementQty(index: number) {
    if (activeTab.value.cart[index].quantity > 1) {
        activeTab.value.cart[index].quantity--;
    }
}

function removeItem(index: number) {
    const item = activeTab.value.cart[index];
    if (item) {
        fetch(route('admin.pos.observacion'), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
            body: JSON.stringify({
                tipo_accion: 'eliminar_item',
                producto_afectado: item.product.name,
                detalle: `${item.product.name} (SKU: ${item.product.sku}) x${item.quantity} — $${(item.product.price * item.quantity).toLocaleString('es-CL')}`,
            }),
        }).catch(() => {});
    }
    activeTab.value.cart.splice(index, 1);
}

function selectProduct(product: Product) {
    addToCart(product);
}

function clearCart() {
    showClearCartModal.value = true;
}

function confirmClearCart() {
    if (activeTab.value.cart.length) {
        const lineas = activeTab.value.cart.map(i =>
            `${i.product.name} (SKU: ${i.product.sku}) x${i.quantity} — $${(i.product.price * i.quantity).toLocaleString('es-CL')}`
        );
        fetch(route('admin.pos.observacion'), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
            body: JSON.stringify({
                tipo_accion: 'limpiar_carrito',
                detalle: `Limpió carrito (${activeTab.value.cart.length} items): ${lineas.join(' | ')}`,
            }),
        }).catch(() => {});
    }
    activeTab.value.cart = [];
    activeTab.value.payments = [
        { method: 'cash', amount: null },
        { method: 'card', amount: null },
        { method: 'transfer', amount: null },
    ];
    lastSaleId.value = null;
    showSuccess.value = false;
    showClearCartModal.value = false;
    focusScanner();
}

function cancelClearCart() {
    showClearCartModal.value = false;
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
    if (remaining.value > 0) {
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
            Accept: 'application/json',
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

function openCashIngreso() {
    cashMovementType.value = 'ingreso';
    showCashMovementModal.value = true;
}

function openCashRetiro() {
    cashMovementType.value = 'retiro';
    showCashMovementModal.value = true;
}

function openCloseSession() {
    if (activeTab.value.cart.length > 0) {
        showMenu.value = false;
        showCartWarning.value = true;
        return;
    }
    showCloseSessionModal.value = true;
    showMenu.value = false;
}

function closeCartWarning() {
    showCartWarning.value = false;
    focusScanner();
}

function onCashMovementSaved() {
    showCashMovementModal.value = false;
    focusScanner();
}

function submitOpenSession() {
    sessionOpening.value = true;
    sessionOpenError.value = '';

    const body: Record<string, any> = {};
    for (const d of denominations) {
        if (d.directInput) {
            // ponytail: coin amounts are divided by denomination to get quantity, rounding may slightly alter exact values
            const amount = Number(coinAmounts[d.key]) || 0;
            body[`cant_${d.key}_apertura`] = Math.round(amount / d.value);
        } else {
            body[`cant_${d.key}_apertura`] = Number(billQtys[d.key]) || 0;
        }
    }

    fetch(route('admin.pos.open-session'), {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
        },
        body: JSON.stringify(body),
    })
        .then((res) => {
            if (!res.ok) return res.json().then((err) => {
                if (err.requiere_justificacion) {
                    discrepancyData.value = err;
                    showDiscrepancyModal.value = true;
                    throw new Error;
                }
                throw new Error(err.message || 'Error al abrir caja');
            });
            return res.json();
        })
        .then(() => {
            sessionOpened.value = true;
            localStorage.setItem('pos_session_opened', 'true');
            nextTick(() => focusScanner());
        })
        .catch((err) => {
            if (err.message) sessionOpenError.value = err.message;
        })
        .finally(() => {
            sessionOpening.value = false;
        });
}

function confirmOpenWithDiscrepancy() {
    sessionOpening.value = true;
    const body: Record<string, any> = {};
    for (const d of denominations) {
        if (d.directInput) {
            body[`cant_${d.key}_apertura`] = Math.round((Number(coinAmounts[d.key]) || 0) / d.value);
        } else {
            body[`cant_${d.key}_apertura`] = Number(billQtys[d.key]) || 0;
        }
    }
    body.observacion = discrepancyReason.value;

    fetch(route('admin.pos.open-session'), {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
        },
        body: JSON.stringify(body),
    })
        .then((res) => {
            if (!res.ok) return res.json().then((err) => { throw new Error(err.message || 'Error al abrir caja'); });
            return res.json();
        })
        .then(() => {
            showDiscrepancyModal.value = false;
            sessionOpened.value = true;
            localStorage.setItem('pos_session_opened', 'true');
            nextTick(() => focusScanner());
        })
        .catch((err) => {
            sessionOpenError.value = err.message;
        })
        .finally(() => {
            sessionOpening.value = false;
        });
}

const fmt = (v: number | undefined | null) =>
    v != null ? '$' + v.toLocaleString('es-CO', { minimumFractionDigits: 0 }) : '$0';

function focusNext(e: Event) {
    const form = (e.target as HTMLElement).closest('form');
    if (!form) return;
    const inputs = Array.from(form.querySelectorAll<HTMLInputElement>(
        'input[type="number"], input[inputmode="numeric"]'
    ));
    const idx = inputs.indexOf(e.target as HTMLInputElement);
    if (idx >= 0 && idx < inputs.length - 1) inputs[idx + 1].focus();
}
const fmtDec = (v: number) =>
    '$' +
    Number(v).toLocaleString('es-CO', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    });

function formatCoin(val: number | null): string {
    if (val === null || val === undefined) return '';
    return val.toLocaleString('es-CL');
}

function onCoinInput(e: Event, key: string) {
    coinErrors[key] = null;
    const raw = (e.target as HTMLInputElement).value.replace(/\D/g, '');
    const num = raw ? parseInt(raw, 10) : null;
    coinAmounts[key] = num;
    (e.target as HTMLInputElement).value = num ? num.toLocaleString('es-CL') : '';
}

function validateCoin(key: string) {
    const d = coins.find((c) => c.key === key);
    if (!d) return;
    const val = coinAmounts[key];
    if (val !== null && val > 0 && val % d.value !== 0) {
        coinErrors[key] = `Debe ser múltiplo de ${d.label}`;
    } else {
        coinErrors[key] = null;
    }
}
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

        <!-- Opening overlay (bloqueante) -->
        <div
            v-if="!sessionOpened"
            class="flex min-h-[60vh] items-center justify-center"
        >
            <div class="w-full max-w-5xl">
                <div
                    class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-surface-dark"
                >
                    <div
                        class="flex items-center gap-2 border-b border-gray-100 px-5 py-3 dark:border-gray-800"
                    >
                        <Wallet class="h-5 w-5 text-primary-500" />
                        <h2
                            class="font-display text-sm font-bold text-content-primary dark:text-white"
                        >
                            Apertura de Caja
                        </h2>
                    </div>

                    <form @submit.prevent="submitOpenSession" class="space-y-4 p-5">
                        <p class="text-xs leading-relaxed text-content-muted">
                            El cajero entrante compara su conteo físico con el cierre anterior (columna izquierda)
                            y registra sus cantidades (columna derecha).
                        </p>

                        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                            <!-- ═══ COLUMNA IZQUIERDA: Cierre Anterior (Solo Lectura) ═══ -->
                            <div v-if="desgloseAnterior" class="space-y-4 opacity-75">
                                <div class="flex items-center gap-2 rounded-lg bg-gray-100 px-3 py-2 dark:bg-gray-800">
                                    <FileText class="h-4 w-4 text-content-muted" />
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-content-muted">
                                        Cierre Anterior
                                    </span>
                                    <span class="ml-auto text-[10px] text-content-muted">{{ props.ultimaSesion?.cerrado_por }} · {{ props.ultimaSesion?.cerrado_at }}</span>
                                </div>

                                <!-- Bills -->
                                <div class="rounded-xl bg-blue-50/30 p-3 dark:bg-blue-900/5">
                                    <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-content-muted">Billetes</label>
                                    <table class="w-full text-left">
                                        <thead>
                                            <tr class="text-[10px] font-bold uppercase tracking-wider text-content-muted">
                                                <th class="pb-1">Denominación</th>
                                                <th class="pb-1 text-center">Cant.</th>
                                                <th class="pb-1 text-right">Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                            <tr v-for="d in bills" :key="d.key">
                                                <td class="py-1.5 text-sm font-semibold text-content-muted">{{ d.label }}</td>
                                                <td class="py-1.5 text-center">
                                                    <input :value="desgloseAnterior[d.key] ?? 0" disabled
                                                        class="w-16 rounded-lg border border-gray-200 bg-gray-100 px-1 py-1 text-center text-sm text-content-muted opacity-60 dark:border-gray-700 dark:bg-gray-800"
                                                    />
                                                </td>
                                                <td class="py-1.5 text-right text-sm font-bold text-content-muted">
                                                    {{ fmt((desgloseAnterior[d.key] ?? 0) * d.value) }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Coins -->
                                <div class="rounded-xl bg-amber-50/30 p-3 dark:bg-amber-900/5">
                                    <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-content-muted">Monedas</label>
                                    <table class="w-full text-left">
                                        <thead>
                                            <tr class="text-[10px] font-bold uppercase tracking-wider text-content-muted">
                                                <th class="pb-1">Denominación</th>
                                                <th class="pb-1 text-center">Cant.</th>
                                                <th class="pb-1 text-right">Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                            <tr v-for="d in coins" :key="d.key">
                                                <td class="py-1.5 text-sm font-semibold text-content-muted">{{ d.label }}</td>
                                                <td class="py-1.5 text-center">
                                                    <input :value="desgloseAnterior[d.key] ?? 0" disabled
                                                        class="w-16 rounded-lg border border-gray-200 bg-gray-100 px-1 py-1 text-center text-sm text-content-muted opacity-60 dark:border-gray-700 dark:bg-gray-800"
                                                    />
                                                </td>
                                                <td class="py-1.5 text-right text-sm font-bold text-content-muted">
                                                    {{ fmt((desgloseAnterior[d.key] ?? 0) * d.value) }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Total Cierre Anterior -->
                                <div class="rounded-xl bg-gray-100 p-3 text-center dark:bg-gray-800">
                                    <span class="text-xs font-bold uppercase tracking-wider text-content-muted">Total Cierre Anterior</span>
                                    <p class="font-mono text-lg font-black text-content-muted">
                                        {{ fmt(props.ultimaSesion?.total_efectivo_cierre ?? 0) }}
                                    </p>
                                </div>
                            </div>

                            <!-- ═══ Sin cierre anterior: placeholder informativo ═══ -->
                            <div v-else class="flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-200 p-8 dark:border-gray-700">
                                <FileText class="mb-2 h-8 w-8 text-content-muted opacity-40" />
                                <p class="text-sm font-medium text-content-muted">No hay cierre anterior</p>
                                <p class="text-xs text-content-muted/60">Este es el primer turno registrado</p>
                            </div>

                            <!-- ═══ COLUMNA DERECHA: Apertura Actual (Formulario Activo) ═══ -->
                            <div class="space-y-4">
                                <div class="flex items-center gap-2 rounded-lg bg-primary-50 px-3 py-2 dark:bg-primary-900/20">
                                    <Wallet class="h-4 w-4 text-primary-500" />
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-primary-600 dark:text-primary-400">
                                        Tu Apertura
                                    </span>
                                </div>

                                <!-- Bills -->
                                <div class="rounded-xl bg-blue-50/50 p-3 dark:bg-blue-900/10">
                                    <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-content-primary">Billetes</label>
                                    <table class="w-full text-left">
                                        <thead>
                                            <tr class="text-[10px] font-bold uppercase tracking-wider text-content-muted">
                                                <th class="pb-1">Denominación</th>
                                                <th class="pb-1 text-center">Cant.</th>
                                                <th class="pb-1 text-right">Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                            <tr v-for="d in bills" :key="d.key">
                                                <td class="py-1.5 text-sm font-semibold text-content-primary dark:text-white">{{ d.label }}</td>
                                                <td class="py-1.5 text-center">
                                                    <input
                                                        v-model.number="billQtys[d.key]"
                                                        type="number"
                                                        min="0"
                                                        :autofocus="d.key === '20k'"
                                                        :id="d.key === '20k' ? 'input-20k' : undefined"
                                                        @keydown.enter.prevent="focusNext"
                                                        class="w-16 rounded-lg border border-gray-200 bg-gray-50 px-1 py-1 text-center text-sm text-content-primary transition-shadow focus:border-primary-500 focus:ring-1 focus:ring-primary-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                                    />
                                                </td>
                                                <td class="py-1.5 text-right text-sm font-bold text-content-primary dark:text-white">
                                                    {{ fmt((billQtys[d.key] || 0) * d.value) }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Coins -->
                                <div class="rounded-xl bg-amber-50/50 p-3 dark:bg-amber-900/10">
                                    <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-content-primary">Monedas</label>
                                    <table class="w-full text-left">
                                        <thead>
                                            <tr class="text-[10px] font-bold uppercase tracking-wider text-content-muted">
                                                <th class="pb-1">Denominación</th>
                                                <th class="pb-1 text-center">Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                            <tr v-for="d in coins" :key="d.key">
                                                <td class="py-1.5 text-sm font-semibold text-content-primary dark:text-white">{{ d.label }}</td>
                                                <td class="py-1.5 text-center">
                                                    <input
                                                        :value="formatCoin(coinAmounts[d.key])"
                                                        @input="onCoinInput($event, d.key)"
                                                        @blur="validateCoin(d.key)"
                                                        type="text"
                                                        inputmode="numeric"
                                                        placeholder="0"
                                                        @keydown.enter.prevent="focusNext"
                                                        :class="[
                                                            'w-28 rounded-lg px-2 py-1.5 text-center text-sm transition-shadow',
                                                            coinErrors[d.key]
                                                                ? 'border-red-500 bg-red-50 focus:ring-red-500/30'
                                                                : 'border-gray-200 bg-gray-50 focus:border-primary-500 focus:ring-primary-500/30',
                                                            'dark:bg-gray-900 dark:text-white',
                                                        ]"
                                                    />
                                                    <p
                                                        v-if="coinErrors[d.key]"
                                                        class="mt-1 text-[10px] font-medium text-red-500"
                                                    >
                                                        {{ coinErrors[d.key] }}
                                                    </p>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Total Apertura -->
                                <div class="rounded-xl bg-primary-50 p-3 text-center dark:bg-primary-900/20">
                                    <span class="text-xs font-bold uppercase tracking-wider text-content-muted">Total Apertura</span>
                                    <p class="font-mono text-xl font-black text-primary-600 dark:text-primary-400">
                                        {{ fmt(totalOpening) }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <p
                            v-if="sessionOpenError"
                            class="rounded-xl bg-danger/10 px-3 py-2 text-xs font-bold text-danger"
                        >
                            {{ sessionOpenError }}
                        </p>

                        <button
                            type="submit"
                            :disabled="sessionOpening || hasCoinErrors"
                            class="flex w-full items-center justify-center gap-2 rounded-xl bg-primary-500 py-3 text-sm font-bold text-white shadow-sm transition-colors hover:bg-primary-600 disabled:opacity-50"
                        >
                            <template v-if="sessionOpening">
                                <span
                                    class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-white/30 border-t-white"
                                ></span>
                                Abriendo caja...
                            </template>
                            <template v-else>
                                <Wallet class="h-4 w-4" />
                                Abrir Caja
                            </template>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- POS content (visible solo si caja abierta) -->
        <div v-if="sessionOpened">
        <!-- Tab Bar -->
        <div class="mb-4 flex items-center gap-2 overflow-x-auto">
            <button
                v-for="(tab, i) in tabs"
                :key="tab.id"
                @click="switchTab(i)"
                class="flex items-center gap-2 whitespace-nowrap rounded-full px-4 py-1.5 text-sm font-bold transition-colors"
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
                             @keydown.enter="
                                 if (filteredProducts.length) {
                                     selectProduct(filteredProducts[0]);
                                 }
                             "
                             type="text"
                             placeholder="Buscar por nombre"
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
                                class="flex w-full items-center gap-2 border-b border-gray-100 px-4 py-2.5 text-left text-sm last:border-0 hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-800"
                            >
                                <span
                                    class="min-w-0 flex-1 whitespace-normal break-words font-medium text-content-primary dark:text-white"
                                    >{{ p.name }}</span
                                >
                                <span
                                    class="shrink-0 font-bold text-primary-500"
                                    >{{ fmt(p.price / 100) }}</span
                                >
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
                <div class="relative mb-2 flex items-center justify-between">
                    <h3
                        class="text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                    >
                        Resumen
                    </h3>
                    <button
                        @click.stop="toggleMenu"
                        class="flex h-7 w-7 items-center justify-center rounded-xl bg-primary-50 text-primary-500 transition-colors hover:bg-primary-100 dark:bg-primary-900/30 dark:text-primary-400 dark:hover:bg-primary-900/50"
                        title="Acciones"
                    >
                        <Menu class="h-4 w-4" />
                    </button>

                    <Transition
                        enter-active-class="transition duration-150 ease-out"
                        enter-from-class="scale-95 opacity-0"
                        enter-to-class="scale-100 opacity-100"
                        leave-active-class="transition duration-100 ease-in"
                        leave-from-class="scale-100 opacity-100"
                        leave-to-class="scale-95 opacity-0"
                    >
                        <div
                            v-if="showMenu"
                            ref="menuRef"
                            class="absolute right-0 top-full z-50 mt-1 w-56 origin-top-right overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-xl dark:border-gray-700 dark:bg-surface-dark"
                        >
                            <button
                                v-for="opt in menuOptions"
                                :key="opt.label"
                                @click="
                                    opt.action();
                                    showMenu = false;
                                "
                                class="flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm font-medium text-content-primary transition-colors hover:bg-gray-50 dark:text-white dark:hover:bg-gray-800"
                            >
                                <component
                                    :is="opt.icon"
                                    :class="[
                                        'h-4 w-4 shrink-0',
                                        opt.label === 'Entrada de Dinero'
                                            ? 'text-emerald-500'
                                            : opt.label === 'Salida de Dinero'
                                                ? 'text-orange-500'
                                                : 'text-primary-500',
                                    ]"
                                />
                                {{ opt.label }}
                            </button>
                        </div>
                    </Transition>
                </div>

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
                        >{{
                            activeTab.cart.reduce((s, i) => s + i.quantity, 0)
                        }}
                        unidades / {{ activeTab.cart.length }} productos</span
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
                        <div
                            class="flex w-36 items-center gap-2 text-content-primary dark:text-white"
                        >
                            <component
                                :is="methodIcons[payment.method]"
                                :class="[
                                    'h-5 w-5',
                                    methodColors[payment.method],
                                ]"
                            />
                            <span class="text-sm font-medium">{{
                                methodLabels[payment.method]
                            }}</span>
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
                    v-if="balanceState"
                    :class="[
                        'mt-4 rounded-lg p-3 text-center transition-all',
                        balanceClasses,
                    ]"
                >
                    <template v-if="balanceState === 'exacto'">
                        <span>✓ Pago Exacto</span>
                    </template>
                    <template v-else-if="balanceState === 'faltante'">
                        <span class="font-medium">Faltan</span>
                        <span class="ml-1 text-xl font-bold">{{
                            fmtDec(remaining)
                        }}</span>
                    </template>
                    <template v-else>
                        <span>Vuelto:</span>
                        <span class="ml-1 text-xl font-bold">{{
                            fmtDec(Math.abs(remaining))
                        }}</span>
                    </template>
                </div>

                <!-- Checkout Button -->
                <button
                    @click="finalizeSale"
                    :disabled="!canCheckout"
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
                        <span class="ml-1 opacity-60">(F9)</span>
                    </template>
                </button>
            </div>
        </div>
        </div>

        <CashMovementModal
            :show="showCashMovementModal"
            :type="cashMovementType"
            @close="
                showCashMovementModal = false;
                focusScanner();
            "
            @saved="onCashMovementSaved"
        />

        <SessionCloseModal
            v-if="showCloseSessionModal"
            @close="
                showCloseSessionModal = false;
                focusScanner();
            "
        />

        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="showCartWarning"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4 backdrop-blur-sm"
                @click.self="closeCartWarning"
            >
                <div class="w-full max-w-sm rounded-2xl bg-white shadow-xl dark:bg-surface-dark">
                    <div class="flex items-center justify-between border-b border-gray-100 px-5 py-3 dark:border-gray-800">
                        <h3 class="font-display text-sm font-bold text-content-primary dark:text-white">
                            Carrito con Productos
                        </h3>
                        <button @click="closeCartWarning"
                            class="rounded-lg p-1 transition-colors hover:bg-gray-100 dark:hover:bg-gray-800">
                            <X class="h-4 w-4 text-content-muted" />
                        </button>
                    </div>
                    <div class="space-y-4 p-5">
                        <p class="text-sm text-content-secondary leading-relaxed">
                            Termina la Venta para Cerrar Caja
                        </p>
                        <button @click="closeCartWarning"
                            class="w-full rounded-xl bg-rose-500 py-2.5 text-xs font-bold text-white shadow-sm transition-colors hover:bg-rose-600">
                            Entendido
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
                v-if="showClearCartModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4 backdrop-blur-sm"
                @click.self="cancelClearCart"
            >
                <div class="w-full max-w-sm rounded-2xl bg-white shadow-xl dark:bg-surface-dark">
                    <div class="flex items-center justify-between border-b border-gray-100 px-5 py-3 dark:border-gray-800">
                        <h3 class="font-display text-sm font-bold text-content-primary dark:text-white">
                            Limpiar Carrito
                        </h3>
                        <button @click="cancelClearCart"
                            class="rounded-lg p-1 transition-colors hover:bg-gray-100 dark:hover:bg-gray-800">
                            <X class="h-4 w-4 text-content-muted" />
                        </button>
                    </div>
                    <div class="space-y-4 p-5">
                        <p class="text-sm text-content-secondary leading-relaxed">
                            ¿Está seguro que desea eliminar la venta?
                        </p>
                        <div class="flex gap-2">
                            <button ref="cancelClearBtnRef" @click="cancelClearCart"
                                class="flex-1 rounded-xl bg-rose-500 py-2.5 text-xs font-bold text-white shadow-sm transition-colors hover:bg-rose-600">
                                Cancelar
                            </button>
                            <button @click="confirmClearCart"
                                class="flex-1 rounded-xl border border-gray-200 bg-white py-2.5 text-xs font-bold text-content-secondary transition-colors hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:hover:bg-gray-700">
                                Aceptar
                            </button>
                        </div>
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
                v-if="showDiscrepancyModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4 backdrop-blur-sm"
                @click.self="showDiscrepancyModal = false"
            >
                <div class="w-full max-w-md rounded-2xl bg-white shadow-xl dark:bg-surface-dark">
                    <div class="flex items-center justify-between border-b border-gray-100 px-5 py-3 dark:border-gray-800">
                        <h3 class="font-display text-sm font-bold text-content-primary dark:text-white">
                            ⚠️ Diferencia en gaveta
                        </h3>
                        <button @click="showDiscrepancyModal = false"
                            class="rounded-lg p-1 transition-colors hover:bg-gray-100 dark:hover:bg-gray-800">
                            <X class="h-4 w-4 text-content-muted" />
                        </button>
                    </div>
                    <div class="space-y-4 p-5">
                        <p class="text-sm text-content-secondary leading-relaxed">
                            El monto de apertura (<strong class="text-content-primary">{{ fmt(discrepancyData?.nuevo_apertura_monto) }}</strong>)
                            difiere del último cierre (<strong class="text-content-primary">{{ fmt(discrepancyData?.ultimo_cierre_monto) }}</strong>)
                            por <strong class="text-amber-600 dark:text-amber-400">{{ fmt(discrepancyData?.diferencia) }}</strong>.
                        </p>
                        <p class="text-xs text-content-muted">Escriba una justificación para continuar:</p>
                        <textarea v-model="discrepancyReason"
                            class="w-full resize-none rounded-xl border border-gray-200 bg-gray-50 p-3 text-sm outline-none transition-colors focus:border-amber-400 focus:ring-2 focus:ring-amber-200 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white dark:focus:border-amber-500"
                            rows="3" placeholder="Ej: Se realizó un depósito bancario después del cierre..."></textarea>
                        <div class="flex gap-2">
                            <button @click="showDiscrepancyModal = false"
                                class="flex-1 rounded-xl bg-gray-200 py-2.5 text-xs font-bold text-gray-600 transition-colors hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                                Cancelar
                            </button>
                            <button @click="confirmOpenWithDiscrepancy" :disabled="!discrepancyReason.trim()"
                                class="flex-1 rounded-xl bg-amber-500 py-2.5 text-xs font-bold text-white shadow-sm transition-colors hover:bg-amber-600 disabled:opacity-50">
                                Justificar y Abrir
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </AdminLayout>
</template>
