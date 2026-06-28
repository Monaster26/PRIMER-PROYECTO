<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { FileSpreadsheet, Upload, X } from 'lucide-vue-next';
import { computed, nextTick, ref, watch } from 'vue';

import ProductToolbar from '@/Components/Product/ProductToolbar.vue';
import AdminProductTable from '@/Components/Product/AdminProductTable.vue';
import AdminProductForm from '@/Components/Product/AdminProductForm.vue';
import StockFormModal from '@/Components/Product/StockFormModal.vue';
import CategoryManager from '@/Components/Product/CategoryManager.vue';

interface CategoryItem {
    id: number;
    name: string;
    slug: string;
    parent_id: number | null;
}

interface CategoryTreeItem {
    id: number;
    name: string;
    slug: string;
    children: CategoryTreeItem[];
}

const props = defineProps<{
    products: {
        data: any[];
        links: any[];
        current_page: number;
        last_page: number;
        prev_page_url: string | null;
        next_page_url: string | null;
    };
    categories: CategoryItem[];
    categoryTree: CategoryTreeItem[];
}>();

const localCategories = ref([...props.categories]);

const search = ref('');
const filterCategory = ref('');
const showCategoryModal = ref(false);
const activeTab = ref<'crear' | 'gestionar'>('crear');
const newCategoryName = ref('');
const parentId = ref<number | null>(null);
const savingCategory = ref(false);
const editingCategoryId = ref<number | null>(null);
const editingCategoryName = ref('');
const expandedCategories = ref<Set<number>>(new Set());
const dropdownOpen = ref(false);
const expandedRoot = ref<number | null>(null);

const selectedLabel = computed(() => {
    if (!filterCategory.value) return 'Todas las categorías';
    for (const root of props.categoryTree) {
        if (String(root.id) === filterCategory.value) return root.name;
        const child = root.children.find(c => String(c.id) === filterCategory.value);
        if (child) return child.name;
    }
    return 'Todas las categorías';
});

function toggleRoot(id: number) {
    expandedRoot.value = expandedRoot.value === id ? null : id;
}

function selectCategory(id: number | null) {
    filterCategory.value = id ? String(id) : '';
    expandedRoot.value = null;
    dropdownOpen.value = false;
    handleSearch();
}

const deletingCategory = ref(false);
const localCategoryTree = ref([...props.categoryTree]);
const showForm = ref(false);
const editingId = ref<number | null>(null);
const imageFile = ref<File | null>(null);
const imagePreview = ref<string | null>(null);

const units = [
    'und',
    'kg',
    'lt',
    'caja',
    'paquete',
    'botella',
    'bolsa',
    'm',
    'm²',
    'm³',
    'par',
    'docena',
];

const form = useForm({
    name: '',
    sku: '',
    barcode: '',
    category_slug: '',
    sub_category: '',
    price: 0,
    cost_price: 0,
    unit: 'und',
    tax_rate: 19,
    stock: 0,
    min_stock: 5,
    is_active: true,
    is_featured: false,
    expiration_date: '',
    sort_order: 0,
    description: '',
});

function openNew() {
    form.reset();
    form.is_active = true;
    form.unit = 'und';
    form.tax_rate = 19;
    form.min_stock = 5;
    editingId.value = null;
    imageFile.value = null;
    imagePreview.value = null;
    showForm.value = true;
}

function openEdit(product: any) {
    form.reset();
    form.name = product.name;
    form.sku = product.sku;
    form.barcode = product.barcode || '';
    form.category_slug = product.category_slug || '';
    form.sub_category = product.sub_category || '';
    form.price = product.price / 100;
    form.cost_price = (product.cost_price || 0) / 100;
    form.unit = product.unit || 'und';
    form.tax_rate = product.tax_rate ?? 19;
    form.stock = product.stock;
    form.min_stock = product.min_stock ?? 5;
    form.is_active = product.is_active;
    form.is_featured = product.is_featured ?? false;
    form.expiration_date = product.expiration_date || '';
    form.sort_order = product.sort_order || 0;
    form.description = product.description || '';
    editingId.value = product.id;
    imageFile.value = null;
    imagePreview.value = product.image_path
        ? '/storage/' + product.image_path
        : null;
    showForm.value = true;
}

