<script setup lang="ts">
import { ref } from 'vue';
import { Wallet, FileText, X } from 'lucide-vue-next';

interface Denomination {
    key: string;
    label: string;
    value: number;
    directInput: boolean;
}

defineProps<{
    sessionOpening: boolean;
    sessionOpenError: string;
    denominations: readonly Denomination[];
    bills: readonly Denomination[];
    coins: readonly Denomination[];
    billQtys: Record<string, number | null>;
    coinAmounts: Record<string, number | null>;
    coinErrors: Record<string, string | null>;
    desgloseAnterior: Record<string, number> | null;
    ultimaSesion: {
        cierre_desglose: Record<string, number> | null;
        total_efectivo_cierre: number;
        cerrado_por: string;
        cerrado_at: string;
    } | null;
    totalOpening: number;
    hasCoinErrors: boolean;
    showDiscrepancyModal: boolean;
    discrepancyData: {
        requiere_justificacion: boolean;
        diferencia: number;
        ultimo_cierre_monto: number;
        ultimo_cierre_desglose: Record<string, number> | null;
        nuevo_apertura_monto: number;
    } | null;
    discrepancyReason: string;
}>();

const emit = defineEmits<{
    'update:billQtys': [key: string, value: number | null];
    'update:coinAmounts': [key: string, value: number | null];
    'update:discrepancyReason': [value: string];
    'submit-open-session': [];
    'coin-input': [key: string, event: Event];
    'validate-coin': [key: string];
    'focus-next': [event: Event];
    'confirm-discrepancy': [];
    'cancel-discrepancy': [];
}>();

function fmt(v: number | undefined | null): string {
    return v != null
        ? '$' + v.toLocaleString('es-CO', { minimumFractionDigits: 0 })
        : '$0';
}

function formatCoin(val: number | null): string {
    if (val === null || val === undefined) return '';
    return val.toLocaleString('es-CL');
}

function onCoinInput(e: Event, key: string) {
    emit('update:coinAmounts', key, null);
    emit('coin-input', key, e);
}

function onCoinBlur(key: string) {
    emit('validate-coin', key);
}
</script>

