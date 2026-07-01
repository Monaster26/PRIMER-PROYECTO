<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Percent } from 'lucide-vue-next';
import { onUnmounted, ref } from 'vue';

import BundleDiscountForm from '@/Components/Promotions/BundleDiscountForm.vue';
import BuyXGetYForm from '@/Components/Promotions/BuyXGetYForm.vue';
import CategoryDiscountForm from '@/Components/Promotions/CategoryDiscountForm.vue';
import MinQtyDiscountForm from '@/Components/Promotions/MinQtyDiscountForm.vue';
import PromotionList from '@/Components/Promotions/PromotionList.vue';
import PromotionSchedule from '@/Components/Promotions/PromotionSchedule.vue';
import SpecialPriceForm from '@/Components/Promotions/SpecialPriceForm.vue';

interface PromotionItem {
    id: number;
    name: string;
    description: string | null;
    type: string;
    conditions: Record<string, any>;
    rewards: Record<string, any>;
    is_active: boolean;
    is_exclusive: boolean;
    max_uses: number | null;
    used_count: number;
    priority: number;
    starts_at: string | null;
    expires_at: string | null;
    created_at: string;
    total_discount_given?: number;
}

const props = defineProps<{
    promotions: {
        data: PromotionItem[];
        links: any[];
        current_page: number;
        last_page: number;
        prev_page_url: string | null;
        next_page_url: string | null;
    };
    filters: {
        search?: string;
        type?: string;
        status?: string;
    };
    categories: { id: number; name: string; parent_id: number | null }[];
}>();

const filters = ref({ ...props.filters });
const filterTimer = ref<ReturnType<typeof setTimeout> | null>(null);
onUnmounted(() => {
    if (filterTimer.value) clearTimeout(filterTimer.value);
});
const showForm = ref(false);
const editingId = ref<number | null>(null);
const discountMode = ref<'pct' | 'fixed'>('pct');

const typeLabels: Record<string, string> = {
    buy_x_get_y: 'Compra X, Lleva Y',
    min_qty_discount: 'Dto. por Cantidad',
    bundle_discount: 'Combo',
    special_price: 'Precio Especial',
    category_discount: 'Dto. Categoría',
};

const typeColors: Record<string, string> = {
    buy_x_get_y:
        'bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-300',
    min_qty_discount:
        'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
    bundle_discount:
        'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300',
    special_price:
        'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300',
    category_discount:
        'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300',
};

const form = useForm({
    name: '',
    description: '',
    type: 'min_qty_discount',
    is_active: true,
    is_exclusive: false,
    max_uses: null as number | null,
    priority: 0,
    starts_at: '',
    expires_at: '',
    conditions: {} as Record<string, any>,
    rewards: {} as Record<string, any>,
});

function resetForm() {
    form.reset();
    form.is_active = true;
    form.is_exclusive = false;
    form.max_uses = null;
    form.priority = 0;
    form.conditions = {};
    form.rewards = {};
    form.type = 'min_qty_discount';
    discountMode.value = 'pct';
}

function openNew() {
    resetForm();
    editingId.value = null;
    showForm.value = true;
}

function openEdit(promotion: PromotionItem) {
    resetForm();
    editingId.value = promotion.id;
    form.name = promotion.name;
    form.description = promotion.description || '';
    form.type = promotion.type;
    form.is_active = promotion.is_active;
    form.is_exclusive = promotion.is_exclusive;
    form.max_uses = promotion.max_uses;
    form.priority = promotion.priority;
    form.starts_at = toDatetimeLocal(promotion.starts_at);
    form.expires_at = toDatetimeLocal(promotion.expires_at);
    form.conditions = { ...promotion.conditions };
    form.rewards = { ...promotion.rewards };

    if (promotion.type === 'min_qty_discount') {
        discountMode.value = promotion.conditions.special_price
            ? 'fixed'
            : 'pct';
    }
    if (
        promotion.type === 'buy_x_get_y' ||
        promotion.type === 'bundle_discount'
    ) {
        discountMode.value = promotion.conditions.special_price_total
            ? 'fixed'
            : 'pct';
    }
    showForm.value = true;
}

