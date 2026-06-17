<script setup lang="ts">
import { formatDate } from '@/helpers/format';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import {
    ArrowUpDown,
    CalendarRange,
    Check,
    DollarSign,
    Landmark,
    Pencil,
    Plus,
    ReceiptText,
    Trash2,
    X,
} from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    expenses: {
        data: any[];
        links: any[];
        current_page: number;
        last_page: number;
        prev_page_url: string | null;
        next_page_url: string | null;
    };
    suppliers: { id: number; company_name: string }[];
    month: number;
    year: number;
    month_name: string;
    summary: { total: number; cash: number; transfer: number; count: number };
}>();

const months = [
    'Enero',
    'Febrero',
    'Marzo',
    'Abril',
    'Mayo',
    'Junio',
    'Julio',
    'Agosto',
    'Septiembre',
    'Octubre',
    'Noviembre',
    'Diciembre',
];

const selectedMonth = ref(props.month);
const selectedYear = ref(props.year);
const showForm = ref(false);
const editingId = ref<number | null>(null);

const form = useForm({
    date: new Date().toISOString().split('T')[0],
    supplier_id: null as number | null,
    category: '',
    cash_spent: 0,
    transfer_spent: 0,
});

function loadMonth() {
    router.get(
        route('admin.gastos.index'),
        {
            month: selectedMonth.value,
            year: selectedYear.value,
        },
        { preserveState: true, preserveScroll: true },
    );
}

function openNew() {
    form.reset();
    form.date = new Date().toISOString().split('T')[0];
    editingId.value = null;
    showForm.value = true;
}
function openEdit(e: any) {
    form.date = e.date;
    form.supplier_id = e.supplier_id;
    form.category = e.category;
    form.cash_spent = e.cash_spent ?? 0;
    form.transfer_spent = e.transfer_spent ?? 0;
    editingId.value = e.id;
    showForm.value = true;
}
function closeForm() {
    showForm.value = false;
    editingId.value = null;
    form.reset();
}
function submitForm() {
    if (editingId.value) {
        form.put(route('admin.gastos.update', {
            expense: editingId.value,
            month: selectedMonth.value,
            year: selectedYear.value,
        }), {
            onSuccess: closeForm,
        });
    } else {
        form.post(route('admin.gastos.store'), { onSuccess: closeForm });
    }
}
function deleteExpense(id: number) {
    if (!confirm('¿Eliminar este gasto?')) return;
    router.delete(route('admin.gastos.destroy', id), {
        data: { month: selectedMonth.value, year: selectedYear.value },
        preserveScroll: true,
    });
}

const fmt = (v: number) =>
    '$' + v.toLocaleString('es-CO', { minimumFractionDigits: 0 });
</script>

