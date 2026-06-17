<script setup lang="ts">
import { formatDate, formatTime } from '@/helpers/format';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Check, EyeOff, Plus, Trash2, Wallet, X } from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';

const props = defineProps<{
    sessions: {
        data: any[];
        links: any[];
        current_page: number;
        last_page: number;
        prev_page_url: string | null;
        next_page_url: string | null;
    };
    cashiers: { id: number; name: string }[];
    cashMovements?: {
        total_ingresos: number;
        total_retiros: number;
        count: number;
    };
}>();

const denominations = [
    { key: '20k', label: '$20.000', value: 20000 },
    { key: '10k', label: '$10.000', value: 10000 },
    { key: '5k', label: '$5.000', value: 5000 },
    { key: '2k', label: '$2.000', value: 2000 },
    { key: '1k', label: '$1.000', value: 1000 },
    { key: '500', label: '$500', value: 500 },
    { key: '100', label: '$100', value: 100 },
    { key: '50', label: '$50', value: 50 },
    { key: '10', label: '$10', value: 10 },
] as const;

const showForm = ref(false);
const mode = ref<'create' | 'close'>('create');
const editingSession = ref<any>(null);

const emptyForm = {
    user_id: null as number | null,
    opened_at: new Date().toISOString().slice(0, 16),
    closed_at: new Date().toISOString().slice(0, 16),
    // Apertura
    cant_20k_apertura: 0,
    cant_10k_apertura: 0,
    cant_5k_apertura: 0,
    cant_2k_apertura: 0,
    cant_1k_apertura: 0,
    cant_500_apertura: 0,
    cant_100_apertura: 0,
    cant_50_apertura: 0,
    cant_10_apertura: 0,
    // Cierre
    cant_20k_cierre: null as number | null,
    cant_10k_cierre: null as number | null,
    cant_5k_cierre: null as number | null,
    cant_2k_cierre: null as number | null,
    cant_1k_cierre: null as number | null,
    cant_500_cierre: null as number | null,
    cant_100_cierre: null as number | null,
    cant_50_cierre: null as number | null,
    cant_10_cierre: null as number | null,
    // Otros medios de pago
    total_red_compra: 0,
    total_transferencia: 0,
    total_retiros: 0,
    total_ingresos: 0,
    // Observaciones
    observations: '',
};

const form = useForm({ ...emptyForm });

function openNew() {
    mode.value = 'create';
    editingSession.value = null;
    form.reset();
    form.opened_at = new Date().toISOString().slice(0, 16);
    Object.assign(form, {
        closed_at: new Date().toISOString().slice(0, 16),
        cant_20k_apertura: 0,
        cant_10k_apertura: 0,
        cant_5k_apertura: 0,
        cant_2k_apertura: 0,
        cant_1k_apertura: 0,
        cant_500_apertura: 0,
        cant_100_apertura: 0,
        cant_50_apertura: 0,
        cant_10_apertura: 0,
        cant_20k_cierre: null,
        cant_10k_cierre: null,
        cant_5k_cierre: null,
        cant_2k_cierre: null,
        cant_1k_cierre: null,
        cant_500_cierre: null,
        cant_100_cierre: null,
        cant_50_cierre: null,
        cant_10_cierre: null,
        total_red_compra: 0,
        total_transferencia: 0,
        total_retiros: 0,
        total_ingresos: 0,
        observations: '',
    });
    showForm.value = true;
}

function openClose(session: any) {
    mode.value = 'close';
    editingSession.value = session;
    form.reset();
    form.closed_at = new Date().toISOString().slice(0, 16);
    // Load apertura data from session (readonly in close mode)
    form.user_id = session.user_id;
    form.opened_at = session.opened_at?.slice(0, 16) || '';
    form.cant_20k_apertura = session.cant_20k_apertura ?? 0;
    form.cant_10k_apertura = session.cant_10k_apertura ?? 0;
    form.cant_5k_apertura = session.cant_5k_apertura ?? 0;
    form.cant_2k_apertura = session.cant_2k_apertura ?? 0;
    form.cant_1k_apertura = session.cant_1k_apertura ?? 0;
    form.cant_500_apertura = session.cant_500_apertura ?? 0;
    form.cant_100_apertura = session.cant_100_apertura ?? 0;
    form.cant_50_apertura = session.cant_50_apertura ?? 0;
    form.cant_10_apertura = session.cant_10_apertura ?? 0;
    // Reset closing fields
    form.cant_20k_cierre = null;
    form.cant_10k_cierre = null;
    form.cant_5k_cierre = null;
    form.cant_2k_cierre = null;
    form.cant_1k_cierre = null;
    form.cant_500_cierre = null;
    form.cant_100_cierre = null;
    form.cant_50_cierre = null;
    form.cant_10_cierre = null;
    form.total_red_compra = 0;
    form.total_transferencia = 0;
    form.total_retiros = 0;
    form.total_ingresos = 0;
    form.observations = '';
    showForm.value = true;
}

