<script setup lang="ts">
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { CreditCard, Plus, Pencil, Trash2, X, Check } from 'lucide-vue-next';
import { formatDate } from '@/helpers/format';

const props = defineProps<{
    invoices: { data: any[]; links: any[]; current_page: number; last_page: number; prev_page_url: string | null; next_page_url: string | null };
    suppliers: { id: number; company_name: string }[];
}>();

const showForm = ref(false);
const editingId = ref<number | null>(null);

const form = useForm({
    supplier_id: null as number | null,
    invoice_number: '',
    issue_date: '',
    due_date: '',
    total_amount: 0,
    status: 'pending',
});

function openNew() { form.reset(); editingId.value = null; showForm.value = true; }
function openEdit(inv: any) {
    form.supplier_id = inv.supplier_id;
    form.invoice_number = inv.invoice_number || '';
    form.issue_date = inv.issue_date;
    form.due_date = inv.due_date;
    form.total_amount = inv.total_amount;
    form.status = inv.status;
    editingId.value = inv.id;
    showForm.value = true;
}
function closeForm() { showForm.value = false; editingId.value = null; form.reset(); }
function submitForm() {
    if (editingId.value) {
        form.put(route('admin.facturas-pendientes.update', editingId.value), { onSuccess: closeForm });
    } else {
        form.post(route('admin.facturas-pendientes.store'), { onSuccess: closeForm });
    }
}
function deleteInvoice(id: number) {
    if (!confirm('¿Eliminar esta factura?')) return;
    router.delete(route('admin.facturas-pendientes.destroy', id), { preserveScroll: true });
}

const fmt = (v: number) => '$' + v.toLocaleString('es-CO', { minimumFractionDigits: 0 });

const statusLabel: Record<string, string> = {
    pending: 'Pendiente',
    partially_paid: 'Parcial',
    paid: 'Pagada',
};
const statusColor: Record<string, string> = {
    pending: 'bg-warning/10 text-warning',
    partially_paid: 'bg-info/10 text-info',
    paid: 'bg-success/10 text-success',
};
</script>

