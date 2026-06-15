<script setup lang="ts">
import { ref, computed } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import { 
    Search, Plus, Edit2, Trash2, 
    AlertTriangle, CheckCircle, XCircle, 
    Image as ImageIcon, ChevronRight, ChevronDown,
    LayoutGrid, List
} from 'lucide-vue-next';
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
    category?: { id: number, name: string };
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
    router.get(route('products.index'), { search: search.value }, { 
        preserveState: true, 
        replace: true,
        only: ['products'] 
    });
}

// ─── Grouping Logic ───────────────────────────────────────────────
const groupedProducts = computed(() => {
    if (!isGrouped.value) return { 'Todos los Productos': props.products.data };

    const groups: Record<string, Product[]> = {};
    
    props.products.data.forEach(product => {
        const catName = product.category?.name || 'Sin Categoría';
        if (!groups[catName]) groups[catName] = [];
        groups[catName].push(product);
    });

    return groups;
});

const expandedGroups = ref<Record<string, boolean>>({});
// Inicializar todos los grupos como expandidos
computed(() => {
    Object.keys(groupedProducts.value).forEach(key => {
        if (expandedGroups.value[key] === undefined) expandedGroups.value[key] = true;
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
    return new Intl.NumberFormat('es-CL', { style: 'currency', currency: 'CLP' }).format(price);
}
</script>

<template>
    <div class="space-y-6">
        <!-- Control Bar -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-surface-dark p-4 rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm">
            <div class="relative flex-1 max-w-lg">
                <Search class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-content-muted" />
                <input 
                    v-model="search"
                    @input="handleSearch"
                    type="text" 
                    placeholder="Buscar por nombre, SKU o código de barras..."
                    class="w-full pl-12 pr-4 py-3 rounded-2xl border-none bg-gray-50 dark:bg-gray-900 focus:ring-2 focus:ring-primary-500 transition-all text-sm font-medium"
                />
            </div>
            
            <div class="flex items-center gap-3">
                <!-- Grouping Toggle -->
                <button 
                    @click="isGrouped = !isGrouped"
                    class="p-3 rounded-2xl border border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800 transition-all text-content-secondary"
                    :title="isGrouped ? 'Vista de Lista' : 'Vista Agrupada'"
                >
                    <LayoutGrid v-if="!isGrouped" class="w-5 h-5" />
                    <List v-else class="w-5 h-5" />
                </button>

                <button 
                    @click="openCreateModal"
                    class="flex items-center gap-2 bg-primary-500 hover:bg-primary-600 text-white px-6 py-3 rounded-2xl font-bold text-sm transition-all shadow-primary-md active:scale-95"
                >
                    <Plus class="w-5 h-5" />
                    Nuevo Producto
                </button>
            </div>
        </div>

        <!-- Grouped Product View -->
        <div class="space-y-4">
            <div v-for="(items, categoryName) in groupedProducts" :key="categoryName" class="space-y-2">
                <!-- Category Header -->
                <button 
                    @click="toggleGroup(categoryName)"
                    class="w-full flex items-center justify-between px-6 py-3 bg-gray-50 dark:bg-gray-900/50 rounded-2xl hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors group"
                >
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-500">
                            <component :is="expandedGroups[categoryName] !== false ? ChevronDown : ChevronRight" class="w-4 h-4" />
                        </div>
                        <span class="font-display font-bold text-content-primary dark:text-white uppercase tracking-wider text-sm">{{ categoryName }}</span>
                        <span class="text-xs font-bold text-content-muted bg-white dark:bg-gray-800 px-2 py-1 rounded-lg border border-gray-100 dark:border-gray-700">
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
                    <div v-if="expandedGroups[categoryName] !== false" class="overflow-hidden bg-white dark:bg-surface-dark rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50/50 dark:bg-gray-900/30 text-content-muted dark:text-gray-500 text-[10px] uppercase tracking-widest font-black">
                                    <th class="px-6 py-4">Producto</th>
                                    <th class="px-6 py-4">Sub-categoría</th>
                                    <th class="px-6 py-4">Precio</th>
                                    <th class="px-6 py-4 text-center">Stock</th>
                                    <th class="px-6 py-4 text-center">Estado</th>
                                    <th class="px-6 py-4 text-right"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                <tr v-for="product in items" :key="product.id" 
                                    class="hover:bg-primary-50/30 dark:hover:bg-primary-900/5 transition-colors group"
                                    :class="{ 'bg-red-50/50 dark:bg-red-900/10': product.stock < 5 }"
                                >
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-4">
                                            <div class="w-12 h-12 rounded-xl bg-gray-100 dark:bg-gray-800 overflow-hidden flex-shrink-0 border border-gray-100 dark:border-gray-700">
                                                <img v-if="product.image_path" :src="`/storage/${product.image_path}`" class="w-full h-full object-cover" />
                                                <div v-else class="w-full h-full flex items-center justify-center text-gray-300">
                                                    <ImageIcon class="w-5 h-5" />
                                                </div>
                                            </div>
                                            <div class="min-w-0">
                                                <p class="text-sm font-bold text-content-primary dark:text-white truncate group-hover:text-primary-500 transition-colors">{{ product.name }}</p>
                                                <p class="text-[10px] text-content-muted font-mono uppercase tracking-tighter">{{ product.sku || 'SIN SKU' }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span v-if="product.sub_category" class="text-[10px] font-bold px-2 py-1 rounded-lg bg-accent-50 dark:bg-accent-900/20 text-accent-700 dark:text-accent-400 uppercase tracking-tight">
                                            {{ product.sub_category }}
                                        </span>
                                        <span v-else class="text-[10px] text-content-muted italic">Sin sub-categoría</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-sm font-black text-primary-500">{{ formatPrice(product.price) }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col items-center">
                                            <span class="text-sm font-bold" :class="product.stock < 5 ? 'text-danger' : 'text-content-primary dark:text-white'">
                                                {{ product.stock }}
                                            </span>
                                            <span v-if="product.stock < 5" class="flex items-center gap-1 text-[9px] font-black text-danger uppercase animate-pulse">
                                                Bajo Stock
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex justify-center">
                                            <span :class="product.is_active ? 'bg-success/10 text-success' : 'bg-gray-100 text-gray-400'" class="p-1.5 rounded-full">
                                                <CheckCircle v-if="product.is_active" class="w-4 h-4" />
                                                <XCircle v-else class="w-4 h-4" />
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <button @click="openEditModal(product)" class="p-2 rounded-xl text-content-secondary hover:text-primary-500 hover:bg-primary-50 dark:hover:bg-gray-800 transition-all" title="Editar">
                                                <Edit2 class="w-4 h-4" />
                                            </button>
                                            <button @click="handleDelete(product.id)" class="p-2 rounded-xl text-content-secondary hover:text-danger hover:bg-red-50 dark:hover:bg-red-900/20 transition-all" title="Eliminar">
                                                <Trash2 class="w-4 h-4" />
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
        <div v-if="props.products.data.length === 0" class="bg-white dark:bg-surface-dark rounded-4xl p-20 text-center border border-gray-100 dark:border-gray-800 shadow-sm">
            <div class="w-20 h-20 bg-gray-50 dark:bg-gray-900 rounded-full flex items-center justify-center mx-auto mb-6">
                <Search class="w-10 h-10 text-gray-300" />
            </div>
            <h3 class="text-xl font-display font-bold text-content-primary dark:text-white">No encontramos productos</h3>
            <p class="text-content-secondary dark:text-gray-400 mt-2 max-w-xs mx-auto">Intenta ajustar tu búsqueda o crea un nuevo producto ahora mismo.</p>
            <button @click="openCreateModal" class="mt-8 bg-primary-500 text-white px-8 py-3 rounded-2xl font-bold hover:bg-primary-600 transition-all">
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
