<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import {
    CheckCircle,
    ChevronDown,
    ChevronRight,
    Edit2,
    Image as ImageIcon,
    LayoutGrid,
    List,
    Plus,
    Search,
    Trash2,
    XCircle,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import ProductFormModal from './ProductFormModal.vue';

interface Product {
    id: number;
    category_id: number;
    name: string;
    description: string;
    sku: string;
    barcode: string;
    price: number;
    stock: number;
    min_stock: number;
    is_active: boolean;
    image_path: string | null;
    sub_category: string | null;
    category?: { id: number; name: string };
}

const props = defineProps<{
    products: {
        data: Product[];
        links: any[];
    };
    categories: any[];
    filters: any;
}>();

// ─── State ────────────────────────────────────────────────────────
const search = ref(props.filters.search || '');
const showModal = ref(false);
const editingProduct = ref<Product | null>(null);
const isGrouped = ref(true); // Por defecto agrupado por categoría

// ─── Search Logic ─────────────────────────────────────────────────
function handleSearch() {
    router.get(
        route('products.index'),
        { search: search.value },
        {
            preserveState: true,
            replace: true,
            only: ['products'],
        },
    );
}

// ─── Grouping Logic ───────────────────────────────────────────────
const groupedProducts = computed(() => {
    if (!isGrouped.value) return { 'Todos los Productos': props.products.data };

    const groups: Record<string, Product[]> = {};

    props.products.data.forEach((product) => {
        const catName = product.category?.name || 'Sin Categoría';
        if (!groups[catName]) groups[catName] = [];
        groups[catName].push(product);
    });

    return groups;
});

const expandedGroups = ref<Record<string, boolean>>({});
// Inicializar todos los grupos como expandidos
computed(() => {
    Object.keys(groupedProducts.value).forEach((key) => {
        if (expandedGroups.value[key] === undefined)
            expandedGroups.value[key] = true;
    });
});

function toggleGroup(name: string) {
    expandedGroups.value[name] = !expandedGroups.value[name];
}

// ─── Modal Actions ────────────────────────────────────────────────
function openCreateModal() {
    editingProduct.value = null;
    showModal.value = true;
}

function openEditModal(product: Product) {
    editingProduct.value = product;
    showModal.value = true;
}

function handleDelete(id: number) {
    if (confirm('¿Estás seguro de eliminar este producto?')) {
        router.delete(route('products.destroy', id));
    }
}

function formatPrice(price: number) {
    return new Intl.NumberFormat('es-CL', {
        style: 'currency',
        currency: 'CLP',
    }).format(price);
}
</script>

<template>
    <div class="space-y-6">
        <!-- Control Bar -->
        <div
            class="flex flex-col justify-between gap-4 rounded-3xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-surface-dark md:flex-row md:items-center"
        >
            <div class="relative max-w-lg flex-1">
                <Search
                    class="absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-content-muted"
                />
                <input
                    v-model="search"
                    @input="handleSearch"
                    type="text"
                    placeholder="Buscar por nombre, SKU o código de barras..."
                    class="w-full rounded-2xl border-none bg-gray-50 py-3 pl-12 pr-4 text-sm font-medium transition-all focus:ring-2 focus:ring-primary-500 dark:bg-gray-900"
                />
            </div>

            <div class="flex items-center gap-3">
                <!-- Grouping Toggle -->
                <button
                    @click="isGrouped = !isGrouped"
                    class="rounded-2xl border border-gray-100 p-3 text-content-secondary transition-all hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-800"
                    :title="isGrouped ? 'Vista de Lista' : 'Vista Agrupada'"
                >
                    <LayoutGrid v-if="!isGrouped" class="h-5 w-5" />
                    <List v-else class="h-5 w-5" />
                </button>

                <button
                    @click="openCreateModal"
                    class="flex items-center gap-2 rounded-2xl bg-primary-500 px-6 py-3 text-sm font-bold text-white shadow-primary-md transition-all hover:bg-primary-600 active:scale-95"
                >
                    <Plus class="h-5 w-5" />
                    Nuevo Producto
                </button>
            </div>
        </div>

        <!-- Grouped Product View -->
        <div class="space-y-4">
            <div
                v-for="(items, categoryName) in groupedProducts"
                :key="categoryName"
                class="space-y-2"
            >
                <!-- Category Header -->
                <button
                    @click="toggleGroup(categoryName)"
                    class="group flex w-full items-center justify-between rounded-2xl bg-gray-50 px-6 py-3 transition-colors hover:bg-gray-100 dark:bg-gray-900/50 dark:hover:bg-gray-800"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary-100 text-primary-500 dark:bg-primary-900/30"
                        >
                            <component
                                :is="
                                    expandedGroups[categoryName] !== false
                                        ? ChevronDown
                                        : ChevronRight
                                "
                                class="h-4 w-4"
                            />
                        </div>
                        <span
                            class="font-display text-sm font-bold uppercase tracking-wider text-content-primary dark:text-white"
                            >{{ categoryName }}</span
                        >
                        <span
                            class="rounded-lg border border-gray-100 bg-white px-2 py-1 text-xs font-bold text-content-muted dark:border-gray-700 dark:bg-gray-800"
                        >
                            {{ items.length }} Productos
                        </span>
                    </div>
                </button>

                <!-- Table Content (Collapsible) -->
                <Transition
                    enter-active-class="transition-all duration-300 ease-out"
                    enter-from-class="opacity-0 -translate-y-2 max-h-0"
                    enter-to-class="opacity-100 translate-y-0 max-h-[2000px]"
                    leave-active-class="transition-all duration-200 ease-in"
                    leave-from-class="opacity-100 translate-y-0 max-h-[2000px]"
                    leave-to-class="opacity-0 -translate-y-2 max-h-0"
                >
                    <div
                        v-if="expandedGroups[categoryName] !== false"
                        class="overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-surface-dark"
                    >
                        <table class="w-full border-collapse text-left">
                            <thead>
                                <tr
                                    class="bg-gray-50/50 text-[10px] font-black uppercase tracking-widest text-content-muted dark:bg-gray-900/30 dark:text-gray-500"
                                >
                                    <th class="px-6 py-4">Producto</th>
                                    <th class="px-6 py-4">Sub-categoría</th>
                                    <th class="px-6 py-4">Precio</th>
                                    <th class="px-6 py-4 text-center">Stock</th>
                                    <th class="px-6 py-4 text-center">
                                        Estado
                                    </th>
                                    <th class="px-6 py-4 text-right"></th>
                                </tr>
                            </thead>
                            <tbody
                                class="divide-y divide-gray-100 dark:divide-gray-800"
                            >
                                <tr
                                    v-for="product in items"
                                    :key="product.id"
                                    class="group transition-colors hover:bg-primary-50/30 dark:hover:bg-primary-900/5"
                                    :class="{
                                        'bg-red-50/50 dark:bg-red-900/10':
                                            product.stock <=
                                            (product.min_stock || 5),
                                    }"
                                >
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-4">
                                            <div
                                                class="h-12 w-12 flex-shrink-0 overflow-hidden rounded-xl border border-gray-100 bg-gray-100 dark:border-gray-700 dark:bg-gray-800"
                                            >
                                                <img
                                                    v-if="product.image_path"
                                                    :src="`/storage/${product.image_path}`"
                                                    class="h-full w-full object-cover"
                                                />
                                                <div
                                                    v-else
                                                    class="flex h-full w-full items-center justify-center text-gray-300"
                                                >
                                                    <ImageIcon
                                                        class="h-5 w-5"
                                                    />
                                                </div>
                                            </div>
                                            <div class="min-w-0">
                                                <p
                                                    class="truncate text-sm font-bold text-content-primary transition-colors group-hover:text-primary-500 dark:text-white"
                                                >
                                                    {{ product.name }}
                                                </p>
                                                <p
                                                    class="font-mono text-[10px] uppercase tracking-tighter text-content-muted"
                                                >
                                                    {{
                                                        product.sku || 'SIN SKU'
                                                    }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span
                                            v-if="product.sub_category"
                                            class="rounded-lg bg-accent-50 px-2 py-1 text-[10px] font-bold uppercase tracking-tight text-accent-700 dark:bg-accent-900/20 dark:text-accent-400"
                                        >
                                            {{ product.sub_category }}
                                        </span>
                                        <span
                                            v-else
                                            class="text-[10px] italic text-content-muted"
                                            >Sin sub-categoría</span
                                        >
                                    </td>
                                    <td class="px-6 py-4">
                                        <span
                                            class="text-sm font-black text-primary-500"
                                            >{{
                                                formatPrice(product.price / 100)
                                            }}</span
                                        >
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col items-center">
                                            <span
                                                class="text-sm font-bold"
                                                :class="
                                                    product.stock <=
                                                    (product.min_stock || 5)
                                                        ? 'text-danger'
                                                        : 'text-content-primary dark:text-white'
                                                "
                                            >
                                                {{ product.stock }}
                                            </span>
                                            <span
                                                v-if="
                                                    product.stock <=
                                                    (product.min_stock || 5)
                                                "
                                                class="flex animate-pulse items-center gap-1 text-[9px] font-black uppercase text-danger"
                                            >
                                                Bajo Stock
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex justify-center">
                                            <span
                                                :class="
                                                    product.is_active
                                                        ? 'bg-success/10 text-success'
                                                        : 'bg-gray-100 text-gray-400'
                                                "
                                                class="rounded-full p-1.5"
                                            >
                                                <CheckCircle
                                                    v-if="product.is_active"
                                                    class="h-4 w-4"
                                                />
                                                <XCircle
                                                    v-else
                                                    class="h-4 w-4"
                                                />
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div
                                            class="flex items-center justify-end gap-1 opacity-0 transition-opacity group-hover:opacity-100"
                                        >
                                            <button
                                                @click="openEditModal(product)"
                                                class="rounded-xl p-2 text-content-secondary transition-all hover:bg-primary-50 hover:text-primary-500 dark:hover:bg-gray-800"
                                                title="Editar"
                                            >
                                                <Edit2 class="h-4 w-4" />
                                            </button>
                                            <button
                                                @click="
                                                    handleDelete(product.id)
                                                "
                                                class="rounded-xl p-2 text-content-secondary transition-all hover:bg-red-50 hover:text-danger dark:hover:bg-red-900/20"
                                                title="Eliminar"
                                            >
                                                <Trash2 class="h-4 w-4" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </Transition>
            </div>
        </div>

        <!-- Empty State -->
        <div
            v-if="props.products.data.length === 0"
            class="rounded-4xl border border-gray-100 bg-white p-20 text-center shadow-sm dark:border-gray-800 dark:bg-surface-dark"
        >
            <div
                class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-gray-50 dark:bg-gray-900"
            >
                <Search class="h-10 w-10 text-gray-300" />
            </div>
            <h3
                class="font-display text-xl font-bold text-content-primary dark:text-white"
            >
                No encontramos productos
            </h3>
            <p
                class="mx-auto mt-2 max-w-xs text-content-secondary dark:text-gray-400"
            >
                Intenta ajustar tu búsqueda o crea un nuevo producto ahora
                mismo.
            </p>
            <button
                @click="openCreateModal"
                class="mt-8 rounded-2xl bg-primary-500 px-8 py-3 font-bold text-white transition-all hover:bg-primary-600"
            >
                Crear Primer Producto
            </button>
        </div>

        <!-- Product Form Modal -->
        <ProductFormModal
            :show="showModal"
            :product="editingProduct"
            :categories="categories"
            @close="showModal = false"
            @success="handleSearch"
        />
    </div>
</template>
