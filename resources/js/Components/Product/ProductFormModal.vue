<script setup lang="ts">
import { useCategoryStore } from '@/Stores/categoryStore';
import { useForm } from '@inertiajs/vue3';
import { AlertCircle, Image as ImageIcon, Save, X } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

const props = defineProps<{
    show: boolean;
    product: any | null; // Null para nuevo, objeto para editar
    categories: any[];
}>();

const emit = defineEmits(['close', 'success']);
const categoryStore = useCategoryStore();

// ─── Formulario Reactivo ──────────────────────────────────────────
const form = useForm({
    _method: props.product ? 'PUT' : 'POST',
    name: '',
    description: '',
    sku: '',
    barcode: '',
    price: 0,
    cost_price: 0,
    stock: 0,
    min_stock: 5,
    category_id: '',
    sub_category: '',
    is_active: true,
    is_featured: false,
    expiration_date: '',
    sort_order: 0,
    unit: 'und',
    tax_rate: '0',
    max_discount: 0,
    image: null as File | null,
});

// ─── Preview de Imagen ───────────────────────────────────────────
const imagePreview = ref<string | null>(null);

function handleImageChange(e: Event) {
    const file = (e.target as HTMLInputElement).files?.[0];
    if (file) {
        form.image = file;
        imagePreview.value = URL.createObjectURL(file);
    }
}

// ─── Sincronizar datos al abrir ──────────────────────────────────
watch(
    () => props.show,
    (isShowing) => {
        if (isShowing) {
            categoryStore.fetchCategories();
            if (props.product) {
                form._method = 'PUT';
                form.name = props.product.name;
                form.description = props.product.description || '';
                form.sku = props.product.sku;
                form.barcode = props.product.barcode || '';
                form.price = props.product.price / 100;
                form.cost_price = (props.product.cost_price || 0) / 100;
                form.stock = props.product.stock;
                form.min_stock = props.product.min_stock || 5;
                form.category_id = props.product.category_id.toString();
                form.sub_category = props.product.sub_category || '';
                form.is_active = props.product.is_active;
                form.is_featured = props.product.is_featured ?? false;
                form.expiration_date = props.product.expiration_date || '';
                form.sort_order = props.product.sort_order || 0;
                form.unit = props.product.unit || 'und';
                form.tax_rate = props.product.tax_rate?.toString() || '0';
                form.max_discount = props.product.max_discount || 0;
                imagePreview.value = props.product.image_path
                    ? `/storage/${props.product.image_path}`
                    : null;
            } else {
                form.reset();
                form._method = 'POST';
                imagePreview.value = null;
            }
        }
    },
);

// ─── Lógica de Subcategorías Jerárquicas ──────────────────────────
const filteredSubcategories = computed(() => {
    if (!form.category_id) return [];
    const catId = Number(form.category_id);
    const selectedCat = categoryStore.categories.find((c) => c.id === catId);
    return selectedCat?.children ?? [];
});