function closeForm() {
    showForm.value = false;
    editingId.value = null;
    form.reset();
    imageFile.value = null;
    imagePreview.value = null;
}

function onImageSelect(file: File | undefined) {
    if (!file) return;
    imageFile.value = file;
    const reader = new FileReader();
    reader.onload = () => {
        imagePreview.value = reader.result as string;
    };
    reader.readAsDataURL(file);
}

function removeImage() {
    imageFile.value = null;
    imagePreview.value = null;
}

function submitForm() {
    const payload: Record<string, any> = {
        name: form.name,
        sku: form.sku,
        barcode: form.barcode,
        category_slug: form.category_slug,
        sub_category: form.sub_category,
        price: form.price,
        cost_price: form.cost_price,
        unit: form.unit,
        tax_rate: form.tax_rate,
        stock: form.stock,
        min_stock: form.min_stock,
        is_active: form.is_active,
        is_featured: form.is_featured,
        expiration_date: form.expiration_date || null,
        sort_order: form.sort_order,
        description: form.description,
    };

    if (imageFile.value) {
        payload.image = imageFile.value;
    }

    const options = {
        onSuccess: closeForm,
        onError: (errors: Record<string, string>) => {
            console.error(
                'Validation errors:',
                JSON.stringify(errors, null, 2),
            );
            alert('Error de validación:\n' + Object.values(errors).join('\n'));
        },
    };

    if (editingId.value) {
        payload._method = 'PUT';
        router.post(
            route('admin.codigos.update', editingId.value),
            payload,
            options,
        );
    } else {
        router.post(route('admin.codigos.store'), payload, options);
    }
}

function deleteProduct(id: number) {
    if (!confirm('¿Eliminar este producto?')) return;
    router.delete(route('admin.codigos.destroy', id), { preserveScroll: true });
}

async function saveCategory() {
    const name = newCategoryName.value.trim();
    if (!name || savingCategory.value) return;
    savingCategory.value = true;
    try {
        await window.axios.post(route('admin.categorias.store'), {
            name,
            parent_id: parentId.value,
        });
        newCategoryName.value = '';
        parentId.value = null;
        showCategoryModal.value = false;
        router.reload({ only: ['categories', 'categoryTree'] });
    } catch (e: any) {
        alert(
            e.response?.data?.errors?.name?.[0] ||
                'Error al guardar la categoría',
        );
    } finally {
        savingCategory.value = false;
    }
}

function toggleExpand(id: number) {
    const s = new Set(expandedCategories.value);
    if (s.has(id)) s.delete(id);
    else s.add(id);
    expandedCategories.value = s;
}

function startEdit(cat: { id: number; name: string }) {
    editingCategoryId.value = cat.id;
    editingCategoryName.value = cat.name;
}

function cancelEdit() {
    editingCategoryId.value = null;
    editingCategoryName.value = '';
}

async function confirmEdit(id: number) {
    const name = editingCategoryName.value.trim();
    if (!name) return;
    try {
        await window.axios.put(route('admin.categorias.update', id), { name });
        editingCategoryId.value = null;
        editingCategoryName.value = '';
        router.reload({ only: ['categories', 'categoryTree'] });
    } catch (e: any) {
        alert(
            e.response?.data?.errors?.name?.[0] ||
                'Error al editar la categoría',
        );
    }
}

async function deleteCategory(id: number) {
    if (!confirm('¿Eliminar esta categoría?')) return;
    if (deletingCategory.value) return;
    deletingCategory.value = true;
    try {
        await window.axios.delete(route('admin.categorias.destroy', id));
        router.reload({ only: ['categories', 'categoryTree'] });
    } catch (e: any) {
        if (e.response?.status === 409) {
            alert(
                'No puedes eliminar una categoría que contiene productos asignados.',
            );
        } else {
            alert('Error al eliminar la categoría.');
        }
    } finally {
        deletingCategory.value = false;
    }
}

function handleSearch() {
    router.get(
        route('admin.codigos.index'),
        { search: search.value, category_id: filterCategory.value || null },
        { preserveState: true, replace: true },
    );
}

const showStockForm = ref(false);
const stockProduct = ref<any>(null);
const keepOpen = ref(false);
const skuQuery = ref('');
const nameQuery = ref('');
const nameResults = ref<any[]>([]);
const showNameDropdown = ref(false);
const nameLoading = ref(false);

