<script setup lang="ts">
import { formatDateTime } from '@/helpers/format';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DateFilter from '@/Components/DateFilter.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Check, Eye, FileText, PencilLine, ShoppingCart, Trash2, X } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    observaciones: {
        data: any[];
        links: any[];
        current_page: number;
        last_page: number;
        prev_page_url: string | null;
        next_page_url: string | null;
    };
    users: { id: number; name: string }[];
    filters: { user_id?: string; fecha?: string; estado?: string };
}>();

const selectedUser = ref(props.filters.user_id ?? '');
const todayStr = new Date().toISOString().slice(0, 10);
const filterDate = ref<string>(props.filters?.fecha ?? todayStr);
const selectedEstado = ref(props.filters.estado ?? '');

function consultar() {
    const params: Record<string, any> = {};
    if (selectedUser.value !== '') params.user_id = selectedUser.value;
    if (filterDate.value) params.fecha = filterDate.value;
    if (selectedEstado.value !== '') params.estado = selectedEstado.value;
    router.get(route('admin.observaciones.index', params));
}

function onDatePicked(payload: { dia: number; mes: number; anio: number }) {
    const m = String(payload.mes).padStart(2, '0');
    const d = String(payload.dia).padStart(2, '0');
    filterDate.value = `${payload.anio}-${m}-${d}`;
    consultar();
}

const accionLabel: Record<string, string> = {
    eliminar_item: 'Eliminar Ítem',
    limpiar_carrito: 'Limpiar Carrito',
    descuadre_apertura: 'Descuadre Apertura',
};

const accionIcon: Record<string, any> = {
    eliminar_item: Trash2,
    limpiar_carrito: ShoppingCart,
    descuadre_apertura: Check,
};

const accionColor: Record<string, string> = {
    eliminar_item: 'bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400',
    limpiar_carrito: 'bg-amber-50 text-amber-700 dark:bg-amber-900/20 dark:text-amber-400',
    descuadre_apertura: 'bg-rose-50 text-rose-700 dark:bg-rose-900/20 dark:text-rose-400',
};

const fmt = (v: number | null | undefined) => v != null ? '$' + v.toLocaleString('es-CO') : null;

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
];

// ─── Detail Modal ───
const showDetailModal = ref(false);
const detailItem = ref<any>(null);

function openDetail(o: any) {
    detailItem.value = o;
    showDetailModal.value = true;
}

function closeDetail() {
    showDetailModal.value = false;
    detailItem.value = null;
}

function qtyFromDesglose(desglose: Record<string, number> | null, key: string): number {
    return desglose?.[key] ?? 0;
}

function subtotalFromDesglose(desglose: Record<string, number> | null, key: string, value: number): number {
    return qtyFromDesglose(desglose, key) * value;
}

const detailDesgloseAnterior = ref<Record<string, number> | null>(null);
const detailDesgloseNuevo = ref<Record<string, number> | null>(null);
const detailOldTotal = ref(0);
const detailNewTotal = ref(0);

// Watch detailItem to populate computed refs
import { watch } from 'vue';
watch(showDetailModal, (val) => {
    if (val && detailItem.value?.metadata) {
        detailDesgloseAnterior.value = detailItem.value.metadata.old_desglose ?? null;
        detailDesgloseNuevo.value = detailItem.value.metadata.new_desglose ?? null;
        detailOldTotal.value = detailItem.value.metadata.old_total ?? 0;
        detailNewTotal.value = detailItem.value.metadata.new_total ?? 0;
    } else {
        detailDesgloseAnterior.value = null;
        detailDesgloseNuevo.value = null;
        detailOldTotal.value = 0;
        detailNewTotal.value = 0;
    }
});

// ─── Note Modal ───
const showNoteModal = ref(false);
const noteItem = ref<any>(null);
const noteText = ref('');
const noteSaving = ref(false);

function openNote(o: any) {
    noteItem.value = o;
    noteText.value = o.nota_admin ?? '';
    showNoteModal.value = true;
}

function closeNote() {
    showNoteModal.value = false;
    noteItem.value = null;
    noteText.value = '';
}

function saveNote() {
    if (!noteItem.value) return;
    noteSaving.value = true;
    router.put(route('admin.observaciones.update', noteItem.value.id), {
        nota_admin: noteText.value,
    }, {
        preserveScroll: true,
        onSuccess: () => { closeNote(); },
        onError: () => { noteSaving.value = false; },
        onFinish: () => { noteSaving.value = false; },
    });
}
</script>

