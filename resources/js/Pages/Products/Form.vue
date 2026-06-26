<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useCategoryStore } from '@/Stores/categoryStore';
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    AlertCircle,
    ArrowLeft,
    Barcode,
    CheckCircle,
    Image as ImageIcon,
    Info,
    Layers,
    Package,
    Save,
    Tag,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

const props = defineProps<{
    product: any | null;
    categories: any[];
}>();

const categoryStore = useCategoryStore();
categoryStore.fetchCategories();

// ─── Estado del Formulario ────────────────────────────────────────
const form = useForm({
    _method: props.product ? 'PUT' : 'POST',
    category_id: null as number | null,
    sub_category: '',
    name: props.product?.name || '',
    description: props.product?.description || '',
    sku: props.product?.sku || '',
    barcode: props.product?.barcode || '',
    price: (props.product?.price ?? 0) / 100,
    cost_price: (props.product?.cost_price ?? 0) / 100,
    stock: props.product?.stock || 0,
    min_stock: props.product?.min_stock || 5,
    unit: props.product?.unit || 'und',
    tax_rate: props.product?.tax_rate?.toString() || '0',
    max_discount: props.product?.max_discount || 0,
    is_active: props.product ? Boolean(props.product.is_active) : true,
    image: null as File | null,
});

// ─── Previsualización de Imagen ──────────────────────────────────
const imagePreview = ref<string | null>(
    props.product?.image_path ? `/storage/${props.product.image_path}` : null,
);

function handleImageChange(e: Event) {
    const file = (e.target as HTMLInputElement).files?.[0];
    if (file) {
        form.image = file;
        imagePreview.value = URL.createObjectURL(file);
    }
}

// ─── Selector jerárquico Categoría → Subcategoría ─────────────────
const selectedParentId = ref<number | null>(null);

const filteredSubcategories = computed(() => {
    if (!selectedParentId.value) return [];
    const parent = categoryStore.categories.find(
        (c) => c.id === Number(selectedParentId.value),
    );
    return parent?.children ?? [];
});

function onSubcategoryChange() {
    const sub = filteredSubcategories.value.find(
        (s) => s.id === Number(form.category_id),
    );
    form.sub_category = sub?.name ?? '';
}

// Inicializar selects si es edición
watch(
    () => categoryStore.categories,
    (cats) => {
        if (props.product && cats.length > 0) {
            const catId = Number(props.product.category_id);
            const allCats = cats.flatMap((p) => p.children ?? []);
            const child = allCats.find((c) => c.id === catId);
            if (child) {
                selectedParentId.value = child.parent_id;
                form.category_id = catId;
                form.sub_category = props.product.sub_category || child.name;
            } else {
                selectedParentId.value = catId;
                form.category_id = null;
                form.sub_category = '';
            }
        }
    },
    { immediate: true },
);

// ─── Envío de Datos ──────────────────────────────────────────────
function submit() {
    const url = props.product
        ? route('products.update', props.product.id)
        : route('products.store');

    form.post(url, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {},
    });
}
</script>

