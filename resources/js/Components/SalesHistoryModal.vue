<script setup lang="ts">
import { Printer, Search, X } from 'lucide-vue-next';
import { computed, nextTick, ref, watch } from 'vue';

interface TodaySaleItem {
    name: string;
    quantity: number;
    price: number;
    total: number;
}

interface TodaySalePayment {
    method: string;
    amount: number;
}

interface TodaySale {
    id: number;
    folio: number;
    time: string;
    total: number;
    items: TodaySaleItem[];
    payments: TodaySalePayment[];
    cash_amount: number;
    card_amount: number;
    transfer_amount: number;
    cashier_name: string;
    created_at: string;
}

const props = defineProps<{
    show: boolean;
    sales: TodaySale[] | null;
}>();

const emit = defineEmits<{
    close: [];
    reprint: [saleId: number];
}>();

const searchQuery = ref('');
const selectedSale = ref<TodaySale | null>(null);
const searchInput = ref<HTMLInputElement | null>(null);

watch(
    () => props.show,
    (val) => {
        if (val && props.sales?.length) {
            selectedSale.value = props.sales[0];
        } else {
            selectedSale.value = null;
        }
        if (val) {
            nextTick(() => searchInput.value?.focus());
        }
    },
);

const filteredSales = computed(() => {
    if (!props.sales?.length) return [];
    if (!searchQuery.value) return props.sales;
    const q = searchQuery.value.toLowerCase();
    return props.sales.filter((s) => {
        const totalStr = '$' + (s.total / 100).toLocaleString('es-CL');
        const productsStr = s.items.map((i) => i.name.toLowerCase()).join(' ');
        return (
            String(s.folio).includes(q) ||
            totalStr.includes(q) ||
            productsStr.includes(q)
        );
    });
});

function paymentLabel(sale: TodaySale): string {
    const methods: string[] = [];
    if (sale.cash_amount > 0) methods.push('Efectivo');
    if (sale.card_amount > 0) methods.push('Tarjeta');
    if (sale.transfer_amount > 0) methods.push('Transferencia');
    return methods.join(' + ') || '—';
}

function paymentBadgeClass(sale: TodaySale): string {
    if (
        sale.card_amount > 0 &&
        sale.cash_amount === 0 &&
        sale.transfer_amount === 0
    )
        return 'bg-blue-100 text-blue-800';
    if (
        sale.cash_amount > 0 &&
        sale.card_amount === 0 &&
        sale.transfer_amount === 0
    )
        return 'bg-emerald-100 text-emerald-800';
    if (
        sale.transfer_amount > 0 &&
        sale.cash_amount === 0 &&
        sale.card_amount === 0
    )
        return 'bg-purple-100 text-purple-800';
    return 'bg-amber-100 text-amber-800';
}

const subtotalNeto = computed(() => {
    if (!selectedSale.value) return 0;
    return Math.round(selectedSale.value.total / 1.19);
});

const iva = computed(() => {
    if (!selectedSale.value) return 0;
    return selectedSale.value.total - subtotalNeto.value;
});

function fmtPesos(cents: number): string {
    return '$' + (cents / 100).toLocaleString('es-CL');
}

function formatDate(iso: string): string {
    const d = new Date(iso);
    return d.toLocaleDateString('es-CL', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        hour12: false,
    });
}

function handleClose() {
    searchQuery.value = '';
    selectedSale.value = null;
    emit('close');
}
</script>