function cancelForm() {
    showForm.value = false;
    editingId.value = null;
    resetForm();
}

function submitForm() {
    if (form.type === 'min_qty_discount' && !form.conditions.product_id) {
        alert('Selecciona un producto antes de guardar.');
        return;
    }

    const payload: Record<string, any> = {
        name: form.name,
        description: form.description || null,
        type: form.type,
        is_active: form.is_active,
        is_exclusive: form.is_exclusive,
        max_uses: form.max_uses || null,
        priority: form.priority,
        starts_at: form.starts_at || null,
        expires_at: form.expires_at || null,
    };

    if (form.type === 'min_qty_discount') {
        payload.conditions = {
            product_id: form.conditions.product_id,
            min_qty: form.conditions.min_qty,
        };
        if (discountMode.value === 'pct') {
            payload.conditions.discount_pct = form.conditions.discount_pct;
        } else {
            payload.conditions.special_price = form.conditions.special_price;
        }
    } else if (form.type === 'buy_x_get_y') {
        payload.conditions = {
            buy_product_id: form.conditions.buy_product_id,
            buy_qty: form.conditions.buy_qty,
        };
        payload.rewards = {
            get_product_id: form.rewards.get_product_id,
            get_qty: form.rewards.get_qty,
        };
        if (discountMode.value === 'pct') {
            payload.rewards.discount_pct = form.rewards.discount_pct ?? 100;
        } else {
            payload.conditions.special_price_total =
                form.conditions.special_price_total;
        }
    } else if (form.type === 'bundle_discount') {
        payload.conditions = { product_ids: form.conditions.product_ids };
        if (discountMode.value === 'pct') {
            payload.conditions.discount_pct = form.conditions.discount_pct;
        } else {
            payload.conditions.special_price_total =
                form.conditions.special_price_total;
        }
    } else if (form.type === 'special_price') {
        payload.conditions = {
            product_id: form.conditions.product_id,
            special_price: form.conditions.special_price,
        };
    } else if (form.type === 'category_discount') {
        payload.conditions = {
            category_id: form.conditions.category_id,
            discount_pct: form.conditions.discount_pct,
        };
    }

    const options = {
        onSuccess: () => cancelForm(),
        onError: (errors: Record<string, string>) => {
            const msgs = Object.values(errors);
            if (msgs.length) alert('Error:\n' + msgs.join('\n'));
        },
    };

    if (editingId.value) {
        payload._method = 'PUT';
        router.post(
            route('admin.promociones.update', editingId.value),
            payload,
            options,
        );
    } else {
        router.post(route('admin.promociones.store'), payload, options);
    }
}

function deletePromotion(id: number) {
    if (!confirm('¿Eliminar esta promoción?')) return;
    try {
        router.post(
            route('admin.promociones.destroy', id),
            { _method: 'DELETE' },
            {
                preserveScroll: true,
                onError: (errors) =>
                    alert(
                        'Error al eliminar:\n' +
                            Object.values(errors).join('\n'),
                    ),
            },
        );
    } catch (e) {
        alert('Error inesperado: ' + String(e));
    }
}

function toggleActive(id: number) {
    router.patch(
        route('admin.promociones.toggle', id),
        {},
        {
            preserveScroll: true,
            preserveState: true,
            onError: (errors) =>
                alert(
                    'Error al cambiar estado:\n' +
                        Object.values(errors).join('\n'),
                ),
        },
    );
}

function applyFilters() {
    if (filterTimer.value) clearTimeout(filterTimer.value);
    filterTimer.value = setTimeout(() => {
        router.get(
            route('admin.promociones.index'),
            {
                search: filters.value.search || '',
                type: filters.value.type || '',
                status: filters.value.status || '',
            },
            { preserveState: true, replace: true },
        );
    }, 300);
}

