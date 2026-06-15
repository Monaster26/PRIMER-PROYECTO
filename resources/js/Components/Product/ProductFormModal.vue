<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { 
    X, Save, Image as ImageIcon, Plus, 
    CheckCircle, AlertCircle, Trash2 
} from 'lucide-vue-next';
import { useCategoryStore } from '@/Stores/categoryStore';

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
watch(() => props.show, (isShowing) => {
    if (isShowing) {
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
            form.sort_order = props.product.sort_order || 0;
            form.unit = props.product.unit || 'und';
            form.tax_rate = props.product.tax_rate?.toString() || '0';
            form.max_discount = props.product.max_discount || 0;
            imagePreview.value = props.product.image_path ? `/storage/${props.product.image_path}` : null;
        } else {
            form.reset();
            form._method = 'POST';
            imagePreview.value = null;
        }
    }
});

// ─── Lógica de Subcategorías Jerárquicas ──────────────────────────
const filteredSubcategories = computed(() => {
    if (!form.category_id) return [];
    // Buscamos en el store central de categorías la estructura jerárquica
    const selectedCat = categoryStore.categories.find(c => 
        c.id === form.category_id || c.id === form.category_id.toString()
    );
    return selectedCat?.subcategories ?? [];
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
        }
    });
}
</script>

