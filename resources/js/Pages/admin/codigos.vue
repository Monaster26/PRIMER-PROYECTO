<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { categoriesData, type Category } from '@/Stores/categoryStore';
import {
    Package, Plus, Pencil, Trash2, X, Check, Search,
    Image, Save, ToggleLeft, ToggleRight
} from 'lucide-vue-next';

const props = defineProps<{
    products: { data: any[]; links: any[]; current_page: number; last_page: number; prev_page_url: string | null; next_page_url: string | null };
}>();

const search = ref('');
const showForm = ref(false);
const editingId = ref<number | null>(null);
const imageFile = ref<File | null>(null);
const imagePreview = ref<string | null>(null);
const fileInputRef = ref<HTMLInputElement | null>(null);
const barcodeInputRef = ref<HTMLInputElement | null>(null);

const units = ['und', 'kg', 'lt', 'caja', 'paquete', 'botella', 'bolsa', 'm', 'm²', 'm³', 'par', 'docena'];

const selectedCategory = computed(() =>
    categoriesData.find(c => c.id === form.category_slug) ?? null
);

const subcategories = computed(() =>
    selectedCategory.value?.subcategories ?? []
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
    is_active: true,
    sort_order: 0,
    description: '',
});

function openNew() {
    form.reset();
    form.is_active = true;
    form.unit = 'und';
    form.tax_rate = 19;
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
    form.is_active = product.is_active;
    form.sort_order = product.sort_order || 0;
    form.description = product.description || '';
    editingId.value = product.id;
    imageFile.value = null;
    imagePreview.value = product.image_path ? '/storage/' + product.image_path : null;
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
    reader.onload = () => { imagePreview.value = reader.result as string; };
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
        is_active: form.is_active,
        sort_order: form.sort_order,
        description: form.description,
    };

    if (imageFile.value) {
        payload.image = imageFile.value;
    }

    const options = {
        onSuccess: closeForm,
        onError: (errors: Record<string, string>) => {
            console.error('Validation errors:', JSON.stringify(errors, null, 2));
            alert('Error de validación:\n' + Object.values(errors).join('\n'));
        },
    };

    if (editingId.value) {
        payload._method = 'PUT';
        router.post(route('admin.codigos.update', editingId.value), payload, options);
    } else {
        router.post(route('admin.codigos.store'), payload, options);
    }
}

function deleteProduct(id: number) {
    if (!confirm('¿Eliminar este producto?')) return;
    router.delete(route('admin.codigos.destroy', id), { preserveScroll: true });
}

function handleSearch() {
    router.get(route('admin.codigos.index'), { search: search.value }, { preserveState: true, replace: true });
}

const showStockForm = ref(false);
const stockProduct = ref<any>(null);

const stockForm = useForm({
    quantity: 1,
    unit_cost: 0,
    notes: '',
});

function openStockForm(product: any) {
    stockProduct.value = product;
    stockForm.reset();
    stockForm.quantity = 1;
    stockForm.unit_cost = 0;
    showStockForm.value = true;
}

function closeStockForm() {
    showStockForm.value = false;
    stockProduct.value = null;
    stockForm.reset();
}

function submitStockForm() {
    if (!stockProduct.value) return;
    stockForm.post(route('admin.codigos.add-stock', stockProduct.value.id), {
        onSuccess: closeStockForm,
        preserveScroll: true,
    });
}

const fmt = (v: number) => '$' + v.toLocaleString('es-CO', { minimumFractionDigits: 0 });
</script>

