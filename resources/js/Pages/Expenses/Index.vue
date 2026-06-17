<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import {
    ArrowRightLeft,
    Check,
    Pencil,
    Plus,
    ReceiptText,
    Trash2,
    TrendingDown,
    Wallet,
    X,
} from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    expenses: any;
    providers: { id: number; name: string }[];
    totals: { total_cash: number; total_transfer: number; grand_total: number };
    categories: string[];
    filters: { from?: string; to?: string; category?: string };
}>();

// ─── Filters ─────────────────────────────────────────────────────
const filterForm = useForm({
    from: props.filters.from ?? '',
    to: props.filters.to ?? '',
    category: props.filters.category ?? '',
});

function applyFilters() {
    filterForm.get(route('expenses.index'), {
        preserveState: true,
        preserveScroll: true,
    });
}

function clearFilters() {
    filterForm.from = '';
    filterForm.to = '';
    filterForm.category = '';
    applyFilters();
}

// ─── New Expense Form ─────────────────────────────────────────────
const showForm = ref(false);
const editingId = ref<number | null>(null);

const form = useForm({
    provider_id: null as number | null,
    category: '',
    cash_spent: 0,
    transfer_spent: 0,
    date: new Date().toISOString().split('T')[0],
});

function openNew() {
    form.reset();
    editingId.value = null;
    showForm.value = true;
}
function openEdit(exp: any) {
    form.provider_id = exp.provider_id;
    form.category = exp.category;
    form.cash_spent = exp.cash_spent;
    form.transfer_spent = exp.transfer_spent;
    form.date = exp.date;
    editingId.value = exp.id;
    showForm.value = true;
}
function closeForm() {
    showForm.value = false;
    editingId.value = null;
    form.reset();
}

function submitForm() {
    if (editingId.value) {
        form.put(route('expenses.update', editingId.value), {
            onSuccess: closeForm,
        });
    } else {
        form.post(route('expenses.store'), {
            onSuccess: closeForm,
        });
    }
}

function deleteExpense(id: number) {
    if (!confirm('¿Eliminar este gasto?')) return;
    router.delete(route('expenses.destroy', id), { preserveScroll: true });
}

// ─── Formatters ───────────────────────────────────────────────────
const fmt = (v: number) =>
    '$' + (v / 100).toLocaleString('es-CO', { minimumFractionDigits: 0 });
</script>

