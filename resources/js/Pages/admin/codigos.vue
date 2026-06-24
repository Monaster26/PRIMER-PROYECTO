<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { categoriesData } from '@/Stores/categoryStore';
import { Head, router, useForm } from '@inertiajs/vue3';
import {
    FileSpreadsheet,
    Image,
    Package,
    Pencil,
    Plus,
    Save,
    Search,
    ToggleLeft,
    ToggleRight,
    Trash2,
    Upload,
    X,
} from 'lucide-vue-next';
import { computed, nextTick, ref, watch } from 'vue';

const props = defineProps<{
    products: {
        data: any[];
        links: any[];
        current_page: number;
        last_page: number;
        prev_page_url: string | null;
        next_page_url: string | null;
    };
}>();

const search = ref('');
const showForm = ref(false);
const editingId = ref<number | null>(null);
const imageFile = ref<File | null>(null);
const imagePreview = ref<string | null>(null);
const fileInputRef = ref<HTMLInputElement | null>(null);
const barcodeInputRef = ref<HTMLInputElement | null>(null);

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

const selectedCategory = computed(
    () => categoriesData.find((c) => c.id === form.category_slug) ?? null,
);

const subcategories = computed(
    () => selectedCategory.value?.subcategories ?? [],
);

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
    setTimeout(() => barcodeInputRef.value?.focus(), 100);
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
    setTimeout(() => barcodeInputRef.value?.focus(), 100);
}

function closeForm() {
    showForm.value = false;
    editingId.value = null;
    form.reset();
    imageFile.value = null;
    imagePreview.value = null;
}

function onImageSelect(e: Event) {
    const target = e.target as HTMLInputElement;
    const file = target.files?.[0];
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
    if (fileInputRef.value) fileInputRef.value.value = '';
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

function handleSearch() {
    router.get(
        route('admin.codigos.index'),
        { search: search.value },
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
const skuSearchRef = ref<HTMLInputElement | null>(null);
const nameSearchRef = ref<HTMLInputElement | null>(null);
const quantityInputRef = ref<HTMLInputElement | null>(null);
const costoInputRef = ref<HTMLInputElement | null>(null);
const fechaInputRef = ref<HTMLInputElement | null>(null);
const notaInputRef = ref<HTMLInputElement | null>(null);

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
    } catch {}
}

function selectSearchProduct(product: any) {
    stockProduct.value = product;
    skuQuery.value = '';
    nameQuery.value = '';
    nameResults.value = [];
    showNameDropdown.value = false;
    nextTick(() => quantityInputRef.value?.focus());
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
    nextTick(() => skuSearchRef.value?.focus());
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
    nextTick(() => skuSearchRef.value?.focus());
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

function cascadeCantidad() {
    nextTick(() => costoInputRef.value?.focus());
}
function cascadeCosto() {
    nextTick(() => fechaInputRef.value?.focus());
}
function cascadeFecha() {
    nextTick(() => notaInputRef.value?.focus());
}
function cascadeNota() {
    if (stockProduct.value) submitStockForm();
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
                nextTick(() => skuSearchRef.value?.focus());
            } else {
                closeStockForm();
            }
        },
    });
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

