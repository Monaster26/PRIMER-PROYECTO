<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import {
    ReceiptText, Plus, Trash2, Pencil, X, Check,
    TrendingDown, Wallet, ArrowRightLeft, Filter
} from 'lucide-vue-next';

const props = defineProps<{
    expenses: any;
    providers: { id: number; name: string }[];
    totals: { total_cash: number; total_transfer: number; grand_total: number };
    categories: string[];
    filters: { from?: string; to?: string; category?: string };
}>();

// ─── Filters ─────────────────────────────────────────────────────
const filterForm = useForm({
    from:     props.filters.from     ?? '',
    to:       props.filters.to       ?? '',
    category: props.filters.category ?? '',
});

function applyFilters() {
    filterForm.get(route('expenses.index'), { preserveState: true, preserveScroll: true });
}

function clearFilters() {
    filterForm.from = ''; filterForm.to = ''; filterForm.category = '';
    applyFilters();
}

// ─── New Expense Form ─────────────────────────────────────────────
const showForm = ref(false);
const editingId = ref<number | null>(null);

const form = useForm({
    provider_id:    null as number | null,
    category:       '',
    cash_spent:     0,
    transfer_spent: 0,
    date:           new Date().toISOString().split('T')[0],
});

function openNew() { form.reset(); editingId.value = null; showForm.value = true; }
function openEdit(exp: any) {
    form.provider_id    = exp.provider_id;
    form.category       = exp.category;
    form.cash_spent     = exp.cash_spent;
    form.transfer_spent = exp.transfer_spent;
    form.date           = exp.date;
    editingId.value     = exp.id;
    showForm.value      = true;
}
function closeForm() { showForm.value = false; editingId.value = null; form.reset(); }

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
const fmt = (v: number) => '$' + (v / 100).toLocaleString('es-CO', { minimumFractionDigits: 0 });
</script>

