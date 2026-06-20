<script setup lang="ts">
import { formatDateTime } from '@/helpers/format';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DateFilter from '@/Components/DateFilter.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Eye, FileText, ShoppingCart, Trash2 } from 'lucide-vue-next';
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
    filters: { user_id?: string; fecha?: string };
}>();

const selectedUser = ref(props.filters.user_id ?? '');
const todayStr = new Date().toISOString().slice(0, 10);
const filterDate = ref<string>(props.filters?.fecha ?? todayStr);

function consultar() {
    const params: Record<string, any> = {};
    if (selectedUser.value !== '') params.user_id = selectedUser.value;
    if (filterDate.value) params.fecha = filterDate.value;
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
};

const accionIcon: Record<string, any> = {
    eliminar_item: Trash2,
    limpiar_carrito: ShoppingCart,
};

const accionColor: Record<string, string> = {
    eliminar_item: 'bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400',
    limpiar_carrito: 'bg-amber-50 text-amber-700 dark:bg-amber-900/20 dark:text-amber-400',
};
</script>

<template>
    <Head title="Observaciones" />
    <AdminLayout>
        <div class="mx-auto max-w-6xl space-y-6 p-4 sm:p-6">
            <!-- Header -->
            <div class="flex items-center gap-3">
                <div class="rounded-xl bg-primary-50 p-2.5 dark:bg-primary-900/20">
                    <Eye class="h-5 w-5 text-primary-600 dark:text-primary-400" />
                </div>
                <div>
                    <h1 class="text-xl font-bold text-content-primary dark:text-white">Reporte de Observaciones</h1>
                    <p class="text-xs text-content-muted">Registro de eliminación de ítems y limpieza de carrito en Caja Rápida</p>
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
                <button @click="selectedUser = ''; filterDate = todayStr; consultar()"
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
                                <th class="px-4 py-3 font-bold">Fecha y Hora</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            <tr v-for="o in observaciones.data" :key="o.id"
                                class="transition-colors hover:bg-gray-50 dark:hover:bg-gray-900/50">
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
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-content-muted">
                                    {{ formatDateTime(o.created_at) }}
                                </td>
                            </tr>
                            <tr v-if="!observaciones.data.length">
                                <td colspan="4" class="px-4 py-12 text-center text-sm text-content-muted">
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
    </AdminLayout>
</template>