const stockForm = useForm({
    quantity: 1,
    unit_cost: 0,
    notes: '',
    expiration_date: '',
});

const totalInvestment = computed(
    () => stockForm.quantity * (stockForm.unit_cost || 0),
);
const totalInvestmentFormatted = computed(
    () =>
        '$ ' +
        totalInvestment.value.toLocaleString('es-CO', {
            minimumFractionDigits: 0,
        }),
);

watch(
    () => props.categories,
    (val) => {
        localCategories.value = [...val];
    },
    { deep: true },
);

watch(
    () => props.categoryTree,
    (val) => {
        localCategoryTree.value = [...val];
    },
    { deep: true },
);

let nameTimer: ReturnType<typeof setTimeout>;
watch(nameQuery, (val) => {
    clearTimeout(nameTimer);
    if (!val.trim()) {
        nameResults.value = [];
        showNameDropdown.value = false;
        return;
    }
    nameLoading.value = true;
    nameTimer = setTimeout(async () => {
        try {
            const res = await window.axios.get(
                route('admin.codigos.search-name'),
                {
                    params: { query: val.trim() },
                },
            );
            nameResults.value = res.data;
            showNameDropdown.value = true;
        } catch {
            nameResults.value = [];
            showNameDropdown.value = false;
        } finally {
            nameLoading.value = false;
        }
    }, 300);
});

function hideNameDropdown() {
    setTimeout(() => (showNameDropdown.value = false), 200);
}

async function handleSkuEnter() {
    const q = skuQuery.value.trim();
    if (!q) return;
    try {
        const res = await window.axios.get(route('admin.codigos.search-sku'), {
            params: { query: q },
        });
        if (res.data) {
            selectSearchProduct(res.data);
        }
    } catch {
        /* SKU not found, ignore */
    }
}

function selectSearchProduct(product: any) {
    stockProduct.value = product;
    skuQuery.value = '';
    nameQuery.value = '';
    nameResults.value = [];
    showNameDropdown.value = false;
}

function openStockForm(product: any) {
    stockProduct.value = product;
    stockForm.reset();
    stockForm.quantity = 1;
    stockForm.unit_cost = 0;
    keepOpen.value = false;
    skuQuery.value = '';
    nameQuery.value = '';
    nameResults.value = [];
    showNameDropdown.value = false;
    showStockForm.value = true;
}

function openEmptyStockForm() {
    stockProduct.value = null;
    stockForm.reset();
    stockForm.quantity = 1;
    stockForm.unit_cost = 0;
    keepOpen.value = false;
    skuQuery.value = '';
    nameQuery.value = '';
    nameResults.value = [];
    showNameDropdown.value = false;
    showStockForm.value = true;
}

function closeStockForm() {
    showStockForm.value = false;
    stockProduct.value = null;
    stockForm.reset();
    keepOpen.value = false;
    skuQuery.value = '';
    nameQuery.value = '';
    nameResults.value = [];
    showNameDropdown.value = false;
}

function submitStockForm() {
    if (!stockProduct.value) return;
    stockForm.post(route('admin.codigos.add-stock', stockProduct.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            if (keepOpen.value) {
                stockForm.reset('quantity', 'unit_cost', 'expiration_date');
                stockForm.quantity = 1;
                skuQuery.value = '';
                nameQuery.value = '';
                nameResults.value = [];
                showNameDropdown.value = false;
            } else {
                closeStockForm();
            }
        },
    });
}

const showBatchesModal = ref(false);
const batchesProduct = ref<any>(null);
const batchesData = ref<any[]>([]);
const batchesLoading = ref(false);
const editingBatchId = ref<number | null>(null);
const editQuantity = ref<number>(0);
const batchSaving = ref(false);

async function fetchBatches(productId: number) {
    const res = await window.axios.get(route('admin.codigos.batches', productId));
    batchesData.value = res.data;
}

async function openBatches(product: any) {
    batchesProduct.value = product;
    batchesData.value = [];
    editingBatchId.value = null;
    batchesLoading.value = true;
    showBatchesModal.value = true;
    try {
        await fetchBatches(product.id);
    } catch {
        batchesData.value = [];
    } finally {
        batchesLoading.value = false;
    }
}

function closeBatches() {
    showBatchesModal.value = false;
    batchesProduct.value = null;
    batchesData.value = [];
    editingBatchId.value = null;
}

