<script setup lang="ts">
import { Percent, Plus } from 'lucide-vue-next';

const props = defineProps<{
    promotions: any;
    filters: { search?: string; type?: string; status?: string };
    typeLabels: Record<string, string>;
    typeColors: Record<string, string>;
}>();

const emit = defineEmits<{
    'update:filters': [f: any];
    new: [];
    edit: [promotion: any];
    delete: [id: number];
    toggle: [id: number];
    applyFilters: [];
    clearFilters: [];
}>();

const statusText = (p: any) => {
    if (!p.is_active) return 'Inactiva';
    if (p.expires_at && new Date(p.expires_at) < new Date()) return 'Vencida';
    if (p.starts_at && new Date(p.starts_at) > new Date()) return 'Programada';
    return 'Activa';
};

const statusColor = (p: any) => {
    if (!p.is_active || (p.expires_at && new Date(p.expires_at) < new Date()))
        return 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300';
    if (p.starts_at && new Date(p.starts_at) > new Date())
        return 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300';
    return 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300';
};

const fmtDate = (d: string | null) =>
    d ? new Date(d).toLocaleDateString('es-CL') : '—';

const fmtCents = (v: number | undefined | null) =>
    v != null ? '$' + (v / 100).toLocaleString('es-CL') : null;

const discountText = (p: any) => {
    if (p.type === 'buy_x_get_y') {
        const t = fmtCents(p.conditions?.special_price_total);
        if (t) return t;
        return `${p.rewards?.discount_pct ?? 100}%`;
    }
    if (p.type === 'min_qty_discount') {
        const t = fmtCents(p.conditions?.special_price);
        if (t) return t;
        return `${p.conditions?.discount_pct}%`;
    }
    if (p.type === 'bundle_discount') {
        const t = fmtCents(p.conditions?.special_price_total);
        if (t) return t;
        return `${p.conditions?.discount_pct}%`;
    }
    if (p.type === 'special_price') {
        return fmtCents(p.conditions?.special_price) ?? '—';
    }
    if (p.type === 'category_discount') {
        return `${p.conditions?.discount_pct ?? '—'}%`;
    }
    return '—';
};

const usageText = (p: any) => {
    if (!p.max_uses) return '∞';
    return `${p.used_count ?? 0}/${p.max_uses}`;
};
</script>