<template>
    <div class="flex min-h-[60vh] items-center justify-center">
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

                <form
                    @submit.prevent="emit('submit-open-session')"
                    class="space-y-4 p-5"
                >
                    <p class="text-xs leading-relaxed text-content-muted">
                        El cajero entrante compara su conteo físico con el
                        cierre anterior (columna izquierda) y registra sus
                        cantidades (columna derecha).
                    </p>

                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                        <!-- LEFT: Previous Close (Read Only) -->
                        <div
                            v-if="desgloseAnterior"
                            class="space-y-4 opacity-75"
                        >
                            <div
                                class="flex items-center gap-2 rounded-lg bg-gray-100 px-3 py-2 dark:bg-gray-800"
                            >
                                <FileText class="h-4 w-4 text-content-muted" />
                                <span
                                    class="text-[10px] font-bold uppercase tracking-wider text-content-muted"
                                >
                                    Cierre Anterior
                                </span>
                                <span
                                    class="ml-auto text-[10px] text-content-muted"
                                >{{ ultimaSesion?.cerrado_por }} · {{ ultimaSesion?.cerrado_at }}</span>
                            </div>

                            <div
                                class="rounded-xl bg-blue-50/30 p-3 dark:bg-blue-900/5"
                            >
                                <label
                                    class="mb-2 block text-xs font-bold uppercase tracking-wider text-content-muted"
                                >Billetes</label>
                                <table class="w-full text-left">
                                    <thead>
                                        <tr
                                            class="text-[10px] font-bold uppercase tracking-wider text-content-muted"
                                        >
                                            <th class="pb-1">Denominación</th>
                                            <th class="pb-1 text-center">Cant.</th>
                                            <th class="pb-1 text-right">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody
                                        class="divide-y divide-gray-100 dark:divide-gray-800"
                                    >
                                        <tr v-for="d in bills" :key="d.key">
                                            <td
                                                class="py-1.5 text-sm font-semibold text-content-muted"
                                            >{{ d.label }}</td>
                                            <td class="py-1.5 text-center">
                                                <input
                                                    :value="desgloseAnterior[d.key] ?? 0"
                                                    disabled
                                                    class="w-16 rounded-lg border border-gray-200 bg-gray-100 px-1 py-1 text-center text-sm text-content-muted opacity-60 dark:border-gray-700 dark:bg-gray-800"
                                                />
                                            </td>
                                            <td
                                                class="py-1.5 text-right text-sm font-bold text-content-muted"
                                            >{{ fmt((desgloseAnterior[d.key] ?? 0) * d.value) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div
                                class="rounded-xl bg-amber-50/30 p-3 dark:bg-amber-900/5"
                            >
                                <label
                                    class="mb-2 block text-xs font-bold uppercase tracking-wider text-content-muted"
                                >Monedas</label>
                                <table class="w-full text-left">
                                    <thead>
                                        <tr
                                            class="text-[10px] font-bold uppercase tracking-wider text-content-muted"
                                        >
                                            <th class="pb-1">Denominación</th>
                                            <th class="pb-1 text-center">Cant.</th>
                                            <th class="pb-1 text-right">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody
                                        class="divide-y divide-gray-100 dark:divide-gray-800"
                                    >
                                        <tr v-for="d in coins" :key="d.key">
                                            <td
                                                class="py-1.5 text-sm font-semibold text-content-muted"
                                            >{{ d.label }}</td>
                                            <td class="py-1.5 text-center">
                                                <input
                                                    :value="desgloseAnterior[d.key] ?? 0"
                                                    disabled
                                                    class="w-16 rounded-lg border border-gray-200 bg-gray-100 px-1 py-1 text-center text-sm text-content-muted opacity-60 dark:border-gray-700 dark:bg-gray-800"
                                                />
                                            </td>
                                            <td
                                                class="py-1.5 text-right text-sm font-bold text-content-muted"
                                            >{{ fmt((desgloseAnterior[d.key] ?? 0) * d.value) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div
                                class="rounded-xl bg-gray-100 p-3 text-center dark:bg-gray-800"
                            >
                                <span
                                    class="text-xs font-bold uppercase tracking-wider text-content-muted"
                                >Total Cierre Anterior</span>
                                <p
                                    class="font-mono text-lg font-black text-content-muted"
                                >{{ fmt(ultimaSesion?.total_efectivo_cierre ?? 0) }}</p>
                            </div>
                        </div>

                        <!-- LEFT placeholder: no previous close -->
                        <div
                            v-else
                            class="flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-200 p-8 dark:border-gray-700"
                        >
                            <FileText
                                class="mb-2 h-8 w-8 text-content-muted opacity-40"
                            />
                            <p class="text-sm font-medium text-content-muted">
                                No hay cierre anterior
                            </p>
                            <p class="text-xs text-content-muted/60">
                                Este es el primer turno registrado
                            </p>
                        </div>

                        <!-- RIGHT: Current Opening Form -->
                        <div class="space-y-4">
                            <div
                                class="flex items-center gap-2 rounded-lg bg-primary-50 px-3 py-2 dark:bg-primary-900/20"
                            >
                                <Wallet class="h-4 w-4 text-primary-500" />
                                <span
                                    class="text-[10px] font-bold uppercase tracking-wider text-primary-600 dark:text-primary-400"
                                >
                                    Tu Apertura
                                </span>
                            </div>

                            <div
                                class="rounded-xl bg-blue-50/50 p-3 dark:bg-blue-900/10"
                            >
                                <label
                                    class="mb-2 block text-xs font-bold uppercase tracking-wider text-content-primary"
                                >Billetes</label>
                                <table class="w-full text-left">
                                    <thead>
                                        <tr
                                            class="text-[10px] font-bold uppercase tracking-wider text-content-muted"
                                        >
                                            <th class="pb-1">Denominación</th>
                                            <th class="pb-1 text-center">Cant.</th>
                                            <th class="pb-1 text-right">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody
                                        class="divide-y divide-gray-100 dark:divide-gray-800"
                                    >
                                        <tr v-for="d in bills" :key="d.key">
                                            <td
                                                class="py-1.5 text-sm font-semibold text-content-primary dark:text-white"
                                            >{{ d.label }}</td>
                                            <td class="py-1.5 text-center">
                                                <input
                                                    :value="billQtys[d.key]"
                                                    @input="emit('update:billQtys', d.key, Number(($event.target as HTMLInputElement).value) || null)"
                                                    type="number"
                                                    min="0"
                                                    :autofocus="d.key === '20k'"
                                                    :id="d.key === '20k' ? 'input-20k' : undefined"
                                                    @keydown.enter.prevent="emit('focus-next', $event)"
                                                    class="w-16 rounded-lg border border-gray-200 bg-gray-50 px-1 py-1 text-center text-sm text-content-primary transition-shadow focus:border-primary-500 focus:ring-1 focus:ring-primary-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                                />
                                            </td>
                                            <td
                                                class="py-1.5 text-right text-sm font-bold text-content-primary dark:text-white"
                                            >{{ fmt((billQtys[d.key] || 0) * d.value) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div
                                class="rounded-xl bg-amber-50/50 p-3 dark:bg-amber-900/10"
                            >
                                <label
                                    class="mb-2 block text-xs font-bold uppercase tracking-wider text-content-primary"
                                >Monedas</label>
                                <table class="w-full text-left">
                                    <thead>
                                        <tr
                                            class="text-[10px] font-bold uppercase tracking-wider text-content-muted"
                                        >
                                            <th class="pb-1">Denominación</th>
                                            <th class="pb-1 text-center">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody
                                        class="divide-y divide-gray-100 dark:divide-gray-800"
                                    >
                                        <tr v-for="d in coins" :key="d.key">
                                            <td
                                                class="py-1.5 text-sm font-semibold text-content-primary dark:text-white"
                                            >{{ d.label }}</td>
                                            <td class="py-1.5 text-center">
                                                <input
                                                    :value="formatCoin(coinAmounts[d.key])"
                                                    @input="onCoinInput($event, d.key)"
                                                    @blur="onCoinBlur(d.key)"
                                                    type="text"
                                                    inputmode="numeric"
                                                    placeholder="0"
                                                    @keydown.enter.prevent="emit('focus-next', $event)"
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
                                                >{{ coinErrors[d.key] }}</p>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div
                                class="rounded-xl bg-primary-50 p-3 text-center dark:bg-primary-900/20"
                            >
                                <span
                                    class="text-xs font-bold uppercase tracking-wider text-content-muted"
                                >Total Apertura</span>
                                <p
                                    class="font-mono text-xl font-black text-primary-600 dark:text-primary-400"
                                >{{ fmt(totalOpening) }}</p>
                            </div>
                        </div>
                    </div>

                    <p
                        v-if="sessionOpenError"
                        class="rounded-xl bg-danger/10 px-3 py-2 text-xs font-bold text-danger"
                    >{{ sessionOpenError }}</p>

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

    <!-- Discrepancy Modal -->
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
            @click.self="emit('cancel-discrepancy')"
        >
            <div
                class="w-full max-w-md rounded-2xl bg-white shadow-xl dark:bg-surface-dark"
            >
                <div
                    class="flex items-center justify-between border-b border-gray-100 px-5 py-3 dark:border-gray-800"
                >
                    <h3
                        class="font-display text-sm font-bold text-content-primary dark:text-white"
                    >
                        ⚠️ Diferencia en gaveta
                    </h3>
                    <button
                        @click="emit('cancel-discrepancy')"
                        class="rounded-lg p-1 transition-colors hover:bg-gray-100 dark:hover:bg-gray-800"
                    >
                        <X class="h-4 w-4 text-content-muted" />
                    </button>
                </div>
                <div class="space-y-4 p-5">
                    <p class="text-sm leading-relaxed text-content-secondary">
                        El monto de apertura (<strong class="text-content-primary">{{ fmt(discrepancyData?.nuevo_apertura_monto) }}</strong>) difiere del último cierre (<strong class="text-content-primary">{{ fmt(discrepancyData?.ultimo_cierre_monto) }}</strong>) por
                        <strong class="text-amber-600 dark:text-amber-400">{{ fmt(discrepancyData?.diferencia) }}</strong>.
                    </p>
                    <p class="text-xs text-content-muted">
                        Escriba una justificación para continuar:
                    </p>
                    <textarea
                        :value="discrepancyReason"
                        @input="emit('update:discrepancyReason', ($event.target as HTMLTextAreaElement).value)"
                        class="w-full resize-none rounded-xl border border-gray-200 bg-gray-50 p-3 text-sm outline-none transition-colors focus:border-amber-400 focus:ring-2 focus:ring-amber-200 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white dark:focus:border-amber-500"
                        rows="3"
                        placeholder="Ej: Se realizó un depósito bancario después del cierre..."
                    ></textarea>
                    <div class="flex gap-2">
                        <button
                            @click="emit('cancel-discrepancy')"
                            class="flex-1 rounded-xl bg-gray-200 py-2.5 text-xs font-bold text-gray-600 transition-colors hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
                        >
                            Cancelar
                        </button>
                        <button
                            @click="emit('confirm-discrepancy')"
                            :disabled="!discrepancyReason.trim()"
                            class="flex-1 rounded-xl bg-amber-500 py-2.5 text-xs font-bold text-white shadow-sm transition-colors hover:bg-amber-600 disabled:opacity-50"
                        >
                            Justificar y Abrir
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Transition>
</template>
