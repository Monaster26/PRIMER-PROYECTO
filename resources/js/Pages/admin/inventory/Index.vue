<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { CalendarDays, Eye, Plus, X } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    adjustments: {
        data: any[];
        links: any[];
        current_page: number;
        last_page: number;
        prev_page_url: string | null;
        next_page_url: string | null;
    };
    filters: { date: string | null };
}>();

const showDetail = ref(false);
const detailAdj = ref<any>(null);

function viewDetail(adj: any) {
    detailAdj.value = adj;
    showDetail.value = true;
}

function fmtCLP(cents: number): string {
    return '$' + Math.abs(Math.round(cents / 100)).toLocaleString('es-CL');
}

function fmtDate(iso: string): string {
    const d = new Date(iso);
    return d.toLocaleDateString('es-CL', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        hour12: false,
    });
}

function applyFilter() {
    router.get(
        route('admin.inventory-adjustments.index'),
        { date: filterDate.value || null },
        {
            preserveState: true,
            replace: true,
        },
    );
}

const filterDate = ref(props.filters.date || '');

function itemsTotal(
    items: any[],
    pred: (d: number) => boolean,
): { units: number; cost: number } {
    let units = 0,
        cost = 0;
    for (const i of items) {
        if (pred(i.difference)) {
            units += Math.abs(i.difference);
            cost += Math.abs(i.difference * i.cost_price);
        }
    }
    return { units, cost };
}

function diffClass(d: number): string {
    if (d < 0) return 'text-red-600';
    if (d > 0) return 'text-green-600';
    return '';
}

function fmtDiff(d: number): string {
    return d > 0 ? '+' + d : String(d);
}
</script>

