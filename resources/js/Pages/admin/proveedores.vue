<script setup lang="ts">
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Truck, Plus, Pencil, Trash2, X, Check } from 'lucide-vue-next';

const props = defineProps<{
    suppliers: { data: any[]; links: any[]; current_page: number; last_page: number; prev_page_url: string | null; next_page_url: string | null };
}>();

const showForm = ref(false);
const editingId = ref<number | null>(null);

const form = useForm({
    company_name: '',
    category: '',
    contact_name: '',
    visit_day: '',
    delivery_time_hours: 24,
    minimum_order_amount: 0,
});

const days = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];

function openNew() { form.reset(); editingId.value = null; showForm.value = true; }
function openEdit(s: any) {
    form.company_name = s.company_name;
    form.category = s.category || '';
    form.contact_name = s.contact_name || '';
    form.visit_day = s.visit_day || '';
    form.delivery_time_hours = s.delivery_time_hours;
    form.minimum_order_amount = s.minimum_order_amount;
    editingId.value = s.id;
    showForm.value = true;
}
function closeForm() { showForm.value = false; editingId.value = null; form.reset(); }
function submitForm() {
    if (editingId.value) {
        form.put(route('admin.proveedores.update', editingId.value), { onSuccess: closeForm });
    } else {
        form.post(route('admin.proveedores.store'), { onSuccess: closeForm });
    }
}
function deleteSupplier(id: number) {
    if (!confirm('¿Eliminar este proveedor?')) return;
    router.delete(route('admin.proveedores.destroy', id), { preserveScroll: true });
}
</script>