<template>
    <div
        class="overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-surface-dark"
    >
        <!-- Toolbar -->
        <div
            class="flex flex-wrap items-center gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-800"
        >
            <Percent class="h-5 w-5 text-primary-500" />
            <h2
                class="flex-1 text-sm font-bold text-content-primary dark:text-white"
            >
                Todas las promociones
            </h2>

            <select
                :value="filters.type"
                @change="
                    emit('update:filters', {
                        ...filters,
                        type: ($event.target as HTMLSelectElement).value,
                    });
                    emit('applyFilters');
                "
                class="rounded-2xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs font-bold text-content-secondary dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400"
            >
                <option value="">Todos los tipos</option>
                <option value="buy_x_get_y">Compra X, Lleva Y</option>
                <option value="min_qty_discount">Dto. por Cantidad</option>
                <option value="bundle_discount">Combo</option>
                <option value="special_price">Precio Especial</option>
                <option value="category_discount">Dto. Categoría</option>
            </select>

            <select
                :value="filters.status"
                @change="
                    emit('update:filters', {
                        ...filters,
                        status: ($event.target as HTMLSelectElement).value,
                    });
                    emit('applyFilters');
                "
                class="rounded-2xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs font-bold text-content-secondary dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400"
            >
                <option value="">Todos los estados</option>
                <option value="active">Activas</option>
                <option value="expired">Vencidas / Inactivas</option>
                <option value="scheduled">Programadas</option>
            </select>

            <input
                :value="filters.search"
                @input="
                    emit('update:filters', {
                        ...filters,
                        search: ($event.target as HTMLInputElement).value,
                    });
                    emit('applyFilters');
                "
                placeholder="Buscar por nombre..."
                class="max-w-xs rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2 text-xs font-bold text-content-primary placeholder:text-content-muted dark:border-gray-700 dark:bg-gray-900 dark:text-white"
            />

            <button
                v-if="filters.search || filters.type || filters.status"
                @click="emit('clearFilters')"
                class="rounded-2xl border border-gray-200 px-3 py-2 text-xs font-bold text-content-secondary hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800"
            >
                Limpiar
            </button>

            <button
                @click="emit('new')"
                class="ml-auto flex items-center gap-2 rounded-2xl bg-primary-500 px-4 py-2 text-sm font-bold text-white shadow-sm transition-colors hover:bg-primary-600"
            >
                <Plus class="h-4 w-4" /> Nueva Promoción
            </button>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead
                    class="bg-gray-50 text-xs uppercase tracking-wider text-content-muted dark:bg-gray-900/50 dark:text-gray-500"
                >
                    <tr>
                        <th class="px-6 py-3 font-bold">Nombre</th>
                        <th class="px-6 py-3 font-bold">Tipo</th>
                        <th class="px-6 py-3 text-right font-bold">Dto.</th>
                        <th class="px-6 py-3 font-bold">Vigencia</th>
                        <th class="px-6 py-3 text-center font-bold">
                            Prioridad
                        </th>
                        <th class="px-6 py-3 text-center font-bold">Usos</th>
                        <th class="px-6 py-3 text-right font-bold">Impacto</th>
                        <th class="px-6 py-3 text-center font-bold">Estado</th>
                        <th class="px-6 py-3 text-right font-bold">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    <tr v-if="!promotions.data?.length">
                        <td
                            colspan="9"
                            class="px-6 py-12 text-center text-sm text-content-muted dark:text-gray-500"
                        >
                            No hay promociones registradas.
                        </td>
                    </tr>
                    <tr
                        v-for="p in promotions.data"
                        :key="p.id"
                        class="group transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/50"
                    >
                        <td class="px-6 py-4">
                            <span
                                class="text-sm font-bold text-content-primary dark:text-white"
                                >{{ p.name }}</span
                            >
                            <p
                                v-if="p.description"
                                class="mt-0.5 line-clamp-1 text-xs text-content-muted"
                            >
                                {{ p.description }}
                            </p>
                        </td>
                        <td class="px-6 py-4">
                            <span
                                class="rounded-lg px-2.5 py-1 text-xs font-bold"
                                :class="typeColors[p.type] || ''"
                            >
                                {{ typeLabels[p.type] || p.type }}
                            </span>
                        </td>
                        <td
                            class="px-6 py-4 text-right text-sm font-bold text-primary-500"
                        >
                            {{ discountText(p) }}
                        </td>
                        <td class="px-6 py-4 text-xs text-content-secondary">
                            {{ fmtDate(p.starts_at) }} →
                            {{ fmtDate(p.expires_at) }}
                        </td>
                        <td
                            class="px-6 py-4 text-center text-sm font-bold text-content-primary dark:text-white"
                        >
                            {{ p.priority }}
                        </td>
                        <td
                            class="px-6 py-4 text-center text-xs font-bold text-content-secondary"
                        >
                            {{ usageText(p) }}
                        </td>
                        <td
                            class="px-6 py-4 text-right text-xs font-bold text-content-secondary"
                        >
                            {{ fmtCents(p.total_discount_given) }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span
                                class="rounded-full px-2.5 py-1 text-[11px] font-bold uppercase"
                                :class="statusColor(p)"
                                >{{ statusText(p) }}</span
                            >
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div
                                class="flex items-center justify-end gap-2 opacity-0 transition-opacity group-hover:opacity-100"
                            >
                                <button
                                    @click="emit('toggle', p.id)"
                                    class="rounded-xl p-2 transition-colors"
                                    :class="
                                        p.is_active
                                            ? 'text-amber-500 hover:bg-amber-50 dark:hover:bg-amber-900/20'
                                            : 'text-green-500 hover:bg-green-50 dark:hover:bg-green-900/20'
                                    "
                                    :title="
                                        p.is_active ? 'Desactivar' : 'Activar'
                                    "
                                >
                                    <span class="text-sm font-bold">{{
                                        p.is_active ? '🔴' : '🟢'
                                    }}</span>
                                </button>
                                <button
                                    @click="emit('edit', p)"
                                    class="rounded-xl p-2 text-blue-500 transition-colors hover:bg-blue-50 dark:hover:bg-blue-900/20"
                                >
                                    <svg
                                        class="h-4 w-4"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                        />
                                    </svg>
                                </button>
                                <button
                                    @click="emit('delete', p.id)"
                                    class="rounded-xl p-2 text-danger transition-colors hover:bg-red-50 dark:hover:bg-red-900/20"
                                >
                                    <svg
                                        class="h-4 w-4"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                        stroke-width="2"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                        />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div
            v-if="promotions.last_page > 1"
            class="flex items-center justify-between border-t border-gray-100 px-6 py-4 text-sm text-content-muted dark:border-gray-800 dark:text-gray-500"
        >
            <span
                >Página {{ promotions.current_page }} de
                {{ promotions.last_page }}</span
            >
            <div class="flex gap-2">
                <a
                    v-if="promotions.prev_page_url"
                    :href="promotions.prev_page_url"
                    class="rounded-lg bg-gray-50 px-3 py-1.5 font-bold transition-colors hover:bg-gray-100 dark:bg-gray-900"
                    >←</a
                >
                <a
                    v-if="promotions.next_page_url"
                    :href="promotions.next_page_url"
                    class="rounded-lg bg-gray-50 px-3 py-1.5 font-bold transition-colors hover:bg-gray-100 dark:bg-gray-900"
                    >→</a
                >
            </div>
        </div>
    </div>
</template>
