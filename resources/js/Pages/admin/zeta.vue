<script setup lang="ts">
import { formatDate } from '@/helpers/format';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Check, FileText, Pencil, Plus, Trash2, X } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    reports: {
        data: any[];
        links: any[];
        current_page: number;
        last_page: number;
        prev_page_url: string | null;
        next_page_url: string | null;
    };
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

function openNew() {
    form.reset();
    form.date = new Date().toISOString().split('T')[0];
    editingId.value = null;
    showForm.value = true;
}
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
function closeForm() {
    showForm.value = false;
    editingId.value = null;
    form.reset();
}
function submitForm() {
    if (editingId.value) {
        form.put(route('admin.zeta.update', editingId.value), {
            onSuccess: closeForm,
        });
    } else {
        form.post(route('admin.zeta.store'), { onSuccess: closeForm });
    }
}
function deleteReport(id: number) {
    if (!confirm('¿Eliminar este reporte Z?')) return;
    router.delete(route('admin.zeta.destroy', id), { preserveScroll: true });
}

const fmt = (v: number) =>
    '$' + v.toLocaleString('es-CO', { minimumFractionDigits: 0 });
</script>

<template>
    <Head title="Reportes Zeta" />
    <AdminLayout>
        <template #title>
            <h1
                class="font-display text-xl font-bold text-content-primary dark:text-white"
            >
                Reportes Zeta
            </h1>
        </template>

        <div
            class="overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-surface-dark"
        >
            <div
                class="flex flex-wrap items-center gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-800"
            >
                <FileText class="h-5 w-5 text-primary-500" />
                <h2
                    class="flex-1 font-bold text-content-primary dark:text-white"
                >
                    Reportes de Cierre POS
                </h2>
                <button
                    @click="openNew"
                    class="flex items-center gap-2 rounded-2xl bg-primary-500 px-4 py-2 text-sm font-bold text-white shadow-sm transition-colors hover:bg-primary-600"
                >
                    <Plus class="h-4 w-4" /> Nuevo Reporte Z
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead
                        class="bg-gray-50 text-xs uppercase tracking-wider text-content-muted dark:bg-gray-900/50 dark:text-gray-500"
                    >
                        <tr>
                            <th class="px-6 py-3 font-bold">Fecha</th>
                            <th class="px-6 py-3 font-bold">Cajero</th>
                            <th class="px-6 py-3 text-right font-bold">
                                Total Z
                            </th>
                            <th class="px-6 py-3 text-right font-bold">
                                Efectivo Neto
                            </th>
                            <th class="px-6 py-3 text-right font-bold">
                                Transferencias
                            </th>
                            <th class="px-6 py-3 text-right font-bold">
                                Tarjeta POS
                            </th>
                            <th class="px-6 py-3 text-center font-bold">
                                Estado
                            </th>
                            <th class="px-6 py-3 text-right font-bold">
                                Acciones
                            </th>
                        </tr>
                    </thead>
                    <tbody
                        class="divide-y divide-gray-100 dark:divide-gray-800"
                    >
                        <tr v-if="!reports.data?.length">
                            <td
                                colspan="8"
                                class="px-6 py-12 text-center text-sm text-content-muted dark:text-gray-500"
                            >
                                No hay reportes Z registrados.
                            </td>
                        </tr>
                        <tr
                            v-for="r in reports.data"
                            :key="r.id"
                            class="group transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/50"
                        >
                            <td
                                class="px-6 py-4 text-sm text-content-primary dark:text-white"
                            >
                                {{ formatDate(r.date) }}
                            </td>
                            <td
                                class="px-6 py-4 text-sm font-medium text-content-primary dark:text-white"
                            >
                                {{ r.cashier?.name || '—' }}
                            </td>
                            <td
                                class="px-6 py-4 text-right text-sm font-bold text-content-primary"
                            >
                                {{ fmt(r.total_z) }}
                            </td>
                            <td
                                class="px-6 py-4 text-right text-sm text-success"
                            >
                                {{ fmt(r.net_cash) }}
                            </td>
                            <td
                                class="px-6 py-4 text-right text-sm text-blue-500"
                            >
                                {{ fmt(r.transfers) }}
                            </td>
                            <td
                                class="px-6 py-4 text-right text-sm text-accent-500"
                            >
                                {{ fmt(r.pos_card_total) }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span
                                    :class="
                                        r.status === 'audited'
                                            ? 'bg-success/10 text-success'
                                            : 'bg-warning/10 text-warning'
                                    "
                                    class="rounded-lg px-2.5 py-1 text-[10px] font-bold uppercase"
                                >
                                    {{
                                        r.status === 'audited'
                                            ? 'Auditado'
                                            : 'Revisión'
                                    }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div
                                    class="flex items-center justify-end gap-2 opacity-0 transition-opacity group-hover:opacity-100"
                                >
                                    <button
                                        @click="openEdit(r)"
                                        class="rounded-xl p-2 text-blue-500 transition-colors hover:bg-blue-50 dark:hover:bg-blue-900/20"
                                    >
                                        <Pencil class="h-4 w-4" />
                                    </button>
                                    <button
                                        @click="deleteReport(r.id)"
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
                v-if="reports.last_page > 1"
                class="flex items-center justify-between border-t border-gray-100 px-6 py-4 text-sm text-content-muted dark:border-gray-800 dark:text-gray-500"
            >
                <span
                    >Página {{ reports.current_page }} de
                    {{ reports.last_page }}</span
                >
                <div class="flex gap-2">
                    <a
                        v-if="reports.prev_page_url"
                        :href="reports.prev_page_url"
                        class="rounded-lg bg-gray-50 px-3 py-1.5 font-bold transition-colors hover:bg-gray-100 dark:bg-gray-900"
                        >←</a
                    >
                    <a
                        v-if="reports.next_page_url"
                        :href="reports.next_page_url"
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
                    class="relative max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-3xl bg-white p-6 shadow-xl dark:bg-surface-dark"
                >
                    <div class="mb-6 flex items-center justify-between">
                        <h3
                            class="font-display text-lg font-bold text-content-primary dark:text-white"
                        >
                            {{
                                editingId
                                    ? 'Editar Reporte Z'
                                    : 'Nuevo Reporte Z'
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
                                    >Cajero</label
                                >
                                <select
                                    v-model="form.cashier_id"
                                    required
                                    class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                >
                                    <option :value="null" disabled>
                                        Seleccionar...
                                    </option>
                                    <option
                                        v-for="c in cashiers"
                                        :key="c.id"
                                        :value="c.id"
                                    >
                                        {{ c.name }}
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                                    >Total Z</label
                                >
                                <input
                                    v-model.number="form.total_z"
                                    type="number"
                                    min="0"
                                    required
                                    class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                />
                            </div>
                            <div>
                                <label
                                    class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                                    >Efectivo Neto</label
                                >
                                <input
                                    v-model.number="form.net_cash"
                                    type="number"
                                    min="0"
                                    required
                                    class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                />
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                                    >Transferencias</label
                                >
                                <input
                                    v-model.number="form.transfers"
                                    type="number"
                                    min="0"
                                    required
                                    class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                />
                            </div>
                            <div>
                                <label
                                    class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                                    >Ventas Tarjeta</label
                                >
                                <input
                                    v-model.number="form.pos_card_total"
                                    type="number"
                                    min="0"
                                    required
                                    class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                />
                            </div>
                        </div>
                        <div>
                            <label
                                class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                                >Estado</label
                            >
                            <select
                                v-model="form.status"
                                required
                                class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                            >
                                <option value="pending_review">
                                    Pendiente Revisión
                                </option>
                                <option value="audited">Auditado</option>
                            </select>
                        </div>
                        <div>
                            <label
                                class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                                >Observaciones</label
                            >
                            <textarea
                                v-model="form.observations"
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
                                {{ editingId ? 'Actualizar' : 'Guardar' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>
    </AdminLayout>
</template>