<template>
    <Head :title="product ? 'Editar Producto' : 'Nuevo Producto'" />

    <AdminLayout>
        <div class="mx-auto max-w-5xl space-y-8 pb-20">
            <!-- Header de la Página -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link
                        :href="route('products.index')"
                        class="rounded-2xl border border-gray-100 bg-white p-2 text-content-secondary shadow-sm transition-all hover:text-primary-500 dark:border-gray-800 dark:bg-surface-dark"
                    >
                        <ArrowLeft class="h-5 w-5" />
                    </Link>
                    <div>
                        <h1
                            class="font-display text-3xl font-black leading-tight text-content-primary dark:text-white"
                        >
                            {{
                                product
                                    ? 'Editar Producto'
                                    : 'Añadir Nuevo Producto'
                            }}
                        </h1>
                        <p class="mt-1 text-sm font-medium text-content-muted">
                            Gestiona los detalles, stock y precios de tu
                            inventario.
                        </p>
                    </div>
                </div>

                <button
                    @click="submit"
                    :disabled="form.processing"
                    class="hidden items-center gap-2 rounded-2xl bg-primary-500 px-8 py-3.5 font-bold text-white shadow-primary-md transition-all hover:bg-primary-600 active:scale-95 disabled:opacity-50 md:flex"
                >
                    <Save class="h-5 w-5" />
                    {{ product ? 'Guardar Cambios' : 'Crear Producto' }}
                </button>
            </div>

            <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
                <!-- Columna Izquierda: Imagen y Estado -->
                <div class="space-y-8 lg:col-span-1">
                    <!-- Card de Imagen -->
                    <div
                        class="group relative overflow-hidden rounded-4xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-surface-dark"
                    >
                        <label
                            class="mb-4 block flex items-center gap-2 text-xs font-black uppercase tracking-widest text-content-muted"
                        >
                            <ImageIcon class="h-4 w-4" /> Fotografía del
                            Producto
                        </label>

                        <div
                            class="relative flex aspect-square items-center justify-center overflow-hidden rounded-3xl border-2 border-dashed border-gray-200 bg-gray-50 transition-all group-hover:border-primary-400 group-hover:bg-primary-50/10 dark:border-gray-700 dark:bg-gray-900"
                        >
                            <img
                                v-if="imagePreview"
                                :src="imagePreview"
                                class="h-full w-full animate-fade-in object-cover"
                            />
                            <div
                                v-else
                                class="flex flex-col items-center p-6 text-center"
                            >
                                <div
                                    class="mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-white shadow-sm dark:bg-gray-800"
                                >
                                    <Plus class="h-8 w-8 text-primary-400" />
                                </div>
                                <p
                                    class="text-sm font-bold text-content-secondary dark:text-gray-400"
                                >
                                    Click o arrastra para subir
                                </p>
                                <p
                                    class="mt-2 text-[10px] font-black uppercase text-content-muted"
                                >
                                    PNG, JPG hasta 5MB
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
                            class="mt-4 rounded-xl bg-red-50 p-3 text-[11px] font-bold uppercase text-danger"
                        >
                            {{ form.errors.image }}
                        </div>
                    </div>

                    <!-- Card de Estado -->
                    <div
                        class="rounded-4xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-surface-dark"
                    >
                        <label
                            class="mb-6 block text-xs font-black uppercase tracking-widest text-content-muted"
                            >Estado y Visibilidad</label
                        >

                        <div
                            class="flex items-center justify-between rounded-3xl border border-gray-100 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-900"
                        >
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-xl"
                                    :class="
                                        form.is_active
                                            ? 'bg-success/10 text-success'
                                            : 'bg-gray-200 text-gray-400'
                                    "
                                >
                                    <CheckCircle
                                        v-if="form.is_active"
                                        class="h-5 w-5"
                                    />
                                    <AlertCircle v-else class="h-5 w-5" />
                                </div>
                                <div>
                                    <p
                                        class="text-sm font-bold text-content-primary dark:text-white"
                                    >
                                        Producto Activo
                                    </p>
                                    <p class="text-[10px] text-content-muted">
                                        {{
                                            form.is_active
                                                ? 'Visible en tienda'
                                                : 'Oculto'
                                        }}
                                    </p>
                                </div>
                            </div>
                            <button
                                type="button"
                                @click="form.is_active = !form.is_active"
                                class="relative h-6 w-12 rounded-full transition-all"
                                :class="
                                    form.is_active
                                        ? 'bg-primary-500'
                                        : 'bg-gray-300'
                                "
                            >
                                <span
                                    class="absolute left-1 top-1 h-4 w-4 rounded-full bg-white shadow-sm transition-transform"
                                    :class="{ 'translate-x-6': form.is_active }"
                                ></span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Columna Derecha: Información Principal -->
                <div class="space-y-8 lg:col-span-2">
                    <!-- Datos Básicos -->
                    <div
                        class="space-y-6 rounded-4xl border border-gray-100 bg-white p-8 shadow-sm dark:border-gray-800 dark:bg-surface-dark"
                    >
                        <label
                            class="block flex items-center gap-2 text-xs font-black uppercase tracking-widest text-content-muted"
                        >
                            <Info class="h-4 w-4" /> Información General
                        </label>

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div class="md:col-span-2">
                                <label
                                    class="mb-2 block text-xs font-bold text-content-secondary dark:text-gray-400"
                                    >Nombre del Producto</label
                                >
                                <input
                                    v-model="form.name"
                                    type="text"
                                    placeholder="Ej: Papas Kryzpo Sabor Queso"
                                    class="w-full rounded-2xl border border-gray-100 bg-gray-50 px-5 py-4 font-medium transition-all focus:ring-2 focus:ring-primary-500 dark:border-gray-800 dark:bg-gray-900"
                                />
                                <p
                                    v-if="form.errors.name"
                                    class="mt-2 text-xs font-bold text-danger"
                                >
                                    {{ form.errors.name }}
                                </p>
                            </div>

                            <div class="md:col-span-2">
                                <label
                                    class="mb-2 block text-xs font-bold text-content-secondary dark:text-gray-400"
                                    >Descripción Detallada</label
                                >
                                <textarea
                                    v-model="form.description"
                                    rows="4"
                                    class="w-full resize-none rounded-2xl border border-gray-100 bg-gray-50 px-5 py-4 font-medium transition-all focus:ring-2 focus:ring-primary-500 dark:border-gray-800 dark:bg-gray-900"
                                    placeholder="Describe las características principales..."
                                ></textarea>
                            </div>

                            <div>
                                <label
                                    class="mb-2 block text-xs font-bold text-content-secondary dark:text-gray-400"
                                    >Categoría</label
                                >
                                <div class="relative">
                                    <Layers
                                        class="absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-content-muted"
                                    />
                                    <select
                                        v-model="selectedParentId"
                                        class="w-full appearance-none rounded-2xl border border-gray-100 bg-gray-50 py-4 pl-12 pr-5 text-sm font-bold focus:ring-2 focus:ring-primary-500 dark:border-gray-800 dark:bg-gray-900"
                                    >
                                        <option :value="null">
                                            Seleccione Categoría
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
                                <p
                                    v-if="form.errors.category_id"
                                    class="mt-2 text-xs font-bold text-danger"
                                >
                                    {{ form.errors.category_id }}
                                </p>
                            </div>

                            <div>
                                <label
                                    class="mb-2 block text-xs font-bold text-content-secondary dark:text-gray-400"
                                    >Subcategoría</label
                                >
                                <select
                                    v-model="form.category_id"
                                    class="w-full rounded-2xl border border-gray-100 bg-gray-50 px-5 py-4 text-sm font-bold focus:ring-2 focus:ring-primary-500 dark:border-gray-800 dark:bg-gray-900"
                                    :disabled="!selectedParentId"
                                    @change="onSubcategoryChange"
                                >
                                    <option :value="null">
                                        Seleccione Subcategoría
                                    </option>
                                    <option
                                        v-for="sub in filteredSubcategories"
                                        :key="sub.id"
                                        :value="sub.id"
                                    >
                                        {{ sub.name }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label
                                    class="mb-2 block text-xs font-bold text-content-secondary dark:text-gray-400"
                                    >Unidad de Medida</label
                                >
                                <select
                                    v-model="form.unit"
                                    class="w-full rounded-2xl border border-gray-100 bg-gray-50 px-5 py-4 text-sm font-bold focus:ring-2 focus:ring-primary-500 dark:border-gray-800 dark:bg-gray-900"
                                >
                                    <option value="und">Unidad (und)</option>
                                    <option value="kg">Kilogramo (kg)</option>
                                    <option value="lt">Litro (lt)</option>
                                    <option value="ml">Mililitro (ml)</option>
                                    <option value="g">Gramo (g)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Inventario y Precios -->
                    <div
                        class="space-y-6 rounded-4xl border border-gray-100 bg-white p-8 shadow-sm dark:border-gray-800 dark:bg-surface-dark"
                    >
                        <label
                            class="block flex items-center gap-2 text-xs font-black uppercase tracking-widest text-content-muted"
                        >
                            <Tag class="h-4 w-4" /> Finanzas e Inventario
                        </label>

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label
                                    class="mb-2 block text-xs font-bold text-content-secondary dark:text-gray-400"
                                    >Precio de Venta ($)</label
                                >
                                <div class="relative">
                                    <span
                                        class="absolute left-5 top-1/2 -translate-y-1/2 font-black text-primary-500"
                                        >$</span
                                    >
                                    <input
                                        v-model.number="form.price"
                                        type="number"
                                        class="w-full rounded-2xl border border-gray-100 bg-gray-50 py-4 pl-10 pr-5 text-lg font-black text-primary-600 focus:ring-2 focus:ring-primary-500 dark:border-gray-800 dark:bg-gray-900"
                                    />
                                </div>
                                <p
                                    v-if="form.errors.price"
                                    class="mt-2 text-xs font-bold text-danger"
                                >
                                    {{ form.errors.price }}
                                </p>
                            </div>

                            <div>
                                <label
                                    class="mb-2 block text-xs font-bold text-content-secondary dark:text-gray-400"
                                    >Stock Inicial</label
                                >
                                <div class="relative">
                                    <Package
                                        class="absolute left-5 top-1/2 h-4 w-4 -translate-y-1/2 text-content-muted"
                                    />
                                    <input
                                        v-model.number="form.stock"
                                        type="number"
                                        class="w-full rounded-2xl border border-gray-100 bg-gray-50 py-4 pl-12 pr-5 font-bold focus:ring-2 focus:ring-primary-500 dark:border-gray-800 dark:bg-gray-900"
                                    />
                                </div>
                                <p
                                    v-if="form.errors.stock"
                                    class="mt-2 text-xs font-bold text-danger"
                                >
                                    {{ form.errors.stock }}
                                </p>
                            </div>

                            <div>
                                <label
                                    class="mb-2 block text-xs font-bold text-content-secondary dark:text-gray-400"
                                    >Código SKU</label
                                >
                                <input
                                    v-model="form.sku"
                                    type="text"
                                    placeholder="Ej: MK-PAP-01"
                                    class="w-full rounded-2xl border border-gray-100 bg-gray-50 px-5 py-4 font-mono text-sm uppercase focus:ring-2 focus:ring-primary-500 dark:border-gray-800 dark:bg-gray-900"
                                />
                                <p
                                    v-if="form.errors.sku"
                                    class="mt-2 text-xs font-bold text-danger"
                                >
                                    {{ form.errors.sku }}
                                </p>
                            </div>

                            <div>
                                <label
                                    class="mb-2 block text-xs font-bold text-content-secondary dark:text-gray-400"
                                    >Código de Barras</label
                                >
                                <div class="relative">
                                    <Barcode
                                        class="absolute left-5 top-1/2 h-4 w-4 -translate-y-1/2 text-content-muted"
                                    />
                                    <input
                                        v-model="form.barcode"
                                        type="text"
                                        placeholder="Ej: 779123456789"
                                        class="w-full rounded-2xl border border-gray-100 bg-gray-50 py-4 pl-12 pr-5 font-mono text-sm focus:ring-2 focus:ring-primary-500 dark:border-gray-800 dark:bg-gray-900"
                                    />
                                </div>
                                <p
                                    v-if="form.errors.barcode"
                                    class="mt-2 text-xs font-bold text-danger"
                                >
                                    {{ form.errors.barcode }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Botón Mobile -->
                    <div class="pt-4 md:hidden">
                        <button
                            @click="submit"
                            :disabled="form.processing"
                            class="flex w-full items-center justify-center gap-3 rounded-3xl bg-primary-500 py-5 font-black text-white shadow-primary-lg transition-all hover:bg-primary-600"
                        >
                            <Save class="h-6 w-6" />
                            {{ product ? 'Guardar Cambios' : 'Crear Producto' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.animate-fade-in {
    animation: fadeIn 0.3s ease-out forwards;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: scale(0.95);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}
</style>
