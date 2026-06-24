<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import { Check, Download, FileText, X } from 'lucide-vue-next';
import { computed, nextTick, reactive, ref, watch } from 'vue';

const emit = defineEmits<{
    close: [];
}>();

// ─── Denominaciones ────────────────────────────────────────────
const bills = [
    { key: '20k', label: '$20.000', value: 20000 },
    { key: '10k', label: '$10.000', value: 10000 },
    { key: '5k', label: '$5.000', value: 5000 },
    { key: '2k', label: '$2.000', value: 2000 },
    { key: '1k', label: '$1.000', value: 1000 },
] as const;

const coins = [
    { key: '500', label: '$500', value: 500 },
    { key: '100', label: '$100', value: 100 },
    { key: '50', label: '$50', value: 50 },
    { key: '10', label: '$10', value: 10 },
] as const;

// ─── Estado reactivo ──────────────────────────────────────────
// Billetes: el cajero ingresa CANTIDAD de piezas
const billQtys = reactive<Record<string, number | null>>({
    '20k': null,
    '10k': null,
    '5k': null,
    '2k': null,
    '1k': null,
});

// Monedas: el cajero ingresa el MONTO TOTAL acumulado en pesos (NO la cantidad de piezas)
const coinAmounts = reactive<Record<string, number | null>>({
    '500': null,
    '100': null,
    '50': null,
    '10': null,
});

const coinErrors = reactive<Record<string, string | null>>({});

const redCompra = ref<number | null>(null);
const transferencia = ref<number | null>(null);
const loading = ref(false);
const error = ref('');

const step = ref<'close' | 'done'>('close');
const result = ref<{
    session: any;
    summary: any;
    pdf_url: string;
} | null>(null);

const fmt = (v: number) =>
    '$' + v.toLocaleString('es-CO', { minimumFractionDigits: 0 });

function csrfToken(): string {
    const token = (usePage().props.csrf_token as string) || '';
    const meta = document.querySelector<HTMLMetaElement>(
        'meta[name="csrf-token"]',
    );
    if (meta && token) meta.content = token;
    return token;
}

// ─── Computed properties ──────────────────────────────────────
const subtotalBilletes = computed(() =>
    bills.reduce((s, d) => s + (billQtys[d.key] || 0) * d.value, 0),
);

const totalMonedas = computed(() =>
    coins.reduce((s, d) => s + (coinAmounts[d.key] || 0), 0),
);

const totalEfectivo = computed(
    () => subtotalBilletes.value + totalMonedas.value,
);

const hasCoinErrors = computed(() =>
    Object.values(coinErrors).some((v) => v !== null),
);

// ─── Funciones ────────────────────────────────────────────────
function subtotalBill(key: string): number {
    return (
        (billQtys[key] || 0) * (bills.find((b) => b.key === key)?.value || 0)
    );
}

function resetForm() {
    for (const k of Object.keys(billQtys)) billQtys[k] = null;
    for (const k of Object.keys(coinAmounts)) coinAmounts[k] = null;
    for (const k of Object.keys(coinErrors)) coinErrors[k] = null;
    redCompra.value = null;
    transferencia.value = null;
    error.value = '';
}

function formatCoin(val: number | null): string {
    if (val === null || val === undefined) return '';
    return val.toLocaleString('es-CL');
}