function clearFilters() {
    filters.value = { search: '', type: '', status: '' };
    applyFilters();
}

function toDatetimeLocal(iso: string | null): string {
    if (!iso) return '';
    const d = new Date(iso);
    const pad = (n: number) => n.toString().padStart(2, '0');
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
}
</script>

<template>
    <Head title="Promociones" />
    <AdminLayout>
        <template #title>
            <h1
                class="font-display text-xl font-bold text-content-primary dark:text-white"
            >
                Promociones
            </h1>
        </template>

        <!-- List View -->
        <PromotionList
            v-if="!showForm"
            :promotions="promotions"
            :filters="filters"
            :type-labels="typeLabels"
            :type-colors="typeColors"
            @update:filters="filters = $event"
            @new="openNew"
            @edit="openEdit"
            @delete="deletePromotion"
            @toggle="toggleActive"
            @apply-filters="applyFilters"
            @clear-filters="clearFilters"
        />

        <!-- Form View -->
        <template v-else>
            <div
                class="overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-surface-dark"
            >
                <div
                    class="flex items-center gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-800"
                >
                    <Percent class="h-5 w-5 text-primary-500" />
                    <h2
                        class="flex-1 text-sm font-bold text-content-primary dark:text-white"
                    >
                        {{ editingId ? 'Editar Promoción' : 'Nueva Promoción' }}
                    </h2>
                </div>

                <form @submit.prevent="submitForm" class="space-y-6 p-6">
                    <!-- Common fields -->
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label
                                class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted"
                                >Nombre</label
                            >
                            <input
                                v-model="form.name"
                                type="text"
                                required
                                class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                            />
                        </div>
                        <div>
                            <label
                                class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted"
                                >Tipo</label
                            >
                            <select
                                v-model="form.type"
                                class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                            >
                                <option value="min_qty_discount">
                                    Dto. por Cantidad
                                </option>
                                <option value="buy_x_get_y">
                                    Compra X, Lleva Y
                                </option>
                                <option value="bundle_discount">Combo</option>
                                <option value="special_price">
                                    Precio Especial
                                </option>
                                <option value="category_discount">
                                    Dto. Categoría
                                </option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label
                                class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted"
                                >Descripción</label
                            >
                            <textarea
                                v-model="form.description"
                                rows="2"
                                class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                            ></textarea>
                        </div>
                    </div>

                    <MinQtyDiscountForm
                        v-if="form.type === 'min_qty_discount'"
                        :form="form"
                        :discount-mode="discountMode"
                        @update:discount-mode="discountMode = $event"
                    />

                    <BuyXGetYForm
                        v-if="form.type === 'buy_x_get_y'"
                        :form="form"
                        :discount-mode="discountMode"
                        @update:discount-mode="discountMode = $event"
                    />

                    <BundleDiscountForm
                        v-if="form.type === 'bundle_discount'"
                        :form="form"
                        :discount-mode="discountMode"
                        @update:discount-mode="discountMode = $event"
                    />

                    <SpecialPriceForm
                        v-if="form.type === 'special_price'"
                        :form="form"
                    />

                    <CategoryDiscountForm
                        v-if="form.type === 'category_discount'"
                        :form="form"
                        :categories="categories"
                    />

                    <PromotionSchedule :form="form" />

                    <!-- Submit buttons -->
                    <div class="flex gap-3 pt-2">
                        <button
                            type="button"
                            @click="cancelForm"
                            class="flex-1 rounded-2xl border border-gray-200 py-2.5 text-sm font-bold text-content-secondary transition-colors hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800"
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="flex flex-1 items-center justify-center gap-2 rounded-2xl bg-primary-500 py-2.5 text-sm font-bold text-white shadow-sm transition-colors hover:bg-primary-600 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            {{
                                form.processing
                                    ? 'Guardando...'
                                    : editingId
                                      ? 'Actualizar'
                                      : 'Crear Promoción'
                            }}
                        </button>
                    </div>
                </form>
            </div>
        </template>
    </AdminLayout>
</template>
