<script setup lang="ts">
import { formatDate } from '@/helpers/format';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import {
    ArrowRightLeft,
    Check,
    DollarSign,
    Download,
    FileText,
    Landmark,
    Pencil,
    Plus,
    ReceiptText,
    Search,
    ShoppingBag,
    Trash2,
    Wrench,
    X,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps<{
    expenses: any;
    suppliers: { id: number; company_name: string }[];
    users: { id: number; name: string }[];
    summary: {
        total: number;
        efectivo: number;
        transferencia: number;
        proveedores: number;
        operacionales: number;
        count: number;
    };
    filters: {
        from?: string;
        to?: string;
        type?: string;
        payment_method?: string;
        beneficiary?: string;
        user_id?: number | string;
    };
    chart: Record<string, { label: string; total: number; color: string }>;
}>();

const typeLabels: Record<string, string> = {
    proveedor: 'Proveedor',
    servicio: 'Servicio',
    sueldo: 'Sueldo',
    arriendo: 'Arriendo',
    gasto_comun: 'Gasto Común',
    mantencion: 'Mantención',
    impuesto: 'Impuesto',
    otros: 'Otros',
};

const typeColors: Record<string, string> = {
    proveedor: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
    servicio: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
    sueldo: 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
    arriendo: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
    gasto_comun: 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
    mantencion: 'bg-pink-100 text-pink-700 dark:bg-pink-900/30 dark:text-pink-400',
    impuesto: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
    otros: 'bg-slate-100 text-slate-700 dark:bg-slate-900/30 dark:text-slate-400',
};

const pmColors: Record<string, string> = {
    efectivo: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
    transferencia: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
};

const pmLabels: Record<string, string> = {
    efectivo: 'Efectivo',
    transferencia: 'Transferencia',
};

const chartMax = computed(() => Math.max(...Object.values(props.chart).map(c => c.total), 1));

const filterForm = useForm({
    from: props.filters.from ?? '',
    to: props.filters.to ?? '',
    type: props.filters.type ?? '',
    payment_method: props.filters.payment_method ?? '',
    beneficiary: props.filters.beneficiary ?? '',
    user_id: props.filters.user_id ?? '',
});

function applyFilters() {
    router.get(route('admin.gastos.index'), {
        ...filterForm.data(),
    }, { preserveState: true, preserveScroll: true });
}

function clearFilters() {
    filterForm.reset();
    router.get(route('admin.gastos.index'), {}, { preserveState: true, preserveScroll: true });
}

const showForm = ref(false);
const editingId = ref<number | null>(null);

const form = useForm({
    date: new Date().toISOString().split('T')[0],
    type: '',
    concept: '',
    beneficiary: '',
    payment_method: '',
    amount: 0,
    observation: '',
    receipt_file: null as File | null,
    supplier_id: null as number | null,
});

function openNew() {
    form.reset();
    form.date = new Date().toISOString().split('T')[0];
    editingId.value = null;
    showForm.value = true;
}

function openEdit(e: any) {
    form.date = e.date;
    form.type = e.type;
    form.concept = e.concept ?? '';
    form.beneficiary = e.beneficiary ?? '';
    form.payment_method = e.payment_method;
    form.amount = e.amount;
    form.observation = e.observation ?? '';
    form.supplier_id = e.supplier_id;
    editingId.value = e.id;
    showForm.value = true;
}

function closeForm() {
    showForm.value = false;
    editingId.value = null;
    form.reset();
}

function getFilterParams() {
    const p: Record<string, any> = {};
    for (const [k, v] of Object.entries(filterForm.data())) {
        if (v) p[k] = v;
    }
    return p;
}

function submitForm() {
    if (editingId.value) {
        form.put(route('admin.gastos.update', {
            expense: editingId.value,
            ...getFilterParams(),
        }), {
            onSuccess: closeForm,
        });
    } else {
        form.post(route('admin.gastos.store'), {
            onSuccess: closeForm,
        });
    }
}

function deleteExpense(id: number) {
    if (!confirm('¿Eliminar este egreso?')) return;
    router.delete(route('admin.gastos.destroy', id), {
        data: getFilterParams(),
        preserveScroll: true,
    });
}

const fmt = (v: number) =>
    '$' + v.toLocaleString('es-CO', { minimumFractionDigits: 0 });

function receiptUrl(path: string | null): string | undefined {
    if (!path) return undefined;
    return '/storage/' + path;
}
</script>

<template>
    <Head title="Registro de Egresos" />
    <AdminLayout>
        <template #title>
            <div>
                <h1 class="font-display text-xl font-bold text-content-primary dark:text-white">
                    Registro de Egresos
                </h1>
                <p class="text-sm text-content-muted dark:text-gray-400">
                    Gestiona y consulta todas las salidas de dinero del minimarket incluyendo pagos a proveedores, servicios, arriendos, sueldos y otros gastos operacionales.
                </p>
            </div>
        </template>

        <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
            <div class="flex items-center gap-4 rounded-3xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-surface-dark">
                <div class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-2xl bg-danger/10">
                    <DollarSign class="h-5 w-5 text-danger" />
                </div>
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-content-muted dark:text-gray-400">Total Egresos</p>
                    <p class="text-base font-bold text-content-primary dark:text-white">{{ fmt(summary.total) }}</p>
                    <p class="text-[10px] text-content-muted">{{ summary.count }} registros</p>
                </div>
            </div>
            <div class="flex items-center gap-4 rounded-3xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-surface-dark">
                <div class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-2xl bg-amber-50 dark:bg-amber-900/20">
                    <Landmark class="h-5 w-5 text-amber-500" />
                </div>
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-content-muted dark:text-gray-400">Efectivo</p>
                    <p class="text-base font-bold text-content-primary dark:text-white">{{ fmt(summary.efectivo) }}</p>
                </div>
            </div>
            <div class="flex items-center gap-4 rounded-3xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-surface-dark">
                <div class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-2xl bg-blue-50 dark:bg-blue-900/20">
                    <ArrowRightLeft class="h-5 w-5 text-blue-500" />
                </div>
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-content-muted dark:text-gray-400">Transferencia</p>
                    <p class="text-base font-bold text-content-primary dark:text-white">{{ fmt(summary.transferencia) }}</p>
                </div>
            </div>
            <div class="flex items-center gap-4 rounded-3xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-surface-dark">
                <div class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-2xl bg-red-50 dark:bg-red-900/20">
                    <ShoppingBag class="h-5 w-5 text-red-500" />
                </div>
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-content-muted dark:text-gray-400">Proveedores</p>
                    <p class="text-base font-bold text-content-primary dark:text-white">{{ fmt(summary.proveedores) }}</p>
                </div>
            </div>
            <div class="flex items-center gap-4 rounded-3xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-surface-dark">
                <div class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-2xl bg-purple-50 dark:bg-purple-900/20">
                    <Wrench class="h-5 w-5 text-purple-500" />
                </div>
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-content-muted dark:text-gray-400">Gastos Operacionales</p>
                    <p class="text-base font-bold text-content-primary dark:text-white">{{ fmt(summary.operacionales) }}</p>
                </div>
            </div>
        </div>

        <div class="mb-6 overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-surface-dark">
            <div class="flex flex-wrap items-center gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                <Search class="h-5 w-5 text-primary-500" />
                <h2 class="flex-1 font-bold text-content-primary dark:text-white">Filtros</h2>
            </div>
            <div class="grid grid-cols-1 gap-4 p-4 sm:grid-cols-2 lg:grid-cols-6">
                <div>
                    <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-content-muted dark:text-gray-400">Desde</label>
                    <input type="date" v-model="filterForm.from" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                </div>
                <div>
                    <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-content-muted dark:text-gray-400">Hasta</label>
                    <input type="date" v-model="filterForm.to" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                </div>
                <div>
                    <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-content-muted dark:text-gray-400">Tipo</label>
                    <select v-model="filterForm.type" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        <option value="">Todos</option>
                        <option v-for="(label, key) in typeLabels" :key="key" :value="key">{{ label }}</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-content-muted dark:text-gray-400">Método de Pago</label>
                    <select v-model="filterForm.payment_method" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        <option value="">Todos</option>
                        <option value="efectivo">Efectivo</option>
                        <option value="transferencia">Transferencia</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-content-muted dark:text-gray-400">Beneficiario</label>
                    <input type="text" v-model="filterForm.beneficiary" placeholder="Buscar..." class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                </div>
                <div>
                    <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-content-muted dark:text-gray-400">Responsable</label>
                    <select v-model="filterForm.user_id" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        <option value="">Todos</option>
                        <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
                    </select>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-3 border-t border-gray-100 px-6 py-3 dark:border-gray-800">
                <button @click="applyFilters" class="flex items-center gap-2 rounded-2xl bg-primary-500 px-4 py-2 text-sm font-bold text-white shadow-sm transition-colors hover:bg-primary-600">
                    <Search class="h-4 w-4" /> Buscar
                </button>
                <button @click="clearFilters" class="flex items-center gap-2 rounded-2xl border border-gray-200 px-4 py-2 text-sm font-bold text-content-secondary transition-colors hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800">
                    <X class="h-4 w-4" /> Limpiar
                </button>
                <div class="flex-1" />
                <button @click="openNew" class="flex items-center gap-2 rounded-2xl bg-primary-500 px-4 py-2 text-sm font-bold text-white shadow-sm transition-colors hover:bg-primary-600">
                    <Plus class="h-4 w-4" /> Nuevo Egreso
                </button>
                <a :href="route('admin.gastos.export', filterForm.data())" class="flex items-center gap-2 rounded-2xl border border-gray-200 px-4 py-2 text-sm font-bold text-content-secondary transition-colors hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800">
                    <Download class="h-4 w-4" /> Exportar Excel
                </a>
            </div>
        </div>

        <div class="mb-6 overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-surface-dark">
            <div class="flex flex-wrap items-center gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                <ReceiptText class="h-5 w-5 text-primary-500" />
                <h2 class="flex-1 font-bold text-content-primary dark:text-white">Registro de Egresos</h2>
                <span class="text-sm text-content-muted">{{ expenses.total ?? 0 }} resultados</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 text-xs uppercase tracking-wider text-content-muted dark:bg-gray-900/50 dark:text-gray-500">
                        <tr>
                            <th class="px-4 py-3 font-bold">Fecha</th>
                            <th class="px-4 py-3 font-bold">Tipo</th>
                            <th class="px-4 py-3 font-bold">Concepto</th>
                            <th class="px-4 py-3 font-bold">Beneficiario</th>
                            <th class="px-4 py-3 font-bold">Método de Pago</th>
                            <th class="px-4 py-3 text-right font-bold">Monto</th>
                            <th class="px-4 py-3 font-bold">Responsable</th>
                            <th class="px-4 py-3 font-bold">Observación</th>
                            <th class="px-4 py-3 font-bold">Comprobante</th>
                            <th class="px-4 py-3 text-right font-bold">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <tr v-if="!expenses.data?.length">
                            <td colspan="10" class="px-6 py-12 text-center text-sm text-content-muted dark:text-gray-500">
                                No hay egresos registrados.
                            </td>
                        </tr>
                        <tr v-for="e in expenses.data" :key="e.id" class="group transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="whitespace-nowrap px-4 py-3.5 text-sm text-content-primary dark:text-white">
                                {{ formatDate(e.date) }}
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="inline-block rounded-lg px-2.5 py-1 text-[11px] font-bold" :class="typeColors[e.type] || typeColors['otros']">
                                    {{ typeLabels[e.type] || e.type }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-sm text-content-secondary">
                                {{ e.concept || '—' }}
                            </td>
                            <td class="px-4 py-3.5 text-sm font-medium text-content-primary dark:text-white">
                                {{ e.beneficiary || '—' }}
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="inline-block rounded-lg px-2.5 py-1 text-[11px] font-bold" :class="pmColors[e.payment_method]">
                                    {{ pmLabels[e.payment_method] || e.payment_method }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-right text-sm font-bold text-content-primary dark:text-white">
                                {{ fmt(e.amount) }}
                            </td>
                            <td class="px-4 py-3.5 text-sm text-content-secondary">
                                {{ e.user?.name || '—' }}
                            </td>
                            <td class="max-w-[150px] truncate px-4 py-3.5 text-sm text-content-secondary" :title="e.observation ?? ''">
                                {{ e.observation || '—' }}
                            </td>
                            <td class="px-4 py-3.5">
                                <a v-if="e.receipt_file" :href="receiptUrl(e.receipt_file)" target="_blank" class="inline-flex items-center gap-1 rounded-lg bg-primary-50 px-2.5 py-1 text-[11px] font-bold text-primary-600 transition-colors hover:bg-primary-100 dark:bg-primary-900/20 dark:text-primary-400">
                                    <FileText class="h-3 w-3" /> Ver Archivo
                                </a>
                                <span v-else class="text-[11px] text-content-muted">—</span>
                            </td>
                            <td class="px-4 py-3.5 text-right">
                                <div class="flex items-center justify-end gap-1 opacity-0 transition-opacity group-hover:opacity-100">
                                    <button @click="openEdit(e)" class="rounded-xl p-1.5 text-blue-500 transition-colors hover:bg-blue-50 dark:hover:bg-blue-900/20">
                                        <Pencil class="h-3.5 w-3.5" />
                                    </button>
                                    <button @click="deleteExpense(e.id)" class="rounded-xl p-1.5 text-danger transition-colors hover:bg-red-50 dark:hover:bg-red-900/20">
                                        <Trash2 class="h-3.5 w-3.5" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="expenses.last_page > 1" class="flex items-center justify-between border-t border-gray-100 px-6 py-4 text-sm text-content-muted dark:border-gray-800 dark:text-gray-500">
                <span>Página {{ expenses.current_page }} de {{ expenses.last_page }}</span>
                <div class="flex gap-2">
                    <a v-if="expenses.prev_page_url" :href="expenses.prev_page_url" class="rounded-lg bg-gray-50 px-3 py-1.5 font-bold transition-colors hover:bg-gray-100 dark:bg-gray-900">&larr;</a>
                    <a v-if="expenses.next_page_url" :href="expenses.next_page_url" class="rounded-lg bg-gray-50 px-3 py-1.5 font-bold transition-colors hover:bg-gray-100 dark:bg-gray-900">&rarr;</a>
                </div>
            </div>
        </div>

        <div class="mb-6 overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-surface-dark">
            <div class="flex flex-wrap items-center gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                <ReceiptText class="h-5 w-5 text-primary-500" />
                <h2 class="flex-1 font-bold text-content-primary dark:text-white">Distribución de Egresos</h2>
            </div>
            <div class="space-y-3 p-6">
                <div v-for="(item, key) in chart" :key="key" class="flex items-center gap-3">
                    <span class="w-28 text-right text-xs font-bold text-content-secondary dark:text-gray-400">{{ item.label }}</span>
                    <div class="flex-1">
                        <div class="h-5 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                            <div class="h-full rounded-full transition-all duration-500" :style="{ width: (item.total / chartMax * 100) + '%', backgroundColor: item.color }" />
                        </div>
                    </div>
                    <span class="w-28 text-right text-xs font-bold text-content-primary dark:text-white">{{ fmt(item.total) }}</span>
                </div>
                <p v-if="summary.total === 0" class="py-4 text-center text-sm text-content-muted">No hay datos para el período seleccionado.</p>
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
            <div v-if="showForm" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4 backdrop-blur-sm">
                <div class="relative w-full max-w-lg rounded-3xl bg-white p-6 shadow-xl dark:bg-surface-dark">
                    <div class="mb-6 flex items-center justify-between">
                        <h3 class="font-display text-lg font-bold text-content-primary dark:text-white">
                            {{ editingId ? 'Editar Egreso' : 'Nuevo Egreso' }}
                        </h3>
                        <button @click="closeForm" class="rounded-xl p-2 transition-colors hover:bg-gray-100 dark:hover:bg-gray-800">
                            <X class="h-5 w-5 text-content-muted" />
                        </button>
                    </div>
                    <form @submit.prevent="submitForm" class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400">Fecha *</label>
                                <input v-model="form.date" type="date" required class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                                <p v-if="form.errors.date" class="mt-1 text-xs text-danger">{{ form.errors.date }}</p>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400">Tipo de Egreso *</label>
                                <select v-model="form.type" required class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                    <option value="" disabled>Seleccionar...</option>
                                    <option v-for="(label, key) in typeLabels" :key="key" :value="key">{{ label }}</option>
                                </select>
                                <p v-if="form.errors.type" class="mt-1 text-xs text-danger">{{ form.errors.type }}</p>
                            </div>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400">Concepto</label>
                            <input v-model="form.concept" type="text" maxlength="255" placeholder="ej: Compra bebidas, Pago luz mayo..." class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400">Proveedor / Beneficiario</label>
                                <input v-model="form.beneficiary" type="text" list="beneficiaries" placeholder="Nombre..." class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                                <datalist id="beneficiaries">
                                    <option v-for="s in suppliers" :key="s.id" :value="s.company_name" />
                                </datalist>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400">Método de Pago *</label>
                                <select v-model="form.payment_method" required class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                    <option value="" disabled>Seleccionar...</option>
                                    <option value="efectivo">Efectivo</option>
                                    <option value="transferencia">Transferencia</option>
                                </select>
                                <p v-if="form.errors.payment_method" class="mt-1 text-xs text-danger">{{ form.errors.payment_method }}</p>
                            </div>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400">Monto *</label>
                            <input v-model.number="form.amount" type="number" min="1" required placeholder="0" class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                            <p v-if="form.errors.amount" class="mt-1 text-xs text-danger">{{ form.errors.amount }}</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400">Observación</label>
                            <textarea v-model="form.observation" rows="2" maxlength="500" placeholder="Opcional..." class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400">Adjuntar Comprobante</label>
                            <input type="file" accept=".pdf,.jpg,.jpeg,.png" @input="form.receipt_file = ($event.target as HTMLInputElement).files?.[0] ?? null" class="w-full text-sm text-content-secondary file:mr-3 file:rounded-xl file:border-0 file:bg-primary-50 file:px-3 file:py-1.5 file:text-xs file:font-bold file:text-primary-600 dark:file:bg-primary-900/20 dark:file:text-primary-400" />
                        </div>
                        <div class="flex gap-3 pt-2">
                            <button type="button" @click="closeForm" class="flex-1 rounded-2xl border border-gray-200 py-2.5 text-sm font-bold text-content-secondary transition-colors hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800">
                                Cancelar
                            </button>
                            <button type="submit" :disabled="form.processing" class="flex flex-1 items-center justify-center gap-2 rounded-2xl bg-primary-500 py-2.5 text-sm font-bold text-white shadow-sm transition-colors hover:bg-primary-600">
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
