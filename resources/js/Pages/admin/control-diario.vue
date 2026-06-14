<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ClipboardList, Pencil, Trash2, X, Check, Plus } from 'lucide-vue-next';
import { formatDate } from '@/helpers/format';
import { ref } from 'vue';

const props = defineProps<{
    controls: { data: any[]; links: any[]; current_page: number; last_page: number; prev_page_url: string | null; next_page_url: string | null };
}>();

const showForm = ref(false);
const editingId = ref<number | null>(null);

const form = useForm({
    date: '',
    cash_withdrawals: 0,
    card_sales_z: 0,
    mercado_pago_sales: 0,
});

function openNew() { form.reset(); form.date = new Date().toISOString().split('T')[0]; editingId.value = null; showForm.value = true; }
function openEdit(c: any) {
    form.date = c.date;
    form.cash_withdrawals = c.cash_withdrawals;
    form.card_sales_z = c.card_sales_z;
    form.mercado_pago_sales = c.mercado_pago_sales;
    editingId.value = c.id;
    showForm.value = true;
}
function closeForm() { showForm.value = false; editingId.value = null; form.reset(); }
function submitForm() {
    if (editingId.value) {
        form.put(route('admin.control-diario.update', editingId.value), { onSuccess: closeForm });
    } else {
        form.post(route('admin.control-diario.store'), { onSuccess: closeForm });
    }
}
function deleteControl(id: number) {
    if (!confirm('¿Eliminar este control diario?')) return;
    router.delete(route('admin.control-diario.destroy', id), { preserveScroll: true });
}

const fmt = (v: number) => '$' + v.toLocaleString('es-CO', { minimumFractionDigits: 0 });
</script>

<template>
    <Head title="Control Diario" />
    <AdminLayout>
        <template #title>
            <h1 class="text-xl font-display font-bold text-content-primary dark:text-white">Control Diario</h1>
        </template>

        <div class="bg-white dark:bg-surface-dark rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 flex flex-wrap items-center gap-3">
                <ClipboardList class="w-5 h-5 text-primary-500" />
                <h2 class="font-bold text-content-primary dark:text-white flex-1">Registro de Control Diario</h2>
                <button @click="openNew"
                    class="flex items-center gap-2 bg-primary-500 hover:bg-primary-600 text-white text-sm font-bold px-4 py-2 rounded-2xl transition-colors shadow-sm">
                    <Plus class="w-4 h-4" /> Nuevo Control
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 dark:bg-gray-900/50 text-xs uppercase tracking-wider text-content-muted dark:text-gray-500">
                        <tr>
                            <th class="px-6 py-3 font-bold">Fecha</th>
                            <th class="px-6 py-3 font-bold text-right">Retiro Efectivo</th>
                            <th class="px-6 py-3 font-bold text-right">Ventas Tarjeta</th>
                            <th class="px-6 py-3 font-bold text-right">Mercado Pago</th>
                            <th class="px-6 py-3 font-bold text-right">Total</th>
                            <th class="px-6 py-3 font-bold text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <tr v-if="!controls.data?.length">
                            <td colspan="6" class="px-6 py-12 text-center text-sm text-content-muted dark:text-gray-500">No hay controles diarios registrados.</td>
                        </tr>
                        <tr v-for="c in controls.data" :key="c.id"
                            class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors group">
                            <td class="px-6 py-4 text-sm font-medium text-content-primary dark:text-white">{{ formatDate(c.date) }}</td>
                            <td class="px-6 py-4 text-sm text-right text-amber-600 dark:text-amber-400">{{ fmt(c.cash_withdrawals) }}</td>
                            <td class="px-6 py-4 text-sm text-right text-blue-600 dark:text-blue-400">{{ fmt(c.card_sales_z) }}</td>
                            <td class="px-6 py-4 text-sm text-right text-accent-600 dark:text-accent-400">{{ fmt(c.mercado_pago_sales) }}</td>
                            <td class="px-6 py-4 text-sm text-right font-bold text-primary-500">{{ fmt(c.cash_withdrawals + c.card_sales_z + c.mercado_pago_sales) }}</td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button @click="openEdit(c)" class="p-2 rounded-xl hover:bg-blue-50 dark:hover:bg-blue-900/20 text-blue-500 transition-colors">
                                        <Pencil class="w-4 h-4" />
                                    </button>
                                    <button @click="deleteControl(c.id)" class="p-2 rounded-xl hover:bg-red-50 dark:hover:bg-red-900/20 text-danger transition-colors">
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="controls.last_page > 1"
                class="px-6 py-4 border-t border-gray-100 dark:border-gray-800 flex justify-between items-center text-sm text-content-muted dark:text-gray-500">
                <span>Página {{ controls.current_page }} de {{ controls.last_page }}</span>
                <div class="flex gap-2">
                    <a v-if="controls.prev_page_url" :href="controls.prev_page_url"
                        class="px-3 py-1.5 rounded-lg bg-gray-50 dark:bg-gray-900 hover:bg-gray-100 font-bold transition-colors">←</a>
                    <a v-if="controls.next_page_url" :href="controls.next_page_url"
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
                            {{ editingId ? 'Editar Control Diario' : 'Nuevo Control Diario' }}
                        </h3>
                        <button @click="closeForm" class="p-2 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                            <X class="w-5 h-5 text-content-muted" />
                        </button>
                    </div>
                    <form @submit.prevent="submitForm" class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-content-muted dark:text-gray-400 uppercase tracking-wider mb-1">Fecha</label>
                            <input v-model="form.date" type="date" required
                                class="w-full border border-gray-200 dark:border-gray-700 rounded-2xl px-4 py-2.5 bg-gray-50 dark:bg-gray-900 text-content-primary dark:text-white text-sm" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-content-muted dark:text-gray-400 uppercase tracking-wider mb-1">Retiro de Efectivo</label>
                            <input v-model.number="form.cash_withdrawals" type="number" min="0" required
                                class="w-full border border-gray-200 dark:border-gray-700 rounded-2xl px-4 py-2.5 bg-gray-50 dark:bg-gray-900 text-content-primary dark:text-white text-sm" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-content-muted dark:text-gray-400 uppercase tracking-wider mb-1">Ventas Tarjeta (Z)</label>
                            <input v-model.number="form.card_sales_z" type="number" min="0" required
                                class="w-full border border-gray-200 dark:border-gray-700 rounded-2xl px-4 py-2.5 bg-gray-50 dark:bg-gray-900 text-content-primary dark:text-white text-sm" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-content-muted dark:text-gray-400 uppercase tracking-wider mb-1">Ventas Mercado Pago</label>
                            <input v-model.number="form.mercado_pago_sales" type="number" min="0" required
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
