<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useForm, Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { 
    Save, ArrowLeft, Image as ImageIcon, 
    CheckCircle, AlertCircle, Package,
    Layers, Barcode, Tag, Info
} from 'lucide-vue-next';

const props = defineProps<{
    product: any | null;
    categories: any[];
}>();

// ─── Estado del Formulario ────────────────────────────────────────
const form = useForm({
    _method: props.product ? 'PUT' : 'POST',
    category_id: props.product?.category_id?.toString() || '',
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
const imagePreview = ref<string | null>(props.product?.image_path ? `/storage/${props.product.image_path}` : null);

function handleImageChange(e: Event) {
    const file = (e.target as HTMLInputElement).files?.[0];
    if (file) {
        form.image = file;
        imagePreview.value = URL.createObjectURL(file);
    }
}

// ─── Envío de Datos ──────────────────────────────────────────────
function submit() {
    const url = props.product 
        ? route('products.update', props.product.id) 
        : route('products.store');

    form.post(url, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            // Manejado por el controlador
        },
    });
}
</script>

<template>
    <Head :title="product ? 'Editar Producto' : 'Nuevo Producto'" />

    <AdminLayout>
        <div class="max-w-5xl mx-auto space-y-8 pb-20">
            <!-- Header de la Página -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link 
                        :href="route('products.index')"
                        class="p-2 rounded-2xl bg-white dark:bg-surface-dark border border-gray-100 dark:border-gray-800 text-content-secondary hover:text-primary-500 transition-all shadow-sm"
                    >
                        <ArrowLeft class="w-5 h-5" />
                    </Link>
                    <div>
                        <h1 class="text-3xl font-display font-black text-content-primary dark:text-white leading-tight">
                            {{ product ? 'Editar Producto' : 'Añadir Nuevo Producto' }}
                        </h1>
                        <p class="text-sm text-content-muted mt-1 font-medium">Gestiona los detalles, stock y precios de tu inventario.</p>
                    </div>
                </div>
                
                <button 
                    @click="submit"
                    :disabled="form.processing"
                    class="hidden md:flex items-center gap-2 bg-primary-500 hover:bg-primary-600 text-white px-8 py-3.5 rounded-2xl font-bold transition-all shadow-primary-md active:scale-95 disabled:opacity-50"
                >
                    <Save class="w-5 h-5" />
                    {{ product ? 'Guardar Cambios' : 'Crear Producto' }}
                </button>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Columna Izquierda: Imagen y Estado -->
                <div class="lg:col-span-1 space-y-8">
                    <!-- Card de Imagen -->
                    <div class="bg-white dark:bg-surface-dark rounded-4xl p-6 border border-gray-100 dark:border-gray-800 shadow-sm relative group overflow-hidden">
                        <label class="block text-xs font-black text-content-muted uppercase tracking-widest mb-4 flex items-center gap-2">
                            <ImageIcon class="w-4 h-4" /> Fotografía del Producto
                        </label>
                        
                        <div class="relative aspect-square rounded-3xl bg-gray-50 dark:bg-gray-900 border-2 border-dashed border-gray-200 dark:border-gray-700 flex items-center justify-center overflow-hidden transition-all group-hover:border-primary-400 group-hover:bg-primary-50/10">
                            <img v-if="imagePreview" :src="imagePreview" class="w-full h-full object-cover animate-fade-in" />
                            <div v-else class="flex flex-col items-center text-center p-6">
                                <div class="w-16 h-16 bg-white dark:bg-gray-800 rounded-2xl shadow-sm flex items-center justify-center mb-4">
                                    <Plus class="w-8 h-8 text-primary-400" />
                                </div>
                                <p class="text-sm font-bold text-content-secondary dark:text-gray-400">Click o arrastra para subir</p>
                                <p class="text-[10px] text-content-muted mt-2 uppercase font-black">PNG, JPG hasta 5MB</p>
                            </div>
                            <input type="file" @change="handleImageChange" class="absolute inset-0 opacity-0 cursor-pointer" accept="image/*" />
                        </div>
                        <div v-if="form.errors.image" class="mt-4 p-3 rounded-xl bg-red-50 text-danger text-[11px] font-bold uppercase">{{ form.errors.image }}</div>
                    </div>

                    <!-- Card de Estado -->
                    <div class="bg-white dark:bg-surface-dark rounded-4xl p-6 border border-gray-100 dark:border-gray-800 shadow-sm">
                        <label class="block text-xs font-black text-content-muted uppercase tracking-widest mb-6">Estado y Visibilidad</label>
                        
                        <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-900 rounded-3xl border border-gray-100 dark:border-gray-800">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center" :class="form.is_active ? 'bg-success/10 text-success' : 'bg-gray-200 text-gray-400'">
                                    <CheckCircle v-if="form.is_active" class="w-5 h-5" />
                                    <AlertCircle v-else class="w-5 h-5" />
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-content-primary dark:text-white">Producto Activo</p>
                                    <p class="text-[10px] text-content-muted">{{ form.is_active ? 'Visible en tienda' : 'Oculto' }}</p>
                                </div>
                            </div>
                            <button 
                                type="button"
                                @click="form.is_active = !form.is_active"
                                class="w-12 h-6 rounded-full transition-all relative"
                                :class="form.is_active ? 'bg-primary-500' : 'bg-gray-300'"
                            >
                                <span class="absolute top-1 left-1 bg-white w-4 h-4 rounded-full transition-transform shadow-sm" :class="{ 'translate-x-6': form.is_active }"></span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Columna Derecha: Información Principal -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Datos Básicos -->
                    <div class="bg-white dark:bg-surface-dark rounded-4xl p-8 border border-gray-100 dark:border-gray-800 shadow-sm space-y-6">
                        <label class="block text-xs font-black text-content-muted uppercase tracking-widest flex items-center gap-2">
                            <Info class="w-4 h-4" /> Información General
                        </label>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold text-content-secondary dark:text-gray-400 mb-2">Nombre del Producto</label>
                                <input 
                                    v-model="form.name" 
                                    type="text" 
                                    placeholder="Ej: Papas Kryzpo Sabor Queso"
                                    class="w-full px-5 py-4 rounded-2xl border border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-900 focus:ring-2 focus:ring-primary-500 transition-all font-medium"
                                />
                                <p v-if="form.errors.name" class="text-xs text-danger mt-2 font-bold">{{ form.errors.name }}</p>
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold text-content-secondary dark:text-gray-400 mb-2">Descripción Detallada</label>
                                <textarea 
                                    v-model="form.description" 
                                    rows="4"
                                    class="w-full px-5 py-4 rounded-2xl border border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-900 focus:ring-2 focus:ring-primary-500 transition-all font-medium resize-none"
                                    placeholder="Describe las características principales..."
                                ></textarea>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-content-secondary dark:text-gray-400 mb-2">Departamento / Categoría</label>
                                <div class="relative">
                                    <Layers class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-content-muted" />
                                    <select v-model="form.category_id" class="w-full pl-12 pr-5 py-4 rounded-2xl border border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-900 focus:ring-2 focus:ring-primary-500 appearance-none font-bold text-sm">
                                        <option value="">Selecciona una categoría</option>
                                        <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                                    </select>
                                </div>
                                <p v-if="form.errors.category_id" class="text-xs text-danger mt-2 font-bold">{{ form.errors.category_id }}</p>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-content-secondary dark:text-gray-400 mb-2">Unidad de Medida</label>
                                <select v-model="form.unit" class="w-full px-5 py-4 rounded-2xl border border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-900 focus:ring-2 focus:ring-primary-500 font-bold text-sm">
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
                    <div class="bg-white dark:bg-surface-dark rounded-4xl p-8 border border-gray-100 dark:border-gray-800 shadow-sm space-y-6">
                        <label class="block text-xs font-black text-content-muted uppercase tracking-widest flex items-center gap-2">
                            <Tag class="w-4 h-4" /> Finanzas e Inventario
                        </label>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-bold text-content-secondary dark:text-gray-400 mb-2">Precio de Venta ($)</label>
                                <div class="relative">
                                    <span class="absolute left-5 top-1/2 -translate-y-1/2 font-black text-primary-500">$</span>
                                    <input v-model.number="form.price" type="number" class="w-full pl-10 pr-5 py-4 rounded-2xl border border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-900 focus:ring-2 focus:ring-primary-500 font-black text-lg text-primary-600" />
                                </div>
                                <p v-if="form.errors.price" class="text-xs text-danger mt-2 font-bold">{{ form.errors.price }}</p>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-content-secondary dark:text-gray-400 mb-2">Stock Inicial</label>
                                <div class="relative">
                                    <Package class="absolute left-5 top-1/2 -translate-y-1/2 w-4 h-4 text-content-muted" />
                                    <input v-model.number="form.stock" type="number" class="w-full pl-12 pr-5 py-4 rounded-2xl border border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-900 focus:ring-2 focus:ring-primary-500 font-bold" />
                                </div>
                                <p v-if="form.errors.stock" class="text-xs text-danger mt-2 font-bold">{{ form.errors.stock }}</p>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-content-secondary dark:text-gray-400 mb-2">Código SKU</label>
                                <input v-model="form.sku" type="text" placeholder="Ej: MK-PAP-01" class="w-full px-5 py-4 rounded-2xl border border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-900 focus:ring-2 focus:ring-primary-500 font-mono text-sm uppercase" />
                                <p v-if="form.errors.sku" class="text-xs text-danger mt-2 font-bold">{{ form.errors.sku }}</p>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-content-secondary dark:text-gray-400 mb-2">Código de Barras</label>
                                <div class="relative">
                                    <Barcode class="absolute left-5 top-1/2 -translate-y-1/2 w-4 h-4 text-content-muted" />
                                    <input v-model="form.barcode" type="text" placeholder="Ej: 779123456789" class="w-full pl-12 pr-5 py-4 rounded-2xl border border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-900 focus:ring-2 focus:ring-primary-500 font-mono text-sm" />
                                </div>
                                <p v-if="form.errors.barcode" class="text-xs text-danger mt-2 font-bold">{{ form.errors.barcode }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Botón Mobile -->
                    <div class="md:hidden pt-4">
                        <button 
                            @click="submit"
                            :disabled="form.processing"
                            class="w-full flex items-center justify-center gap-3 bg-primary-500 hover:bg-primary-600 text-white py-5 rounded-3xl font-black transition-all shadow-primary-lg"
                        >
                            <Save class="w-6 h-6" />
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
    from { opacity: 0; transform: scale(0.95); }
    to { opacity: 1; transform: scale(1); }
}
</style>