<template>
    <Teleport to="body">
        <Transition 
            enter-active-class="transition-opacity duration-300" enter-from-class="opacity-0" enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-200" leave-from-class="opacity-100" leave-to-class="opacity-0"
        >
            <div v-if="show" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[100] flex items-center justify-center p-4">
                <div class="bg-white dark:bg-surface-dark w-full max-w-4xl rounded-4xl shadow-2xl flex flex-col max-h-[90vh] overflow-hidden animate-pop-in">
                    
                    <!-- Header -->
                    <div class="px-8 py-6 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between bg-gradient-to-r from-secondary-50 to-white dark:from-gray-900 dark:to-surface-dark">
                        <div>
                            <h2 class="text-2xl font-display font-extrabold text-content-primary dark:text-white">
                                {{ product ? 'Editar Producto' : 'Nuevo Producto' }}
                            </h2>
                            <p class="text-xs text-content-muted mt-1 uppercase tracking-widest font-bold">Monasterios Market Inventory System</p>
                        </div>
                        <button @click="emit('close')" class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                            <X class="w-6 h-6" />
                        </button>
                    </div>

                    <!-- Form Content -->
                    <form @submit.prevent="submit" class="flex-1 overflow-y-auto p-8">
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                            
                            <!-- Left: Image & Main Stats -->
                            <div class="lg:col-span-1 space-y-6">
                                <div class="relative group">
                                    <div class="aspect-square rounded-4xl bg-gray-50 dark:bg-gray-900 border-2 border-dashed border-gray-200 dark:border-gray-700 flex flex-col items-center justify-center overflow-hidden transition-all group-hover:border-primary-400">
                                        <img v-if="imagePreview" :src="imagePreview" class="w-full h-full object-cover" />
                                        <div v-else class="flex flex-col items-center text-gray-400 p-6 text-center">
                                            <ImageIcon class="w-12 h-12 mb-3" />
                                            <p class="text-xs font-bold uppercase tracking-tighter">Click para subir foto</p>
                                        </div>
                                        <input type="file" @change="handleImageChange" class="absolute inset-0 opacity-0 cursor-pointer" accept="image/*" />
                                    </div>
                                    <div v-if="form.errors.image" class="mt-2 text-xs text-danger font-bold uppercase">{{ form.errors.image }}</div>
                                </div>

                                <div class="p-5 bg-primary-50 dark:bg-primary-900/20 rounded-3xl space-y-4">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-bold text-primary-600 uppercase">Estado Visible</span>
                                        <button 
                                            type="button"
                                            @click="form.is_active = !form.is_active"
                                            class="w-12 h-6 rounded-full transition-colors relative"
                                            :class="form.is_active ? 'bg-primary-500' : 'bg-gray-300'"
                                        >
                                            <span class="absolute top-1 left-1 bg-white w-4 h-4 rounded-full transition-transform" :class="{ 'translate-x-6': form.is_active }"></span>
                                        </button>
                                    </div>
                                    <div class="pt-4 border-t border-primary-100 dark:border-primary-800">
                                        <label class="block text-xs font-bold text-primary-600 uppercase mb-2">Orden en Tienda</label>
                                        <input v-model.number="form.sort_order" type="number" class="w-full bg-white dark:bg-gray-900 border-none rounded-xl text-sm font-bold p-3" />
                                    </div>
                                </div>
                            </div>

                            <!-- Right: Detailed Info -->
                            <div class="lg:col-span-2 space-y-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="md:col-span-2">
                                        <label class="block text-xs font-bold text-content-muted uppercase tracking-wider mb-2">Nombre Comercial</label>
                                        <input v-model="form.name" type="text" placeholder="Ej: Arroz Grado 1 1kg" class="w-full px-5 py-3 rounded-2xl border border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-900 focus:ring-2 focus:ring-primary-500" required />
                                        <div v-if="form.errors.name" class="text-xs text-danger mt-1">{{ form.errors.name }}</div>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold text-content-muted uppercase tracking-wider mb-2">SKU / Código Interno</label>
                                        <input v-model="form.sku" type="text" class="w-full px-5 py-3 rounded-2xl border border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-900 font-mono text-sm" />
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold text-content-muted uppercase tracking-wider mb-2">Código de Barras</label>
                                        <input v-model="form.barcode" type="text" class="w-full px-5 py-3 rounded-2xl border border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-900 font-mono text-sm" />
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold text-content-muted uppercase tracking-wider mb-2">Departamento (Padre)</label>
                                        <select v-model="form.category_id" class="w-full px-5 py-3 rounded-2xl border border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-900 text-sm font-bold" required>
                                            <option value="">Seleccione Departamento</option>
                                            <option v-for="cat in categoryStore.categories" :key="cat.id" :value="cat.id">
                                                {{ cat.emoji }} {{ cat.name }}
                                            </option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold text-content-muted uppercase tracking-wider mb-2">Sub-categoría</label>
                                        <select v-model="form.sub_category" class="w-full px-5 py-3 rounded-2xl border border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-900 text-sm" :disabled="!form.category_id">
                                            <option value="">Ninguna</option>
                                            <option v-for="sub in filteredSubcategories" :key="sub" :value="sub">{{ sub }}</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold text-content-muted uppercase tracking-wider mb-2">Precio de Venta ($)</label>
                                        <input v-model.number="form.price" type="number" class="w-full px-5 py-3 rounded-2xl border border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-900 text-lg font-black text-primary-500" required />
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold text-content-muted uppercase tracking-wider mb-2">Unidad</label>
                                        <select v-model="form.unit" class="w-full px-5 py-3 rounded-2xl border border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-900 text-sm">
                                            <option value="und">Unidad (und)</option>
                                            <option value="kg">Kilogramo (kg)</option>
                                            <option value="lt">Litro (lt)</option>
                                            <option value="ml">Mililitro (ml)</option>
                                            <option value="g">Gramo (g)</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold text-content-muted uppercase tracking-wider mb-2">Impuesto (IVA %)</label>
                                        <select v-model="form.tax_rate" class="w-full px-5 py-3 rounded-2xl border border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-900 text-sm">
                                            <option value="0">Exento (0%)</option>
                                            <option value="5">IVA Reducido (5%)</option>
                                            <option value="19">IVA General (19%)</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold text-content-muted uppercase tracking-wider mb-2">Stock Disponible</label>
                                        <div class="flex items-center gap-3">
                                            <input v-model.number="form.stock" type="number" class="flex-1 px-5 py-3 rounded-2xl border border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-900 font-bold" />
                                            <div class="flex items-center gap-1 text-[10px] font-bold text-danger uppercase px-3 py-1 bg-red-50 rounded-lg" v-if="form.stock < 5">
                                                <AlertCircle class="w-3 h-3" /> Bajo Stock
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-content-muted uppercase tracking-wider mb-2">Descripción del Producto</label>
                                    <textarea v-model="form.description" rows="4" class="w-full px-5 py-3 rounded-2xl border border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-900 text-sm" placeholder="Detalles, peso, ingredientes..."></textarea>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Footer -->
                    <div class="px-8 py-6 border-t border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-900/50 flex items-center justify-end gap-4">
                        <button @click="emit('close')" type="button" class="px-6 py-3 rounded-2xl font-bold text-content-secondary hover:bg-gray-100 dark:hover:bg-gray-800 transition-all">
                            Cancelar
                        </button>
                        <button 
                            @click="submit"
                            :disabled="form.processing" 
                            class="px-10 py-3 rounded-2xl bg-primary-500 hover:bg-primary-600 text-white font-bold shadow-primary-md flex items-center gap-2 transition-all disabled:opacity-50"
                        >
                            <Save v-if="!form.processing" class="w-5 h-5" />
                            <div v-else class="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
                            {{ form.processing ? 'Guardando...' : (product ? 'Actualizar Producto' : 'Guardar Producto') }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