function startEditBatch(batch: any) {
    editingBatchId.value = batch.id;
    editQuantity.value = batch.quantity;
}

function cancelEditBatch() {
    editingBatchId.value = null;
}

async function saveBatchAdjustment(batch: any) {
    if (editQuantity.value === batch.quantity) { editingBatchId.value = null; return; }
    batchSaving.value = true;
    try {
        const res = await window.axios.patch(
            route('admin.codigos.batches.update', [batchesProduct.value!.id, batch.id]),
            { quantity: editQuantity.value }
        );
        batchesData.value = res.data;
        editingBatchId.value = null;
        router.reload({ only: ['products'] });
    } catch (e: any) {
        alert(e.response?.data?.error ?? 'Error al ajustar cantidad.');
    } finally {
        batchSaving.value = false;
    }
}

async function writeOffBatch(batch: any) {
    if (!confirm(`¿Confirmas dar de baja ${batch.quantity} unidades vencidas de ${batchesProduct.value?.name}?`)) return;
    batchSaving.value = true;
    try {
        const res = await window.axios.delete(
            route('admin.codigos.batches.destroy', [batchesProduct.value!.id, batch.id])
        );
        batchesData.value = res.data;
        router.reload({ only: ['products'] });
    } catch (e: any) {
        alert(e.response?.data?.error ?? 'Error al dar de baja.');
    } finally {
        batchSaving.value = false;
    }
}

const showImportForm = ref(false);
const importFile = ref<File | null>(null);
const importProcessing = ref(false);
const importInput = ref<HTMLInputElement | null>(null);

function openImport() {
    importFile.value = null;
    showImportForm.value = true;
}

function closeImport() {
    showImportForm.value = false;
    importFile.value = null;
    importProcessing.value = false;
    if (importInput.value) importInput.value.value = '';
}

function onImportFileSelect(e: Event) {
    const target = e.target as HTMLInputElement;
    importFile.value = target.files?.[0] ?? null;
}

