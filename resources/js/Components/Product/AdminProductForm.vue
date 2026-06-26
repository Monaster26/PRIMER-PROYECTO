<script setup lang="ts">
import { Image, Save, ToggleLeft, ToggleRight, X } from 'lucide-vue-next';
import { computed, nextTick, ref, watch } from 'vue';

interface CategoryTreeItem {
    id: number;
    name: string;
    slug: string;
    children: CategoryTreeItem[];
}

const props = defineProps<{
    showForm: boolean;
    editingId: number | null;
    form: any;
    imagePreview: string | null;
    categoryTree: CategoryTreeItem[];
    units: string[];
}>();

const emit = defineEmits<{
    close: [];
    submit: [];
    selectImage: [file: File | undefined];
    removeImage: [];
}>();

const fileInputRef = ref<HTMLInputElement | null>(null);
const barcodeInputRef = ref<HTMLInputElement | null>(null);

const selectedCategory = computed(() =>
    props.categoryTree.find((c) => c.slug === props.form.category_slug) ?? null,
);

const subcategories = computed(() =>
    selectedCategory.value?.children ?? [],
);

watch(() => props.showForm, (val) => {
    if (val) {
        nextTick(() => barcodeInputRef.value?.focus());
    }
});

function handleImageSelect(e: Event) {
    const target = e.target as HTMLInputElement;
    emit('selectImage', target.files?.[0]);
}

function handleRemoveImage() {
    if (fileInputRef.value) fileInputRef.value.value = '';
    emit('removeImage');
}
</script>

<template>
    <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
            v-if="props.showForm"
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
                            props.editingId ? 'Editar Producto' : 'Nuevo Producto'
                        }}
                    </h3>
                    <button
                        @click="emit('close')"
                        class="rounded-xl p-2 transition-colors hover:bg-gray-100 dark:hover:bg-gray-800"
                    >
                        <X class="h-5 w-5 text-content-muted" />
                    </button>
                </div>

                <form @submit.prevent="emit('submit')">
                    <div class="flex flex-col gap-6 lg:flex-row">
                        <div
                            class="w-full space-y-5 lg:w-64 lg:flex-shrink-0"
                        >
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
                                        @change="handleImageSelect"
                                    />
                                    <template v-if="props.imagePreview">
                                        <img
                                            :src="props.imagePreview"
                                            class="absolute inset-0 h-full w-full object-cover"
                                        />
                                        <button
                                            type="button"
                                            @click.stop="handleRemoveImage"
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
                                        props.form.is_active = !props.form.is_active
                                    "
                                    class="flex w-full items-center gap-3 rounded-2xl border border-gray-200 px-4 py-2.5 transition-colors hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800"
                                >
                                    <component
                                        :is="
                                            props.form.is_active
                                                ? ToggleRight
                                                : ToggleLeft
                                        "
                                        class="h-6 w-6"
                                        :class="
                                            props.form.is_active
                                                ? 'text-success'
                                                : 'text-content-muted'
                                        "
                                    />
                                    <span
                                        class="text-sm font-bold"
                                        :class="
                                            props.form.is_active
                                                ? 'text-success'
                                                : 'text-content-muted'
                                        "
                                    >
                                        {{
                                            props.form.is_active
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
                                        props.form.is_featured = !props.form.is_featured
                                    "
                                    class="flex w-full items-center gap-3 rounded-2xl border border-gray-200 px-4 py-2.5 transition-colors hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800"
                                >
                                    <component
                                        :is="
                                            props.form.is_featured
                                                ? ToggleRight
                                                : ToggleLeft
                                        "
                                        class="h-6 w-6"
                                        :class="
                                            props.form.is_featured
                                                ? 'text-amber-500'
                                                : 'text-content-muted'
                                        "
                                    />
                                    <span
                                        class="text-sm font-bold"
                                        :class="
                                            props.form.is_featured
                                                ? 'text-amber-500'
                                                : 'text-content-muted'
                                        "
                                    >
                                        {{
                                            props.form.is_featured
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
                                    v-model.number="props.form.sort_order"
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
                                    v-model="props.form.name"
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
                                        v-model="props.form.sku"
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
                                        v-model="props.form.barcode"
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
                                        v-model="props.form.category_slug"
                                        class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                    >
                                        <option value="">
                                            Seleccionar departamento
                                        </option>
                                        <option
                                            v-for="cat in props.categoryTree"
                                            :key="cat.id"
                                            :value="cat.slug"
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
                                        v-model="props.form.sub_category"
                                        class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                    >
                                        <option value="">
                                            Sin subcategoría
                                        </option>
                                        <option
                                            v-for="sub in subcategories"
                                            :key="sub.id"
                                            :value="sub.name"
                                        >
                                            {{ sub.name }}
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
                                    v-model.number="props.form.price"
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
                                        v-model="props.form.unit"
                                        class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                    >
                                        <option
                                            v-for="u in props.units"
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
                                        v-model.number="props.form.tax_rate"
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
                                        v-model="props.form.expiration_date"
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
                                        v-model.number="props.form.stock"
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
                                        v-model.number="props.form.min_stock"
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
                                        v-model.number="props.form.cost_price"
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
                            @click="emit('close')"
                            class="px-4 py-2 text-sm font-bold text-content-secondary transition-colors hover:text-content-primary"
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            :disabled="props.form.processing"
                            class="flex items-center gap-2 rounded-2xl bg-primary-500 px-6 py-2.5 text-sm font-bold text-white shadow-sm transition-colors hover:bg-primary-600"
                        >
                            <Save class="h-4 w-4" />
                            {{
                                props.editingId
                                    ? 'Actualizar Producto'
                                    : 'Guardar Producto'
                            }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </Transition>
</template>
