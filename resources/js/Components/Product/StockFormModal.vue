<script setup lang="ts">
import { Plus, Search, X } from 'lucide-vue-next';
import { nextTick, ref } from 'vue';

const props = defineProps<{
    showStockForm: boolean;
    stockProduct: any | null;
    stockForm: any;
    totalInvestmentFormatted: string;
    keepOpen: boolean;
    skuQuery: string;
    nameQuery: string;
    nameResults: any[];
    showNameDropdown: boolean;
    nameLoading: boolean;
}>();

const emit = defineEmits<{
    close: [];
    submit: [];
    'update:skuQuery': [value: string];
    'update:nameQuery': [value: string];
    'update:keepOpen': [value: boolean];
    skuEnter: [];
    selectSearchProduct: [product: any];
    hideNameDropdown: [];
}>();

const skuSearchRef = ref<HTMLInputElement | null>(null);
const nameSearchRef = ref<HTMLInputElement | null>(null);
const quantityInputRef = ref<HTMLInputElement | null>(null);
const costoInputRef = ref<HTMLInputElement | null>(null);
const fechaInputRef = ref<HTMLInputElement | null>(null);
const notaInputRef = ref<HTMLInputElement | null>(null);

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
    if (props.stockProduct) emit('submit');
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
            v-if="props.showStockForm"
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
                        @click="emit('close')"
                        class="rounded-xl p-2 transition-colors hover:bg-gray-100 dark:hover:bg-gray-800"
                    >
                        <X class="h-5 w-5 text-content-muted" />
                    </button>
                </div>
                <form @submit.prevent="emit('submit')" class="space-y-4">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
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
                                    :value="props.skuQuery"
                                    @input="emit('update:skuQuery', ($event.target as HTMLInputElement).value)"
                                    type="text"
                                    placeholder="Código / SKU..."
                                    @keydown.enter.prevent="emit('skuEnter')"
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
                                    :value="props.nameQuery"
                                    @input="emit('update:nameQuery', ($event.target as HTMLInputElement).value)"
                                    type="text"
                                    placeholder="Buscar por nombre..."
                                    class="w-full rounded-2xl border-0 bg-transparent py-3 pl-11 pr-4 text-sm text-content-primary placeholder:text-content-muted focus:ring-2 focus:ring-primary-500 dark:text-white"
                                    @blur="emit('hideNameDropdown')"
                                    @focus="
                                        props.nameResults.length > 0
                                            ? null
                                            : null
                                    "
                                />
                                <div
                                    v-if="props.nameLoading"
                                    class="absolute right-4 h-4 w-4 animate-spin rounded-full border-2 border-primary-500 border-t-transparent"
                                ></div>
                            </div>
                            <div
                                v-if="props.showNameDropdown"
                                class="absolute left-0 right-0 top-full z-50 mt-1 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-900"
                            >
                                <button
                                    v-for="p in props.nameResults"
                                    :key="p.id"
                                    type="button"
                                    @click="emit('selectSearchProduct', p)"
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

                    <template v-if="props.stockProduct">
                        <div
                            class="rounded-xl bg-gray-50 p-3 dark:bg-gray-800/50"
                        >
                            <span
                                class="text-xs font-bold text-content-muted"
                                >Producto:</span
                            >
                            <span
                                class="ml-1 text-sm font-bold text-content-primary dark:text-white"
                                >{{ props.stockProduct.name }}</span
                            >
                            <span class="mx-3 text-content-muted">|</span>
                            <span
                                class="text-xs font-bold text-content-muted"
                                >Stock Actual:</span
                            >
                            <span
                                class="ml-1 text-sm font-bold text-content-primary dark:text-white"
                                >{{ props.stockProduct.stock ?? '—' }} un.</span
                            >
                            <span class="mx-3 text-content-muted">|</span>
                            <span
                                class="text-xs font-bold text-content-muted"
                                >Último Costo:</span
                            >
                            <span
                                class="ml-1 text-sm font-bold text-primary-500"
                                >{{
                                    props.stockProduct.cost_price != null
                                        ? '$ ' +
                                          (
                                              props.stockProduct.cost_price / 100
                                          ).toLocaleString('es-CO')
                                        : '—'
                                }}</span
                            >
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <label
                                    class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                                    >Cantidad</label
                                >
                                <input
                                    ref="quantityInputRef"
                                    v-model.number="props.stockForm.quantity"
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
                                        v-model.number="props.stockForm.unit_cost"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        placeholder="0"
                                        @keydown.enter.prevent="cascadeCosto"
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
                                    v-model="props.stockForm.expiration_date"
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
                                v-model="props.stockForm.notes"
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
                                >{{ props.totalInvestmentFormatted }}</span
                            >
                        </div>

                        <div
                            class="flex items-center gap-3 rounded-2xl border border-gray-100 bg-gray-50 px-4 py-3 dark:border-gray-700 dark:bg-gray-800/50"
                        >
                            <button
                                type="button"
                                @click="emit('update:keepOpen', !props.keepOpen)"
                                class="relative h-5 w-10 flex-shrink-0 rounded-full transition-colors"
                                :class="
                                    props.keepOpen
                                        ? 'bg-primary-500'
                                        : 'bg-gray-300 dark:bg-gray-600'
                                "
                            >
                                <span
                                    class="absolute left-0.5 top-0.5 h-4 w-4 rounded-full bg-white transition-transform"
                                    :class="{
                                        'translate-x-5': props.keepOpen,
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
                                @click="emit('close')"
                                class="flex-1 rounded-2xl border border-gray-200 py-2.5 text-sm font-bold text-content-secondary transition-colors hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800"
                            >
                                Cancelar
                            </button>
                            <button
                                type="submit"
                                :disabled="props.stockForm.processing"
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
</template>