function onCoinInput(e: Event, key: string) {
    coinErrors[key] = null;
    const raw = (e.target as HTMLInputElement).value.replace(/\D/g, '');
    const num = raw ? parseInt(raw, 10) : null;
    coinAmounts[key] = num;
    (e.target as HTMLInputElement).value = num
        ? num.toLocaleString('es-CL')
        : '';
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

function onMontoInput(e: Event, field: 'redCompra' | 'transferencia') {
    const raw = (e.target as HTMLInputElement).value.replace(/\D/g, '');
    const num = raw ? parseInt(raw, 10) : null;
    if (field === 'redCompra') redCompra.value = num;
    else transferencia.value = num;
    (e.target as HTMLInputElement).value = num
        ? num.toLocaleString('es-CL')
        : '';
}

async function submit() {
    if (totalEfectivo.value <= 0) {
        error.value = 'Debes ingresar al menos un valor en billetes o monedas.';
        return;
    }

    loading.value = true;
    error.value = '';

    try {
        const body: Record<string, any> = {
            total_red_compra: redCompra.value ?? 0,
            total_transferencia: transferencia.value ?? 0,
        };
        for (const d of bills)
            body[`cant_${d.key}_cierre`] = billQtys[d.key] || 0;
        for (const d of coins) body[`coin_${d.key}`] = coinAmounts[d.key] || 0;

        const res = await fetch(route('admin.pos.close-session'), {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
            },
            body: JSON.stringify(body),
        });

        if (!res.ok) {
            let msg = `Error del servidor (${res.status})`;
            try {
                const errData = await res.json();
                msg = errData.error || errData.message || msg;
            } catch {}
            throw new Error(msg);
        }

        const data = await res.json();
        localStorage.removeItem('pos_session_opened');
        router.visit(
            route('admin.pos.close-summary', { cashSession: data.session.id }),
        );
    } catch (err: any) {
        error.value = err.message || 'Error inesperado al cerrar caja';
    } finally {
        loading.value = false;
    }
}

function downloadPdf() {
    if (result.value?.pdf_url) {
        window.open(result.value.pdf_url, '_blank');
    }
}

function handleClose() {
    resetForm();
    step.value = 'close';
    result.value = null;
    emit('close');
}