<template>
    <Head title="Catálogo de Productos" />
    <AdminLayout>
        <template #title>
            <h1 class="text-xl font-display font-bold text-content-primary dark:text-white">Catálogo de Productos</h1>
        </template>

        <div class="bg-white dark:bg-surface-dark rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 flex flex-wrap items-center gap-3">
                <Package class="w-5 h-5 text-primary-500" />
                <h2 class="font-bold text-content-primary dark:text-white flex-1">Productos Registrados</h2>
                <div class="relative w-64">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-content-muted" />
                    <input v-model="search" @input="handleSearch" type="text" placeholder="Buscar producto..."
                        class="w-full pl-10 pr-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-sm text-content-primary dark:text-white" />
                </div>
                <button @click="openNew"
                    class="flex items-center gap-2 bg-primary-500 hover:bg-primary-600 text-white text-sm font-bold px-4 py-2 rounded-2xl transition-colors shadow-sm">
                    <Plus class="w-4 h-4" /> Nuevo Producto
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 dark:bg-gray-900/50 text-xs uppercase tracking-wider text-content-muted dark:text-gray-500">
                        <tr>
                            <th class="px-6 py-3 font-bold">SKU</th>
                            <th class="px-6 py-3 font-bold">Nombre</th>
                            <th class="px-6 py-3 font-bold text-right">Precio</th>
                            <th class="px-6 py-3 font-bold text-center">Stock</th>
                            <th class="px-6 py-3 font-bold text-center">Activo</th>
                            <th class="px-6 py-3 font-bold text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <tr v-if="!products.data?.length">
                            <td colspan="6" class="px-6 py-12 text-center text-sm text-content-muted dark:text-gray-500">No hay productos registrados.</td>
                        </tr>
                        <tr v-for="p in products.data" :key="p.id"
                            class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors group">
                            <td class="px-6 py-4 text-sm font-mono font-bold text-content-primary dark:text-white">{{ p.sku }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-content-primary dark:text-white">{{ p.name }}</td>
                            <td class="px-6 py-4 text-sm text-right font-bold text-primary-500">{{ fmt(p.price / 100) }}</td>
                            <td class="px-6 py-4 text-sm text-center font-bold" :class="p.stock < 5 ? 'text-danger' : 'text-content-primary dark:text-white'">{{ p.stock }}</td>
                            <td class="px-6 py-4 text-center">
                                <span :class="p.is_active ? 'bg-success/10 text-success' : 'bg-gray-100 text-gray-400'" class="px-2 py-1 rounded-full text-[10px] font-bold uppercase">
                                    {{ p.is_active ? 'Sí' : 'No' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button @click="openStockForm(p)" class="p-2 rounded-xl hover:bg-success/10 text-success transition-colors"
                                        title="Añadir stock">
                                        <Plus class="w-4 h-4" />
                                    </button>
                                    <button @click="openEdit(p)" class="p-2 rounded-xl hover:bg-blue-50 dark:hover:bg-blue-900/20 text-blue-500 transition-colors">
                                        <Pencil class="w-4 h-4" />
                                    </button>
                                    <button @click="deleteProduct(p.id)" class="p-2 rounded-xl hover:bg-red-50 dark:hover:bg-red-900/20 text-danger transition-colors">
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="products.last_page > 1" class="px-6 py-4 border-t border-gray-100 dark:border-gray-800 flex justify-between items-center text-sm text-content-muted dark:text-gray-500">
                <span>Página {{ products.current_page }} de {{ products.last_page }}</span>
                <div class="flex gap-2">
                    <a v-if="products.prev_page_url" :href="products.prev_page_url" class="px-3 py-1.5 rounded-lg bg-gray-50 dark:bg-gray-900 hover:bg-gray-100 font-bold transition-colors">←</a>
                    <a v-if="products.next_page_url" :href="products.next_page_url" class="px-3 py-1.5 rounded-lg bg-gray-50 dark:bg-gray-900 hover:bg-gray-100 font-bold transition-colors">→</a>
                </div>
            </div>
        </div>

        <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100"
                    leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="showForm" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm flex items-center justify-center p-4">
                <div class="bg-white dark:bg-surface-dark rounded-3xl shadow-xl w-full max-w-3xl p-6 relative max-h-[90vh] overflow-y-auto">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="font-display font-bold text-lg text-content-primary dark:text-white">
                            {{ editingId ? 'Editar Producto' : 'Nuevo Producto' }}
                        </h3>
                        <button @click="closeForm" class="p-2 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                            <X class="w-5 h-5 text-content-muted" />
                        </button>
                    </div>

                    <form @submit.prevent="submitForm">
                        <div class="flex gap-6">
                            <div class="w-64 flex-shrink-0 space-y-5">
                                <div>
                                    <label class="block text-xs font-bold text-content-muted dark:text-gray-400 uppercase tracking-wider mb-2">Imagen</label>
                                    <div @click="fileInputRef?.click()"
                                        class="relative aspect-square rounded-2xl border-2 border-dashed border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-900 flex flex-col items-center justify-center cursor-pointer hover:border-primary-400 transition-colors overflow-hidden">
                                        <input ref="fileInputRef" type="file" accept="image/*" class="hidden" @change="onImageSelect" />
                                        <template v-if="imagePreview">
                                            <img :src="imagePreview" class="absolute inset-0 w-full h-full object-cover" />
                                            <button type="button" @click.stop="removeImage"
                                                class="absolute top-2 right-2 p-1.5 bg-black/50 rounded-full text-white hover:bg-black/70 transition-colors z-10">
                                                <X class="w-4 h-4" />
                                            </button>
                                        </template>
                                        <template v-else>
                                            <Image class="w-10 h-10 text-content-muted mb-2" />
                                            <span class="text-xs font-bold text-content-muted text-center px-4">CLICK PARA SUBIR FOTO</span>
                                            <span class="text-[10px] text-content-muted mt-1">800x800px · max 2MB</span>
                                        </template>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-content-muted dark:text-gray-400 uppercase tracking-wider mb-2">Estado Visible</label>
                                    <button type="button" @click="form.is_active = !form.is_active"
                                        class="flex items-center gap-3 px-4 py-2.5 rounded-2xl border border-gray-200 dark:border-gray-700 w-full hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                        <component :is="form.is_active ? ToggleRight : ToggleLeft" class="w-6 h-6" :class="form.is_active ? 'text-success' : 'text-content-muted'" />
                                        <span class="text-sm font-bold" :class="form.is_active ? 'text-success' : 'text-content-muted'">
                                            {{ form.is_active ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </button>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-content-muted dark:text-gray-400 uppercase tracking-wider mb-1">Orden en Tienda</label>
                                    <input v-model.number="form.sort_order" type="number" min="0"
                                        class="w-full border border-gray-200 dark:border-gray-700 rounded-2xl px-4 py-2.5 bg-gray-50 dark:bg-gray-900 text-content-primary dark:text-white text-sm" />
                                </div>
                            </div>

                            <div class="flex-1 space-y-4 min-w-0">
                                <div>
                                    <label class="block text-xs font-bold text-content-muted dark:text-gray-400 uppercase tracking-wider mb-1">Nombre Comercial</label>
                                    <input v-model="form.name" type="text" required placeholder="Ej: Arroz Grado 1 1kg"
                                        class="w-full border border-gray-200 dark:border-gray-700 rounded-2xl px-4 py-2.5 bg-gray-50 dark:bg-gray-900 text-content-primary dark:text-white text-sm" />
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold text-content-muted dark:text-gray-400 uppercase tracking-wider mb-1">SKU / Código Interno</label>
                                        <input v-model="form.sku" type="text" required
                                            class="w-full border border-gray-200 dark:border-gray-700 rounded-2xl px-4 py-2.5 bg-gray-50 dark:bg-gray-900 text-content-primary dark:text-white text-sm font-mono" />
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-content-muted dark:text-gray-400 uppercase tracking-wider mb-1">Código de Barras</label>
                                        <input ref="barcodeInputRef" v-model="form.barcode" type="text"
                                            class="w-full border border-gray-200 dark:border-gray-700 rounded-2xl px-4 py-2.5 bg-gray-50 dark:bg-gray-900 text-content-primary dark:text-white text-sm font-mono" />
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold text-content-muted dark:text-gray-400 uppercase tracking-wider mb-1">Departamento</label>
                                        <select v-model="form.category_slug"
                                            class="w-full border border-gray-200 dark:border-gray-700 rounded-2xl px-4 py-2.5 bg-gray-50 dark:bg-gray-900 text-content-primary dark:text-white text-sm">
                                            <option value="">Sin categoría</option>
                                            <option v-for="cat in categoriesData" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-content-muted dark:text-gray-400 uppercase tracking-wider mb-1">Sub-Categoría</label>
                                        <select v-model="form.sub_category"
                                            class="w-full border border-gray-200 dark:border-gray-700 rounded-2xl px-4 py-2.5 bg-gray-50 dark:bg-gray-900 text-content-primary dark:text-white text-sm">
                                            <option value="">Sin subcategoría</option>
                                            <option v-for="sub in subcategories" :key="sub" :value="sub">{{ sub }}</option>
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-content-muted dark:text-gray-400 uppercase tracking-wider mb-1">Precio de Venta ($)</label>
                                    <input v-model.number="form.price" type="number" min="0" step="0.01" required
                                        class="w-full border border-gray-200 dark:border-gray-700 rounded-2xl px-4 py-2.5 bg-gray-50 dark:bg-gray-900 text-sm font-bold text-primary-500 text-right text-xl" placeholder="0" />
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold text-content-muted dark:text-gray-400 uppercase tracking-wider mb-1">Unidad</label>
                                        <select v-model="form.unit"
                                            class="w-full border border-gray-200 dark:border-gray-700 rounded-2xl px-4 py-2.5 bg-gray-50 dark:bg-gray-900 text-content-primary dark:text-white text-sm">
                                            <option v-for="u in units" :key="u" :value="u">{{ u }}</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-content-muted dark:text-gray-400 uppercase tracking-wider mb-1">IVA (%)</label>
                                        <input v-model.number="form.tax_rate" type="number" min="0" max="100"
                                            class="w-full border border-gray-200 dark:border-gray-700 rounded-2xl px-4 py-2.5 bg-gray-50 dark:bg-gray-900 text-content-primary dark:text-white text-sm" />
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold text-content-muted dark:text-gray-400 uppercase tracking-wider mb-1">Stock Disponible</label>
                                        <input v-model.number="form.stock" type="number" min="0" required
                                            class="w-full border border-gray-200 dark:border-gray-700 rounded-2xl px-4 py-2.5 bg-gray-50 dark:bg-gray-900 text-content-primary dark:text-white text-sm" />
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-content-muted dark:text-gray-400 uppercase tracking-wider mb-1">Precio Costo ($)</label>
                                        <input v-model.number="form.cost_price" type="number" min="0" step="0.01"
                                            class="w-full border border-gray-200 dark:border-gray-700 rounded-2xl px-4 py-2.5 bg-gray-50 dark:bg-gray-900 text-content-primary dark:text-white text-sm" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-6 border-t-2 border-primary-500" />

                        <div class="flex items-center justify-between gap-4">
                            <button type="button" @click="closeForm"
                                class="text-sm font-bold text-content-secondary hover:text-content-primary transition-colors px-4 py-2">
                                Cancelar
                            </button>
                            <button type="submit" :disabled="form.processing"
                                class="flex items-center gap-2 bg-primary-500 hover:bg-primary-600 text-white text-sm font-bold px-6 py-2.5 rounded-2xl transition-colors shadow-sm">
                                <Save class="w-4 h-4" />
                                {{ editingId ? 'Actualizar Producto' : 'Guardar Producto' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>

        <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100"
                    leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="showStockForm" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm flex items-center justify-center p-4">
                <div class="bg-white dark:bg-surface-dark rounded-3xl shadow-xl w-full max-w-md p-6 relative">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="font-display font-bold text-lg text-content-primary dark:text-white">
                            Añadir Stock: {{ stockProduct?.name }}
                        </h3>
                        <button @click="closeStockForm" class="p-2 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                            <X class="w-5 h-5 text-content-muted" />
                        </button>
                    </div>
                    <form @submit.prevent="submitStockForm" class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-content-muted dark:text-gray-400 uppercase tracking-wider mb-1">Cantidad</label>
                            <input v-model.number="stockForm.quantity" type="number" min="1" required
                                class="w-full border border-gray-200 dark:border-gray-700 rounded-2xl px-4 py-2.5 bg-gray-50 dark:bg-gray-900 text-content-primary dark:text-white text-sm" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-content-muted dark:text-gray-400 uppercase tracking-wider mb-1">Costo Unitario (opcional)</label>
                            <input v-model.number="stockForm.unit_cost" type="number" min="0" step="0.01"
                                class="w-full border border-gray-200 dark:border-gray-700 rounded-2xl px-4 py-2.5 bg-gray-50 dark:bg-gray-900 text-content-primary dark:text-white text-sm" />
                            <p class="text-[11px] text-content-muted mt-1">En pesos, ej: 1500</p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-content-muted dark:text-gray-400 uppercase tracking-wider mb-1">Nota / Referencia</label>
                            <input v-model="stockForm.notes" type="text" maxlength="500"
                                class="w-full border border-gray-200 dark:border-gray-700 rounded-2xl px-4 py-2.5 bg-gray-50 dark:bg-gray-900 text-content-primary dark:text-white text-sm" />
                        </div>
                        <div class="flex gap-3 pt-2">
                            <button type="button" @click="closeStockForm"
                                class="flex-1 py-2.5 rounded-2xl border border-gray-200 dark:border-gray-700 text-sm font-bold text-content-secondary hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                Cancelar
                            </button>
                            <button type="submit" :disabled="stockForm.processing"
                                class="flex-1 py-2.5 rounded-2xl bg-success hover:bg-success/90 text-white text-sm font-bold transition-colors shadow-sm flex items-center justify-center gap-2">
                                <Plus class="w-4 h-4" />
                                Añadir Stock
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>
    </AdminLayout>
</template>