function closeForm() {
    showForm.value = false;
    editingSession.value = null;
    form.reset();
}

function submitForm() {
    if (mode.value === 'create') {
        form.post(route('admin.arqueo-caja.store'), { onSuccess: closeForm });
    } else if (mode.value === 'close' && editingSession.value) {
        form.post(route('admin.arqueo-caja.close', editingSession.value.id), {
            onSuccess: closeForm,
        });
    }
}

function deleteSession(id: number) {
    if (!confirm('¿Eliminar esta sesión de caja?')) return;
    router.delete(route('admin.arqueo-caja.destroy', id), {
        preserveScroll: true,
    });
}

onMounted(() => {
    if (form.hasErrors) {
        showForm.value = true;
    }
});

// ─── Computed totals ──────────────────────────────────────────────

const totalApertura = computed(() => {
    let t = 0;
    for (const d of denominations) {
        const v = (form as any)[`cant_${d.key}_apertura`] ?? 0;
        t += Number(v) * d.value;
    }
    return t;
});

const totalCierre = computed(() => {
    let t = 0;
    for (const d of denominations) {
        const v = (form as any)[`cant_${d.key}_cierre`];
        if (v !== null && v !== undefined && v !== '') t += Number(v) * d.value;
    }
    return t;
});

const totalCajaEsperado = computed(() => {
    return (
        totalCierre.value +
        (form.total_red_compra || 0) +
        (form.total_transferencia || 0) +
        (form.total_ingresos || 0) -
        (form.total_retiros || 0)
    );
});

const diferencia = computed(() => {
    return totalCajaEsperado.value - totalApertura.value;
});

const fmtCLP = (v: number) => '$' + Math.round(v).toLocaleString('es-CL');
</script>