function submitImport() {
    if (!importFile.value) return;
    importProcessing.value = true;
    const formData = new FormData();
    formData.append('file', importFile.value);
    router.post(route('admin.codigos.import'), formData, {
        onSuccess: () => {
            closeImport();
        },
        onError: (errors) => {
            alert('Error al importar:\n' + Object.values(errors).join('\n'));
            importProcessing.value = false;
        },
        onFinish: () => {
            importProcessing.value = false;
        },
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Catálogo de Productos" />
    <AdminLayout>
        <template #title>
            <h1
                class="font-display text-xl font-bold text-content-primary dark:text-white"
            >
                Catálogo de Productos
            </h1>
        </template>

        <div
            class="overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-surface-dark"
        >
            <ProductToolbar
                :search="search"
                :filter-category="filterCategory"
                :selected-label="selectedLabel"
                :category-tree="localCategoryTree"
                :dropdown-open="dropdownOpen"
                :expanded-root="expandedRoot"
                @update:search="search = $event"
                @search="handleSearch"
                @update:dropdown-open="dropdownOpen = $event"
                @toggle-root="toggleRoot"
                @select-category="selectCategory"
                @open-new="openNew"
                @open-import="openImport"
                @open-empty-stock-form="openEmptyStockForm"
                @open-category-modal="showCategoryModal = true"
            />

            <AdminProductTable
                :products="products"
                @open-edit="openEdit"
                @open-stock-form="openStockForm"
                @delete-product="deleteProduct"
                @open-batches="openBatches"
            />
        </div>

        <AdminProductForm
            :show-form="showForm"
            :editing-id="editingId"
            :form="form"
            :image-preview="imagePreview"
            :category-tree="props.categoryTree"
            :units="units"
            @close="closeForm"
            @submit="submitForm"
            @select-image="onImageSelect"
            @remove-image="removeImage"
        />

        <StockFormModal
            :show-stock-form="showStockForm"
            :stock-product="stockProduct"
            :stock-form="stockForm"
            :total-investment-formatted="totalInvestmentFormatted"
            :keep-open="keepOpen"
            :sku-query="skuQuery"
            :name-query="nameQuery"
            :name-results="nameResults"
            :show-name-dropdown="showNameDropdown"
            :name-loading="nameLoading"
            @close="closeStockForm"
            @submit="submitStockForm"
            @update:sku-query="skuQuery = $event"
            @update:name-query="nameQuery = $event"
            @update:keep-open="keepOpen = $event"
            @sku-enter="handleSkuEnter"
            @select-search-product="selectSearchProduct"
            @hide-name-dropdown="hideNameDropdown"
        />

        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="showBatchesModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4 backdrop-blur-sm"
            >
                <div
                    class="relative w-full max-w-lg rounded-3xl bg-white p-6 shadow-xl dark:bg-surface-dark"
                >
                    <div class="mb-4 flex items-center justify-between">
                        <h3
                            class="font-display text-lg font-bold text-content-primary dark:text-white"
                        >
                            Lotes — {{ batchesProduct?.name }}
                        </h3>
                        <button
                            @click="closeBatches"
                            class="rounded-xl p-2 transition-colors hover:bg-gray-100 dark:hover:bg-gray-800"
                        >
                            <X class="h-5 w-5 text-content-muted" />
                        </button>
                    </div>

                    <div v-if="batchesLoading" class="flex justify-center py-8">
                        <div
                            class="h-6 w-6 animate-spin rounded-full border-2 border-primary-500 border-t-transparent"
                        ></div>
                    </div>

                    <div v-else-if="!batchesData.length" class="py-8 text-center text-sm text-content-muted">
                        No hay lotes activos para este producto.
                    </div>

                    <div v-else class="space-y-2">
                        <div
                            v-for="b in batchesData"
                            :key="b.id"
                            class="flex items-center justify-between rounded-2xl border border-gray-100 bg-gray-50 px-4 py-3 dark:border-gray-700 dark:bg-gray-800/50"
                        >
                            <div class="flex flex-col gap-0.5">
                                <template v-if="editingBatchId === b.id">
                                    <div class="flex items-center gap-2">
                                        <input
                                            type="number"
                                            min="0"
                                            v-model.number="editQuantity"
                                            class="w-24 rounded-xl border border-gray-300 px-3 py-1.5 text-sm dark:border-gray-600 dark:bg-gray-800"
                                            :disabled="batchSaving"
                                        />
                                        <span class="text-xs text-content-muted">uds</span>
                                    </div>
                                    <div class="flex gap-1.5 mt-1">
                                        <button
                                            @click="saveBatchAdjustment(b)"
                                            :disabled="batchSaving"
                                            class="rounded-lg bg-primary-500 px-3 py-1 text-xs font-bold text-white hover:bg-primary-600 disabled:opacity-50"
                                        >
                                            Guardar
                                        </button>
                                        <button
                                            @click="cancelEditBatch"
                                            :disabled="batchSaving"
                                            class="rounded-lg border border-gray-300 px-3 py-1 text-xs font-bold text-content-secondary hover:bg-gray-100 dark:border-gray-600 dark:hover:bg-gray-700"
                                        >
                                            Cancelar
                                        </button>
                                    </div>
                                </template>
                                <template v-else>
                                    <span class="text-sm font-bold text-content-primary dark:text-white">
                                        {{ b.quantity }} uds
                                    </span>
                                    <span class="text-xs text-content-muted">
                                        Vence: {{ b.expiration_date ?? 'Sin fecha' }}
                                    </span>
                                    <span v-if="b.notes" class="text-xs text-content-muted">
                                        {{ b.notes }}
                                    </span>
                                </template>
                            </div>
                            <div class="flex flex-col items-end gap-1.5">
                                <span
                                    class="rounded-full px-2.5 py-1 text-[11px] font-bold uppercase"
                                    :class="{
                                        'bg-red-100 text-red-700': b.status === 'expired',
                                        'bg-amber-100 text-amber-700': b.status === 'warning',
                                        'bg-green-100 text-green-700': b.status === 'ok',
                                    }"
                                >
                                    {{ b.status === 'expired' ? 'Vencido' : b.status === 'warning' ? 'Por vencer' : 'Ok' }}
                                </span>
                                <div v-if="editingBatchId !== b.id" class="flex gap-1.5">
                                    <button
                                        @click="startEditBatch(b)"
                                        class="rounded-lg border border-gray-300 px-2.5 py-1 text-[11px] font-bold text-content-secondary hover:bg-gray-100 dark:border-gray-600 dark:hover:bg-gray-700"
                                    >
                                        Ajustar
                                    </button>
                                    <button
                                        v-if="b.status === 'expired' || b.status === 'warning'"
                                        @click="writeOffBatch(b)"
                                        :disabled="batchSaving"
                                        class="rounded-lg bg-red-500 px-2.5 py-1 text-[11px] font-bold text-white hover:bg-red-600 disabled:opacity-50"
                                    >
                                        Dar de baja
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>

        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="showImportForm"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4 backdrop-blur-sm"
            >
                <div
                    class="relative w-full max-w-md rounded-3xl bg-white p-6 shadow-xl dark:bg-surface-dark"
                >
                    <div class="mb-6 flex items-center justify-between">
                        <h3
                            class="font-display text-lg font-bold text-content-primary dark:text-white"
                        >
                            Importar Productos
                        </h3>
                        <button
                            @click="closeImport"
                            class="rounded-xl p-2 transition-colors hover:bg-gray-100 dark:hover:bg-gray-800"
                        >
                            <X class="h-5 w-5 text-content-muted" />
                        </button>
                    </div>
                    <form @submit.prevent="submitImport" class="space-y-4">
                        <div>
                            <label
                                class="mb-2 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                            >
                                Archivo Excel (.xlsx, .xls, .csv)
                            </label>
                            <div
                                @click="importInput?.click()"
                                class="flex cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed border-gray-300 bg-gray-50 py-8 transition-colors hover:border-primary-400 dark:border-gray-600 dark:bg-gray-900"
                            >
                                <input
                                    ref="importInput"
                                    type="file"
                                    accept=".xlsx,.xls,.csv"
                                    class="hidden"
                                    @change="onImportFileSelect"
                                />
                                <FileSpreadsheet
                                    class="mb-2 h-10 w-10 text-content-muted"
                                />
                                <template v-if="importFile">
                                    <p
                                        class="text-sm font-bold text-content-primary dark:text-white"
                                    >
                                        {{ importFile.name }}
                                    </p>
                                    <p class="mt-1 text-xs text-content-muted">
                                        {{
                                            (importFile.size / 1024).toFixed(1)
                                        }}
                                        KB
                                    </p>
                                </template>
                                <template v-else>
                                    <p
                                        class="text-sm font-bold text-content-muted"
                                    >
                                        CLICK PARA SELECCIONAR
                                    </p>
                                    <p class="text-xs text-content-muted">
                                        Columnas: Codigo, Descripcion, Precio
                                        Costo, Precio Venta, Precio Mayoreo,
                                        Inventario, Inv. Minimo, Departamento
                                    </p>
                                </template>
                            </div>
                        </div>
                        <div class="flex gap-3 pt-2">
                            <button
                                type="button"
                                @click="closeImport"
                                class="flex-1 rounded-2xl border border-gray-200 py-2.5 text-sm font-bold text-content-secondary transition-colors hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800"
                            >
                                Cancelar
                            </button>
                            <button
                                type="submit"
                                :disabled="!importFile || importProcessing"
                                class="flex flex-1 items-center justify-center gap-2 rounded-2xl bg-primary-500 py-2.5 text-sm font-bold text-white shadow-sm transition-colors hover:bg-primary-600 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <Upload class="h-4 w-4" />
                                {{
                                    importProcessing
                                        ? 'Importando...'
                                        : 'Importar'
                                }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>

        <CategoryManager
            :show-category-modal="showCategoryModal"
            :active-tab="activeTab"
            :new-category-name="newCategoryName"
            :parent-id="parentId"
            :saving-category="savingCategory"
            :local-categories="localCategories"
            :local-category-tree="localCategoryTree"
            :editing-category-id="editingCategoryId"
            :editing-category-name="editingCategoryName"
            :expanded-categories="expandedCategories"
            :deleting-category="deletingCategory"
            @close="showCategoryModal = false"
            @update:active-tab="activeTab = $event"
            @update:new-category-name="newCategoryName = $event"
            @update:parent-id="parentId = $event"
            @update:editing-category-name="editingCategoryName = $event"
            @save="saveCategory"
            @start-edit="startEdit"
            @cancel-edit="cancelEdit"
            @confirm-edit="confirmEdit"
            @delete-category="deleteCategory"
            @toggle-expand="toggleExpand"
        />
    </AdminLayout>
</template>
