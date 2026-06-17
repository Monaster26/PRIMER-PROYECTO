<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Check, Pencil, Plus, Search, Trash2, Truck, X } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    suppliers: {
        data: any[];
        links: any[];
        current_page: number;
        last_page: number;
        prev_page_url: string | null;
        next_page_url: string | null;
    };
    search?: string;
}>();

const searchQuery = ref(props.search || '');
let searchTimer: ReturnType<typeof setTimeout> | null = null;

function onSearchInput() {
    if (searchTimer) clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        router.get(
            route('admin.proveedores.index'),
            { search: searchQuery.value || '' },
            { preserveState: true, preserveScroll: true, replace: true },
        );
    }, 300);
}

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

const days = [
    'Lunes',
    'Martes',
    'Miércoles',
    'Jueves',
    'Viernes',
    'Sábado',
    'Domingo',
    'Todos los días',
];

function openNew() {
    form.reset();
    editingId.value = null;
    showForm.value = true;
}
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
function closeForm() {
    showForm.value = false;
    editingId.value = null;
    form.reset();
}
function submitForm() {
    if (editingId.value) {
        form.put(route('admin.proveedores.update', editingId.value), {
            onSuccess: closeForm,
        });
    } else {
        form.post(route('admin.proveedores.store'), { onSuccess: closeForm });
    }
}
function deleteSupplier(id: number) {
    if (!confirm('¿Eliminar este proveedor?')) return;
    router.delete(route('admin.proveedores.destroy', id), {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Proveedores" />
    <AdminLayout>
        <template #title>
            <h1
                class="font-display text-xl font-bold text-content-primary dark:text-white"
            >
                Proveedores
            </h1>
        </template>

        <div
            class="overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-surface-dark"
        >
            <div
                class="flex flex-wrap items-center gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-800"
            >
                <Truck class="h-5 w-5 text-primary-500" />
                <h2 class="font-bold text-content-primary dark:text-white">
                    Proveedores
                </h2>
                <div class="relative ml-auto max-w-xs flex-1">
                    <Search
                        class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-content-muted"
                    />
                    <input
                        v-model="searchQuery"
                        @input="onSearchInput"
                        type="text"
                        placeholder="Buscar por empresa, contacto, categoría, día..."
                        class="w-full rounded-2xl border border-gray-200 bg-gray-50 py-2 pl-9 pr-4 text-sm text-content-primary placeholder:text-content-muted/60 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                    />
                </div>
                <button
                    @click="openNew"
                    class="flex items-center gap-2 rounded-2xl bg-primary-500 px-4 py-2 text-sm font-bold text-white shadow-sm transition-colors hover:bg-primary-600"
                >
                    <Plus class="h-4 w-4" /> Nuevo Proveedor
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead
                        class="bg-gray-50 text-xs uppercase tracking-wider text-content-muted dark:bg-gray-900/50 dark:text-gray-500"
                    >
                        <tr>
                            <th class="px-6 py-3 font-bold">Empresa</th>
                            <th class="px-6 py-3 font-bold">Contacto</th>
                            <th class="px-6 py-3 font-bold">Categoría</th>
                            <th class="px-6 py-3 font-bold">Día Visita</th>
                            <th class="px-6 py-3 text-center font-bold">
                                Horas Entrega
                            </th>
                            <th class="px-6 py-3 text-right font-bold">
                                Mínimo
                            </th>
                            <th class="px-6 py-3 text-right font-bold">
                                Acciones
                            </th>
                        </tr>
                    </thead>
                    <tbody
                        class="divide-y divide-gray-100 dark:divide-gray-800"
                    >
                        <tr v-if="!suppliers.data?.length">
                            <td
                                colspan="7"
                                class="px-6 py-12 text-center text-sm text-content-muted dark:text-gray-500"
                            >
                                No hay proveedores registrados.
                            </td>
                        </tr>
                        <tr
                            v-for="s in suppliers.data"
                            :key="s.id"
                            class="group transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/50"
                        >
                            <td
                                class="px-6 py-4 text-sm font-bold text-content-primary dark:text-white"
                            >
                                {{ s.company_name }}
                            </td>
                            <td
                                class="px-6 py-4 text-sm text-content-secondary"
                            >
                                {{ s.contact_name || '—' }}
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    v-if="s.category"
                                    class="rounded-lg bg-gray-100 px-2.5 py-1 text-xs font-bold text-content-secondary dark:bg-gray-800"
                                    >{{ s.category }}</span
                                >
                                <span v-else class="text-xs text-content-muted"
                                    >—</span
                                >
                            </td>
                            <td
                                class="px-6 py-4 text-sm text-content-secondary"
                            >
                                {{ s.visit_day || '—' }}
                            </td>
                            <td
                                class="px-6 py-4 text-center text-sm font-medium text-content-primary dark:text-white"
                            >
                                {{ s.delivery_time_hours }}h
                            </td>
                            <td
                                class="px-6 py-4 text-right text-sm text-content-secondary"
                            >
                                {{
                                    s.minimum_order_amount
                                        ? '$' + s.minimum_order_amount
                                        : '—'
                                }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div
                                    class="flex items-center justify-end gap-2 opacity-0 transition-opacity group-hover:opacity-100"
                                >
                                    <button
                                        @click="openEdit(s)"
                                        class="rounded-xl p-2 text-blue-500 transition-colors hover:bg-blue-50 dark:hover:bg-blue-900/20"
                                    >
                                        <Pencil class="h-4 w-4" />
                                    </button>
                                    <button
                                        @click="deleteSupplier(s.id)"
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
                v-if="suppliers.last_page > 1"
                class="flex items-center justify-between border-t border-gray-100 px-6 py-4 text-sm text-content-muted dark:border-gray-800 dark:text-gray-500"
            >
                <span
                    >Página {{ suppliers.current_page }} de
                    {{ suppliers.last_page }}</span
                >
                <div class="flex gap-2">
                    <a
                        v-if="suppliers.prev_page_url"
                        :href="suppliers.prev_page_url"
                        class="rounded-lg bg-gray-50 px-3 py-1.5 font-bold transition-colors hover:bg-gray-100 dark:bg-gray-900"
                        >←</a
                    >
                    <a
                        v-if="suppliers.next_page_url"
                        :href="suppliers.next_page_url"
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
                    class="relative w-full max-w-lg rounded-3xl bg-white p-6 shadow-xl dark:bg-surface-dark"
                >
                    <div class="mb-6 flex items-center justify-between">
                        <h3
                            class="font-display text-lg font-bold text-content-primary dark:text-white"
                        >
                            {{
                                editingId
                                    ? 'Editar Proveedor'
                                    : 'Nuevo Proveedor'
                            }}
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
                                >Nombre Empresa</label
                            >
                            <input
                                v-model="form.company_name"
                                type="text"
                                required
                                class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                            />
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                                    >Contacto</label
                                >
                                <input
                                    v-model="form.contact_name"
                                    type="text"
                                    class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                />
                            </div>
                            <div>
                                <label
                                    class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                                    >Categoría</label
                                >
                                <input
                                    v-model="form.category"
                                    type="text"
                                    class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                />
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                                    >Día Visita</label
                                >
                                <select
                                    v-model="form.visit_day"
                                    class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                >
                                    <option value="">Seleccionar...</option>
                                    <option
                                        v-for="d in days"
                                        :key="d"
                                        :value="d"
                                    >
                                        {{ d }}
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label
                                    class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                                    >Horas Entrega</label
                                >
                                <input
                                    v-model.number="form.delivery_time_hours"
                                    type="number"
                                    min="1"
                                    required
                                    class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                />
                            </div>
                        </div>
                        <div>
                            <label
                                class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                                >Monto Mínimo Pedido</label
                            >
                            <input
                                v-model.number="form.minimum_order_amount"
                                type="number"
                                min="0"
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