function submit() {
    const url = props.product
        ? route('products.update', props.product.id)
        : route('products.store');

    form.post(url, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            emit('success');
            emit('close');
        },
        onError: (errors) => {
            console.error('Error guardando producto:', errors);
        },
    });
}
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition-opacity duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="show"
                class="fixed inset-0 z-[100] flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm"
            >
                <div
                    class="flex max-h-[90vh] w-full max-w-4xl animate-pop-in flex-col overflow-hidden rounded-4xl bg-white shadow-2xl dark:bg-surface-dark"
                >
                    <!-- Header -->
                    <div
                        class="flex items-center justify-between border-b border-gray-100 bg-gradient-to-r from-secondary-50 to-white px-8 py-6 dark:border-gray-800 dark:from-gray-900 dark:to-surface-dark"
                    >
                        <div>
                            <h2
                                class="font-display text-2xl font-extrabold text-content-primary dark:text-white"
                            >
                                {{
                                    product
                                        ? 'Editar Producto'
                                        : 'Nuevo Producto'
                                }}
                            </h2>
                            <p
                                class="mt-1 text-xs font-bold uppercase tracking-widest text-content-muted"
                            >
                                Monasterios Market Inventory System
                            </p>
                        </div>
                        <button
                            @click="emit('close')"
                            class="rounded-full p-2 transition-colors hover:bg-gray-100 dark:hover:bg-gray-800"
                        >
                            <X class="h-6 w-6" />
                        </button>
                    </div>

                    <!-- Form Content -->
                    <form
                        @submit.prevent="submit"
                        class="flex-1 overflow-y-auto p-8"
                    >
                        <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
                            <!-- Left: Image & Main Stats -->
                            <div class="space-y-6 lg:col-span-1">
                                <div class="group relative">
                                    <div
                                        class="flex aspect-square flex-col items-center justify-center overflow-hidden rounded-4xl border-2 border-dashed border-gray-200 bg-gray-50 transition-all group-hover:border-primary-400 dark:border-gray-700 dark:bg-gray-900"
                                    >
                                        <img
                                            v-if="imagePreview"
                                            :src="imagePreview"
                                            class="h-full w-full object-cover"
                                        />
                                        <div
                                            v-else
                                            class="flex flex-col items-center p-6 text-center text-gray-400"
                                        >
                                            <ImageIcon class="mb-3 h-12 w-12" />
                                            <p
                                                class="text-xs font-bold uppercase tracking-tighter"
                                            >
                                                Click para subir foto
                                            </p>
                                        </div>
                                        <input
                                            type="file"
                                            @change="handleImageChange"
                                            class="absolute inset-0 cursor-pointer opacity-0"
                                            accept="image/*"
                                        />
                                    </div>
                                    <div
                                        v-if="form.errors.image"
                                        class="mt-2 text-xs font-bold uppercase text-danger"
                                    >
                                        {{ form.errors.image }}
                                    </div>
                                </div>

                                <div
                                    class="space-y-4 rounded-3xl bg-primary-50 p-5 dark:bg-primary-900/20"
                                >
                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <span
                                            class="text-xs font-bold uppercase text-primary-600"
                                            >Estado Visible</span
                                        >
                                        <button
                                            type="button"
                                            @click="
                                                form.is_active = !form.is_active
                                            "
                                            class="relative h-6 w-12 rounded-full transition-colors"
                                            :class="
                                                form.is_active
                                                    ? 'bg-primary-500'
                                                    : 'bg-gray-300'
                                            "
                                        >
                                            <span
                                                class="absolute left-1 top-1 h-4 w-4 rounded-full bg-white transition-transform"
                                                :class="{
                                                    'translate-x-6':
                                                        form.is_active,
                                                }"
                                            ></span>
                                        </button>
                                    </div>
                                    <div
                                        class="mt-4 flex items-center justify-between"
                                    >
                                        <span
                                            class="text-xs font-bold uppercase text-primary-600"
                                            >Producto Destacado / Oferta</span
                                        >
                                        <button
                                            type="button"
                                            @click="
                                                form.is_featured =
                                                    !form.is_featured
                                            "
                                            class="relative h-6 w-12 rounded-full transition-colors"
                                            :class="
                                                form.is_featured
                                                    ? 'bg-amber-500'
                                                    : 'bg-gray-300'
                                            "
                                        >
                                            <span
                                                class="absolute left-1 top-1 h-4 w-4 rounded-full bg-white transition-transform"
                                                :class="{
                                                    'translate-x-6':
                                                        form.is_featured,
                                                }"
                                            ></span>
                                        </button>
                                    </div>
                                    <div
                                        class="border-t border-primary-100 pt-4 dark:border-primary-800"
                                    >
                                        <label
                                            class="mb-2 block text-xs font-bold uppercase text-primary-600"
                                            >Orden en Tienda</label
                                        >
                                        <input
                                            v-model.number="form.sort_order"
                                            type="number"
                                            class="w-full rounded-xl border-none bg-white p-3 text-sm font-bold dark:bg-gray-900"
                                        />
                                    </div>
                                </div>
                            </div>

                            <!-- Right: Detailed Info -->
                            <div class="space-y-6 lg:col-span-2">
                                <div
                                    class="grid grid-cols-1 gap-6 md:grid-cols-2"
                                >
                                    <div class="md:col-span-2">
                                        <label
                                            class="mb-2 block text-xs font-bold uppercase tracking-wider text-content-muted"
                                            >Nombre Comercial</label
                                        >
                                        <input
                                            v-model="form.name"
                                            type="text"
                                            placeholder="Ej: Arroz Grado 1 1kg"
                                            class="w-full rounded-2xl border border-gray-100 bg-gray-50 px-5 py-3 focus:ring-2 focus:ring-primary-500 dark:border-gray-800 dark:bg-gray-900"
                                            required
                                        />
                                        <div
                                            v-if="form.errors.name"
                                            class="mt-1 text-xs text-danger"
                                        >
                                            {{ form.errors.name }}
                                        </div>
                                    </div>

                                    <div>
                                        <label
                                            class="mb-2 block text-xs font-bold uppercase tracking-wider text-content-muted"
                                            >SKU / Código Interno</label
                                        >
                                        <input
                                            v-model="form.sku"
                                            type="text"
                                            class="w-full rounded-2xl border border-gray-100 bg-gray-50 px-5 py-3 font-mono text-sm dark:border-gray-800 dark:bg-gray-900"
                                        />
                                    </div>

                                    <div>
                                        <label
                                            class="mb-2 block text-xs font-bold uppercase tracking-wider text-content-muted"
                                            >Código de Barras</label
                                        >
                                        <input
                                            v-model="form.barcode"
                                            type="text"
                                            class="w-full rounded-2xl border border-gray-100 bg-gray-50 px-5 py-3 font-mono text-sm dark:border-gray-800 dark:bg-gray-900"
                                        />
                                    </div>

                                    <div>
                                        <label
                                            class="mb-2 block text-xs font-bold uppercase tracking-wider text-content-muted"
                                            >Departamento (Padre)</label
                                        >
                                        <select
                                            v-model="form.category_id"
                                            class="w-full rounded-2xl border border-gray-100 bg-gray-50 px-5 py-3 text-sm font-bold dark:border-gray-800 dark:bg-gray-900"
                                            required
                                        >
                                            <option value="">
                                                Seleccione Departamento
                                            </option>
                                            <option
                                                v-for="cat in categoryStore.categories"
                                                :key="cat.id"
                                                :value="cat.id"
                                            >
                                                {{ cat.icon }} {{ cat.name }}
                                            </option>
                                        </select>
                                    </div>

                                    <div>
                                        <label
                                            class="mb-2 block text-xs font-bold uppercase tracking-wider text-content-muted"
                                            >Sub-categoría</label
                                        >
                                        <select
                                            v-model="form.sub_category"
                                            class="w-full rounded-2xl border border-gray-100 bg-gray-50 px-5 py-3 text-sm dark:border-gray-800 dark:bg-gray-900"
                                            :disabled="!form.category_id"
                                        >
                                            <option value="">Ninguna</option>
                                            <option
                                                v-for="sub in filteredSubcategories"
                                                :key="sub.id"
                                                :value="sub.name"
                                            >
                                                {{ sub.name }}
                                            </option>
                                        </select>
                                    </div>

                                    <div>
                                        <label
                                            class="mb-2 block text-xs font-bold uppercase tracking-wider text-content-muted"
                                            >Precio de Venta ($)</label
                                        >
                                        <input
                                            v-model.number="form.price"
                                            type="number"
                                            class="w-full rounded-2xl border border-gray-100 bg-gray-50 px-5 py-3 text-lg font-black text-primary-500 dark:border-gray-800 dark:bg-gray-900"
                                            required
                                        />
                                    </div>

                                    <div>
                                        <label
                                            class="mb-2 block text-xs font-bold uppercase tracking-wider text-content-muted"
                                            >Unidad</label
                                        >
                                        <select
                                            v-model="form.unit"
                                            class="w-full rounded-2xl border border-gray-100 bg-gray-50 px-5 py-3 text-sm dark:border-gray-800 dark:bg-gray-900"
                                        >
                                            <option value="und">
                                                Unidad (und)
                                            </option>
                                            <option value="kg">
                                                Kilogramo (kg)
                                            </option>
                                            <option value="lt">
                                                Litro (lt)
                                            </option>
                                            <option value="ml">
                                                Mililitro (ml)
                                            </option>
                                            <option value="g">Gramo (g)</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label
                                            class="mb-2 block text-xs font-bold uppercase tracking-wider text-content-muted"
                                            >Fecha de Vencimiento</label
                                        >
                                        <input
                                            v-model="form.expiration_date"
                                            type="date"
                                            class="w-full rounded-2xl border border-gray-100 bg-gray-50 px-5 py-3 text-sm dark:border-gray-800 dark:bg-gray-900"
                                        />
                                    </div>

                                    <div>
                                        <label
                                            class="mb-2 block text-xs font-bold uppercase tracking-wider text-content-muted"
                                            >Impuesto (IVA %)</label
                                        >
                                        <select
                                            v-model="form.tax_rate"
                                            class="w-full rounded-2xl border border-gray-100 bg-gray-50 px-5 py-3 text-sm dark:border-gray-800 dark:bg-gray-900"
                                        >
                                            <option value="0">
                                                Exento (0%)
                                            </option>
                                            <option value="5">
                                                IVA Reducido (5%)
                                            </option>
                                            <option value="19">
                                                IVA General (19%)
                                            </option>
                                        </select>
                                    </div>

                                    <div>
                                        <label
                                            class="mb-2 block text-xs font-bold uppercase tracking-wider text-content-muted"
                                            >Stock Disponible</label
                                        >
                                        <div class="flex items-center gap-3">
                                            <input
                                                v-model.number="form.stock"
                                                type="number"
                                                class="flex-1 rounded-2xl border border-gray-100 bg-gray-50 px-5 py-3 font-bold dark:border-gray-800 dark:bg-gray-900"
                                            />
                                            <div
                                                class="flex items-center gap-1 rounded-lg bg-red-50 px-3 py-1 text-[10px] font-bold uppercase text-danger"
                                                v-if="
                                                    form.stock < form.min_stock
                                                "
                                            >
                                                <AlertCircle class="h-3 w-3" />
                                                Bajo Stock
                                            </div>
                                        </div>
                                    </div>

                                    <div>
                                        <label
                                            class="mb-2 block text-xs font-bold uppercase tracking-wider text-content-muted"
                                            >Stock Mínimo</label
                                        >
                                        <input
                                            v-model.number="form.min_stock"
                                            type="number"
                                            min="0"
                                            class="w-full rounded-2xl border border-gray-100 bg-gray-50 px-5 py-3 font-bold dark:border-gray-800 dark:bg-gray-900"
                                        />
                                    </div>
                                </div>

                                <div>
                                    <label
                                        class="mb-2 block text-xs font-bold uppercase tracking-wider text-content-muted"
                                        >Descripción del Producto</label
                                    >
                                    <textarea
                                        v-model="form.description"
                                        rows="4"
                                        class="w-full rounded-2xl border border-gray-100 bg-gray-50 px-5 py-3 text-sm dark:border-gray-800 dark:bg-gray-900"
                                        placeholder="Detalles, peso, ingredientes..."
                                    ></textarea>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Footer -->
                    <div
                        class="flex items-center justify-end gap-4 border-t border-gray-100 bg-gray-50 px-8 py-6 dark:border-gray-800 dark:bg-gray-900/50"
                    >
                        <button
                            @click="emit('close')"
                            type="button"
                            class="rounded-2xl px-6 py-3 font-bold text-content-secondary transition-all hover:bg-gray-100 dark:hover:bg-gray-800"
                        >
                            Cancelar
                        </button>
                        <button
                            @click="submit"
                            :disabled="form.processing"
                            class="flex items-center gap-2 rounded-2xl bg-primary-500 px-10 py-3 font-bold text-white shadow-primary-md transition-all hover:bg-primary-600 disabled:opacity-50"
                        >
                            <Save v-if="!form.processing" class="h-5 w-5" />
                            <div
                                v-else
                                class="h-5 w-5 animate-spin rounded-full border-2 border-white/30 border-t-white"
                            ></div>
                            {{
                                form.processing
                                    ? 'Guardando...'
                                    : product
                                      ? 'Actualizar Producto'
                                      : 'Guardar Producto'
                            }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