<template>
    <Head title="Gastos" />
    <AdminLayout>
        <template #title>
            <h1
                class="font-display text-xl font-bold text-content-primary dark:text-white"
            >
                Registro de Gastos
            </h1>
        </template>

        <!-- ── KPI Cards ───────────────────────────────────────────── -->
        <div class="mb-8 grid grid-cols-1 gap-5 sm:grid-cols-3">
            <div
                class="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-surface-dark"
            >
                <div class="mb-2 flex items-center gap-3">
                    <div class="rounded-2xl bg-red-50 p-2.5 dark:bg-red-900/20">
                        <TrendingDown class="h-5 w-5 text-danger" />
                    </div>
                    <p
                        class="text-sm font-semibold text-content-secondary dark:text-gray-400"
                    >
                        Total Egresos
                    </p>
                </div>
                <h3
                    class="font-display text-2xl font-extrabold text-content-primary dark:text-white"
                >
                    {{ fmt(totals?.grand_total ?? 0) }}
                </h3>
            </div>
            <div
                class="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-surface-dark"
            >
                <div class="mb-2 flex items-center gap-3">
                    <div
                        class="rounded-2xl bg-amber-50 p-2.5 dark:bg-amber-900/20"
                    >
                        <Wallet class="h-5 w-5 text-amber-500" />
                    </div>
                    <p
                        class="text-sm font-semibold text-content-secondary dark:text-gray-400"
                    >
                        En Efectivo
                    </p>
                </div>
                <h3
                    class="font-display text-2xl font-extrabold text-content-primary dark:text-white"
                >
                    {{ fmt(totals?.total_cash ?? 0) }}
                </h3>
            </div>
            <div
                class="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-surface-dark"
            >
                <div class="mb-2 flex items-center gap-3">
                    <div
                        class="rounded-2xl bg-blue-50 p-2.5 dark:bg-blue-900/20"
                    >
                        <ArrowRightLeft class="h-5 w-5 text-blue-500" />
                    </div>
                    <p
                        class="text-sm font-semibold text-content-secondary dark:text-gray-400"
                    >
                        Por Transferencia
                    </p>
                </div>
                <h3
                    class="font-display text-2xl font-extrabold text-content-primary dark:text-white"
                >
                    {{ fmt(totals?.total_transfer ?? 0) }}
                </h3>
            </div>
        </div>

        <!-- ── Toolbar ─────────────────────────────────────────────── -->
        <div
            class="mb-6 overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-surface-dark"
        >
            <div
                class="flex flex-wrap items-center gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-800"
            >
                <ReceiptText class="h-5 w-5 text-primary-500" />
                <h2
                    class="flex-1 font-bold text-content-primary dark:text-white"
                >
                    Gastos Registrados
                </h2>

                <!-- Filters -->
                <input
                    type="date"
                    v-model="filterForm.from"
                    @change="applyFilters"
                    class="rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                />
                <input
                    type="date"
                    v-model="filterForm.to"
                    @change="applyFilters"
                    class="rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                />
                <select
                    v-model="filterForm.category"
                    @change="applyFilters"
                    class="rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                >
                    <option value="">Todas las categorías</option>
                    <option v-for="cat in categories" :key="cat" :value="cat">
                        {{ cat }}
                    </option>
                </select>
                <button
                    @click="clearFilters"
                    class="rounded-xl p-2 text-content-muted transition-colors hover:text-danger"
                >
                    <X class="h-4 w-4" />
                </button>

                <button
                    @click="openNew"
                    class="flex items-center gap-2 rounded-2xl bg-primary-500 px-4 py-2 text-sm font-bold text-white shadow-sm transition-colors hover:bg-primary-600"
                >
                    <Plus class="h-4 w-4" /> Nuevo Gasto
                </button>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead
                        class="bg-gray-50 text-xs uppercase tracking-wider text-content-muted dark:bg-gray-900/50 dark:text-gray-500"
                    >
                        <tr>
                            <th class="px-6 py-3 font-bold">Fecha</th>
                            <th class="px-6 py-3 font-bold">Proveedor</th>
                            <th class="px-6 py-3 font-bold">Categoría</th>
                            <th class="px-6 py-3 text-right font-bold">
                                Efectivo
                            </th>
                            <th class="px-6 py-3 text-right font-bold">
                                Transferencia
                            </th>
                            <th class="px-6 py-3 text-right font-bold">
                                Total
                            </th>
                            <th class="px-6 py-3 text-right font-bold">
                                Acciones
                            </th>
                        </tr>
                    </thead>
                    <tbody
                        class="divide-y divide-gray-100 dark:divide-gray-800"
                    >
                        <tr v-if="!expenses.data?.length">
                            <td
                                colspan="7"
                                class="px-6 py-12 text-center text-sm text-content-muted dark:text-gray-500"
                            >
                                No hay gastos registrados. ¡Registra el primero!
                            </td>
                        </tr>
                        <tr
                            v-for="exp in expenses.data"
                            :key="exp.id"
                            class="group transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/50"
                        >
                            <td
                                class="px-6 py-4 text-sm text-content-primary dark:text-white"
                            >
                                {{ exp.date }}
                            </td>
                            <td
                                class="px-6 py-4 text-sm font-medium text-content-primary dark:text-white"
                            >
                                {{ exp.provider?.name ?? '—' }}
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    class="rounded-lg bg-gray-100 px-2.5 py-1 text-xs font-bold text-content-secondary dark:bg-gray-800 dark:text-gray-400"
                                >
                                    {{ exp.category }}
                                </span>
                            </td>
                            <td
                                class="px-6 py-4 text-right text-sm font-medium text-amber-600 dark:text-amber-400"
                            >
                                {{ fmt(exp.cash_spent) }}
                            </td>
                            <td
                                class="px-6 py-4 text-right text-sm font-medium text-blue-600 dark:text-blue-400"
                            >
                                {{ fmt(exp.transfer_spent) }}
                            </td>
                            <td
                                class="px-6 py-4 text-right text-sm font-bold text-danger"
                            >
                                {{ fmt(exp.total_expense) }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div
                                    class="flex items-center justify-end gap-2 opacity-0 transition-opacity group-hover:opacity-100"
                                >
                                    <button
                                        @click="openEdit(exp)"
                                        class="rounded-xl p-2 text-blue-500 transition-colors hover:bg-blue-50 dark:hover:bg-blue-900/20"
                                    >
                                        <Pencil class="h-4 w-4" />
                                    </button>
                                    <button
                                        @click="deleteExpense(exp.id)"
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

            <!-- Pagination -->
            <div
                v-if="expenses.last_page > 1"
                class="flex items-center justify-between border-t border-gray-100 px-6 py-4 text-sm text-content-muted dark:border-gray-800 dark:text-gray-500"
            >
                <span
                    >Página {{ expenses.current_page }} de
                    {{ expenses.last_page }}</span
                >
                <div class="flex gap-2">
                    <a
                        v-if="expenses.prev_page_url"
                        :href="expenses.prev_page_url"
                        class="rounded-lg bg-gray-50 px-3 py-1.5 font-bold transition-colors hover:bg-gray-100 dark:bg-gray-900"
                        >←</a
                    >
                    <a
                        v-if="expenses.next_page_url"
                        :href="expenses.next_page_url"
                        class="rounded-lg bg-gray-50 px-3 py-1.5 font-bold transition-colors hover:bg-gray-100 dark:bg-gray-900"
                        >→</a
                    >
                </div>
            </div>
        </div>

        <!-- ── Slide-in Form Modal ──────────────────────────────────── -->
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
                            {{ editingId ? 'Editar Gasto' : 'Nuevo Gasto' }}
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
                                >Proveedor</label
                            >
                            <select
                                v-model="form.provider_id"
                                required
                                class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                            >
                                <option :value="null" disabled>
                                    Seleccionar proveedor...
                                </option>
                                <option
                                    v-for="p in providers"
                                    :key="p.id"
                                    :value="p.id"
                                >
                                    {{ p.name }}
                                </option>
                            </select>
                            <p
                                v-if="form.errors.provider_id"
                                class="mt-1 text-xs text-danger"
                            >
                                {{ form.errors.provider_id }}
                            </p>
                        </div>

                        <div>
                            <label
                                class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                                >Categoría</label
                            >
                            <input
                                v-model="form.category"
                                type="text"
                                list="cat-list"
                                required
                                placeholder="ej: transporte, arriendo..."
                                class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                            />
                            <datalist id="cat-list">
                                <option
                                    v-for="c in categories"
                                    :key="c"
                                    :value="c"
                                />
                            </datalist>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                                    >Efectivo (COP)</label
                                >
                                <input
                                    v-model.number="form.cash_spent"
                                    type="number"
                                    min="0"
                                    step="100"
                                    class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                />
                            </div>
                            <div>
                                <label
                                    class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                                    >Transferencia (COP)</label
                                >
                                <input
                                    v-model.number="form.transfer_spent"
                                    type="number"
                                    min="0"
                                    step="100"
                                    class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                />
                            </div>
                        </div>

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
                                {{ editingId ? 'Actualizar' : 'Guardar' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>
    </AdminLayout>
</template>