const fmt = (v: number) =>
    '$' + v.toLocaleString('es-CO', { minimumFractionDigits: 0 });
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
            <div
                class="flex flex-wrap items-center gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-800"
            >
                <Package class="h-5 w-5 text-primary-500" />
                <h2
                    class="flex-1 font-bold text-content-primary dark:text-white"
                >
                    Productos Registrados
                </h2>
                <div class="relative w-64">
                    <Search
                        class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-content-muted"
                    />
                    <input
                        v-model="search"
                        @input="handleSearch"
                        type="text"
                        placeholder="Buscar producto..."
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 py-2 pl-10 pr-4 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                    />
                </div>
                <button
                    @click="openImport"
                    class="flex items-center gap-2 rounded-2xl border border-gray-200 bg-white px-4 py-2 text-sm font-bold text-content-secondary shadow-sm transition-colors hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800"
                >
                    <Upload class="h-4 w-4" /> Importar Excel
                </button>
                <button
                    @click="openEmptyStockForm"
                    class="flex items-center gap-2 rounded-2xl bg-emerald-600 px-4 py-2 text-sm font-bold text-white shadow-sm transition-colors hover:bg-emerald-700"
                >
                    <Package class="h-4 w-4" /> Ingresar Mercancía
                </button>
                <button
                    @click="openNew"
                    class="flex items-center gap-2 rounded-2xl bg-primary-500 px-4 py-2 text-sm font-bold text-white shadow-sm transition-colors hover:bg-primary-600"
                >
                    <Plus class="h-4 w-4" /> Nuevo Producto
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead
                        class="bg-gray-50 text-xs uppercase tracking-wider text-content-muted dark:bg-gray-900/50 dark:text-gray-500"
                    >
                        <tr>
                            <th class="px-6 py-3 font-bold">SKU</th>
                            <th class="px-6 py-3 font-bold">FOTO</th>
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
                        <tr v-if="!products.data?.length">
                            <td
                                colspan="9"
                                class="px-6 py-12 text-center text-sm text-content-muted dark:text-gray-500"
                            >
                                No hay productos registrados.
                            </td>
                        </tr>
                        <tr
                            v-for="p in products.data"
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
                                class="px-6 py-4 text-center text-sm font-bold"
                                :class="
                                    p.stock <= (p.min_stock || 5)
                                        ? 'text-danger'
                                        : 'text-content-primary dark:text-white'
                                "
                            >
                                {{ p.stock }}
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
                                        @click="openStockForm(p)"
                                        class="rounded-xl p-2 text-success transition-colors hover:bg-success/10"
                                        title="Añadir stock"
                                    >
                                        <Plus class="h-4 w-4" />
                                    </button>
                                    <button
                                        @click="openEdit(p)"
                                        class="rounded-xl p-2 text-blue-500 transition-colors hover:bg-blue-50 dark:hover:bg-blue-900/20"
                                    >
                                        <Pencil class="h-4 w-4" />
                                    </button>
                                    <button
                                        @click="deleteProduct(p.id)"
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
                v-if="products.last_page > 1"
                class="flex items-center justify-between border-t border-gray-100 px-6 py-4 text-sm text-content-muted dark:border-gray-800 dark:text-gray-500"
            >
                <span
                    >Página {{ products.current_page }} de
                    {{ products.last_page }}</span
                >
                <div class="flex gap-2">
                    <a
                        v-if="products.prev_page_url"
                        :href="products.prev_page_url"
                        class="rounded-lg bg-gray-50 px-3 py-1.5 font-bold transition-colors hover:bg-gray-100 dark:bg-gray-900"
                        >←</a
                    >
                    <a
                        v-if="products.next_page_url"
                        :href="products.next_page_url"
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
                    class="relative max-h-[90vh] w-full max-w-3xl overflow-y-auto rounded-3xl bg-white p-6 shadow-xl dark:bg-surface-dark"
                >
                    <div class="mb-6 flex items-center justify-between">
                        <h3
                            class="font-display text-lg font-bold text-content-primary dark:text-white"
                        >
                            {{
                                editingId ? 'Editar Producto' : 'Nuevo Producto'
                            }}
                        </h3>
                        <button
                            @click="closeForm"
                            class="rounded-xl p-2 transition-colors hover:bg-gray-100 dark:hover:bg-gray-800"
                        >
                            <X class="h-5 w-5 text-content-muted" />
                        </button>
                    </div>

                    <form @submit.prevent="submitForm">
                        <div class="flex flex-col lg:flex-row gap-6">
                            <div class="w-full lg:w-64 lg:flex-shrink-0 space-y-5">
                                <div>
                                    <label
                                        class="mb-2 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                                        >Imagen</label
                                    >
                                    <div
                                        @click="fileInputRef?.click()"
                                        class="relative flex aspect-square cursor-pointer flex-col items-center justify-center overflow-hidden rounded-2xl border-2 border-dashed border-gray-300 bg-gray-50 transition-colors hover:border-primary-400 dark:border-gray-600 dark:bg-gray-900"
                                    >
                                        <input
                                            ref="fileInputRef"
                                            type="file"
                                            accept="image/*"
                                            class="hidden"
                                            @change="onImageSelect"
                                        />
                                        <template v-if="imagePreview">
                                            <img
                                                :src="imagePreview"
                                                class="absolute inset-0 h-full w-full object-cover"
                                            />
                                            <button
                                                type="button"
                                                @click.stop="removeImage"
                                                class="absolute right-2 top-2 z-10 rounded-full bg-black/50 p-1.5 text-white transition-colors hover:bg-black/70"
                                            >
                                                <X class="h-4 w-4" />
                                            </button>
                                        </template>
                                        <template v-else>
                                            <Image
                                                class="mb-2 h-10 w-10 text-content-muted"
                                            />
                                            <span
                                                class="px-4 text-center text-xs font-bold text-content-muted"
                                                >CLICK PARA SUBIR FOTO</span
                                            >
                                            <span
                                                class="mt-1 text-[10px] text-content-muted"
                                                >800x800px · max 2MB</span
                                            >
                                        </template>
                                    </div>
                                </div>

                                <div>
                                    <label
                                        class="mb-2 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                                        >Estado Visible</label
                                    >
                                    <button
                                        type="button"
                                        @click="
                                            form.is_active = !form.is_active
                                        "
                                        class="flex w-full items-center gap-3 rounded-2xl border border-gray-200 px-4 py-2.5 transition-colors hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800"
                                    >
                                        <component
                                            :is="
                                                form.is_active
                                                    ? ToggleRight
                                                    : ToggleLeft
                                            "
                                            class="h-6 w-6"
                                            :class="
                                                form.is_active
                                                    ? 'text-success'
                                                    : 'text-content-muted'
                                            "
                                        />
                                        <span
                                            class="text-sm font-bold"
                                            :class="
                                                form.is_active
                                                    ? 'text-success'
                                                    : 'text-content-muted'
                                            "
                                        >
                                            {{
                                                form.is_active
                                                    ? 'Activo'
                                                    : 'Inactivo'
                                            }}
                                        </span>
                                    </button>
                                </div>

                                <div>
                                    <label
                                        class="mb-2 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                                        >Producto Destacado / Oferta</label
                                    >
                                    <button
                                        type="button"
                                        @click="
                                            form.is_featured = !form.is_featured
                                        "
                                        class="flex w-full items-center gap-3 rounded-2xl border border-gray-200 px-4 py-2.5 transition-colors hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800"
                                    >
                                        <component
                                            :is="
                                                form.is_featured
                                                    ? ToggleRight
                                                    : ToggleLeft
                                            "
                                            class="h-6 w-6"
                                            :class="
                                                form.is_featured
                                                    ? 'text-amber-500'
                                                    : 'text-content-muted'
                                            "
                                        />
                                        <span
                                            class="text-sm font-bold"
                                            :class="
                                                form.is_featured
                                                    ? 'text-amber-500'
                                                    : 'text-content-muted'
                                            "
                                        >
                                            {{
                                                form.is_featured
                                                    ? 'Destacado'
                                                    : 'Normal'
                                            }}
                                        </span>
                                    </button>
                                </div>

                                <div>
                                    <label
                                        class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                                        >Orden en Tienda</label
                                    >
                                    <input
                                        v-model.number="form.sort_order"
                                        type="number"
                                        min="0"
                                        class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                    />
                                </div>
                            </div>

                            <div class="min-w-0 flex-1 space-y-4">
                                <div>
                                    <label
                                        class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                                        >Nombre Comercial</label
                                    >
                                    <input
                                        v-model="form.name"
                                        type="text"
                                        required
                                        placeholder="Ej: Arroz Grado 1 1kg"
                                        class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                    />
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label
                                            class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                                            >SKU / Código Interno</label
                                        >
                                        <input
                                            v-model="form.sku"
                                            type="text"
                                            required
                                            class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 font-mono text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                        />
                                    </div>
                                    <div>
                                        <label
                                            class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                                            >Código de Barras</label
                                        >
                                        <input
                                            ref="barcodeInputRef"
                                            v-model="form.barcode"
                                            type="text"
                                            class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 font-mono text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                        />
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label
                                            class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                                            >Departamento</label
                                        >
                                        <select
                                            v-model="form.category_slug"
                                            class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                        >
                                            <option value="">
                                                Sin categoría
                                            </option>
                                            <option
                                                v-for="cat in categoriesData"
                                                :key="cat.id"
                                                :value="cat.id"
                                            >
                                                {{ cat.name }}
                                            </option>
                                        </select>
                                    </div>
                                    <div>
                                        <label
                                            class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                                            >Sub-Categoría</label
                                        >
                                        <select
                                            v-model="form.sub_category"
                                            class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                        >
                                            <option value="">
                                                Sin subcategoría
                                            </option>
                                            <option
                                                v-for="sub in subcategories"
                                                :key="sub"
                                                :value="sub"
                                            >
                                                {{ sub }}
                                            </option>
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <label
                                        class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                                        >Precio de Venta ($)</label
                                    >
                                    <input
                                        v-model.number="form.price"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        required
                                        class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-right text-sm text-xl font-bold text-primary-500 dark:border-gray-700 dark:bg-gray-900"
                                        placeholder="0"
                                    />
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label
                                            class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                                            >Unidad</label
                                        >
                                        <select
                                            v-model="form.unit"
                                            class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                        >
                                            <option
                                                v-for="u in units"
                                                :key="u"
                                                :value="u"
                                            >
                                                {{ u }}
                                            </option>
                                        </select>
                                    </div>
                                    <div>
                                        <label
                                            class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                                            >IVA (%)</label
                                        >
                                        <input
                                            v-model.number="form.tax_rate"
                                            type="number"
                                            min="0"
                                            max="100"
                                            class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                        />
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label
                                            class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                                            >Fecha de Vencimiento</label
                                        >
                                        <input
                                            v-model="form.expiration_date"
                                            type="date"
                                            class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                        />
                                    </div>
                                    <div></div>
                                </div>

                                <div class="grid grid-cols-3 gap-4">
                                    <div>
                                        <label
                                            class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                                            >Stock Disponible</label
                                        >
                                        <input
                                            v-model.number="form.stock"
                                            type="number"
                                            min="0"
                                            required
                                            class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                        />
                                    </div>
                                    <div>
                                        <label
                                            class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                                            >Stock Mínimo</label
                                        >
                                        <input
                                            v-model.number="form.min_stock"
                                            type="number"
                                            min="0"
                                            class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                        />
                                    </div>
                                    <div>
                                        <label
                                            class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                                            >Precio Costo ($)</label
                                        >
                                        <input
                                            v-model.number="form.cost_price"
                                            type="number"
                                            min="0"
                                            step="0.01"
                                            class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-6 border-t-2 border-primary-500" />

                        <div class="flex items-center justify-between gap-4">
                            <button
                                type="button"
                                @click="closeForm"
                                class="px-4 py-2 text-sm font-bold text-content-secondary transition-colors hover:text-content-primary"
                            >
                                Cancelar
                            </button>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="flex items-center gap-2 rounded-2xl bg-primary-500 px-6 py-2.5 text-sm font-bold text-white shadow-sm transition-colors hover:bg-primary-600"
                            >
                                <Save class="h-4 w-4" />
                                {{
                                    editingId
                                        ? 'Actualizar Producto'
                                        : 'Guardar Producto'
                                }}
                            </button>
                        </div>
                    </form>
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
                v-if="showStockForm"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4 backdrop-blur-sm"
            >
                <div
                    class="relative w-full max-w-2xl rounded-3xl bg-white p-6 shadow-xl dark:bg-surface-dark"
                >
                    <div class="mb-4 flex items-center justify-between">
                        <h3
                            class="font-display text-lg font-bold text-content-primary dark:text-white"
                        >
                            Añadir Stock
                        </h3>
                        <button
                            @click="closeStockForm"
                            class="rounded-xl p-2 transition-colors hover:bg-gray-100 dark:hover:bg-gray-800"
                        >
                            <X class="h-5 w-5 text-content-muted" />
                        </button>
                    </div>
                    <form @submit.prevent="submitStockForm" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="relative">
                                <div
                                    class="relative flex items-center rounded-2xl border border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-900"
                                >
                                    <span
                                        class="absolute left-4 text-xs font-bold text-content-muted"
                                        >SKU</span
                                    >
                                    <input
                                        ref="skuSearchRef"
                                        v-model="skuQuery"
                                        type="text"
                                        placeholder="Código / SKU..."
                                        @keydown.enter.prevent="handleSkuEnter"
                                        class="w-full rounded-2xl border-0 bg-transparent py-3 pl-12 pr-4 text-sm text-content-primary placeholder:text-content-muted focus:ring-2 focus:ring-primary-500 dark:text-white"
                                    />
                                </div>
                            </div>
                            <div class="relative">
                                <div
                                    class="relative flex items-center rounded-2xl border border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-900"
                                >
                                    <Search
                                        class="absolute left-4 h-4 w-4 text-content-muted"
                                    />
                                    <input
                                        ref="nameSearchRef"
                                        v-model="nameQuery"
                                        type="text"
                                        placeholder="Buscar por nombre..."
                                        class="w-full rounded-2xl border-0 bg-transparent py-3 pl-11 pr-4 text-sm text-content-primary placeholder:text-content-muted focus:ring-2 focus:ring-primary-500 dark:text-white"
                                        @blur="hideNameDropdown"
                                        @focus="
                                            nameResults.length > 0
                                                ? (showNameDropdown = true)
                                                : null
                                        "
                                    />
                                    <div
                                        v-if="nameLoading"
                                        class="absolute right-4 h-4 w-4 animate-spin rounded-full border-2 border-primary-500 border-t-transparent"
                                    ></div>
                                </div>
                                <div
                                    v-if="showNameDropdown"
                                    class="absolute left-0 right-0 top-full z-50 mt-1 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-900"
                                >
                                    <button
                                        v-for="p in nameResults"
                                        :key="p.id"
                                        type="button"
                                        @click="selectSearchProduct(p)"
                                        class="flex w-full items-center gap-3 px-4 py-3 text-left text-sm transition-colors hover:bg-gray-50 dark:hover:bg-gray-800"
                                    >
                                        <span
                                            class="flex-1 font-medium text-content-primary dark:text-white"
                                            >{{ p.name }}</span
                                        >
                                        <span class="text-xs text-content-muted"
                                            >Stock: {{ p.stock }}</span
                                        >
                                    </button>
                                </div>
                            </div>
                        </div>

                        <template v-if="stockProduct">
                            <div
                                class="rounded-xl bg-gray-50 p-3 dark:bg-gray-800/50"
                            >
                                <span
                                    class="text-xs font-bold text-content-muted"
                                    >Producto:</span
                                >
                                <span
                                    class="ml-1 text-sm font-bold text-content-primary dark:text-white"
                                    >{{ stockProduct.name }}</span
                                >
                                <span class="mx-3 text-content-muted">|</span>
                                <span
                                    class="text-xs font-bold text-content-muted"
                                    >Stock Actual:</span
                                >
                                <span
                                    class="ml-1 text-sm font-bold text-content-primary dark:text-white"
                                    >{{ stockProduct.stock ?? '—' }} un.</span
                                >
                                <span class="mx-3 text-content-muted">|</span>
                                <span
                                    class="text-xs font-bold text-content-muted"
                                    >Último Costo:</span
                                >
                                <span
                                    class="ml-1 text-sm font-bold text-primary-500"
                                    >{{
                                        stockProduct.cost_price != null
                                            ? '$ ' +
                                              (
                                                  stockProduct.cost_price / 100
                                              ).toLocaleString('es-CO')
                                            : '—'
                                    }}</span
                                >
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label
                                        class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                                        >Cantidad</label
                                    >
                                    <input
                                        ref="quantityInputRef"
                                        v-model.number="stockForm.quantity"
                                        type="number"
                                        min="1"
                                        required
                                        @keydown.enter.prevent="cascadeCantidad"
                                        class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                    />
                                </div>
                                <div>
                                    <label
                                        class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                                        >Costo Unitario</label
                                    >
                                    <div class="relative">
                                        <span
                                            class="absolute left-4 top-1/2 -translate-y-1/2 text-sm font-bold text-content-muted"
                                            >$</span
                                        >
                                        <input
                                            ref="costoInputRef"
                                            v-model.number="stockForm.unit_cost"
                                            type="number"
                                            min="0"
                                            step="0.01"
                                            placeholder="0"
                                            @keydown.enter.prevent="
                                                cascadeCosto
                                            "
                                            class="w-full rounded-2xl border border-gray-200 bg-gray-50 py-2.5 pl-8 pr-4 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                        />
                                    </div>
                                </div>
                                <div>
                                    <label
                                        class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                                        >Fecha de Vencimiento</label
                                    >
                                    <input
                                        ref="fechaInputRef"
                                        v-model="stockForm.expiration_date"
                                        type="date"
                                        @keydown.enter.prevent="cascadeFecha"
                                        class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                    />
                                </div>
                                <div></div>
                            </div>

                            <div class="col-span-2">
                                <label
                                    class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                                    >Nota / Observaciones</label
                                >
                                <input
                                    ref="notaInputRef"
                                    v-model="stockForm.notes"
                                    type="text"
                                    maxlength="500"
                                    placeholder="Opcional"
                                    @keydown.enter.prevent="cascadeNota"
                                    class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                />
                            </div>

                            <div
                                class="flex items-center justify-between rounded-2xl bg-primary-50 p-4 dark:bg-primary-900/20"
                            >
                                <span
                                    class="text-xs font-bold uppercase text-primary-600 dark:text-primary-400"
                                    >💰 Inversión total de esta carga</span
                                >
                                <span
                                    class="text-lg font-black text-primary-700 dark:text-primary-300"
                                    >{{ totalInvestmentFormatted }}</span
                                >
                            </div>

                            <div
                                class="flex items-center gap-3 rounded-2xl border border-gray-100 bg-gray-50 px-4 py-3 dark:border-gray-700 dark:bg-gray-800/50"
                            >
                                <button
                                    type="button"
                                    @click="keepOpen = !keepOpen"
                                    class="relative h-5 w-10 flex-shrink-0 rounded-full transition-colors"
                                    :class="
                                        keepOpen
                                            ? 'bg-primary-500'
                                            : 'bg-gray-300 dark:bg-gray-600'
                                    "
                                >
                                    <span
                                        class="absolute left-0.5 top-0.5 h-4 w-4 rounded-full bg-white transition-transform"
                                        :class="{
                                            'translate-x-5': keepOpen,
                                        }"
                                    ></span>
                                </button>
                                <span
                                    class="text-sm font-bold text-content-secondary"
                                >
                                    Mantener abierto para seguir cargando
                                </span>
                            </div>

                            <div class="flex gap-3 pt-2">
                                <button
                                    type="button"
                                    @click="closeStockForm"
                                    class="flex-1 rounded-2xl border border-gray-200 py-2.5 text-sm font-bold text-content-secondary transition-colors hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800"
                                >
                                    Cancelar
                                </button>
                                <button
                                    type="submit"
                                    :disabled="stockForm.processing"
                                    class="flex flex-1 items-center justify-center gap-2 rounded-2xl bg-success py-2.5 text-sm font-bold text-white shadow-sm transition-colors hover:bg-success/90"
                                >
                                    <Plus class="h-4 w-4" />
                                    Añadir Stock
                                </button>
                            </div>
                        </template>
                    </form>
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
    </AdminLayout>
</template>