<template>
    <Head title="Proveedores" />
    <AdminLayout>
        <template #title>
            <h1 class="text-xl font-display font-bold text-content-primary dark:text-white">Proveedores</h1>
        </template>

        <div class="bg-white dark:bg-surface-dark rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 flex flex-wrap items-center gap-3">
                <Truck class="w-5 h-5 text-primary-500" />
                <h2 class="font-bold text-content-primary dark:text-white flex-1">Listado de Proveedores</h2>
                <button @click="openNew"
                    class="flex items-center gap-2 bg-primary-500 hover:bg-primary-600 text-white text-sm font-bold px-4 py-2 rounded-2xl transition-colors shadow-sm">
                    <Plus class="w-4 h-4" /> Nuevo Proveedor
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 dark:bg-gray-900/50 text-xs uppercase tracking-wider text-content-muted dark:text-gray-500">
                        <tr>
                            <th class="px-6 py-3 font-bold">Empresa</th>
                            <th class="px-6 py-3 font-bold">Contacto</th>
                            <th class="px-6 py-3 font-bold">Categoría</th>
                            <th class="px-6 py-3 font-bold">Día Visita</th>
                            <th class="px-6 py-3 font-bold text-center">Horas Entrega</th>
                            <th class="px-6 py-3 font-bold text-right">Mínimo</th>
                            <th class="px-6 py-3 font-bold text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <tr v-if="!suppliers.data?.length">
                            <td colspan="7" class="px-6 py-12 text-center text-sm text-content-muted dark:text-gray-500">No hay proveedores registrados.</td>
                        </tr>
                        <tr v-for="s in suppliers.data" :key="s.id"
                            class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors group">
                            <td class="px-6 py-4 text-sm font-bold text-content-primary dark:text-white">{{ s.company_name }}</td>
                            <td class="px-6 py-4 text-sm text-content-secondary">{{ s.contact_name || '—' }}</td>
                            <td class="px-6 py-4">
                                <span v-if="s.category" class="px-2.5 py-1 bg-gray-100 dark:bg-gray-800 rounded-lg text-xs font-bold text-content-secondary">{{ s.category }}</span>
                                <span v-else class="text-xs text-content-muted">—</span>
                            </td>
                            <td class="px-6 py-4 text-sm text-content-secondary">{{ s.visit_day || '—' }}</td>
                            <td class="px-6 py-4 text-sm text-center font-medium text-content-primary dark:text-white">{{ s.delivery_time_hours }}h</td>
                            <td class="px-6 py-4 text-sm text-right text-content-secondary">{{ s.minimum_order_amount ? '$' + s.minimum_order_amount : '—' }}</td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button @click="openEdit(s)" class="p-2 rounded-xl hover:bg-blue-50 dark:hover:bg-blue-900/20 text-blue-500 transition-colors">
                                        <Pencil class="w-4 h-4" />
                                    </button>
                                    <button @click="deleteSupplier(s.id)" class="p-2 rounded-xl hover:bg-red-50 dark:hover:bg-red-900/20 text-danger transition-colors">
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="suppliers.last_page > 1"
                class="px-6 py-4 border-t border-gray-100 dark:border-gray-800 flex justify-between items-center text-sm text-content-muted dark:text-gray-500">
                <span>Página {{ suppliers.current_page }} de {{ suppliers.last_page }}</span>
                <div class="flex gap-2">
                    <a v-if="suppliers.prev_page_url" :href="suppliers.prev_page_url"
                        class="px-3 py-1.5 rounded-lg bg-gray-50 dark:bg-gray-900 hover:bg-gray-100 font-bold transition-colors">←</a>
                    <a v-if="suppliers.next_page_url" :href="suppliers.next_page_url"
                        class="px-3 py-1.5 rounded-lg bg-gray-50 dark:bg-gray-900 hover:bg-gray-100 font-bold transition-colors">→</a>
                </div>
            </div>
        </div>

        <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100"
                    leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="showForm" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm flex items-center justify-center p-4">
                <div class="bg-white dark:bg-surface-dark rounded-3xl shadow-xl w-full max-w-lg p-6 relative">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="font-display font-bold text-lg text-content-primary dark:text-white">
                            {{ editingId ? 'Editar Proveedor' : 'Nuevo Proveedor' }}
                        </h3>
                        <button @click="closeForm" class="p-2 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                            <X class="w-5 h-5 text-content-muted" />
                        </button>
                    </div>
                    <form @submit.prevent="submitForm" class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-content-muted dark:text-gray-400 uppercase tracking-wider mb-1">Nombre Empresa</label>
                            <input v-model="form.company_name" type="text" required
                                class="w-full border border-gray-200 dark:border-gray-700 rounded-2xl px-4 py-2.5 bg-gray-50 dark:bg-gray-900 text-content-primary dark:text-white text-sm" />
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-content-muted dark:text-gray-400 uppercase tracking-wider mb-1">Contacto</label>
                                <input v-model="form.contact_name" type="text"
                                    class="w-full border border-gray-200 dark:border-gray-700 rounded-2xl px-4 py-2.5 bg-gray-50 dark:bg-gray-900 text-content-primary dark:text-white text-sm" />
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-content-muted dark:text-gray-400 uppercase tracking-wider mb-1">Categoría</label>
                                <input v-model="form.category" type="text"
                                    class="w-full border border-gray-200 dark:border-gray-700 rounded-2xl px-4 py-2.5 bg-gray-50 dark:bg-gray-900 text-content-primary dark:text-white text-sm" />
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-content-muted dark:text-gray-400 uppercase tracking-wider mb-1">Día Visita</label>
                                <select v-model="form.visit_day"
                                    class="w-full border border-gray-200 dark:border-gray-700 rounded-2xl px-4 py-2.5 bg-gray-50 dark:bg-gray-900 text-content-primary dark:text-white text-sm">
                                    <option value="">Seleccionar...</option>
                                    <option v-for="d in days" :key="d" :value="d">{{ d }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-content-muted dark:text-gray-400 uppercase tracking-wider mb-1">Horas Entrega</label>
                                <input v-model.number="form.delivery_time_hours" type="number" min="1" required
                                    class="w-full border border-gray-200 dark:border-gray-700 rounded-2xl px-4 py-2.5 bg-gray-50 dark:bg-gray-900 text-content-primary dark:text-white text-sm" />
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-content-muted dark:text-gray-400 uppercase tracking-wider mb-1">Monto Mínimo Pedido</label>
                            <input v-model.number="form.minimum_order_amount" type="number" min="0" required
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
