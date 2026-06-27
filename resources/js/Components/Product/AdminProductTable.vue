<script setup lang="ts">
import { Pencil, Plus, Trash2 } from 'lucide-vue-next';

interface Product {
    id: number;
    sku: string;
    name: string;
    image_path: string | null;
    category?: { name: string } | null;
    category_slug?: string;
    sub_category?: string;
    price: number;
    cost_price: number;
    stock: number;
    min_stock: number | null;
    is_active: boolean;
    expiration_date: string | null;
    batches_expiring_count?: number;
    batches_expired_count?: number;
    [key: string]: any;
}

const props = defineProps<{
    products: {
        data: Product[];
        links: any[];
        current_page: number;
        last_page: number;
        prev_page_url: string | null;
        next_page_url: string | null;
    };
}>();

const emit = defineEmits<{
    openEdit: [product: Product];
    openStockForm: [product: Product];
    deleteProduct: [id: number];
    openBatches: [product: Product];
}>();

const fmt = (v: number) =>
    '$' + v.toLocaleString('es-CO', { minimumFractionDigits: 0 });
</script>

<template>
    <div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead
                    class="bg-gray-50 text-xs uppercase tracking-wider text-content-muted dark:bg-gray-900/50 dark:text-gray-500"
                >
                    <tr>
                        <th class="px-6 py-3 font-bold">SKU</th>
                        <th class="px-6 py-3 font-bold">FOTO</th>
                        <th class="px-6 py-3 font-bold">CATEGORÍA</th>
                        <th class="px-6 py-3 font-bold">Nombre</th>
                        <th class="px-6 py-3 text-right font-bold">
                            Precio Costo
                        </th>
                        <th class="px-6 py-3 text-right font-bold">
                            Precio Venta
                        </th>
                        <th class="px-6 py-3 text-center font-bold">
                            Stock
                        </th>
                        <th class="px-6 py-3 text-center font-bold">
                            Stock Mín.
                        </th>
                        <th class="px-6 py-3 text-center font-bold">
                            Activo
                        </th>
                        <th class="px-6 py-3 text-right font-bold">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody
                    class="divide-y divide-gray-100 dark:divide-gray-800"
                >
                    <tr v-if="!props.products.data?.length">
                        <td
                            colspan="10"
                            class="px-6 py-12 text-center text-sm text-content-muted dark:text-gray-500"
                        >
                            No hay productos registrados.
                        </td>
                    </tr>
                    <tr
                        v-for="p in props.products.data"
                        :key="p.id"
                        class="group transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/50"
                    >
                        <td
                            class="px-6 py-4 font-mono text-sm font-bold text-content-primary dark:text-white"
                        >
                            {{ p.sku }}
                        </td>
                        <td>
                            <div
                                class="flex h-12 w-12 items-center justify-center overflow-hidden rounded-lg border border-gray-200 bg-gray-50"
                            >
                                <img
                                    v-if="p.image_path"
                                    :src="`/storage/${p.image_path}`"
                                    :alt="p.name"
                                    class="h-full w-full object-cover"
                                />
                                <svg
                                    v-else
                                    class="h-6 w-6 text-gray-400"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                    />
                                </svg>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span
                                class="inline-block rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400"
                            >
                                {{ p.category?.name || 'Sin categoría' }}
                            </span>
                        </td>
                        <td
                            class="px-6 py-4 text-sm font-medium text-content-primary dark:text-white"
                        >
                            {{ p.name }}
                        </td>
                        <td
                            class="px-6 py-4 text-right text-sm font-bold text-content-primary dark:text-white"
                        >
                            {{ fmt(p.cost_price / 100) }}
                        </td>
                        <td
                            class="px-6 py-4 text-right text-sm font-bold text-primary-500"
                        >
                            {{ fmt(p.price / 100) }}
                        </td>
                        <td
                            class="px-6 py-4 text-center"
                        >
                            <div class="flex flex-col items-center gap-0.5">
                                <span
                                    class="text-sm font-bold"
                                    :class="
                                        p.stock <= (p.min_stock || 5)
                                            ? 'text-danger'
                                            : 'text-content-primary dark:text-white'
                                    "
                                >
                                    {{ p.stock }}
                                </span>
                                <div
                                    v-if="p.expiration_date"
                                    class="flex items-center gap-1"
                                >
                                    <button
                                        v-if="(p.batches_expired_count ?? 0) > 0"
                                        @click="emit('openBatches', p)"
                                        class="flex items-center gap-0.5 rounded px-1.5 py-0.5 text-[11px] font-bold text-red-600 transition-colors hover:bg-red-50 dark:hover:bg-red-900/20"
                                        title="Ver lotes vencidos"
                                    >
                                        <span>🔴</span>
                                        {{ p.batches_expired_count }}
                                    </button>
                                    <button
                                        v-if="(p.batches_expiring_count ?? 0) > 0"
                                        @click="emit('openBatches', p)"
                                        class="flex items-center gap-0.5 rounded px-1.5 py-0.5 text-[11px] font-bold text-amber-600 transition-colors hover:bg-amber-50 dark:hover:bg-amber-900/20"
                                        title="Ver lotes por vencer"
                                    >
                                        <span>⚠️</span>
                                        {{ p.batches_expiring_count }}
                                    </button>
                                </div>
                            </div>
                        </td>
                        <td
                            class="px-6 py-4 text-center text-sm text-content-secondary dark:text-gray-400"
                        >
                            {{ p.min_stock ?? '—' }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span
                                :class="
                                    p.is_active
                                        ? 'bg-success/10 text-success'
                                        : 'bg-gray-100 text-gray-400'
                                "
                                class="rounded-full px-2 py-1 text-[10px] font-bold uppercase"
                            >
                                {{ p.is_active ? 'Sí' : 'No' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div
                                class="flex items-center justify-end gap-2 opacity-0 transition-opacity group-hover:opacity-100"
                            >
                                <button
                                    @click="emit('openStockForm', p)"
                                    class="rounded-xl p-2 text-success transition-colors hover:bg-success/10"
                                    title="Añadir stock"
                                >
                                    <Plus class="h-4 w-4" />
                                </button>
                                <button
                                    @click="emit('openEdit', p)"
                                    class="rounded-xl p-2 text-blue-500 transition-colors hover:bg-blue-50 dark:hover:bg-blue-900/20"
                                >
                                    <Pencil class="h-4 w-4" />
                                </button>
                                <button
                                    @click="emit('deleteProduct', p.id)"
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
            v-if="props.products.last_page > 1"
            class="flex items-center justify-between border-t border-gray-100 px-6 py-4 text-sm text-content-muted dark:border-gray-800 dark:text-gray-500"
        >
            <span
                >Página {{ props.products.current_page }} de
                {{ props.products.last_page }}</span
            >
            <div class="flex gap-2">
                <a
                    v-if="props.products.prev_page_url"
                    :href="props.products.prev_page_url"
                    class="rounded-lg bg-gray-50 px-3 py-1.5 font-bold transition-colors hover:bg-gray-100 dark:bg-gray-900"
                    >←</a
                >
                <a
                    v-if="props.products.next_page_url"
                    :href="props.products.next_page_url"
                    class="rounded-lg bg-gray-50 px-3 py-1.5 font-bold transition-colors hover:bg-gray-100 dark:bg-gray-900"
                    >→</a
                >
            </div>
        </div>
    </div>
</template>