<template>
    <Head title="Gastos" />
    <AdminLayout>
        <template #title>
            <h1
                class="font-display text-xl font-bold text-content-primary dark:text-white"
            >
                Gastos
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
                <select
                    v-model="selectedMonth"
                    @change="loadMonth"
                    class="rounded-xl border border-gray-200 bg-gray-50 py-2 pl-3 pr-8 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                >
                    <option v-for="(m, i) in months" :key="i" :value="i + 1">
                        {{ m }}
                    </option>
                </select>
                <select
                    v-model="selectedYear"
                    @change="loadMonth"
                    class="rounded-xl border border-gray-200 bg-gray-50 py-2 pl-3 pr-8 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                >
                    <option
                        v-for="y in [year - 1, year, year + 1]"
                        :key="y"
                        :value="y"
                    >
                        {{ y }}
                    </option>
                </select>
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
                        Total Gastos
                    </p>
                    <p
                        class="text-xl font-bold text-content-primary dark:text-white"
                    >
                        {{ fmt(summary.total) }}
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
                    class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-2xl bg-success/10"
                >
                    <Landmark class="h-6 w-6 text-success" />
                </div>
                <div>
                    <p
                        class="text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                    >
                        Caja
                    </p>
                    <p
                        class="text-xl font-bold text-content-primary dark:text-white"
                    >
                        {{ fmt(summary.cash) }}
                    </p>
                </div>
            </div>
            <div
                class="flex items-center gap-4 rounded-3xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-surface-dark"
            >
                <div
                    class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-2xl bg-primary-500/10"
                >
                    <ArrowUpDown class="h-6 w-6 text-primary-500" />
                </div>
                <div>
                    <p
                        class="text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                    >
                        Transferencia
                    </p>
                    <p
                        class="text-xl font-bold text-content-primary dark:text-white"
                    >
                        {{ fmt(summary.transfer) }}
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
                <ReceiptText class="h-5 w-5 text-primary-500" />
                <h2
                    class="flex-1 font-bold text-content-primary dark:text-white"
                >
                    Registro del Mes
                </h2>
                <button
                    @click="openNew"
                    class="flex items-center gap-2 rounded-2xl bg-primary-500 px-4 py-2 text-sm font-bold text-white shadow-sm transition-colors hover:bg-primary-600"
                >
                    <Plus class="h-4 w-4" /> Nuevo Gasto
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead
                        class="bg-gray-50 text-xs uppercase tracking-wider text-content-muted dark:bg-gray-900/50 dark:text-gray-500"
                    >
                        <tr>
                            <th class="px-6 py-3 font-bold">Fecha</th>
                            <th class="px-6 py-3 font-bold">Categoría</th>
                            <th class="px-6 py-3 font-bold">Proveedor</th>
                            <th class="px-6 py-3 text-right font-bold">Caja</th>
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
                                No hay gastos registrados en {{ month_name }}
                                {{ year }}.
                            </td>
                        </tr>
                        <tr
                            v-for="e in expenses.data"
                            :key="e.id"
                            class="group transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/50"
                        >
                            <td
                                class="px-6 py-4 text-sm text-content-primary dark:text-white"
                            >
                                {{ formatDate(e.date) }}
                            </td>
                            <td
                                class="px-6 py-4 text-sm text-content-secondary"
                            >
                                {{ e.category }}
                            </td>
                            <td
                                class="px-6 py-4 text-sm text-content-secondary"
                            >
                                {{ e.supplier?.company_name || '—' }}
                            </td>
                            <td
                                class="px-6 py-4 text-right text-sm font-bold text-danger"
                            >
                                {{ e.cash_spent ? fmt(e.cash_spent) : '—' }}
                            </td>
                            <td
                                class="px-6 py-4 text-right text-sm font-bold text-content-secondary"
                            >
                                {{
                                    e.transfer_spent
                                        ? fmt(e.transfer_spent)
                                        : '—'
                                }}
                            </td>
                            <td
                                class="px-6 py-4 text-right text-sm font-bold text-content-primary"
                            >
                                {{ fmt(e.total_expense) }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div
                                    class="flex items-center justify-end gap-2 opacity-0 transition-opacity group-hover:opacity-100"
                                >
                                    <button
                                        @click="openEdit(e)"
                                        class="rounded-xl p-2 text-blue-500 transition-colors hover:bg-blue-50 dark:hover:bg-blue-900/20"
                                    >
                                        <Pencil class="h-4 w-4" />
                                    </button>
                                    <button
                                        @click="deleteExpense(e.id)"
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
                        <div class="grid grid-cols-2 gap-4">
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
                            <div>
                                <label
                                    class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                                    >Proveedor</label
                                >
                                <select
                                    v-model="form.supplier_id"
                                    class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                >
                                    <option :value="null">Sin proveedor</option>
                                    <option
                                        v-for="s in suppliers"
                                        :key="s.id"
                                        :value="s.id"
                                    >
                                        {{ s.company_name }}
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label
                                class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                                >Categoría</label
                            >
                            <input
                                v-model="form.category"
                                type="text"
                                required
                                class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                            />
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                                    >Caja ($)</label
                                >
                                <input
                                    v-model.number="form.cash_spent"
                                    type="number"
                                    min="0"
                                    class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                />
                            </div>
                            <div>
                                <label
                                    class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                                    >Transferencia ($)</label
                                >
                                <input
                                    v-model.number="form.transfer_spent"
                                    type="number"
                                    min="0"
                                    class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                />
                            </div>
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