<template>
    <Head title="Arqueo de Caja" />
    <AdminLayout>
        <template #title>
            <h1
                class="font-display text-lg font-bold text-content-primary dark:text-white"
            >
                Arqueo de Caja
            </h1>
        </template>

        <!-- ═══════ SESSIONS LIST ═══════ -->
        <div
            class="mb-4 overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-surface-dark"
        >
            <div
                class="flex flex-wrap items-center gap-2 border-b border-gray-100 px-4 py-2.5 dark:border-gray-800"
            >
                <Wallet class="h-4 w-4 text-primary-500" />
                <h2
                    class="flex-1 text-sm font-bold text-content-primary dark:text-white"
                >
                    Sesiones de Caja
                </h2>
                <button
                    @click="openNew"
                    class="flex items-center gap-1.5 rounded-xl bg-primary-500 px-3 py-1.5 text-xs font-bold text-white shadow-sm transition-colors hover:bg-primary-600"
                >
                    <Plus class="h-3.5 w-3.5" /> Nueva Sesión
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead
                        class="bg-gray-50 text-[10px] uppercase tracking-wider text-content-muted dark:bg-gray-900/50 dark:text-gray-500"
                    >
                        <tr>
                            <th class="px-4 py-2 font-bold">Cajero</th>
                            <th class="px-4 py-2 font-bold">Apertura</th>
                            <th class="px-4 py-2 font-bold">Cierre</th>
                            <th class="px-4 py-2 text-right font-bold">
                                F. Apertura
                            </th>
                            <th class="px-4 py-2 text-right font-bold">
                                Efvo. Cierre
                            </th>
                            <th class="px-4 py-2 text-right font-bold">
                                Diferencia
                            </th>
                            <th class="px-4 py-2 text-center font-bold">
                                Estado
                            </th>
                            <th class="px-4 py-2 text-right font-bold">
                                Acciones
                            </th>
                        </tr>
                    </thead>
                    <tbody
                        class="divide-y divide-gray-100 dark:divide-gray-800"
                    >
                        <tr v-if="!sessions.data?.length">
                            <td
                                colspan="8"
                                class="px-4 py-8 text-center text-xs text-content-muted dark:text-gray-500"
                            >
                                No hay sesiones de caja registradas.
                            </td>
                        </tr>
                        <tr
                            v-for="s in sessions.data"
                            :key="s.id"
                            class="group transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/50"
                        >
                            <td
                                class="px-4 py-2 text-xs font-medium text-content-primary dark:text-white"
                            >
                                {{ s.user?.name || '—' }}
                            </td>
                            <td
                                class="px-4 py-2 text-xs text-content-secondary"
                            >
                                {{
                                    s.opened_at
                                        ? formatDate(s.opened_at) +
                                          ' ' +
                                          formatTime(s.opened_at)
                                        : '—'
                                }}
                            </td>
                            <td
                                class="px-4 py-2 text-xs text-content-secondary"
                            >
                                {{
                                    s.closed_at
                                        ? formatDate(s.closed_at) +
                                          ' ' +
                                          formatTime(s.closed_at)
                                        : '—'
                                }}
                            </td>
                            <td
                                class="px-4 py-2 text-right text-xs font-medium"
                            >
                                {{ fmtCLP(s.total_efectivo_apertura || 0) }}
                            </td>
                            <td
                                class="px-4 py-2 text-right text-xs font-medium"
                            >
                                {{
                                    s.total_efectivo_cierre
                                        ? fmtCLP(s.total_efectivo_cierre)
                                        : '—'
                                }}
                            </td>
                            <td
                                class="px-4 py-2 text-right text-xs font-bold"
                                :class="
                                    s.diferencia_descuadre != null
                                        ? s.diferencia_descuadre < 0
                                            ? 'text-danger'
                                            : 'text-success'
                                        : 'text-content-muted'
                                "
                            >
                                {{
                                    s.diferencia_descuadre != null
                                        ? fmtCLP(s.diferencia_descuadre)
                                        : '—'
                                }}
                            </td>
                            <td class="px-4 py-2 text-center">
                                <span
                                    :class="
                                        s.closed_at
                                            ? 'bg-gray-100 text-gray-500'
                                            : 'bg-success/10 text-success'
                                    "
                                    class="rounded-lg px-2 py-0.5 text-[9px] font-bold uppercase"
                                >
                                    {{ s.closed_at ? 'Cerrada' : 'Activa' }}
                                </span>
                            </td>
                            <td class="px-4 py-2 text-right">
                                <div
                                    class="flex items-center justify-end gap-1 opacity-0 transition-opacity group-hover:opacity-100"
                                >
                                    <button
                                        v-if="!s.closed_at"
                                        @click="openClose(s)"
                                        class="rounded-lg px-2 py-1 text-[10px] font-bold text-primary-500 transition-colors hover:bg-primary-50 dark:hover:bg-primary-900/20"
                                    >
                                        Cerrar
                                    </button>
                                    <button
                                        @click="deleteSession(s.id)"
                                        class="rounded-lg p-1 text-danger transition-colors hover:bg-red-50 dark:hover:bg-red-900/20"
                                    >
                                        <Trash2 class="h-3.5 w-3.5" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div
                v-if="sessions.last_page > 1"
                class="flex items-center justify-between border-t border-gray-100 px-4 py-2 text-[10px] text-content-muted dark:border-gray-800 dark:text-gray-500"
            >
                <span
                    >Página {{ sessions.current_page }} de
                    {{ sessions.last_page }}</span
                >
                <div class="flex gap-1.5">
                    <a
                        v-if="sessions.prev_page_url"
                        :href="sessions.prev_page_url"
                        class="rounded-lg bg-gray-50 px-2 py-1 text-xs font-bold transition-colors hover:bg-gray-100 dark:bg-gray-900"
                        >←</a
                    >
                    <a
                        v-if="sessions.next_page_url"
                        :href="sessions.next_page_url"
                        class="rounded-lg bg-gray-50 px-2 py-1 text-xs font-bold transition-colors hover:bg-gray-100 dark:bg-gray-900"
                        >→</a
                    >
                </div>
            </div>
        </div>

        <!-- ═══════ FORM MODAL (COMPACT EXCEL-LIKE) ═══════ -->
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="showForm"
                class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-black/40 px-2 pt-8 backdrop-blur-sm"
            >
                <div
                    class="relative mx-auto my-4 w-full max-w-4xl rounded-2xl bg-white shadow-xl dark:bg-surface-dark"
                >
                    <!-- Modal Header -->
                    <div
                        class="sticky top-0 z-10 flex items-center justify-between rounded-t-2xl border-b border-gray-100 bg-white px-4 py-2.5 dark:border-gray-800 dark:bg-surface-dark"
                    >
                        <div class="flex items-center gap-2">
                            <Wallet class="h-4 w-4 text-primary-500" />
                            <h3
                                class="font-display text-sm font-bold text-content-primary dark:text-white"
                            >
                                {{
                                    mode === 'create'
                                        ? 'Nueva Sesión de Caja'
                                        : 'Cierre de Sesión de Caja'
                                }}
                            </h3>
                        </div>
                        <button
                            @click="closeForm"
                            class="rounded-lg p-1 transition-colors hover:bg-gray-100 dark:hover:bg-gray-800"
                        >
                            <X class="h-4 w-4 text-content-muted" />
                        </button>
                    </div>

                    <form @submit.prevent="submitForm" class="space-y-3 p-4">
                        <!-- Mode indicator -->
                        <div
                            v-if="mode === 'close'"
                            class="flex items-center gap-1.5 rounded-xl border border-warning/20 bg-warning/10 px-3 py-2 text-[11px] font-bold text-warning"
                        >
                            <EyeOff class="h-3.5 w-3.5 flex-shrink-0" />
                            Apertura solo lectura. Completa solo las cantidades
                            de cierre.
                        </div>

                        <div
                            v-if="form.hasErrors"
                            class="flex items-center gap-1.5 rounded-xl border border-danger/20 bg-danger/10 px-3 py-2 text-[11px] font-bold text-danger"
                        >
                            <X class="h-3.5 w-3.5 flex-shrink-0" />
                            Corrige los errores antes de guardar.
                        </div>

                        <!-- ─── TWO COLUMN LAYOUT ─── -->
                        <div class="grid grid-cols-1 gap-3 lg:grid-cols-2">
                            <!-- ═══ LEFT: APERTURA ═══ -->
                            <div
                                class="overflow-hidden rounded-xl border border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-900/50"
                            >
                                <div
                                    class="flex items-center gap-1.5 bg-primary-500 px-3 py-1.5"
                                >
                                    <Wallet class="h-3.5 w-3.5 text-white" />
                                    <h4
                                        class="font-display text-[11px] font-bold text-white"
                                    >
                                        Apertura de Caja
                                    </h4>
                                </div>
                                <div class="p-3">
                                    <div class="mb-2 grid grid-cols-2 gap-2">
                                        <div>
                                            <label
                                                class="block text-[9px] font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                                                >Cajero</label
                                            >
                                            <select
                                                v-model="form.user_id"
                                                required
                                                :disabled="mode === 'close'"
                                                :class="[
                                                    'w-full rounded-lg border bg-white px-2 py-1 text-[11px] text-content-primary transition-shadow disabled:cursor-not-allowed disabled:opacity-60 dark:bg-gray-800 dark:text-white',
                                                    form.errors.user_id
                                                        ? 'border-danger dark:border-danger'
                                                        : 'border-gray-200 dark:border-gray-700',
                                                ]"
                                            >
                                                <option :value="null" disabled>
                                                    Seleccionar...
                                                </option>
                                                <option
                                                    v-for="c in cashiers"
                                                    :key="c.id"
                                                    :value="c.id"
                                                >
                                                    {{ c.name }}
                                                </option>
                                            </select>
                                            <p
                                                v-if="form.errors.user_id"
                                                class="mt-0.5 text-[9px] text-danger"
                                            >
                                                {{ form.errors.user_id }}
                                            </p>
                                        </div>
                                        <div>
                                            <label
                                                class="block text-[9px] font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                                                >Apertura</label
                                            >
                                            <input
                                                v-model="form.opened_at"
                                                type="datetime-local"
                                                required
                                                :disabled="mode === 'close'"
                                                :class="[
                                                    'w-full rounded-lg border bg-white px-2 py-1 text-[11px] text-content-primary transition-shadow disabled:cursor-not-allowed disabled:opacity-60 dark:bg-gray-800 dark:text-white',
                                                    form.errors.opened_at
                                                        ? 'border-danger dark:border-danger'
                                                        : 'border-gray-200 dark:border-gray-700',
                                                ]"
                                            />
                                            <p
                                                v-if="form.errors.opened_at"
                                                class="mt-0.5 text-[9px] text-danger"
                                            >
                                                {{ form.errors.opened_at }}
                                            </p>
                                        </div>
                                    </div>

                                    <table class="w-full">
                                        <thead>
                                            <tr
                                                class="border-b border-gray-200 text-[9px] font-bold uppercase tracking-wider text-content-muted dark:border-gray-700 dark:text-gray-500"
                                            >
                                                <th class="pb-1 text-left">
                                                    Denominación
                                                </th>
                                                <th class="pb-1 text-center">
                                                    Cant.
                                                </th>
                                                <th class="pb-1 text-right">
                                                    Total
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody
                                            class="divide-y divide-gray-100 dark:divide-gray-800"
                                        >
                                            <tr
                                                v-for="d in denominations"
                                                :key="'a-' + d.key"
                                            >
                                                <td
                                                    class="py-1 text-[11px] font-semibold text-content-primary dark:text-white"
                                                >
                                                    {{ d.label }}
                                                </td>
                                                <td class="py-1 text-center">
                                                    <input
                                                        :id="
                                                            'apertura_' + d.key
                                                        "
                                                        :name="
                                                            'cant_' +
                                                            d.key +
                                                            '_apertura'
                                                        "
                                                        v-model.number="
                                                            form[
                                                                ('cant_' +
                                                                    d.key +
                                                                    '_apertura') as keyof typeof form
                                                            ] as any
                                                        "
                                                        type="number"
                                                        min="0"
                                                        :disabled="
                                                            mode === 'close'
                                                        "
                                                        class="w-14 rounded-lg border border-gray-200 bg-white px-1 py-0.5 text-center text-[11px] text-content-primary transition-shadow focus:border-primary-500 focus:ring-1 focus:ring-primary-500/30 disabled:cursor-not-allowed disabled:opacity-60 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                                    />
                                                </td>
                                                <td
                                                    class="py-1 text-right font-mono text-[11px] font-bold text-primary-500"
                                                >
                                                    {{
                                                        fmtCLP(
                                                            ((form as any)[
                                                                'cant_' +
                                                                    d.key +
                                                                    '_apertura'
                                                            ] ?? 0) * d.value,
                                                        )
                                                    }}
                                                </td>
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr
                                                class="border-t-2 border-primary-500"
                                            >
                                                <td
                                                    class="pt-1.5 text-[11px] font-bold text-content-primary dark:text-white"
                                                >
                                                    Total Efectivo Apertura
                                                </td>
                                                <td class="pt-1.5"></td>
                                                <td
                                                    class="pt-1.5 text-right font-mono text-sm font-bold text-primary-600"
                                                >
                                                    {{ fmtCLP(totalApertura) }}
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>

                            <!-- ═══ RIGHT: CIERRE ═══ -->
                            <div
                                class="overflow-hidden rounded-xl border border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-900/50"
                            >
                                <div
                                    class="flex items-center gap-1.5 bg-gray-800 px-3 py-1.5 dark:bg-gray-700"
                                >
                                    <Wallet class="h-3.5 w-3.5 text-white" />
                                    <h4
                                        class="font-display text-[11px] font-bold text-white"
                                    >
                                        Cierre de Caja
                                    </h4>
                                </div>
                                <div class="p-3">
                                    <div class="mb-2">
                                        <label
                                            class="block text-[9px] font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                                            >Cierre</label
                                        >
                                        <input
                                            v-model="form.closed_at"
                                            type="datetime-local"
                                            required
                                            :class="[
                                                'w-full rounded-lg border bg-white px-2 py-1 text-[11px] text-content-primary transition-shadow dark:bg-gray-800 dark:text-white',
                                                form.errors.closed_at
                                                    ? 'border-danger dark:border-danger'
                                                    : 'border-gray-200 dark:border-gray-700',
                                            ]"
                                        />
                                        <p
                                            v-if="form.errors.closed_at"
                                            class="mt-0.5 text-[9px] text-danger"
                                        >
                                            {{ form.errors.closed_at }}
                                        </p>
                                    </div>

                                    <table class="w-full">
                                        <thead>
                                            <tr
                                                class="border-b border-gray-200 text-[9px] font-bold uppercase tracking-wider text-content-muted dark:border-gray-700 dark:text-gray-500"
                                            >
                                                <th class="pb-1 text-left">
                                                    Denominación
                                                </th>
                                                <th class="pb-1 text-center">
                                                    Cant.
                                                </th>
                                                <th class="pb-1 text-right">
                                                    Total
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody
                                            class="divide-y divide-gray-100 dark:divide-gray-800"
                                        >
                                            <tr
                                                v-for="d in denominations"
                                                :key="'c-' + d.key"
                                            >
                                                <td
                                                    class="py-1 text-[11px] font-semibold text-content-primary dark:text-white"
                                                >
                                                    {{ d.label }}
                                                </td>
                                                <td class="py-1 text-center">
                                                    <input
                                                        :id="'cierre_' + d.key"
                                                        :name="
                                                            'cant_' +
                                                            d.key +
                                                            '_cierre'
                                                        "
                                                        v-model.number="
                                                            form[
                                                                ('cant_' +
                                                                    d.key +
                                                                    '_cierre') as keyof typeof form
                                                            ] as any
                                                        "
                                                        type="number"
                                                        min="0"
                                                        class="w-14 rounded-lg border border-gray-200 bg-white px-1 py-0.5 text-center text-[11px] text-content-primary transition-shadow focus:border-primary-500 focus:ring-1 focus:ring-primary-500/30 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                                    />
                                                </td>
                                                <td
                                                    class="py-1 text-right font-mono text-[11px] font-bold text-primary-500"
                                                >
                                                    {{
                                                        fmtCLP(
                                                            ((form as any)[
                                                                'cant_' +
                                                                    d.key +
                                                                    '_cierre'
                                                            ] ?? 0) * d.value,
                                                        )
                                                    }}
                                                </td>
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr
                                                class="border-t-2 border-gray-800 dark:border-gray-500"
                                            >
                                                <td
                                                    class="pt-1.5 text-[11px] font-bold text-content-primary dark:text-white"
                                                >
                                                    Total Efectivo Cierre
                                                </td>
                                                <td class="pt-1.5"></td>
                                                <td
                                                    class="pt-1.5 text-right font-mono text-sm font-bold text-content-primary"
                                                >
                                                    {{ fmtCLP(totalCierre) }}
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- ─── BOTTOM: PAYMENTS + SUMMARY ─── -->
                        <div
                            class="overflow-hidden rounded-xl border border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-900/50"
                        >
                            <div
                                class="flex items-center gap-1.5 border-b border-gray-200 bg-gray-100 px-3 py-1.5 dark:border-gray-700 dark:bg-gray-800"
                            >
                                <Wallet
                                    class="h-3.5 w-3.5 text-content-primary"
                                />
                                <h4
                                    class="font-display text-[11px] font-bold text-content-primary dark:text-white"
                                >
                                    Resumen y Otros Medios de Pago
                                </h4>
                            </div>
                            <div class="p-3">
                                <div class="mb-2 flex flex-wrap gap-2">
                                    <div class="min-w-[120px] flex-1">
                                        <label
                                            class="block text-[9px] font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                                            >Red Compra</label
                                        >
                                        <div class="relative">
                                            <span
                                                class="absolute left-2 top-1/2 -translate-y-1/2 text-[11px] font-bold text-content-muted"
                                                >$</span
                                            >
                                            <input
                                                v-model.number="
                                                    form.total_red_compra
                                                "
                                                type="number"
                                                min="0"
                                                name="total_red_compra"
                                                id="total_red_compra"
                                                :class="[
                                                    'w-full rounded-lg border bg-white py-1 pl-5 pr-2 text-[11px] text-content-primary transition-shadow dark:bg-gray-800 dark:text-white',
                                                    form.errors.total_red_compra
                                                        ? 'border-danger dark:border-danger'
                                                        : 'border-gray-200 dark:border-gray-700',
                                                ]"
                                            />
                                            <p
                                                v-if="
                                                    form.errors.total_red_compra
                                                "
                                                class="mt-0.5 text-[9px] text-danger"
                                            >
                                                {{
                                                    form.errors.total_red_compra
                                                }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="min-w-[120px] flex-1">
                                        <label
                                            class="block text-[9px] font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                                            >Transferencia</label
                                        >
                                        <div class="relative">
                                            <span
                                                class="absolute left-2 top-1/2 -translate-y-1/2 text-[11px] font-bold text-content-muted"
                                                >$</span
                                            >
                                            <input
                                                v-model.number="
                                                    form.total_transferencia
                                                "
                                                type="number"
                                                min="0"
                                                name="total_transferencia"
                                                id="total_transferencia"
                                                :class="[
                                                    'w-full rounded-lg border bg-white py-1 pl-5 pr-2 text-[11px] text-content-primary transition-shadow dark:bg-gray-800 dark:text-white',
                                                    form.errors
                                                        .total_transferencia
                                                        ? 'border-danger dark:border-danger'
                                                        : 'border-gray-200 dark:border-gray-700',
                                                ]"
                                            />
                                            <p
                                                v-if="
                                                    form.errors
                                                        .total_transferencia
                                                "
                                                class="mt-0.5 text-[9px] text-danger"
                                            >
                                                {{
                                                    form.errors
                                                        .total_transferencia
                                                }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="min-w-[120px] flex-1">
                                        <label
                                            class="block text-[9px] font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                                            >Retiros</label
                                        >
                                        <div class="relative">
                                            <span
                                                class="absolute left-2 top-1/2 -translate-y-1/2 text-[11px] font-bold text-content-muted"
                                                >$</span
                                            >
                                            <input
                                                v-model.number="
                                                    form.total_retiros
                                                "
                                                type="number"
                                                min="0"
                                                name="total_retiros"
                                                id="total_retiros"
                                                :class="[
                                                    'w-full rounded-lg border bg-white py-1 pl-5 pr-2 text-[11px] text-content-primary transition-shadow dark:bg-gray-800 dark:text-white',
                                                    form.errors.total_retiros
                                                        ? 'border-danger dark:border-danger'
                                                        : 'border-gray-200 dark:border-gray-700',
                                                ]"
                                            />
                                            <p
                                                v-if="form.errors.total_retiros"
                                                class="mt-0.5 text-[9px] text-danger"
                                            >
                                                {{ form.errors.total_retiros }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="min-w-[120px] flex-1">
                                        <label
                                            class="block text-[9px] font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                                            >Ingresos</label
                                        >
                                        <div class="relative">
                                            <span
                                                class="absolute left-2 top-1/2 -translate-y-1/2 text-[11px] font-bold text-content-muted"
                                                >$</span
                                            >
                                            <input
                                                v-model.number="
                                                    form.total_ingresos
                                                "
                                                type="number"
                                                min="0"
                                                name="total_ingresos"
                                                id="total_ingresos"
                                                :class="[
                                                    'w-full rounded-lg border bg-white py-1 pl-5 pr-2 text-[11px] text-content-primary transition-shadow dark:bg-gray-800 dark:text-white',
                                                    form.errors.total_ingresos
                                                        ? 'border-danger dark:border-danger'
                                                        : 'border-gray-200 dark:border-gray-700',
                                                ]"
                                            />
                                            <p
                                                v-if="form.errors.total_ingresos"
                                                class="mt-0.5 text-[9px] text-danger"
                                            >
                                                {{ form.errors.total_ingresos }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div
                                    class="grid grid-cols-2 gap-2 sm:grid-cols-4"
                                >
                                    <div
                                        class="rounded-lg border border-gray-200 bg-white p-2 dark:border-gray-700 dark:bg-gray-800"
                                    >
                                        <div
                                            class="text-[9px] font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                                        >
                                            Efvo. Cierre
                                        </div>
                                        <div
                                            class="font-mono text-xs font-bold text-content-primary"
                                        >
                                            {{ fmtCLP(totalCierre) }}
                                        </div>
                                    </div>
                                    <div
                                        class="rounded-lg border border-gray-200 bg-white p-2 dark:border-gray-700 dark:bg-gray-800"
                                    >
                                        <div
                                            class="text-[9px] font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                                        >
                                            + Red C./Transf.
                                        </div>
                                        <div
                                            class="font-mono text-xs font-bold text-content-primary"
                                        >
                                            {{
                                                fmtCLP(
                                                    (form.total_red_compra ||
                                                        0) +
                                                        (form.total_transferencia ||
                                                            0),
                                                )
                                            }}
                                        </div>
                                    </div>
                                    <div
                                        class="rounded-lg border border-gray-200 bg-white p-2 dark:border-gray-700 dark:bg-gray-800"
                                    >
                                        <div
                                            class="text-[9px] font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                                        >
                                            Esperado en Caja
                                        </div>
                                        <div
                                            class="font-mono text-xs font-bold text-primary-500"
                                        >
                                            {{ fmtCLP(totalCajaEsperado) }}
                                        </div>
                                    </div>
                                    <div
                                        :class="
                                            totalCierre > 0
                                                ? diferencia < 0
                                                    ? 'border-danger/20 bg-danger/5'
                                                    : diferencia > 0
                                                      ? 'border-success/20 bg-success/5'
                                                      : 'border-gray-200 bg-white'
                                                : 'border-gray-200 bg-white'
                                        "
                                        class="rounded-lg border p-2"
                                    >
                                        <div
                                            class="text-[9px] font-bold uppercase tracking-wider"
                                            :class="
                                                totalCierre > 0
                                                    ? diferencia < 0
                                                        ? 'text-danger'
                                                        : diferencia > 0
                                                          ? 'text-success'
                                                          : 'text-content-muted'
                                                    : 'text-content-muted'
                                            "
                                        >
                                            {{
                                                totalCierre > 0
                                                    ? diferencia < 0
                                                        ? 'Descuadre'
                                                        : diferencia > 0
                                                          ? 'Sobrante'
                                                          : 'Cuadrado'
                                                    : 'Diferencia'
                                            }}
                                        </div>
                                        <div
                                            class="font-mono text-xs font-bold"
                                            :class="
                                                totalCierre > 0
                                                    ? diferencia < 0
                                                        ? 'text-danger'
                                                        : diferencia > 0
                                                          ? 'text-success'
                                                          : 'text-content-primary'
                                                    : 'text-content-muted'
                                            "
                                        >
                                            {{
                                                totalCierre > 0
                                                    ? fmtCLP(diferencia)
                                                    : '—'
                                            }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Cash Movements Reference -->
                                <div
                                    v-if="cashMovements && cashMovements.count > 0"
                                    class="mt-3 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 dark:border-emerald-800 dark:bg-emerald-900/10"
                                >
                                    <div
                                        class="mb-1 text-[9px] font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400"
                                    >
                                        Movimientos del día
                                    </div>
                                    <div class="flex gap-4 text-[11px]">
                                        <span class="font-medium text-emerald-600 dark:text-emerald-400">
                                            +${{ cashMovements.total_ingresos.toLocaleString('es-CL') }} ingresos
                                        </span>
                                        <span class="font-medium text-orange-600 dark:text-orange-400">
                                            -${{ cashMovements.total_retiros.toLocaleString('es-CL') }} retiros
                                        </span>
                                    </div>
                                </div>

                                <div class="mt-2">
                                    <label
                                        class="block text-[9px] font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                                        >Observaciones</label
                                    >
                                    <textarea
                                        v-model="form.observations"
                                        name="observations"
                                        id="observations"
                                        rows="1"
                                        :class="[
                                            'w-full rounded-lg border bg-white px-2 py-1 text-[11px] text-content-primary transition-shadow dark:bg-gray-800 dark:text-white',
                                            form.errors.observations
                                                ? 'border-danger dark:border-danger'
                                                : 'border-gray-200 dark:border-gray-700',
                                        ]"
                                    ></textarea>
                                    <p
                                        v-if="form.errors.observations"
                                        class="mt-0.5 text-[9px] text-danger"
                                    >
                                        {{ form.errors.observations }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- ─── FORM ACTIONS ─── -->
                        <div class="flex gap-2 pt-1">
                            <button
                                type="button"
                                @click="closeForm"
                                class="flex-1 rounded-xl border border-gray-200 py-1.5 text-[11px] font-bold text-content-secondary transition-colors hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800"
                            >
                                Cancelar
                            </button>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="flex flex-1 items-center justify-center gap-1.5 rounded-xl bg-primary-500 py-1.5 text-[11px] font-bold text-white shadow-sm transition-colors hover:bg-primary-600 disabled:opacity-50"
                            >
                                <Check class="h-3.5 w-3.5" />
                                {{
                                    mode === 'create'
                                        ? 'Guardar Apertura'
                                        : 'Cerrar Sesión'
                                }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>
    </AdminLayout>
</template>
