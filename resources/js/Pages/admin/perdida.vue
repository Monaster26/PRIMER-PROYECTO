<script setup lang="ts">
import DateFilter from '@/Components/DateFilter.vue';
import { formatDate } from '@/helpers/format';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import {
    CalendarRange,
    Check,
    DollarSign,
    Hash,
    Package,
    Pencil,
    Plus,
    Trash2,
    TrendingDown,
    X,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps<{
    losses: {
        data: any[];
        links: any[];
        current_page: number;
        last_page: number;
        prev_page_url: string | null;
        next_page_url: string | null;
    };
    products: { id: number; name: string; sku: string; stock: number }[];
    month: number;
    year: number;
    month_name: string;
    summary: { total_value: number; total_quantity: number; count: number };
}>();

const todayStr = new Date().toISOString().slice(0, 10);
const filterDate = ref<string>(
    `${props.year}-${String(props.month).padStart(2, '0')}-01`,
);
const showForm = ref(false);
const searchQuery = ref('');
const showDropdown = ref(false);

const form = useForm({
    date: new Date().toISOString().split('T')[0],
    product_id: null as number | null,
    quantity: 1,
    reason: '',
});

const showEditModal = ref(false);
const editItem = ref<any>(null);
const showConfirmDelete = ref(false);
const deleteTarget = ref<number | null>(null);
const deleting = ref(false);

const editForm = useForm({
    date: '',
    quantity: 1,
    reason: '',
});

const filteredProducts = computed(() => {
    const q = searchQuery.value.toLowerCase().trim();
    if (!q) return [];
    return props.products.filter(
        (p) =>
            p.name.toLowerCase().includes(q) || p.sku.toLowerCase().includes(q),
    );
});

function selectProduct(product: {
    id: number;
    name: string;
    sku: string;
    stock: number;
}) {
    form.product_id = product.id;
    searchQuery.value = product.name;
    showDropdown.value = false;
}

function onSearchInput() {
    if (form.product_id !== null) {
        form.product_id = null;
    }
    showDropdown.value = true;
}

function handleBlur() {
    setTimeout(() => {
        showDropdown.value = false;
    }, 200);
}

function loadMonth() {
    const [y, m] = filterDate.value.split('-');
    router.get(
        route('admin.perdida.index'),
        {
            month: parseInt(m),
            year: parseInt(y),
        },
        { preserveState: true, preserveScroll: true },
    );
}

function onDatePicked(payload: { dia: number; mes: number; anio: number }) {
    const m = String(payload.mes).padStart(2, '0');
    filterDate.value = `${payload.anio}-${m}-01`;
    loadMonth();
}

function openNew() {
    form.reset();
    form.date = new Date().toISOString().split('T')[0];
    searchQuery.value = '';
    showForm.value = true;
}
function closeForm() {
    showForm.value = false;
    form.reset();
    searchQuery.value = '';
}
function submitForm() {
    form.post(route('admin.perdida.store'), { onSuccess: closeForm });
}
function getFilterParams() {
    const [y, m] = filterDate.value.split('-');
    return { month: parseInt(m), year: parseInt(y) };
}
function confirmDelete(id: number) {
    deleteTarget.value = id;
    showConfirmDelete.value = true;
}
function executeDelete() {
    if (deleteTarget.value === null || deleting.value) return;
    deleting.value = true;
    router.delete(
        route('admin.perdida.destroy', {
            loss: deleteTarget.value,
            ...getFilterParams(),
        }),
        {
            preserveScroll: true,
            onSuccess: () => {
                const [y, m] = filterDate.value.split('-');
                router.visit(
                    route('admin.perdida.index', {
                        month: parseInt(m),
                        year: parseInt(y),
                    }),
                    { preserveScroll: true },
                );
            },
            onFinish: () => {
                deleting.value = false;
            },
            onError: () => {
                alert('Error al eliminar la pérdida. Intenta de nuevo.');
            },
        },
    );
    showConfirmDelete.value = false;
    deleteTarget.value = null;
}
function openEdit(loss: any) {
    editItem.value = loss;
    editForm.date = loss.date;
    editForm.quantity = loss.quantity;
    editForm.reason = loss.reason || '';
    showEditModal.value = true;
}
function closeEdit() {
    showEditModal.value = false;
    editItem.value = null;
    editForm.reset();
}
function submitUpdate() {
    if (!editItem.value) return;
    editForm.put(
        route('admin.perdida.update', {
            loss: editItem.value.id,
            ...getFilterParams(),
        }),
        {
            onSuccess: () => {
                const [y, m] = filterDate.value.split('-');
                router.visit(
                    route('admin.perdida.index', {
                        month: parseInt(m),
                        year: parseInt(y),
                    }),
                    { preserveScroll: true },
                );
                closeEdit();
            },
        },
    );
}

const fmt = (v: number) =>
    '$' + v.toLocaleString('es-CO', { minimumFractionDigits: 0 });
</script>

<template>
    <Head title="Pérdidas" />
    <AdminLayout>
        <template #title>
            <h1
                class="font-display text-xl font-bold text-content-primary dark:text-white"
            >
                Pérdidas de Inventario
            </h1>
        </template>

        <div
            class="mb-6 overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-surface-dark"
        >
            <div
                class="flex flex-wrap items-center gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-800"
            >
                <CalendarRange class="h-5 w-5 text-primary-500" />
                <h2 class="font-bold text-content-primary dark:text-white">
                    Período
                </h2>
                <DateFilter
                    v-model="filterDate"
                    label="Mes / Año"
                    @select="onDatePicked"
                />
                <span
                    class="text-sm font-bold text-content-primary dark:text-white"
                    >{{ month_name }} {{ year }}</span
                >
            </div>
        </div>

        <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div
                class="flex items-center gap-4 rounded-3xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-surface-dark"
            >
                <div
                    class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-2xl bg-danger/10"
                >
                    <DollarSign class="h-6 w-6 text-danger" />
                </div>
                <div>
                    <p
                        class="text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                    >
                        Valor Total Perdido
                    </p>
                    <p
                        class="text-xl font-bold text-content-primary dark:text-white"
                    >
                        {{ fmt(summary.total_value) }}
                    </p>
                    <p class="text-[11px] text-content-muted">
                        {{ summary.count }} registros
                    </p>
                </div>
            </div>
            <div
                class="flex items-center gap-4 rounded-3xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-surface-dark"
            >
                <div
                    class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-2xl bg-warning/10"
                >
                    <Package class="h-6 w-6 text-warning" />
                </div>
                <div>
                    <p
                        class="text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                    >
                        Total Unidades
                    </p>
                    <p
                        class="text-xl font-bold text-content-primary dark:text-white"
                    >
                        {{ summary.total_quantity }}
                    </p>
                </div>
            </div>
            <div
                class="flex items-center gap-4 rounded-3xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-surface-dark"
            >
                <div
                    class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-2xl bg-content-muted/10"
                >
                    <Hash class="h-6 w-6 text-content-muted" />
                </div>
                <div>
                    <p
                        class="text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                    >
                        Promedio por Pérdida
                    </p>
                    <p
                        class="text-xl font-bold text-content-primary dark:text-white"
                    >
                        {{
                            summary.count > 0
                                ? fmt(
                                      Math.round(
                                          summary.total_value / summary.count,
                                      ),
                                  )
                                : '$0'
                        }}
                    </p>
                </div>
            </div>
        </div>

        <div
            class="overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-surface-dark"
        >
            <div
                class="flex flex-wrap items-center gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-800"
            >
                <TrendingDown class="h-5 w-5 text-danger" />
                <h2
                    class="flex-1 font-bold text-content-primary dark:text-white"
                >
                    Registro del Mes
                </h2>
                <button
                    @click="openNew"
                    class="flex items-center gap-2 rounded-2xl bg-primary-500 px-4 py-2 text-sm font-bold text-white shadow-sm transition-colors hover:bg-primary-600"
                >
                    <Plus class="h-4 w-4" /> Nueva Pérdida
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead
                        class="bg-gray-50 text-xs uppercase tracking-wider text-content-muted dark:bg-gray-900/50 dark:text-gray-500"
                    >
                        <tr>
                            <th class="px-6 py-3 font-bold">Fecha</th>
                            <th class="px-6 py-3 font-bold">Producto</th>
                            <th class="px-6 py-3 font-bold">SKU</th>
                            <th class="px-6 py-3 text-center font-bold">
                                Cantidad
                            </th>
                            <th class="px-6 py-3 font-bold">Motivo</th>
                            <th class="px-6 py-3 text-right font-bold">
                                Valor Total
                            </th>
                            <th class="px-6 py-3 text-right font-bold">
                                Acciones
                            </th>
                        </tr>
                    </thead>
                    <tbody
                        class="divide-y divide-gray-100 dark:divide-gray-800"
                    >
                        <tr v-if="!losses.data?.length">
                            <td
                                colspan="7"
                                class="px-6 py-12 text-center text-sm text-content-muted dark:text-gray-500"
                            >
                                No hay pérdidas registradas en {{ month_name }}
                                {{ year }}.
                            </td>
                        </tr>
                        <tr
                            v-for="l in losses.data"
                            :key="l.id"
                            class="group transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/50"
                        >
                            <td
                                class="px-6 py-4 text-sm text-content-primary dark:text-white"
                            >
                                {{ formatDate(l.date) }}
                            </td>
                            <td
                                class="px-6 py-4 text-sm font-medium text-content-primary dark:text-white"
                            >
                                {{ l.product?.name || '—' }}
                            </td>
                            <td
                                class="px-6 py-4 font-mono text-sm text-content-secondary"
                            >
                                {{ l.product?.sku || '—' }}
                            </td>
                            <td
                                class="px-6 py-4 text-center text-sm font-bold text-danger"
                            >
                                {{ l.quantity }}
                            </td>
                            <td
                                class="px-6 py-4 text-sm text-content-secondary"
                            >
                                {{ l.reason || '—' }}
                            </td>
                            <td
                                class="px-6 py-4 text-right text-sm font-bold text-danger"
                            >
                                {{ fmt(l.total_loss_value || 0) }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div
                                    class="flex items-center justify-end gap-1 opacity-0 transition-opacity group-hover:opacity-100"
                                >
                                    <button
                                        @click="openEdit(l)"
                                        class="rounded-xl p-2 text-content-muted transition-colors hover:bg-gray-100 dark:hover:bg-gray-800"
                                    >
                                        <Pencil class="h-4 w-4" />
                                    </button>
                                    <button
                                        @click="confirmDelete(l.id)"
                                        class="rounded-xl p-2 text-danger transition-colors hover:bg-red-50 dark:hover:bg-red-900/20"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div
                v-if="losses.last_page > 1"
                class="flex items-center justify-between border-t border-gray-100 px-6 py-4 text-sm text-content-muted dark:border-gray-800 dark:text-gray-500"
            >
                <span
                    >Página {{ losses.current_page }} de
                    {{ losses.last_page }}</span
                >
                <div class="flex gap-2">
                    <a
                        v-if="losses.prev_page_url"
                        :href="losses.prev_page_url"
                        class="rounded-lg bg-gray-50 px-3 py-1.5 font-bold transition-colors hover:bg-gray-100 dark:bg-gray-900"
                        >←</a
                    >
                    <a
                        v-if="losses.next_page_url"
                        :href="losses.next_page_url"
                        class="rounded-lg bg-gray-50 px-3 py-1.5 font-bold transition-colors hover:bg-gray-100 dark:bg-gray-900"
                        >→</a
                    >
                </div>
            </div>
        </div>

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
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4 backdrop-blur-sm"
            >
                <div
                    class="relative w-full max-w-md rounded-3xl bg-white p-6 shadow-xl dark:bg-surface-dark"
                >
                    <div class="mb-6 flex items-center justify-between">
                        <h3
                            class="font-display text-lg font-bold text-content-primary dark:text-white"
                        >
                            Nueva Pérdida
                        </h3>
                        <button
                            @click="closeForm"
                            class="rounded-xl p-2 transition-colors hover:bg-gray-100 dark:hover:bg-gray-800"
                        >
                            <X class="h-5 w-5 text-content-muted" />
                        </button>
                    </div>
                    <form @submit.prevent="submitForm" class="space-y-4">
                        <div>
                            <label
                                class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                                >Fecha</label
                            >
                            <input
                                v-model="form.date"
                                type="date"
                                required
                                class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                            />
                        </div>
                        <div class="relative">
                            <label
                                class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                                >Producto</label
                            >
                            <input
                                v-model="searchQuery"
                                @input="onSearchInput"
                                @focus="showDropdown = true"
                                @blur="handleBlur"
                                type="text"
                                placeholder="Buscar producto por nombre o SKU..."
                                required
                                class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                            />
                            <div
                                v-if="showDropdown && filteredProducts.length"
                                class="absolute left-0 right-0 top-full z-20 mt-1 max-h-48 overflow-y-auto rounded-xl border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-surface-dark"
                            >
                                <button
                                    v-for="p in filteredProducts"
                                    :key="p.id"
                                    @mousedown.prevent="selectProduct(p)"
                                    class="flex w-full items-center gap-2 border-b border-gray-100 px-3 py-2 text-left text-sm text-content-primary last:border-0 hover:bg-gray-50 dark:border-gray-800 dark:text-white dark:hover:bg-gray-800"
                                >
                                    <span class="font-medium">{{
                                        p.name
                                    }}</span>
                                    <span class="text-xs text-content-muted"
                                        >({{ p.sku }})</span
                                    >
                                    <span
                                        class="ml-auto text-xs"
                                        :class="
                                            p.stock < 5
                                                ? 'text-danger'
                                                : 'text-content-muted'
                                        "
                                        >Stock: {{ p.stock }}</span
                                    >
                                </button>
                            </div>
                            <div
                                v-if="
                                    showDropdown &&
                                    searchQuery &&
                                    !filteredProducts.length
                                "
                                class="absolute left-0 right-0 top-full z-20 mt-1 rounded-xl border border-gray-200 bg-white px-3 py-2 text-center text-sm text-content-muted shadow-lg dark:border-gray-700 dark:bg-surface-dark"
                            >
                                Sin resultados
                            </div>
                        </div>
                        <div>
                            <label
                                class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                                >Cantidad</label
                            >
                            <input
                                v-model.number="form.quantity"
                                type="number"
                                min="1"
                                required
                                class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                            />
                        </div>
                        <div>
                            <label
                                class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                                >Motivo</label
                            >
                            <textarea
                                v-model="form.reason"
                                rows="2"
                                class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                            ></textarea>
                        </div>
                        <div class="flex gap-3 pt-2">
                            <button
                                type="button"
                                @click="closeForm"
                                class="flex-1 rounded-2xl border border-gray-200 py-2.5 text-sm font-bold text-content-secondary transition-colors hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800"
                            >
                                Cancelar
                            </button>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="flex flex-1 items-center justify-center gap-2 rounded-2xl bg-primary-500 py-2.5 text-sm font-bold text-white shadow-sm transition-colors hover:bg-primary-600"
                            >
                                <Check class="h-4 w-4" />
                                Registrar Pérdida
                            </button>
                        </div>
                    </form>
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
                v-if="showEditModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4 backdrop-blur-sm"
            >
                <div
                    class="relative w-full max-w-md rounded-3xl bg-white p-6 shadow-xl dark:bg-surface-dark"
                >
                    <div class="mb-6 flex items-center justify-between">
                        <h3
                            class="font-display text-lg font-bold text-content-primary dark:text-white"
                        >
                            Editar Pérdida
                        </h3>
                        <button
                            @click="closeEdit"
                            class="rounded-xl p-2 transition-colors hover:bg-gray-100 dark:hover:bg-gray-800"
                        >
                            <X class="h-5 w-5 text-content-muted" />
                        </button>
                    </div>
                    <form @submit.prevent="submitUpdate" class="space-y-4">
                        <div>
                            <label
                                class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                                >Fecha</label
                            >
                            <input
                                v-model="editForm.date"
                                type="date"
                                required
                                class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                            />
                        </div>
                        <div>
                            <label
                                class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                                >Producto</label
                            >
                            <input
                                :value="editItem?.product?.name || ''"
                                type="text"
                                readonly
                                class="w-full rounded-2xl border border-gray-200 bg-gray-100 px-4 py-2.5 text-sm text-content-muted dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400"
                            />
                        </div>
                        <div>
                            <label
                                class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                                >Cantidad</label
                            >
                            <input
                                v-model.number="editForm.quantity"
                                type="number"
                                min="1"
                                required
                                class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                            />
                        </div>
                        <div>
                            <label
                                class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                                >Motivo</label
                            >
                            <textarea
                                v-model="editForm.reason"
                                rows="2"
                                class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                            ></textarea>
                        </div>
                        <div class="flex gap-3 pt-2">
                            <button
                                type="button"
                                @click="closeEdit"
                                class="flex-1 rounded-2xl border border-gray-200 py-2.5 text-sm font-bold text-content-secondary transition-colors hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800"
                            >
                                Cancelar
                            </button>
                            <button
                                type="submit"
                                :disabled="editForm.processing"
                                class="flex flex-1 items-center justify-center gap-2 rounded-2xl bg-primary-500 py-2.5 text-sm font-bold text-white shadow-sm transition-colors hover:bg-primary-600"
                            >
                                <Check class="h-4 w-4" />
                                Guardar Cambios
                            </button>
                        </div>
                    </form>
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
                v-if="showConfirmDelete"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4 backdrop-blur-sm"
            >
                <div
                    class="relative w-full max-w-sm rounded-3xl bg-white p-6 shadow-xl dark:bg-surface-dark"
                >
                    <h3
                        class="font-display text-lg font-bold text-content-primary dark:text-white"
                    >
                        Confirmar Eliminación
                    </h3>
                    <p class="mt-2 text-sm text-content-secondary">
                        ¿Eliminar este registro? Se restaurará el stock del
                        producto.
                    </p>
                    <div class="mt-6 flex gap-3">
                        <button
                            @click="
                                showConfirmDelete = false;
                                deleteTarget = null;
                            "
                            class="flex-1 rounded-2xl border border-gray-200 py-2.5 text-sm font-bold text-content-secondary transition-colors hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800"
                        >
                            Cancelar
                        </button>
                        <button
                            @click="executeDelete"
                            :disabled="deleting"
                            class="flex flex-1 items-center justify-center gap-2 rounded-2xl bg-danger py-2.5 text-sm font-bold text-white shadow-sm transition-colors hover:bg-red-600 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <Trash2 class="h-4 w-4" /> Eliminar
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </AdminLayout>
</template>