function formatDate(iso: string) {
    if (!iso) return '';
    const d = new Date(iso);
    return d.toLocaleDateString('es-CL', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

function focusNext(e: Event) {
    const form = (e.target as HTMLElement).closest('form');
    if (!form) return;
    const inputs = Array.from(
        form.querySelectorAll<HTMLInputElement>(
            'input[type="number"], input[inputmode="numeric"]',
        ),
    );
    const idx = inputs.indexOf(e.target as HTMLInputElement);
    if (idx >= 0 && idx < inputs.length - 1) inputs[idx + 1].focus();
}

watch(
    step,
    (newStep) => {
        if (newStep === 'close') {
            nextTick(() =>
                document.getElementById('input-20k-cerrar')?.focus(),
            );
        }
    },
    { immediate: true },
);
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
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4 backdrop-blur-sm"
            @click.self="handleClose"
        >
            <div
                class="w-full max-w-2xl rounded-2xl bg-white shadow-xl dark:bg-surface-dark"
            >
                <!-- Header -->
                <div
                    class="flex items-center justify-between border-b border-gray-100 px-5 py-3 dark:border-gray-800"
                >
                    <div class="flex items-center gap-2">
                        <FileText class="h-5 w-5 text-primary-500" />
                        <h3
                            class="font-display text-sm font-bold text-content-primary dark:text-white"
                        >
                            {{
                                step === 'close'
                                    ? 'Cierre de Caja'
                                    : 'Resumen de Cierre de Turno'
                            }}
                        </h3>
                    </div>
                    <button
                        @click="handleClose"
                        class="rounded-lg p-1 transition-colors hover:bg-gray-100 dark:hover:bg-gray-800"
                    >
                        <X class="h-4 w-4 text-content-muted" />
                    </button>
                </div>

                <!-- Close Form -->
                <template v-if="step === 'close'">
                    <form @submit.prevent="submit" class="space-y-4 p-5">
                        <p class="text-xs leading-relaxed text-content-muted">
                            Registra el <strong>conteo físico</strong> de
                            billetes/monedas y los montos de otros medios de
                            pago.
                        </p>

                        <!-- Two-column denomination grid -->
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <!-- Left: Bills -->
                            <div
                                class="rounded-xl bg-blue-50/50 p-3 dark:bg-blue-900/10"
                            >
                                <label
                                    class="mb-2 block text-xs font-bold uppercase tracking-wider text-content-primary"
                                >
                                    Billetes
                                </label>
                                <table class="w-full text-left">
                                    <thead>
                                        <tr
                                            class="text-[10px] font-bold uppercase tracking-wider text-content-muted"
                                        >
                                            <th class="pb-1">Denominación</th>
                                            <th class="pb-1 text-center">
                                                Cant.
                                            </th>
                                            <th class="pb-1 text-right">
                                                Subtotal
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody
                                        class="divide-y divide-gray-100 dark:divide-gray-800"
                                    >
                                        <tr v-for="d in bills" :key="d.key">
                                            <td
                                                class="py-1.5 text-sm font-semibold text-content-primary dark:text-white"
                                            >
                                                {{ d.label }}
                                            </td>
                                            <td class="py-1.5 text-center">
                                                <input
                                                    v-model.number="
                                                        billQtys[d.key]
                                                    "
                                                    type="number"
                                                    min="0"
                                                    :autofocus="d.key === '20k'"
                                                    :id="
                                                        d.key === '20k'
                                                            ? 'input-20k-cerrar'
                                                            : undefined
                                                    "
                                                    @keydown.enter.prevent="
                                                        focusNext
                                                    "
                                                    class="w-16 rounded-lg border border-gray-200 bg-gray-50 px-1 py-1 text-center text-sm text-content-primary transition-shadow focus:border-primary-500 focus:ring-1 focus:ring-primary-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                                />
                                            </td>
                                            <td
                                                class="py-1.5 text-right text-sm font-bold text-content-primary dark:text-white"
                                            >
                                                {{ fmt(subtotalBill(d.key)) }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Right: Coins (monto directo digitado en columna Subtotal) -->
                            <div
                                class="rounded-xl bg-amber-50/50 p-3 dark:bg-amber-900/10"
                            >
                                <label
                                    class="mb-2 block text-xs font-bold uppercase tracking-wider text-content-primary"
                                >
                                    Monedas
                                </label>
                                <table class="w-full text-left">
                                    <thead>
                                        <tr
                                            class="text-[10px] font-bold uppercase tracking-wider text-content-muted"
                                        >
                                            <th class="pb-1">Denominación</th>
                                            <th class="pb-1 text-center"></th>
                                            <th class="pb-1 text-center">
                                                Subtotal
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody
                                        class="divide-y divide-gray-100 dark:divide-gray-800"
                                    >
                                        <tr v-for="d in coins" :key="d.key">
                                            <td
                                                class="py-1.5 text-sm font-semibold text-content-primary dark:text-white"
                                            >
                                                {{ d.label }}
                                            </td>
                                            <td class="py-1.5"></td>
                                            <td class="py-1.5 text-center">
                                                <input
                                                    :value="
                                                        formatCoin(
                                                            coinAmounts[d.key],
                                                        )
                                                    "
                                                    @input="
                                                        onCoinInput(
                                                            $event,
                                                            d.key,
                                                        )
                                                    "
                                                    @blur="validateCoin(d.key)"
                                                    type="text"
                                                    inputmode="numeric"
                                                    placeholder="0"
                                                    @keydown.enter.prevent="
                                                        focusNext
                                                    "
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
                        </div>

                        <!-- Total Efectivo -->
                        <div
                            class="rounded-xl bg-primary-50 p-3 text-center dark:bg-primary-900/20"
                        >
                            <span
                                class="text-xs font-bold uppercase tracking-wider text-content-muted"
                                >Total Efectivo</span
                            >
                            <p
                                class="font-mono text-xl font-black text-primary-600 dark:text-primary-400"
                            >
                                {{ fmt(totalEfectivo) }}
                            </p>
                        </div>

                        <!-- Digital payments -->
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label
                                    class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted"
                                    >Redcompra</label
                                >
                                <input
                                    :value="formatCoin(redCompra)"
                                    type="text"
                                    inputmode="numeric"
                                    placeholder="0"
                                    @input="onMontoInput($event, 'redCompra')"
                                    @keydown.enter.prevent="focusNext"
                                    class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm text-content-primary transition-shadow focus:border-primary-500 focus:ring-1 focus:ring-primary-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                />
                            </div>
                            <div>
                                <label
                                    class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted"
                                    >Transferencias</label
                                >
                                <input
                                    :value="formatCoin(transferencia)"
                                    type="text"
                                    inputmode="numeric"
                                    placeholder="0"
                                    @input="
                                        onMontoInput($event, 'transferencia')
                                    "
                                    @keydown.enter.prevent="focusNext"
                                    class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm text-content-primary transition-shadow focus:border-primary-500 focus:ring-1 focus:ring-primary-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                />
                            </div>
                        </div>

                        <!-- Error -->
                        <p
                            v-if="error"
                            class="rounded-xl bg-danger/10 px-3 py-2 text-xs font-bold text-danger"
                        >
                            {{ error }}
                        </p>

                        <!-- Actions -->
                        <div class="flex gap-2">
                            <button
                                type="button"
                                @click="handleClose"
                                class="flex-1 rounded-xl border border-gray-200 py-2.5 text-xs font-bold text-content-secondary transition-colors hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800"
                            >
                                Cancelar
                            </button>
                            <button
                                type="submit"
                                :disabled="
                                    loading ||
                                    totalEfectivo <= 0 ||
                                    hasCoinErrors
                                "
                                class="flex flex-1 items-center justify-center gap-1.5 rounded-xl bg-primary-600 py-2.5 text-xs font-bold text-white shadow-sm transition-colors hover:bg-primary-700 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <template v-if="loading">
                                    <span
                                        class="inline-block h-3.5 w-3.5 animate-spin rounded-full border-2 border-white/30 border-t-white"
                                    ></span>
                                    Cerrando...
                                </template>
                                <template v-else>
                                    <Check class="h-4 w-4" />
                                    Cerrar Caja
                                </template>
                            </button>
                        </div>
                    </form>
                </template>

                <!-- Post-close Summary -->
                <template v-else-if="result">
                    <div class="space-y-4 p-5">
                        <div
                            class="rounded-xl bg-emerald-50 p-4 text-center dark:bg-emerald-900/20"
                        >
                            <Check class="mx-auto h-8 w-8 text-emerald-500" />
                            <p
                                class="mt-1 text-sm font-bold text-emerald-700 dark:text-emerald-300"
                            >
                                Caja cerrada exitosamente
                            </p>
                        </div>

                        <div class="space-y-2 text-sm">
                            <div
                                class="flex justify-between border-b border-gray-100 pb-1 dark:border-gray-800"
                            >
                                <span class="text-content-muted">Sesión #</span>
                                <span
                                    class="font-bold text-content-primary dark:text-white"
                                    >{{ result.session.id }}</span
                                >
                            </div>
                            <div
                                class="flex justify-between border-b border-gray-100 pb-1 dark:border-gray-800"
                            >
                                <span class="text-content-muted">Cierre</span>
                                <span
                                    class="font-bold text-content-primary dark:text-white"
                                    >{{
                                        formatDate(result.session.closed_at)
                                    }}</span
                                >
                            </div>
                            <div
                                class="flex justify-between border-b border-gray-100 pb-1 dark:border-gray-800"
                            >
                                <span class="text-content-muted"
                                    >Monto Apertura</span
                                >
                                <span
                                    class="font-bold text-content-primary dark:text-white"
                                    >{{
                                        fmt(result.session.opening_balance)
                                    }}</span
                                >
                            </div>
                            <div
                                class="flex justify-between border-b border-gray-100 pb-1 dark:border-gray-800"
                            >
                                <span class="text-content-muted"
                                    >+ Ventas Efectivo</span
                                >
                                <span
                                    class="font-bold text-emerald-600 dark:text-emerald-400"
                                    >{{ fmt(result.summary.cashSales) }}</span
                                >
                            </div>
                            <div
                                class="flex justify-between border-b border-gray-100 pb-1 dark:border-gray-800"
                            >
                                <span class="text-content-muted"
                                    >+ Ingresos</span
                                >
                                <span
                                    class="font-bold text-emerald-600 dark:text-emerald-400"
                                    >{{ fmt(result.summary.ingresos) }}</span
                                >
                            </div>
                            <div
                                class="flex justify-between border-b border-gray-100 pb-1 dark:border-gray-800"
                            >
                                <span class="text-content-muted"
                                    >- Retiros</span
                                >
                                <span
                                    class="font-bold text-orange-600 dark:text-orange-400"
                                    >{{ fmt(result.summary.retiros) }}</span
                                >
                            </div>
                            <div
                                class="flex justify-between border-b border-gray-100 pb-1 font-bold dark:border-gray-800"
                            >
                                <span class="text-content-muted"
                                    >Esperado en Caja</span
                                >
                                <span
                                    class="text-primary-600 dark:text-primary-400"
                                    >{{ fmt(result.summary.esperado) }}</span
                                >
                            </div>
                            <div
                                class="flex justify-between border-b border-gray-100 pb-1 dark:border-gray-800"
                            >
                                <span class="text-content-muted"
                                    >Efectivo Declarado</span
                                >
                                <span
                                    class="font-bold text-content-primary dark:text-white"
                                    >{{ fmt(result.summary.declarado) }}</span
                                >
                            </div>
                            <div
                                class="flex justify-between border-b border-gray-100 pb-1 dark:border-gray-800"
                            >
                                <span class="text-content-muted"
                                    >Redcompra Declarado</span
                                >
                                <span
                                    class="font-bold text-content-primary dark:text-white"
                                    >{{
                                        fmt(result.session.total_red_compra)
                                    }}</span
                                >
                            </div>
                            <div
                                class="flex justify-between border-b border-gray-100 pb-1 dark:border-gray-800"
                            >
                                <span class="text-content-muted"
                                    >Transferencia Declarada</span
                                >
                                <span
                                    class="font-bold text-content-primary dark:text-white"
                                    >{{
                                        fmt(result.session.total_transferencia)
                                    }}</span
                                >
                            </div>

                            <div
                                class="flex justify-between text-base font-black"
                                :class="
                                    result.summary.diferencia < 0
                                        ? 'text-red-600'
                                        : result.summary.diferencia > 0
                                          ? 'text-emerald-600'
                                          : 'text-content-muted'
                                "
                            >
                                <span>Diferencia Total</span>
                                <span>
                                    <template
                                        v-if="result.summary.diferencia < 0"
                                    >
                                        Faltante:
                                        {{
                                            fmt(
                                                Math.abs(
                                                    result.summary.diferencia,
                                                ),
                                            )
                                        }}
                                    </template>
                                    <template
                                        v-else-if="
                                            result.summary.diferencia > 0
                                        "
                                    >
                                        Sobrante: +{{
                                            fmt(result.summary.diferencia)
                                        }}
                                    </template>
                                    <template v-else> $0 (Cuadrado) </template>
                                </span>
                            </div>
                        </div>

                        <button
                            @click="downloadPdf"
                            class="flex w-full items-center justify-center gap-2 rounded-xl bg-primary-600 py-3 text-sm font-bold text-white shadow-sm transition-colors hover:bg-primary-700"
                        >
                            <Download class="h-4 w-4" />
                            Descargar Comprobante PDF
                        </button>

                        <button
                            @click="handleClose"
                            class="w-full rounded-xl border border-gray-200 py-2.5 text-xs font-bold text-content-secondary transition-colors hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800"
                        >
                            Cerrar
                        </button>
                    </div>
                </template>
            </div>
        </div>
    </Transition>
</template>