<template>
    <AdminLayout>
        <Head title="Historial de Auditorías" />
        <div class="mx-auto max-w-6xl space-y-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1
                        class="text-2xl font-bold text-content-primary dark:text-white"
                    >
                        Historial de Auditorías
                    </h1>
                    <p class="mt-1 text-sm text-content-muted">
                        Consulta los inventarios realizados por día o mes.
                    </p>
                </div>
                <Link
                    :href="route('admin.inventory-adjustments.create')"
                    class="flex items-center gap-2 rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-bold text-white shadow-sm transition-colors hover:bg-emerald-700"
                >
                    <Plus class="h-4 w-4" />
                    Nueva Auditoría
                </Link>
            </div>

            <!-- Filter -->
            <div class="flex items-center justify-end">
                <div class="relative">
                    <CalendarDays
                        class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-content-muted"
                    />
                    <input
                        v-model="filterDate"
                        @change="applyFilter"
                        type="date"
                        class="w-52 rounded-xl border border-gray-200 bg-white py-2.5 pl-9 pr-4 text-sm text-content-primary outline-none transition-shadow focus:border-primary-400 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                    />
                </div>
            </div>

            <!-- Table -->
            <div
                v-if="adjustments.data.length === 0"
                class="rounded-2xl border-2 border-dashed border-gray-200 p-16 text-center dark:border-gray-700"
            >
                <p class="text-lg font-medium text-content-muted">
                    No hay auditorías registradas
                </p>
                <p class="mt-1 text-sm text-content-muted">
                    Realiza tu primera toma de inventario para ver resultados
                    aquí.
                </p>
            </div>
            <div
                v-else
                class="overflow-hidden rounded-2xl border border-gray-200 dark:border-gray-700"
            >
                <table class="w-full text-sm">
                    <thead>
                        <tr
                            class="bg-gray-50 text-left text-xs font-bold uppercase tracking-wider text-content-muted dark:bg-gray-800/50"
                        >
                            <th class="px-4 py-3">ID</th>
                            <th class="px-4 py-3">Fecha / Hora</th>
                            <th class="px-4 py-3">Auditor</th>
                            <th class="px-4 py-3 text-right">Pérdidas</th>
                            <th class="px-4 py-3 text-right">Sobrantes</th>
                            <th class="px-4 py-3 text-center">Estado</th>
                            <th class="px-4 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody
                        class="divide-y divide-gray-100 dark:divide-gray-800"
                    >
                        <tr
                            v-for="adj in adjustments.data"
                            :key="adj.id"
                            class="hover:bg-gray-50 dark:hover:bg-gray-800/30"
                        >
                            <td
                                class="px-4 py-3 font-mono text-xs text-content-muted"
                            >
                                #{{ adj.id }}
                            </td>
                            <td
                                class="px-4 py-3 font-mono text-sm tabular-nums text-content-primary dark:text-white"
                            >
                                {{ fmtDate(adj.created_at) }}
                            </td>
                            <td class="px-4 py-3">
                                {{ adj.user?.name || '—' }}
                            </td>
                            <td
                                class="px-4 py-3 text-right font-mono tabular-nums text-red-600"
                            >
                                {{
                                    fmtCLP(
                                        itemsTotal(adj.items, (d) => d < 0)
                                            .cost,
                                    )
                                }}
                            </td>
                            <td
                                class="px-4 py-3 text-right font-mono tabular-nums text-green-600"
                            >
                                {{
                                    fmtCLP(
                                        itemsTotal(adj.items, (d) => d > 0)
                                            .cost,
                                    )
                                }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span
                                    class="inline-flex rounded-full bg-emerald-100 px-2.5 py-0.5 text-[10px] font-bold uppercase text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400"
                                    >Completado</span
                                >
                            </td>
                            <td class="px-4 py-3 text-right">
                                <button
                                    @click="viewDetail(adj)"
                                    class="rounded-lg p-1.5 text-content-muted transition-colors hover:bg-blue-50 hover:text-blue-600 dark:hover:bg-blue-900/30"
                                    title="Ver detalle"
                                >
                                    <Eye class="h-4 w-4" />
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div
                v-if="adjustments.last_page > 1"
                class="flex items-center justify-center gap-1"
            >
                <Link
                    v-for="(link, i) in adjustments.links"
                    :key="i"
                    :href="link.url || '#'"
                    v-html="link.label"
                    class="rounded-lg px-3 py-1.5 text-sm font-medium transition-colors"
                    :class="
                        link.active
                            ? 'bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-400'
                            : 'text-content-muted hover:bg-gray-100 dark:hover:bg-gray-800'
                    "
                />
            </div>
        </div>

        <!-- Detail Modal -->
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="showDetail && detailAdj"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4 backdrop-blur-sm"
                @click.self="showDetail = false"
            >
                <div
                    class="relative w-full max-w-2xl rounded-3xl bg-white shadow-xl dark:bg-gray-900"
                >
                    <!-- Header -->
                    <div
                        class="flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-gray-800"
                    >
                        <div>
                            <h3
                                class="text-lg font-bold text-content-primary dark:text-white"
                            >
                                Auditoría #{{ detailAdj.id }}
                            </h3>
                            <p class="text-xs text-content-muted">
                                {{ fmtDate(detailAdj.created_at) }} · Auditor:
                                {{ detailAdj.user?.name || '—' }}
                            </p>
                        </div>
                        <button
                            @click="showDetail = false"
                            class="rounded-lg p-1 transition-colors hover:bg-gray-100 dark:hover:bg-gray-800"
                        >
                            <X class="h-5 w-5 text-content-muted" />
                        </button>
                    </div>
                    <!-- Body -->
                    <div class="max-h-[60vh] overflow-y-auto p-6">
                        <table class="w-full text-sm">
                            <thead>
                                <tr
                                    class="text-left text-xs font-bold uppercase tracking-wider text-content-muted"
                                >
                                    <th class="pb-2">Producto</th>
                                    <th class="pb-2 text-center">Stock Sis.</th>
                                    <th class="pb-2 text-center">Conteo</th>
                                    <th class="pb-2 text-center">Dif.</th>
                                    <th class="pb-2 text-right">Impacto $</th>
                                </tr>
                            </thead>
                            <tbody
                                class="divide-y divide-gray-100 dark:divide-gray-800"
                            >
                                <tr
                                    v-for="item in detailAdj.items"
                                    :key="item.id"
                                    class="text-sm"
                                >
                                    <td
                                        class="py-2 font-medium text-content-primary dark:text-white"
                                    >
                                        {{ item.product?.name || '—' }}
                                    </td>
                                    <td
                                        class="py-2 text-center font-mono tabular-nums"
                                    >
                                        {{ item.system_stock }}
                                    </td>
                                    <td
                                        class="py-2 text-center font-mono tabular-nums"
                                    >
                                        {{ item.counted_stock }}
                                    </td>
                                    <td
                                        class="py-2 text-center font-mono tabular-nums"
                                        :class="diffClass(item.difference)"
                                    >
                                        {{ fmtDiff(item.difference) }}
                                    </td>
                                    <td
                                        class="py-2 text-right font-mono tabular-nums"
                                        :class="diffClass(item.difference)"
                                    >
                                        {{
                                            fmtCLP(
                                                item.difference *
                                                    item.cost_price,
                                            )
                                        }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <!-- Footer totals -->
                    <div
                        class="flex items-center justify-end gap-6 border-t border-gray-100 px-6 py-4 dark:border-gray-800"
                    >
                        <div>
                            <span class="text-xs text-content-muted"
                                >Pérdidas</span
                            >
                            <p class="font-mono font-bold text-red-600">
                                {{
                                    fmtCLP(
                                        itemsTotal(
                                            detailAdj.items,
                                            (d) => d < 0,
                                        ).cost,
                                    )
                                }}
                            </p>
                        </div>
                        <div>
                            <span class="text-xs text-content-muted"
                                >Sobrantes</span
                            >
                            <p class="font-mono font-bold text-green-600">
                                {{
                                    fmtCLP(
                                        itemsTotal(
                                            detailAdj.items,
                                            (d) => d > 0,
                                        ).cost,
                                    )
                                }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </AdminLayout>
</template>