<template>
    <Head title="Gastos" />
    <AdminLayout>
        <template #title>
            <h1 class="text-xl font-display font-bold text-content-primary dark:text-white">
                Registro de Gastos
            </h1>
        </template>

        <!-- ── KPI Cards ───────────────────────────────────────────── -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-8">
            <div class="bg-white dark:bg-surface-dark rounded-3xl p-5 border border-gray-100 dark:border-gray-800 shadow-sm">
                <div class="flex items-center gap-3 mb-2">
                    <div class="bg-red-50 dark:bg-red-900/20 p-2.5 rounded-2xl">
                        <TrendingDown class="w-5 h-5 text-danger" />
                    </div>
                    <p class="text-sm font-semibold text-content-secondary dark:text-gray-400">Total Egresos</p>
                </div>
                <h3 class="text-2xl font-extrabold font-display text-content-primary dark:text-white">
                    {{ fmt(totals?.grand_total ?? 0) }}
                </h3>
            </div>
            <div class="bg-white dark:bg-surface-dark rounded-3xl p-5 border border-gray-100 dark:border-gray-800 shadow-sm">
                <div class="flex items-center gap-3 mb-2">
                    <div class="bg-amber-50 dark:bg-amber-900/20 p-2.5 rounded-2xl">
                        <Wallet class="w-5 h-5 text-amber-500" />
                    </div>
                    <p class="text-sm font-semibold text-content-secondary dark:text-gray-400">En Efectivo</p>
                </div>
                <h3 class="text-2xl font-extrabold font-display text-content-primary dark:text-white">
                    {{ fmt(totals?.total_cash ?? 0) }}
                </h3>
            </div>
            <div class="bg-white dark:bg-surface-dark rounded-3xl p-5 border border-gray-100 dark:border-gray-800 shadow-sm">
                <div class="flex items-center gap-3 mb-2">
                    <div class="bg-blue-50 dark:bg-blue-900/20 p-2.5 rounded-2xl">
                        <ArrowRightLeft class="w-5 h-5 text-blue-500" />
                    </div>
                    <p class="text-sm font-semibold text-content-secondary dark:text-gray-400">Por Transferencia</p>
                </div>
                <h3 class="text-2xl font-extrabold font-display text-content-primary dark:text-white">
                    {{ fmt(totals?.total_transfer ?? 0) }}
                </h3>
            </div>
        </div>

        <!-- ── Toolbar ─────────────────────────────────────────────── -->
        <div class="bg-white dark:bg-surface-dark rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 flex flex-wrap items-center gap-3">
                <ReceiptText class="w-5 h-5 text-primary-500" />
                <h2 class="font-bold text-content-primary dark:text-white flex-1">Gastos Registrados</h2>

                <!-- Filters -->
                <input type="date" v-model="filterForm.from" @change="applyFilters"
                    class="text-sm border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 bg-gray-50 dark:bg-gray-900 text-content-primary dark:text-white" />
                <input type="date" v-model="filterForm.to" @change="applyFilters"
                    class="text-sm border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 bg-gray-50 dark:bg-gray-900 text-content-primary dark:text-white" />
                <select v-model="filterForm.category" @change="applyFilters"
                    class="text-sm border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 bg-gray-50 dark:bg-gray-900 text-content-primary dark:text-white">
                    <option value="">Todas las categorías</option>
                    <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
                </select>
                <button @click="clearFilters" class="p-2 rounded-xl text-content-muted hover:text-danger transition-colors">
                    <X class="w-4 h-4" />
                </button>

                <button @click="openNew"
                    class="flex items-center gap-2 bg-primary-500 hover:bg-primary-600 text-white text-sm font-bold px-4 py-2 rounded-2xl transition-colors shadow-sm">
                    <Plus class="w-4 h-4" /> Nuevo Gasto
                </button>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 dark:bg-gray-900/50 text-xs uppercase tracking-wider text-content-muted dark:text-gray-500">
                        <tr>
                            <th class="px-6 py-3 font-bold">Fecha</th>
                            <th class="px-6 py-3 font-bold">Proveedor</th>
                            <th class="px-6 py-3 font-bold">Categoría</th>
                            <th class="px-6 py-3 font-bold text-right">Efectivo</th>
                            <th class="px-6 py-3 font-bold text-right">Transferencia</th>
                            <th class="px-6 py-3 font-bold text-right">Total</th>
                            <th class="px-6 py-3 font-bold text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <tr v-if="!expenses.data?.length">
                            <td colspan="7" class="px-6 py-12 text-center text-sm text-content-muted dark:text-gray-500">
                                No hay gastos registrados. ¡Registra el primero!
                            </td>
                        </tr>
                        <tr v-for="exp in expenses.data" :key="exp.id"
                            class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors group">
                            <td class="px-6 py-4 text-sm text-content-primary dark:text-white">{{ exp.date }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-content-primary dark:text-white">
                                {{ exp.provider?.name ?? '—' }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 bg-gray-100 dark:bg-gray-800 rounded-lg text-xs font-bold text-content-secondary dark:text-gray-400">
                                    {{ exp.category }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-right text-amber-600 dark:text-amber-400 font-medium">
                                {{ fmt(exp.cash_spent) }}
                            </td>
                            <td class="px-6 py-4 text-sm text-right text-blue-600 dark:text-blue-400 font-medium">
                                {{ fmt(exp.transfer_spent) }}
                            </td>
                            <td class="px-6 py-4 text-sm text-right font-bold text-danger">
                                {{ fmt(exp.total_expense) }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button @click="openEdit(exp)"
                                        class="p-2 rounded-xl hover:bg-blue-50 dark:hover:bg-blue-900/20 text-blue-500 transition-colors">
                                        <Pencil class="w-4 h-4" />
                                    </button>
                                    <button @click="deleteExpense(exp.id)"
                                        class="p-2 rounded-xl hover:bg-red-50 dark:hover:bg-red-900/20 text-danger transition-colors">
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="expenses.last_page > 1"
                class="px-6 py-4 border-t border-gray-100 dark:border-gray-800 flex justify-between items-center text-sm text-content-muted dark:text-gray-500">
                <span>Página {{ expenses.current_page }} de {{ expenses.last_page }}</span>
                <div class="flex gap-2">
                    <a v-if="expenses.prev_page_url" :href="expenses.prev_page_url"
                        class="px-3 py-1.5 rounded-lg bg-gray-50 dark:bg-gray-900 hover:bg-gray-100 font-bold transition-colors">←</a>
                    <a v-if="expenses.next_page_url" :href="expenses.next_page_url"
                        class="px-3 py-1.5 rounded-lg bg-gray-50 dark:bg-gray-900 hover:bg-gray-100 font-bold transition-colors">→</a>
                </div>
            </div>
        </div>

        <!-- ── Slide-in Form Modal ──────────────────────────────────── -->
        <Transition enter-active-class="transition duration-200 ease-out"
                    enter-from-class="opacity-0" enter-to-class="opacity-100"
                    leave-active-class="transition duration-150 ease-in"
                    leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="showForm" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm flex items-center justify-center p-4">
                <div class="bg-white dark:bg-surface-dark rounded-3xl shadow-xl w-full max-w-md p-6 relative">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="font-display font-bold text-lg text-content-primary dark:text-white">
                            {{ editingId ? 'Editar Gasto' : 'Nuevo Gasto' }}
                        </h3>
                        <button @click="closeForm" class="p-2 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                            <X class="w-5 h-5 text-content-muted" />
                        </button>
                    </div>

                    <form @submit.prevent="submitForm" class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-content-muted dark:text-gray-400 uppercase tracking-wider mb-1">Proveedor</label>
                            <select v-model="form.provider_id" required
                                class="w-full border border-gray-200 dark:border-gray-700 rounded-2xl px-4 py-2.5 bg-gray-50 dark:bg-gray-900 text-content-primary dark:text-white text-sm">
                                <option :value="null" disabled>Seleccionar proveedor...</option>
                                <option v-for="p in providers" :key="p.id" :value="p.id">{{ p.name }}</option>
                            </select>
                            <p v-if="form.errors.provider_id" class="text-xs text-danger mt-1">{{ form.errors.provider_id }}</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-content-muted dark:text-gray-400 uppercase tracking-wider mb-1">Categoría</label>
                            <input v-model="form.category" type="text" list="cat-list" required placeholder="ej: transporte, arriendo..."
                                class="w-full border border-gray-200 dark:border-gray-700 rounded-2xl px-4 py-2.5 bg-gray-50 dark:bg-gray-900 text-content-primary dark:text-white text-sm" />
                            <datalist id="cat-list">
                                <option v-for="c in categories" :key="c" :value="c" />
                            </datalist>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-content-muted dark:text-gray-400 uppercase tracking-wider mb-1">Efectivo (COP)</label>
                                <input v-model.number="form.cash_spent" type="number" min="0" step="100"
                                    class="w-full border border-gray-200 dark:border-gray-700 rounded-2xl px-4 py-2.5 bg-gray-50 dark:bg-gray-900 text-content-primary dark:text-white text-sm" />
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-content-muted dark:text-gray-400 uppercase tracking-wider mb-1">Transferencia (COP)</label>
                                <input v-model.number="form.transfer_spent" type="number" min="0" step="100"
                                    class="w-full border border-gray-200 dark:border-gray-700 rounded-2xl px-4 py-2.5 bg-gray-50 dark:bg-gray-900 text-content-primary dark:text-white text-sm" />
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-content-muted dark:text-gray-400 uppercase tracking-wider mb-1">Fecha</label>
                            <input v-model="form.date" type="date" required
                                class="w-full border border-gray-200 dark:border-gray-700 rounded-2xl px-4 py-2.5 bg-gray-50 dark:bg-gray-900 text-content-primary dark:text-white text-sm" />
                        </div>

                        <div class="flex gap-3 pt-2">
                            <button type="button" @click="closeForm"
                                class="flex-1 py-2.5 rounded-2xl border border-gray-200 dark:border-gray-700 text-sm font-bold text-content-secondary hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                Cancelar
                            </button>
                            <button type="submit" :disabled="form.processing"
                                class="flex-1 py-2.5 rounded-2xl bg-primary-500 hover:bg-primary-600 text-white text-sm font-bold transition-colors shadow-sm flex items-center justify-center gap-2">
                                <Check class="w-4 h-4" />
                                {{ editingId ? 'Actualizar' : 'Guardar' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>
    </AdminLayout>
</template>
