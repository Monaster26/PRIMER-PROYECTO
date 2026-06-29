<script setup lang="ts">
import {
    ChevronDown,
    ChevronRight,
    Package,
    Plus,
    Search,
    Tag,
    Upload,
} from 'lucide-vue-next';
import { onMounted, onUnmounted, ref } from 'vue';

interface CategoryTreeItem {
    id: number;
    name: string;
    slug: string;
    children: CategoryTreeItem[];
}

const props = defineProps<{
    search: string;
    filterCategory: string;
    selectedLabel: string;
    categoryTree: CategoryTreeItem[];
    dropdownOpen: boolean;
    expandedRoot: number | null;
}>();

const emit = defineEmits<{
    'update:search': [value: string];
    search: [];
    'update:dropdownOpen': [value: boolean];
    toggleRoot: [id: number];
    selectCategory: [id: number | null];
    openNew: [];
    openImport: [];
    openEmptyStockForm: [];
    openCategoryModal: [];
}>();

const dropdownRef = ref<HTMLElement | null>(null);

function handleClickOutside(e: MouseEvent) {
    if (!props.dropdownOpen) return;
    const target = e.target as Node;
    if (
        dropdownRef.value &&
        !dropdownRef.value.parentElement?.contains(target)
    ) {
        emit('update:dropdownOpen', false);
    }
}

onMounted(() => document.addEventListener('click', handleClickOutside));
onUnmounted(() => document.removeEventListener('click', handleClickOutside));
</script>

<template>
    <div
        class="flex flex-wrap items-center gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-800"
    >
        <Package class="h-5 w-5 text-primary-500" />
        <h2 class="flex-1 font-bold text-content-primary dark:text-white">
            Productos Registrados
        </h2>
        <div class="relative w-64">
            <Search
                class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-content-muted"
            />
            <input
                :value="props.search"
                @input="
                    emit(
                        'update:search',
                        ($event.target as HTMLInputElement).value,
                    );
                    emit('search');
                "
                type="text"
                placeholder="Buscar producto..."
                class="w-full rounded-xl border border-gray-200 bg-gray-50 py-2 pl-10 pr-4 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
            />
        </div>
        <div class="relative">
            <button
                @click="emit('update:dropdownOpen', !props.dropdownOpen)"
                class="flex items-center gap-2 rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
            >
                <span class="min-w-[100px] text-left">{{
                    props.selectedLabel
                }}</span>
                <ChevronDown
                    class="h-4 w-4 transition-transform duration-200"
                    :class="{ 'rotate-180': props.dropdownOpen }"
                />
            </button>
            <div
                v-if="props.dropdownOpen"
                ref="dropdownRef"
                class="absolute right-0 z-50 mt-1 max-h-96 w-72 overflow-y-auto rounded-xl border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-900"
            >
                <button
                    @click="emit('selectCategory', null)"
                    class="flex w-full items-center px-4 py-2.5 text-left text-sm transition hover:bg-gray-50 dark:hover:bg-gray-800"
                    :class="{
                        'font-bold text-primary-500': !props.filterCategory,
                    }"
                >
                    Todas las categorías
                </button>
                <template v-for="cat in props.categoryTree" :key="cat.id">
                    <div>
                        <button
                            @click.stop="emit('toggleRoot', cat.id)"
                            class="flex w-full items-center gap-2 px-4 py-2.5 text-left text-sm font-medium transition hover:bg-gray-50 dark:hover:bg-gray-800"
                            :class="{
                                'text-primary-500':
                                    props.filterCategory === String(cat.id),
                            }"
                        >
                            <ChevronRight
                                v-if="props.expandedRoot !== cat.id"
                                class="h-3.5 w-3.5 flex-shrink-0"
                            />
                            <ChevronDown
                                v-else
                                class="h-3.5 w-3.5 flex-shrink-0"
                            />
                            {{ cat.name }}
                        </button>
                        <div
                            v-if="props.expandedRoot === cat.id"
                            class="ml-4 border-l border-gray-100 dark:border-gray-700"
                        >
                            <button
                                v-for="child in cat.children"
                                :key="child.id"
                                @click="emit('selectCategory', child.id)"
                                class="flex w-full items-center gap-2 py-2 pl-4 pr-4 text-left text-sm transition hover:bg-gray-50 dark:hover:bg-gray-800"
                                :class="{
                                    'font-bold text-primary-500':
                                        props.filterCategory ===
                                        String(child.id),
                                }"
                            >
                                {{ child.name }}
                            </button>
                            <button
                                @click="emit('selectCategory', cat.id)"
                                class="flex w-full items-center gap-2 py-2 pl-4 pr-4 text-left text-sm font-semibold text-primary-500 transition hover:bg-primary-50 dark:hover:bg-gray-800"
                            >
                                Ver todo en {{ cat.name }}
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
        <button
            @click="emit('openImport')"
            class="flex items-center gap-2 rounded-2xl border border-gray-200 bg-white px-4 py-2 text-sm font-bold text-content-secondary shadow-sm transition-colors hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800"
        >
            <Upload class="h-4 w-4" /> Importar Excel
        </button>
        <button
            @click="emit('openEmptyStockForm')"
            class="flex items-center gap-2 rounded-2xl bg-emerald-600 px-4 py-2 text-sm font-bold text-white shadow-sm transition-colors hover:bg-emerald-700"
        >
            <Package class="h-4 w-4" /> Ingresar Mercancía
        </button>
        <button
            @click="emit('openCategoryModal')"
            class="flex items-center gap-2 rounded-xl bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 transition-all hover:bg-gray-200 active:scale-95"
        >
            <Tag class="h-4 w-4" /> Categoría
        </button>
        <button
            @click="emit('openNew')"
            class="flex items-center gap-2 rounded-2xl bg-primary-500 px-4 py-2 text-sm font-bold text-white shadow-sm transition-colors hover:bg-primary-600"
        >
            <Plus class="h-4 w-4" /> Nuevo Producto
        </button>
    </div>
</template>
