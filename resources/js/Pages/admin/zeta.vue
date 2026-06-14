<script setup lang="ts">
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { FileText, Plus, Pencil, Trash2, X, Check } from 'lucide-vue-next';
import { formatDate } from '@/helpers/format';

const props = defineProps<{
    reports: { data: any[]; links: any[]; current_page: number; last_page: number; prev_page_url: string | null; next_page_url: string | null };
    cashiers: { id: number; name: string }[];
}>();

const showForm = ref(false);
const editingId = ref<number | null>(null);

const form = useForm({
    date: '',
    cashier_id: null as number | null,
    total_z: 0,
    net_cash: 0,
    transfers: 0,
    pos_card_total: 0,
    observations: '',
    status: 'pending_review',
});

function openNew() { form.reset(); form.date = new Date().toISOString().split('T')[0]; editingId.value = null; showForm.value = true; }
function openEdit(r: any) {
    form.date = r.date;
    form.cashier_id = r.cashier_id;
    form.total_z = r.total_z;
    form.net_cash = r.net_cash;
    form.transfers = r.transfers;
    form.pos_card_total = r.pos_card_total;
    form.observations = r.observations || '';
    form.status = r.status;
    editingId.value = r.id;
    showForm.value = true;
}
function closeForm() { showForm.value = false; editingId.value = null; form.reset(); }
function submitForm() {
    if (editingId.value) {
        form.put(route('admin.zeta.update', editingId.value), { onSuccess: closeForm });
    } else {
        form.post(route('admin.zeta.store'), { onSuccess: closeForm });
    }
}
function deleteReport(id: number) {
    if (!confirm('¿Eliminar este reporte Z?')) return;
    router.delete(route('admin.zeta.destroy', id), { preserveScroll: true });
}

const fmt = (v: number) => '$' + v.toLocaleString('es-CO', { minimumFractionDigits: 0 });
</script>

