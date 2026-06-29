<script setup lang="ts">
import {
    ArrowDownLeft,
    ArrowUpRight,
    Banknote,
    Check,
    Clock,
    CreditCard,
    FileText,
    Landmark,
    Menu,
    ShoppingCart,
} from 'lucide-vue-next';
import { onMounted, onUnmounted, ref } from 'vue';

const props = defineProps<{
    total: number;
    rawTotal: number;
    promoDiscount: number;
    previewPromosLoading: boolean;
    remaining: number;
    balanceState: string | null;
    balanceClasses: string;
    cartLength: number;
    totalUnits: number;
    checkoutLoading: boolean;
    canCheckout: boolean;
    couponCode: string;
    couponError: string;
    cashAmount: number | null;
    cardAmount: number | null;
    transferAmount: number | null;
}>();

const emit = defineEmits<{
    'update:couponCode': [value: string];
    'payment-focus': [method: string];
    'update:cashAmount': [value: number | null];
    'update:cardAmount': [value: number | null];
    'update:transferAmount': [value: number | null];
    'finalize-sale': [];
    'open-sales-history': [];
    'menu-action': [label: string];
}>();

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

const paymentMethods = [
    {
        key: 'cash' as const,
        label: 'Efectivo',
        icon: Banknote,
        color: 'text-emerald-500',
    },
    {
        key: 'card' as const,
        label: 'Tarjeta',
        icon: CreditCard,
        color: 'text-amber-500',
    },
    {
        key: 'transfer' as const,
        label: 'Transferencia',
        icon: Landmark,
        color: 'text-content-muted',
    },
];

const menuOptions = [
    { label: 'Entrada de Dinero', icon: ArrowDownLeft },
    { label: 'Salida de Dinero', icon: ArrowUpRight },
    { label: 'Cerrar Caja', icon: FileText },
];

const showMenu = ref(false);
const menuRef = ref<HTMLElement | null>(null);

function handleClickOutside(e: MouseEvent) {
    if (menuRef.value && !menuRef.value.contains(e.target as Node)) {
        showMenu.value = false;
    }
}

onMounted(() => document.addEventListener('click', handleClickOutside));
onUnmounted(() => document.removeEventListener('click', handleClickOutside));

function formatCLP(value: number): string {
    return (
        '$' +
        Math.round(value).toLocaleString('es-CL', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        })
    );
}

function amountValue(key: string): number | null {
    if (key === 'cash') return props.cashAmount;
    if (key === 'card') return props.cardAmount;
    if (key === 'transfer') return props.transferAmount;
    return null;
}

function emitAmount(key: string, val: string) {
    const num = val === '' ? null : Number(val);
    if (key === 'cash') emit('update:cashAmount', num);
    else if (key === 'card') emit('update:cardAmount', num);
    else if (key === 'transfer') emit('update:transferAmount', num);
}
</script>

<template>
    <div
        class="flex w-96 flex-shrink-0 flex-col rounded-3xl border border-gray-100 bg-slate-50 p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900/50"
    >
        <div class="relative mb-2 flex items-center justify-between">
            <h3
                class="text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
            >
                Resumen
            </h3>
            <div class="flex items-center gap-1">
                <button
                    @click="emit('open-sales-history')"
                    class="flex items-center gap-1.5 rounded-xl bg-blue-50 px-3 py-1.5 text-xs font-medium text-blue-600 transition-colors hover:bg-blue-100 dark:bg-blue-900/30 dark:text-blue-400 dark:hover:bg-blue-900/50"
                    title="Historial de Ventas"
                >
                    <Clock class="h-3.5 w-3.5" />
                    <span>Historial</span>
                </button>
                <button
                    @click.stop="showMenu = !showMenu"
                    class="flex h-7 w-7 items-center justify-center rounded-xl bg-primary-50 text-primary-500 transition-colors hover:bg-primary-100 dark:bg-primary-900/30 dark:text-primary-400 dark:hover:bg-primary-900/50"
                    title="Acciones"
                >
                    <Menu class="h-4 w-4" />
                </button>
            </div>

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
                            emit('menu-action', opt.label);
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

        <div class="mb-4">
            <p
                class="mb-1 text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
            >
                Total a Pagar
            </p>
            <p
                class="text-4xl font-black tracking-tight text-content-primary dark:text-white"
            >
                {{ formatCLP(total) }}
            </p>
            <div
                v-if="previewPromosLoading"
                class="mt-1 text-xs italic text-content-muted"
            >
                Calculando descuentos...
            </div>
            <div v-else-if="promoDiscount > 0" class="mt-1 space-y-0.5">
                <p class="text-sm text-content-muted line-through">
                    {{ formatCLP(rawTotal) }}
                </p>
                <p class="text-sm font-semibold text-emerald-600">
                    −{{ formatCLP(promoDiscount / 100) }} descuento promocional
                </p>
            </div>
        </div>

        <div class="mb-4 flex items-center gap-2 text-sm text-content-muted">
            <ShoppingCart class="h-4 w-4" />
            <span>{{ totalUnits }} unidades / {{ cartLength }} productos</span>
        </div>

        <hr class="mb-3 border-gray-200 dark:border-gray-700" />

        <h4
            class="mb-2 text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
        >
            Métodos de Pago
        </h4>

        <div class="space-y-2">
            <div
                v-for="pm in paymentMethods"
                :key="pm.key"
                class="flex items-center gap-2"
            >
                <div
                    class="flex w-36 items-center gap-2 text-content-primary dark:text-white"
                >
                    <component :is="pm.icon" :class="['h-5 w-5', pm.color]" />
                    <span class="text-sm font-medium">{{ pm.label }}</span>
                </div>
                <div class="relative flex-[2]">
                    <span
                        class="absolute left-2.5 top-1/2 -translate-y-1/2 text-xs font-bold text-content-muted"
                        >$</span
                    >
                    <input
                        :value="amountValue(pm.key) ?? ''"
                        @input="
                            emitAmount(
                                pm.key,
                                ($event.target as HTMLInputElement).value,
                            )
                        "
                        @focus="emit('payment-focus', pm.key)"
                        type="number"
                        min="0"
                        step="100"
                        class="w-full rounded-xl border border-gray-200 bg-white py-2 pl-7 pr-3 text-right text-sm font-bold text-content-primary [appearance:textfield] dark:border-gray-700 dark:bg-gray-800 dark:text-white [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none"
                    />
                </div>
            </div>
        </div>

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
                    formatCLP(remaining)
                }}</span>
            </template>
            <template v-else>
                <span>Vuelto:</span>
                <span class="ml-1 text-xl font-bold">{{
                    formatCLP(Math.abs(remaining))
                }}</span>
            </template>
        </div>

        <div class="mt-3 space-y-1">
            <div class="flex items-center gap-2">
                <input
                    :value="couponCode"
                    @input="
                        emit(
                            'update:couponCode',
                            ($event.target as HTMLInputElement).value,
                        )
                    "
                    type="text"
                    placeholder="Código de cupón"
                    class="flex-1 rounded-xl border border-gray-200 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800"
                    :disabled="checkoutLoading"
                />
            </div>
            <div v-if="couponError" class="text-xs font-medium text-red-500">
                {{ couponError }}
            </div>
        </div>

        <button
            @click="emit('finalize-sale')"
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
</template>