<template>
    <Head title="Observaciones" />
    <AdminLayout>
        <div class="mx-auto max-w-7xl space-y-6 p-4 sm:p-6">
            <!-- Header -->
            <div class="flex items-center gap-3">
                <div class="rounded-xl bg-primary-50 p-2.5 dark:bg-primary-900/20">
                    <Eye class="h-5 w-5 text-primary-600 dark:text-primary-400" />
                </div>
                <div>
                    <h1 class="text-xl font-bold text-content-primary dark:text-white">Reporte de Observaciones</h1>
                    <p class="text-xs text-content-muted">Registro de eliminación de ítems, limpieza de carrito y descuadres de apertura en Caja Rápida</p>
                </div>
            </div>

            <!-- Filters -->
            <div class="flex flex-wrap items-end gap-3 rounded-xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-surface-dark">
                <div class="min-w-[200px]">
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400">Cajero</label>
                    <select v-model="selectedUser" @change="consultar"
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm text-content-primary focus:border-primary-500 focus:ring-1 focus:ring-primary-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        <option value="">Todos</option>
                        <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
                    </select>
                </div>
                <DateFilter
                    v-model="filterDate"
                    label="Fecha"
                    @select="onDatePicked"
                />
                <div class="min-w-[160px]">
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400">Estado</label>
                    <select v-model="selectedEstado" @change="consultar"
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm text-content-primary focus:border-primary-500 focus:ring-1 focus:ring-primary-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        <option value="">Todos</option>
                        <option value="pendiente">Pendientes</option>
                        <option value="revisado">Revisados</option>
                    </select>
                </div>
                <button @click="selectedUser = ''; filterDate = todayStr; selectedEstado = ''; consultar()"
                    class="rounded-xl border border-gray-200 px-4 py-2.5 text-xs font-bold text-content-secondary transition-colors hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800">
                    Limpiar Filtros
                </button>
            </div>

            <!-- Table -->
            <div class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-surface-dark">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 text-xs uppercase tracking-wider text-content-muted dark:bg-gray-900/50 dark:text-gray-500">
                            <tr>
                                <th class="px-4 py-3 font-bold">Cajero</th>
                                <th class="px-4 py-3 font-bold">Acción</th>
                                <th class="px-4 py-3 font-bold">Producto / Detalle</th>
                                <th class="px-4 py-3 font-bold text-right">Diferencia</th>
                                <th class="px-4 py-3 font-bold">Fecha y Hora</th>
                                <th class="px-4 py-3 font-bold">Estado</th>
                                <th class="px-4 py-3 font-bold text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            <tr v-for="o in observaciones.data" :key="o.id"
                                class="transition-colors hover:bg-gray-50 dark:hover:bg-gray-900/50"
                                :class="{ 'bg-rose-50/40 dark:bg-rose-900/5': o.tipo_accion === 'descuadre_apertura' && o.estado === 'pendiente' }">
                                <td class="px-4 py-3 text-sm font-medium text-content-primary dark:text-white">
                                    {{ o.user?.name ?? '—' }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1 text-xs font-bold"
                                        :class="accionColor[o.tipo_accion] ?? 'bg-gray-100 text-gray-700'">
                                        <component :is="accionIcon[o.tipo_accion]" class="h-3.5 w-3.5" />
                                        {{ accionLabel[o.tipo_accion] ?? o.tipo_accion }}
                                    </span>
                                </td>
                                <td class="max-w-xs truncate px-4 py-3 text-sm text-content-secondary">
                                    {{ o.producto_afectado ?? o.detalle }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-right text-sm font-bold text-content-primary dark:text-white">
                                    {{ fmt(o.monto_diferencia) ?? '—' }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-content-muted">
                                    {{ formatDateTime(o.created_at) }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm">
                                    <span v-if="o.estado === 'revisado'"
                                        class="inline-flex items-center gap-1 rounded-lg bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-400">
                                        <Check class="h-3 w-3" />
                                        Revisado {{ formatDateTime(o.revisado_at) }}
                                    </span>
                                    <span v-else
                                        class="inline-flex items-center gap-1 rounded-lg bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-700 dark:bg-amber-900/20 dark:text-amber-400">
                                        Pendiente
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <button @click="openDetail(o)" title="Ver Detalle"
                                            class="rounded-lg p-1.5 transition-colors hover:bg-gray-100 dark:hover:bg-gray-800">
                                            <Eye class="h-4 w-4 text-content-muted" />
                                        </button>
                                        <button v-if="o.estado === 'pendiente'" @click="openNote(o)" title="Seguimiento"
                                            class="rounded-lg p-1.5 transition-colors hover:bg-gray-100 dark:hover:bg-gray-800">
                                            <PencilLine class="h-4 w-4 text-content-muted" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!observaciones.data.length">
                                <td colspan="7" class="px-4 py-12 text-center text-sm text-content-muted">
                                    <FileText class="mx-auto mb-2 h-8 w-8 opacity-40" />
                                    No hay observaciones registradas.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="observaciones.last_page > 1"
                    class="flex items-center justify-between border-t border-gray-100 px-4 py-3 dark:border-gray-800">
                    <span class="text-xs text-content-muted">
                        Página {{ observaciones.current_page }} de {{ observaciones.last_page }}
                    </span>
                    <div class="flex gap-1">
                        <Link v-if="observaciones.prev_page_url" :href="observaciones.prev_page_url"
                            class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-bold text-content-secondary transition-colors hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800">
                            Anterior
                        </Link>
                        <Link v-if="observaciones.next_page_url" :href="observaciones.next_page_url"
                            class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-bold text-content-secondary transition-colors hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800">
                            Siguiente
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- ═══ Modal 1: Ver Detalle ═══ -->
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="showDetailModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4 backdrop-blur-sm"
                @click.self="closeDetail"
            >
                <div class="w-full max-w-2xl rounded-2xl bg-white shadow-xl dark:bg-surface-dark">
                    <div class="flex items-center justify-between border-b border-gray-100 px-5 py-3 dark:border-gray-800">
                        <h3 class="font-display text-sm font-bold text-content-primary dark:text-white">
                            Detalle de Observación
                        </h3>
                        <button @click="closeDetail"
                            class="rounded-lg p-1 transition-colors hover:bg-gray-100 dark:hover:bg-gray-800">
                            <X class="h-4 w-4 text-content-muted" />
                        </button>
                    </div>
                    <div class="space-y-4 p-5 max-h-[70vh] overflow-y-auto">
                        <!-- Badge + metadata header -->
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1 text-xs font-bold"
                                :class="accionColor[detailItem?.tipo_accion] ?? 'bg-gray-100 text-gray-700'">
                                <component :is="accionIcon[detailItem?.tipo_accion]" class="h-3.5 w-3.5" />
                                {{ accionLabel[detailItem?.tipo_accion] ?? detailItem?.tipo_accion }}
                            </span>
                            <span class="text-xs text-content-muted">{{ detailItem?.user?.name }} · {{ formatDateTime(detailItem?.created_at) }}</span>
                        </div>

                        <!-- Descuadre Apertura: tabla comparativa -->
                        <template v-if="detailItem?.tipo_accion === 'descuadre_apertura' && detailDesgloseAnterior">
                            <div class="overflow-x-auto rounded-xl border border-gray-100 dark:border-gray-800">
                                <table class="w-full text-left text-xs">
                                    <thead class="bg-gray-50 uppercase tracking-wider text-content-muted dark:bg-gray-900/50">
                                        <tr>
                                            <th class="px-3 py-2 font-bold">Denominación</th>
                                            <th class="px-3 py-2 text-center font-bold">Cierre Ant.</th>
                                            <th class="px-3 py-2 text-center font-bold">Apertura</th>
                                            <th class="px-3 py-2 text-right font-bold">Diferencia</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                        <tr v-for="d in denominations" :key="d.key">
                                            <td class="px-3 py-1.5 font-semibold text-content-primary dark:text-white">{{ d.label }}</td>
                                            <td class="px-3 py-1.5 text-center text-content-muted">
                                                {{ qtyFromDesglose(detailDesgloseAnterior, d.key) }}
                                                <span class="text-[10px]">({{ fmt(subtotalFromDesglose(detailDesgloseAnterior, d.key, d.value)) }})</span>
                                            </td>
                                            <td class="px-3 py-1.5 text-center text-content-muted">
                                                {{ qtyFromDesglose(detailDesgloseNuevo, d.key) }}
                                                <span class="text-[10px]">({{ fmt(subtotalFromDesglose(detailDesgloseNuevo, d.key, d.value)) }})</span>
                                            </td>
                                            <td class="px-3 py-1.5 text-right font-bold"
                                                :class="qtyFromDesglose(detailDesgloseAnterior, d.key) - qtyFromDesglose(detailDesgloseNuevo, d.key) !== 0 ? 'text-amber-600' : 'text-content-muted'">
                                                {{ qtyFromDesglose(detailDesgloseAnterior, d.key) - qtyFromDesglose(detailDesgloseNuevo, d.key) }}
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot class="bg-gray-50 text-xs font-bold dark:bg-gray-900/50">
                                        <tr>
                                            <td class="px-3 py-2 text-content-primary dark:text-white">TOTAL</td>
                                            <td class="px-3 py-2 text-center text-content-muted">{{ fmt(detailOldTotal) }}</td>
                                            <td class="px-3 py-2 text-center text-content-muted">{{ fmt(detailNewTotal) }}</td>
                                            <td class="px-3 py-2 text-right"
                                                :class="detailOldTotal - detailNewTotal !== 0 ? 'text-amber-600' : 'text-content-muted'">
                                                {{ fmt(detailOldTotal - detailNewTotal) }}
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <p class="text-xs text-content-muted italic">
                                Justificación: {{ detailItem?.detalle }}
                            </p>
                        </template>

                        <!-- Otros tipos: detalle textual -->
                        <template v-else>
                            <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900/50">
                                <p class="whitespace-pre-wrap text-sm text-content-secondary">
                                    {{ detailItem?.detalle }}
                                </p>
                            </div>
                            <p v-if="detailItem?.producto_afectado" class="text-xs text-content-muted">
                                Producto afectado: <strong>{{ detailItem.producto_afectado }}</strong>
                            </p>
                        </template>

                        <!-- Nota del admin (si existe) -->
                        <div v-if="detailItem?.nota_admin" class="rounded-xl bg-primary-50 p-3 dark:bg-primary-900/10">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-primary-600 dark:text-primary-400">Nota del administrador</p>
                            <p class="mt-1 text-sm text-content-primary dark:text-white">{{ detailItem.nota_admin }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- ═══ Modal 2: Seguimiento Admin ═══ -->
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="showNoteModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4 backdrop-blur-sm"
                @click.self="closeNote"
            >
                <div class="w-full max-w-lg rounded-2xl bg-white shadow-xl dark:bg-surface-dark">
                    <div class="flex items-center justify-between border-b border-gray-100 px-5 py-3 dark:border-gray-800">
                        <h3 class="font-display text-sm font-bold text-content-primary dark:text-white">
                            Agregar Nota — {{ accionLabel[noteItem?.tipo_accion] ?? '' }}
                        </h3>
                        <button @click="closeNote"
                            class="rounded-lg p-1 transition-colors hover:bg-gray-100 dark:hover:bg-gray-800">
                            <X class="h-4 w-4 text-content-muted" />
                        </button>
                    </div>
                    <div class="space-y-4 p-5">
                        <p class="text-xs text-content-muted">
                            Revisando: <strong>{{ noteItem?.user?.name }}</strong> — {{ formatDateTime(noteItem?.created_at) }}
                        </p>
                        <p class="text-xs text-content-secondary leading-relaxed">
                            {{ noteItem?.detalle }}
                        </p>
                        <textarea v-model="noteText"
                            class="w-full resize-none rounded-xl border border-gray-200 bg-gray-50 p-3 text-sm outline-none transition-colors focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white dark:focus:border-primary-400"
                            rows="4" placeholder="Escribe aquí el resultado de tu revisión..."></textarea>
                        <div class="flex gap-2">
                            <button @click="closeNote"
                                class="flex-1 rounded-xl border border-gray-200 py-2.5 text-xs font-bold text-content-secondary transition-colors hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800">
                                Cancelar
                            </button>
                            <button @click="saveNote" :disabled="noteSaving"
                                class="flex flex-1 items-center justify-center gap-1.5 rounded-xl bg-primary-600 py-2.5 text-xs font-bold text-white shadow-sm transition-colors hover:bg-primary-700 disabled:opacity-50">
                                <template v-if="noteSaving">
                                    <span class="inline-block h-3.5 w-3.5 animate-spin rounded-full border-2 border-white/30 border-t-white"></span>
                                    Guardando...
                                </template>
                                <template v-else>
                                    <Check class="h-4 w-4" />
                                    Guardar y Marcar Revisado
                                </template>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </AdminLayout>
</template>