<template>
    <Head title="Facturas Pendientes" />
    <AdminLayout>
        <template #title>
            <h1 class="text-xl font-display font-bold text-content-primary dark:text-white">Facturas Pendientes</h1>
        </template>

        <div class="bg-white dark:bg-surface-dark rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 flex flex-wrap items-center gap-3">
                <CreditCard class="w-5 h-5 text-primary-500" />
                <h2 class="font-bold text-content-primary dark:text-white flex-1">Cuentas por Pagar</h2>
                <button @click="openNew"
                    class="flex items-center gap-2 bg-primary-500 hover:bg-primary-600 text-white text-sm font-bold px-4 py-2 rounded-2xl transition-colors shadow-sm">
                    <Plus class="w-4 h-4" /> Nueva Factura
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 dark:bg-gray-900/50 text-xs uppercase tracking-wider text-content-muted dark:text-gray-500">
                        <tr>
                            <th class="px-6 py-3 font-bold"># Factura</th>
                            <th class="px-6 py-3 font-bold">Proveedor</th>
                            <th class="px-6 py-3 font-bold">Emisión</th>
                            <th class="px-6 py-3 font-bold">Vencimiento</th>
                            <th class="px-6 py-3 font-bold text-right">Total</th>
                            <th class="px-6 py-3 font-bold text-center">Estado</th>
                            <th class="px-6 py-3 font-bold text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <tr v-if="!invoices.data?.length">
                            <td colspan="7" class="px-6 py-12 text-center text-sm text-content-muted dark:text-gray-500">No hay facturas pendientes.</td>
                        </tr>
                        <tr v-for="inv in invoices.data" :key="inv.id"
                            class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors group">
                            <td class="px-6 py-4 text-sm font-mono font-bold text-content-primary dark:text-white">{{ inv.invoice_number || '—' }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-content-primary dark:text-white">{{ inv.supplier?.company_name || '—' }}</td>
                            <td class="px-6 py-4 text-sm text-content-secondary">{{ formatDate(inv.issue_date) }}</td>
                            <td class="px-6 py-4 text-sm" :class="new Date(inv.due_date) < new Date() && inv.status !== 'paid' ? 'text-danger font-bold' : 'text-content-secondary'">
                                {{ formatDate(inv.due_date) }}
                            </td>
                            <td class="px-6 py-4 text-sm text-right font-bold text-primary-500">{{ fmt(inv.total_amount) }}</td>
                            <td class="px-6 py-4 text-center">
                                <span :class="statusColor[inv.status] || 'bg-gray-100 text-gray-500'" class="px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase">
                                    {{ statusLabel[inv.status] || inv.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button @click="openEdit(inv)" class="p-2 rounded-xl hover:bg-blue-50 dark:hover:bg-blue-900/20 text-blue-500 transition-colors">
                                        <Pencil class="w-4 h-4" />
                                    </button>
                                    <button @click="deleteInvoice(inv.id)" class="p-2 rounded-xl hover:bg-red-50 dark:hover:bg-red-900/20 text-danger transition-colors">
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="invoices.last_page > 1"
                class="px-6 py-4 border-t border-gray-100 dark:border-gray-800 flex justify-between items-center text-sm text-content-muted dark:text-gray-500">
                <span>Página {{ invoices.current_page }} de {{ invoices.last_page }}</span>
                <div class="flex gap-2">
                    <a v-if="invoices.prev_page_url" :href="invoices.prev_page_url"
                        class="px-3 py-1.5 rounded-lg bg-gray-50 dark:bg-gray-900 hover:bg-gray-100 font-bold transition-colors">←</a>
                    <a v-if="invoices.next_page_url" :href="invoices.next_page_url"
                        class="px-3 py-1.5 rounded-lg bg-gray-50 dark:bg-gray-900 hover:bg-gray-100 font-bold transition-colors">→</a>
                </div>
            </div>
        </div>

        <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100"
                    leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="showForm" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm flex items-center justify-center p-4">
                <div class="bg-white dark:bg-surface-dark rounded-3xl shadow-xl w-full max-w-md p-6 relative">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="font-display font-bold text-lg text-content-primary dark:text-white">
                            {{ editingId ? 'Editar Factura' : 'Nueva Factura' }}
                        </h3>
                        <button @click="closeForm" class="p-2 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                            <X class="w-5 h-5 text-content-muted" />
                        </button>
                    </div>
                    <form @submit.prevent="submitForm" class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-content-muted dark:text-gray-400 uppercase tracking-wider mb-1">Proveedor</label>
                            <select v-model="form.supplier_id" required
                                class="w-full border border-gray-200 dark:border-gray-700 rounded-2xl px-4 py-2.5 bg-gray-50 dark:bg-gray-900 text-content-primary dark:text-white text-sm">
                                <option :value="null" disabled>Seleccionar proveedor...</option>
                                <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.company_name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-content-muted dark:text-gray-400 uppercase tracking-wider mb-1">Número Factura</label>
                            <input v-model="form.invoice_number" type="text"
                                class="w-full border border-gray-200 dark:border-gray-700 rounded-2xl px-4 py-2.5 bg-gray-50 dark:bg-gray-900 text-content-primary dark:text-white text-sm" />
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-content-muted dark:text-gray-400 uppercase tracking-wider mb-1">Fecha Emisión</label>
                                <input v-model="form.issue_date" type="date" required
                                    class="w-full border border-gray-200 dark:border-gray-700 rounded-2xl px-4 py-2.5 bg-gray-50 dark:bg-gray-900 text-content-primary dark:text-white text-sm" />
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-content-muted dark:text-gray-400 uppercase tracking-wider mb-1">Fecha Vencimiento</label>
                                <input v-model="form.due_date" type="date" required
                                    class="w-full border border-gray-200 dark:border-gray-700 rounded-2xl px-4 py-2.5 bg-gray-50 dark:bg-gray-900 text-content-primary dark:text-white text-sm" />
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-content-muted dark:text-gray-400 uppercase tracking-wider mb-1">Total</label>
                            <input v-model.number="form.total_amount" type="number" min="0" required
                                class="w-full border border-gray-200 dark:border-gray-700 rounded-2xl px-4 py-2.5 bg-gray-50 dark:bg-gray-900 text-content-primary dark:text-white text-sm" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-content-muted dark:text-gray-400 uppercase tracking-wider mb-1">Estado</label>
                            <select v-model="form.status" required
                                class="w-full border border-gray-200 dark:border-gray-700 rounded-2xl px-4 py-2.5 bg-gray-50 dark:bg-gray-900 text-content-primary dark:text-white text-sm">
                                <option value="pending">Pendiente</option>
                                <option value="partially_paid">Parcialmente Pagada</option>
                                <option value="paid">Pagada</option>
                            </select>
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