<template>
    <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
            v-if="show"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm"
            @click.self="handleClose"
        >
            <div
                class="flex w-full max-w-4xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-surface-dark"
                style="max-height: 85vh"
            >
                <!-- Header -->
                <div
                    class="flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-gray-800"
                >
                    <h2
                        class="text-base font-bold text-content-primary dark:text-white"
                    >
                        Historial de Ventas del Día
                    </h2>
                    <button
                        @click="handleClose"
                        class="rounded-lg p-1 transition-colors hover:bg-gray-100 dark:hover:bg-gray-800"
                    >
                        <X class="h-5 w-5 text-content-muted" />
                    </button>
                </div>

                <div class="flex flex-1 overflow-hidden">
                    <!-- LEFT: 45% List -->
                    <div
                        class="flex w-[60%] flex-col border-r border-gray-100 dark:border-gray-800"
                    >
                        <!-- Search -->
                        <div
                            class="relative border-b border-gray-100 p-4 dark:border-gray-800"
                        >
                            <Search
                                class="pointer-events-none absolute left-7 top-1/2 h-4 w-4 -translate-y-1/2 text-content-muted"
                            />
                            <input
                                v-model="searchQuery"
                                placeholder="Buscar por Folio, Total o Producto..."
                                ref="searchInput"
                                class="w-full rounded-xl border border-gray-200 bg-gray-50 py-2.5 pl-10 pr-4 text-sm outline-none transition-shadow focus:border-primary-400 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:focus:border-primary-500"
                            />
                        </div>

                        <!-- List -->
                        <div class="flex-1 space-y-1 overflow-y-auto p-2">
                            <div
                                v-if="!sales"
                                class="p-8 text-center text-sm text-content-muted"
                            >
                                Cargando...
                            </div>
                            <div
                                v-else-if="sales.length === 0"
                                class="p-8 text-center text-sm text-content-muted"
                            >
                                No hay ventas registradas hoy
                            </div>
                            <div
                                v-else-if="filteredSales.length === 0"
                                class="p-8 text-center text-sm text-content-muted"
                            >
                                Sin resultados para "{{ searchQuery }}"
                            </div>

                            <button
                                v-for="sale in filteredSales"
                                :key="sale.id"
                                @click="selectedSale = sale"
                                class="w-full rounded-xl p-3 text-left transition-all"
                                :class="
                                    selectedSale?.id === sale.id
                                        ? 'border-l-4 border-fuchsia-500 bg-fuchsia-50 ring-1 ring-fuchsia-200 dark:border-fuchsia-400 dark:bg-fuchsia-900/20'
                                        : 'border-l-4 border-transparent hover:bg-gray-50 dark:hover:bg-gray-800/50'
                                "
                            >
                                <!-- Row 1: Folio + Time -->
                                <div class="flex items-center justify-between">
                                    <span
                                        class="text-sm font-bold text-content-primary dark:text-white"
                                    >
                                        #{{ sale.folio }}
                                    </span>
                                    <span
                                        class="text-xs tabular-nums text-content-muted"
                                        >{{ sale.time }}</span
                                    >
                                </div>

                                <!-- Row 2: Product preview -->
                                <p
                                    class="mt-1 truncate text-[11px] text-gray-400 dark:text-gray-500"
                                >
                                    {{
                                        sale.items.map((i) => i.name).join(', ')
                                    }}
                                </p>

                                <!-- Row 3: Payment badge + Total -->
                                <div
                                    class="mt-2 flex items-center justify-between"
                                >
                                    <span
                                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-bold"
                                        :class="paymentBadgeClass(sale)"
                                    >
                                        {{ paymentLabel(sale) }}
                                    </span>
                                    <span
                                        class="text-sm font-bold tabular-nums text-content-primary dark:text-white"
                                    >
                                        {{ fmtPesos(sale.total) }}
                                    </span>
                                </div>
                            </button>
                        </div>
                    </div>

                    <!-- RIGHT: 55% Ticket Preview -->
                    <div
                        class="flex w-[40%] flex-col items-center overflow-y-auto bg-gray-50 px-3 py-8 dark:bg-gray-900/50"
                    >
                        <div
                            v-if="selectedSale"
                            class="flex flex-col items-center gap-4"
                        >
                            <div
                                id="thermal-ticket"
                                class="w-[320px] shrink-0 rounded-xl bg-white p-6 shadow-lg ring-1 ring-gray-200 dark:bg-gray-800 dark:ring-gray-700"
                            >
                                <!-- Company Header -->
                                <div class="text-center">
                                    <img
                                        src="/images/logo.png"
                                        alt="Monasterio Market"
                                        class="mx-auto mb-2 h-12 w-auto object-contain"
                                    />
                                    <p
                                        class="text-xs font-bold uppercase tracking-wider text-content-primary dark:text-white"
                                    >
                                        Monasterios Market Spa
                                    </p>
                                    <p
                                        class="mt-0.5 text-[10px] text-content-muted"
                                    >
                                        RUT: 76.367.537-0
                                    </p>
                                    <p
                                        class="mt-0.5 text-[10px] leading-tight text-content-muted"
                                    >
                                        Venta al por menor de alimentos,
                                        accesorios de teléfono y ventas por
                                        internet
                                    </p>
                                    <p class="text-[10px] text-content-muted">
                                        Código SII: 472101
                                    </p>
                                    <p
                                        class="mt-0.5 text-[10px] text-content-muted"
                                    >
                                        Santiago, Av. Manuel Antonio Matta 833,
                                        Local 7
                                    </p>
                                </div>

                                <div
                                    class="my-3 border-t border-dashed border-gray-300 dark:border-gray-600"
                                ></div>

                                <!-- Sale Metadata -->
                                <div class="space-y-0.5 text-[11px]">
                                    <p class="flex justify-between">
                                        <span class="text-content-muted"
                                            >Folio:</span
                                        >
                                        <span
                                            class="font-bold text-content-primary dark:text-white"
                                            >#{{ selectedSale.folio }}</span
                                        >
                                    </p>
                                    <p class="flex justify-between">
                                        <span class="text-content-muted"
                                            >Fecha:</span
                                        >
                                        <span
                                            class="text-content-primary dark:text-white"
                                            >{{
                                                formatDate(
                                                    selectedSale.created_at,
                                                )
                                            }}</span
                                        >
                                    </p>
                                    <p class="flex justify-between">
                                        <span class="text-content-muted"
                                            >Cajero:</span
                                        >
                                        <span
                                            class="text-content-primary dark:text-white"
                                            >{{
                                                selectedSale.cashier_name
                                            }}</span
                                        >
                                    </p>
                                </div>

                                <div
                                    class="my-3 border-t border-dashed border-gray-300 dark:border-gray-600"
                                ></div>

                                <!-- Items Table -->
                                <table class="w-full text-[11px]">
                                    <thead>
                                        <tr class="text-content-muted">
                                            <th
                                                class="pb-1 text-left font-bold"
                                            >
                                                Cant
                                            </th>
                                            <th
                                                class="pb-1 text-left font-bold"
                                            >
                                                Descripción
                                            </th>
                                            <th
                                                class="pb-1 text-right font-bold"
                                            >
                                                Importe
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="(
                                                item, i
                                            ) in selectedSale.items"
                                            :key="i"
                                            class="align-top"
                                        >
                                            <td
                                                class="py-0.5 font-mono tabular-nums text-content-secondary"
                                            >
                                                {{ item.quantity }}
                                            </td>
                                            <td
                                                class="py-0.5 text-content-primary dark:text-white"
                                            >
                                                {{ item.name }}
                                            </td>
                                            <td
                                                class="py-0.5 text-right font-mono tabular-nums text-content-primary dark:text-white"
                                            >
                                                {{ fmtPesos(item.total) }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>

                                <div
                                    class="my-3 border-t border-dashed border-gray-300 dark:border-gray-600"
                                ></div>

                                <!-- Totals -->
                                <div class="space-y-1 text-[12px]">
                                    <div
                                        class="flex justify-between text-content-secondary"
                                    >
                                        <span>Subtotal Neto</span>
                                        <span class="font-mono">{{
                                            fmtPesos(subtotalNeto)
                                        }}</span>
                                    </div>
                                    <div
                                        class="flex justify-between text-content-secondary"
                                    >
                                        <span>IVA (19%)</span>
                                        <span class="font-mono">{{
                                            fmtPesos(iva)
                                        }}</span>
                                    </div>
                                    <div
                                        class="mt-2 flex justify-between font-bold text-content-primary dark:text-white"
                                    >
                                        <span>Total</span>
                                        <span class="font-mono text-base">{{
                                            fmtPesos(selectedSale.total)
                                        }}</span>
                                    </div>
                                    <div
                                        class="flex justify-between text-content-secondary"
                                    >
                                        <span>Método de Pago</span>
                                        <span class="font-medium">{{
                                            paymentLabel(selectedSale)
                                        }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Reprint Button -->
                            <button
                                @click="emit('reprint', selectedSale.id)"
                                class="flex w-[320px] items-center justify-center gap-2 rounded-2xl bg-emerald-600 py-3 text-sm font-bold text-white shadow-sm transition-colors hover:bg-emerald-700"
                            >
                                <Printer class="h-5 w-5" />
                                Reimprimir Ticket
                            </button>
                        </div>

                        <!-- Empty State Right -->
                        <div
                            v-else
                            class="flex w-full flex-1 items-center justify-center text-sm text-content-muted"
                        >
                            Selecciona una venta para ver el ticket
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Transition>
</template>

<style>
@media print {
    body * {
        visibility: hidden;
    }
    #thermal-ticket,
    #thermal-ticket * {
        visibility: visible;
    }
    #thermal-ticket {
        position: fixed;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
        width: 80mm;
        padding: 0;
        margin: 0;
        box-shadow: none;
        border: none;
        background: white;
    }
    #thermal-ticket .border-dashed {
        border-color: #999;
    }
}
</style>