<template>
    <Head title="Reportes Zeta" />
    <AdminLayout>
        <template #title>
            <h1 class="text-xl font-display font-bold text-content-primary dark:text-white">Reportes Zeta</h1>
        </template>

        <div class="bg-white dark:bg-surface-dark rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 flex flex-wrap items-center gap-3">
                <FileText class="w-5 h-5 text-primary-500" />
                <h2 class="font-bold text-content-primary dark:text-white flex-1">Reportes de Cierre POS</h2>
                <button @click="openNew"
                    class="flex items-center gap-2 bg-primary-500 hover:bg-primary-600 text-white text-sm font-bold px-4 py-2 rounded-2xl transition-colors shadow-sm">
                    <Plus class="w-4 h-4" /> Nuevo Reporte Z
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 dark:bg-gray-900/50 text-xs uppercase tracking-wider text-content-muted dark:text-gray-500">
                        <tr>
                            <th class="px-6 py-3 font-bold">Fecha</th>
                            <th class="px-6 py-3 font-bold">Cajero</th>
                            <th class="px-6 py-3 font-bold text-right">Total Z</th>
                            <th class="px-6 py-3 font-bold text-right">Efectivo Neto</th>
                            <th class="px-6 py-3 font-bold text-right">Transferencias</th>
                            <th class="px-6 py-3 font-bold text-right">Tarjeta POS</th>
                            <th class="px-6 py-3 font-bold text-center">Estado</th>
                            <th class="px-6 py-3 font-bold text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <tr v-if="!reports.data?.length">
                            <td colspan="8" class="px-6 py-12 text-center text-sm text-content-muted dark:text-gray-500">No hay reportes Z registrados.</td>
                        </tr>
                        <tr v-for="r in reports.data" :key="r.id"
                            class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors group">
                            <td class="px-6 py-4 text-sm text-content-primary dark:text-white">                                    {{ formatDate(r.date) }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-content-primary dark:text-white">{{ r.cashier?.name || '—' }}</td>
                            <td class="px-6 py-4 text-sm text-right font-bold text-content-primary">{{ fmt(r.total_z) }}</td>
                            <td class="px-6 py-4 text-sm text-right text-success">{{ fmt(r.net_cash) }}</td>
                            <td class="px-6 py-4 text-sm text-right text-blue-500">{{ fmt(r.transfers) }}</td>
                            <td class="px-6 py-4 text-sm text-right text-accent-500">{{ fmt(r.pos_card_total) }}</td>
                            <td class="px-6 py-4 text-center">
                                <span :class="r.status === 'audited' ? 'bg-success/10 text-success' : 'bg-warning/10 text-warning'"
                                    class="px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase">
                                    {{ r.status === 'audited' ? 'Auditado' : 'Revisión' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button @click="openEdit(r)" class="p-2 rounded-xl hover:bg-blue-50 dark:hover:bg-blue-900/20 text-blue-500 transition-colors">
                                        <Pencil class="w-4 h-4" />
                                    </button>
                                    <button @click="deleteReport(r.id)" class="p-2 rounded-xl hover:bg-red-50 dark:hover:bg-red-900/20 text-danger transition-colors">
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="reports.last_page > 1"
                class="px-6 py-4 border-t border-gray-100 dark:border-gray-800 flex justify-between items-center text-sm text-content-muted dark:text-gray-500">
                <span>Página {{ reports.current_page }} de {{ reports.last_page }}</span>
                <div class="flex gap-2">
                    <a v-if="reports.prev_page_url" :href="reports.prev_page_url"
                        class="px-3 py-1.5 rounded-lg bg-gray-50 dark:bg-gray-900 hover:bg-gray-100 font-bold transition-colors">←</a>
                    <a v-if="reports.next_page_url" :href="reports.next_page_url"
                        class="px-3 py-1.5 rounded-lg bg-gray-50 dark:bg-gray-900 hover:bg-gray-100 font-bold transition-colors">→</a>
                </div>
            </div>
        </div>

        <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100"
                    leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="showForm" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm flex items-center justify-center p-4">
                <div class="bg-white dark:bg-surface-dark rounded-3xl shadow-xl w-full max-w-lg p-6 relative max-h-[90vh] overflow-y-auto">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="font-display font-bold text-lg text-content-primary dark:text-white">
                            {{ editingId ? 'Editar Reporte Z' : 'Nuevo Reporte Z' }}
                        </h3>
                        <button @click="closeForm" class="p-2 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                            <X class="w-5 h-5 text-content-muted" />
                        </button>
                    </div>
                    <form @submit.prevent="submitForm" class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-content-muted dark:text-gray-400 uppercase tracking-wider mb-1">Fecha</label>
                                <input v-model="form.date" type="date" required
                                    class="w-full border border-gray-200 dark:border-gray-700 rounded-2xl px-4 py-2.5 bg-gray-50 dark:bg-gray-900 text-content-primary dark:text-white text-sm" />
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-content-muted dark:text-gray-400 uppercase tracking-wider mb-1">Cajero</label>
                                <select v-model="form.cashier_id" required
                                    class="w-full border border-gray-200 dark:border-gray-700 rounded-2xl px-4 py-2.5 bg-gray-50 dark:bg-gray-900 text-content-primary dark:text-white text-sm">
                                    <option :value="null" disabled>Seleccionar...</option>
                                    <option v-for="c in cashiers" :key="c.id" :value="c.id">{{ c.name }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-content-muted dark:text-gray-400 uppercase tracking-wider mb-1">Total Z</label>
                                <input v-model.number="form.total_z" type="number" min="0" required
                                    class="w-full border border-gray-200 dark:border-gray-700 rounded-2xl px-4 py-2.5 bg-gray-50 dark:bg-gray-900 text-content-primary dark:text-white text-sm" />
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-content-muted dark:text-gray-400 uppercase tracking-wider mb-1">Efectivo Neto</label>
                                <input v-model.number="form.net_cash" type="number" min="0" required
                                    class="w-full border border-gray-200 dark:border-gray-700 rounded-2xl px-4 py-2.5 bg-gray-50 dark:bg-gray-900 text-content-primary dark:text-white text-sm" />
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-content-muted dark:text-gray-400 uppercase tracking-wider mb-1">Transferencias</label>
                                <input v-model.number="form.transfers" type="number" min="0" required
                                    class="w-full border border-gray-200 dark:border-gray-700 rounded-2xl px-4 py-2.5 bg-gray-50 dark:bg-gray-900 text-content-primary dark:text-white text-sm" />
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-content-muted dark:text-gray-400 uppercase tracking-wider mb-1">Ventas Tarjeta</label>
                                <input v-model.number="form.pos_card_total" type="number" min="0" required
                                    class="w-full border border-gray-200 dark:border-gray-700 rounded-2xl px-4 py-2.5 bg-gray-50 dark:bg-gray-900 text-content-primary dark:text-white text-sm" />
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-content-muted dark:text-gray-400 uppercase tracking-wider mb-1">Estado</label>
                            <select v-model="form.status" required
                                class="w-full border border-gray-200 dark:border-gray-700 rounded-2xl px-4 py-2.5 bg-gray-50 dark:bg-gray-900 text-content-primary dark:text-white text-sm">
                                <option value="pending_review">Pendiente Revisión</option>
                                <option value="audited">Auditado</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-content-muted dark:text-gray-400 uppercase tracking-wider mb-1">Observaciones</label>
                            <textarea v-model="form.observations" rows="2"
                                class="w-full border border-gray-200 dark:border-gray-700 rounded-2xl px-4 py-2.5 bg-gray-50 dark:bg-gray-900 text-content-primary dark:text-white text-sm"></textarea>
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
