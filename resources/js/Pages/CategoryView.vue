<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import {
    ChevronDown,
    ChevronRight,
    LayoutGrid,
    Package,
    Search,
    ShoppingCart,
    SlidersHorizontal,
    X,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { useCartStore } from '../Stores/cartStore';
import { useCategoryStore } from '../Stores/categoryStore';

// ─── Tipos ────────────────────────────────────────────────────────────────────
interface SubCategory {
    id: number;
    name: string;
    slug: string;
}
interface RootCategory {
    id: number;
    name: string;
    slug: string;
    icon?: string | null;
    children: SubCategory[];
}
interface ProductItem {
    id: number;
    name: string;
    slug: string;
    sku: string;
    price: number; // en centavos
    stock: number;
    is_low_stock: boolean;
    image_url: string | null;
    category: { id: number; name: string } | null;
    description: string | null;
    brand: string | null;
    unit: string;
}
interface PaginatedProducts {
    data: ProductItem[];
    current_page: number;
    last_page: number;
    total: number;
    per_page: number;
    prev_page_url: string | null;
    next_page_url: string | null;
    links: { url: string | null; label: string; active: boolean }[];
}

// ─── Props de Inertia ─────────────────────────────────────────────────────────
const props = defineProps<{
    rootCategory: RootCategory;
    activeSubcategory: SubCategory | null;
    products: PaginatedProducts;
    allCategories: RootCategory[];
    filters: { q?: string; sort?: string; dir?: string };
}>();

// ─── Stores ───────────────────────────────────────────────────────────────────
const cartStore = useCartStore();
const catStore = useCategoryStore();

// Asegurar que el store tenga categorías cargadas
catStore.fetchCategories();

// ─── Estado local ─────────────────────────────────────────────────────────────
const searchQuery = ref(props.filters.q ?? '');
const isSidebarOpen = ref(false); // para móvil
const expandedCatId = ref<number | null>(props.rootCategory.id); // acordeón abierto por defecto
const addedProductId = ref<number | null>(null); // feedback visual de "añadido"
const sortValue = ref(props.filters.sort ?? 'name');
const sortDir = ref(props.filters.dir ?? 'asc');

// ─── Breadcrumb ───────────────────────────────────────────────────────────────
const breadcrumb = computed(() => {
    const base = [
        { label: 'Inicio', href: '/' },
        {
            label: props.rootCategory.name,
            href: `/categoria/${props.rootCategory.slug}`,
        },
    ];
    if (props.activeSubcategory) {
        base.push({
            label: props.activeSubcategory.name,
            href: `/categoria/${props.rootCategory.slug}/${props.activeSubcategory.slug}`,
        });
    }
    return base;
});

const pageTitle = computed(() =>
    props.activeSubcategory
        ? props.activeSubcategory.name
        : props.rootCategory.name,
);

// ─── Precio formateado ────────────────────────────────────────────────────────
function formatPrice(cents: number): string {
    return new Intl.NumberFormat('es-CO', {
        style: 'currency',
        currency: 'COP',
        minimumFractionDigits: 0,
    }).format(cents / 100);
}

// ─── Navegación de categorías (sidebar) ──────────────────────────────────────
function toggleAccordion(id: number) {
    expandedCatId.value = expandedCatId.value === id ? null : id;
}

function isRootActive(cat: RootCategory) {
    return cat.id === props.rootCategory.id;
}

function isSubActive(sub: SubCategory) {
    return props.activeSubcategory?.id === sub.id;
}

// ─── Añadir al carrito ────────────────────────────────────────────────────────
function addToCart(product: ProductItem) {
    cartStore.addToCart({
        id: product.id,
        name: product.name,
        price: product.price / 100,
        image: product.image_url ?? undefined,
        sku: product.sku,
        stock: product.stock,
        description: product.description ?? undefined,
    });
    addedProductId.value = product.id;
    setTimeout(() => {
        addedProductId.value = null;
    }, 1500);
}

// ─── Filtros / búsqueda ───────────────────────────────────────────────────────
let searchTimer: ReturnType<typeof setTimeout>;

watch(searchQuery, (val) => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => applyFilters({ q: val }), 450);
});

function applyFilters(extra: Record<string, string | undefined> = {}) {
    const base = props.activeSubcategory
        ? `/categoria/${props.rootCategory.slug}/${props.activeSubcategory.slug}`
        : `/categoria/${props.rootCategory.slug}`;
    router.get(
        base,
        {
            q: searchQuery.value || undefined,
            sort: sortValue.value !== 'name' ? sortValue.value : undefined,
            dir: sortDir.value !== 'asc' ? sortDir.value : undefined,
            ...extra,
        },
        { preserveState: true, replace: true },
    );
}

function onSortChange() {
    applyFilters();
}
</script>

<template>
    <AppLayout>
        <Head :title="pageTitle" />

        <!-- ══════════════════════════════════════════════════════════
             HERO / BREADCRUMB BAR
        ══════════════════════════════════════════════════════════════ -->
        <div class="px-4 py-5 text-white" style="background-color: #ffb2d2">
            <div class="mx-auto max-w-7xl">
                <!-- Breadcrumb -->
                <nav
                    class="mb-2 flex items-center gap-1.5 text-xs text-white/70"
                >
                    <template v-for="(crumb, i) in breadcrumb" :key="i">
                        <Link
                            :href="crumb.href"
                            class="transition-colors hover:text-white"
                        >
                            {{ crumb.label }}
                        </Link>
                        <ChevronRight
                            v-if="i < breadcrumb.length - 1"
                            class="h-3 w-3 opacity-60"
                        />
                    </template>
                </nav>

                <div class="flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <span v-if="rootCategory.icon" class="text-3xl">{{
                            rootCategory.icon
                        }}</span>
                        <div>
                            <h1
                                class="font-display text-2xl font-extrabold tracking-tight"
                            >
                                {{ pageTitle }}
                            </h1>
                            <p class="text-sm text-white/70">
                                {{ products.total }} producto{{
                                    products.total !== 1 ? 's' : ''
                                }}
                                encontrado{{ products.total !== 1 ? 's' : '' }}
                            </p>
                        </div>
                    </div>

                    <!-- Botón de filtros en móvil -->
                    <button
                        @click="isSidebarOpen = true"
                        class="flex items-center gap-2 rounded-2xl bg-white/20 px-4 py-2 text-sm font-bold backdrop-blur-sm transition-colors hover:bg-white/30 lg:hidden"
                    >
                        <SlidersHorizontal class="h-4 w-4" />
                        Categorías
                    </button>
                </div>
            </div>
        </div>

        <!-- ══════════════════════════════════════════════════════════
             LAYOUT PRINCIPAL (split-screen)
        ══════════════════════════════════════════════════════════════ -->
        <div class="mx-auto flex max-w-7xl gap-0 px-0 py-6 lg:gap-8 lg:px-4">
            <!-- ─────────────────────────────────────────────────────
                 SIDEBAR IZQUIERDO — ESCRITORIO (25%)
            ───────────────────────────────────────────────────────── -->
            <aside class="hidden w-64 flex-shrink-0 lg:block">
                <div
                    class="sticky top-24 rounded-2xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-surface-dark"
                >
                    <p
                        class="mb-3 text-xs font-bold uppercase tracking-widest text-content-muted dark:text-gray-500"
                    >
                        Departamentos
                    </p>

                    <div
                        v-for="cat in allCategories"
                        :key="cat.id"
                        class="mb-1"
                    >
                        <!-- Categoría raíz -->
                        <button
                            @click="toggleAccordion(cat.id)"
                            class="flex w-full items-center gap-2.5 rounded-xl px-3 py-2.5 text-left text-sm font-semibold transition-all duration-150"
                            :class="
                                isRootActive(cat)
                                    ? 'bg-primary-500 text-white shadow-sm'
                                    : 'text-content-primary hover:bg-primary-50 hover:text-primary-600 dark:text-content-inverted dark:hover:bg-gray-800'
                            "
                        >
                            <span
                                v-if="cat.icon"
                                class="text-base leading-none"
                                >{{ cat.icon }}</span
                            >
                            <span class="flex-1 truncate">{{ cat.name }}</span>
                            <ChevronDown
                                class="h-4 w-4 flex-shrink-0 transition-transform duration-200"
                                :class="
                                    expandedCatId === cat.id ? 'rotate-180' : ''
                                "
                            />
                        </button>

                        <!-- Acordeón de subcategorías -->
                        <Transition
                            enter-active-class="transition-all ease-out duration-200 overflow-hidden"
                            enter-from-class="max-h-0 opacity-0"
                            enter-to-class="max-h-96 opacity-100"
                            leave-active-class="transition-all ease-in duration-150 overflow-hidden"
                            leave-from-class="max-h-96 opacity-100"
                            leave-to-class="max-h-0 opacity-0"
                        >
                            <div
                                v-if="
                                    expandedCatId === cat.id &&
                                    cat.children?.length
                                "
                                class="ml-3 mt-1 border-l-2 border-gray-100 pl-3 dark:border-gray-700"
                            >
                                <!-- "Ver todo" de la raíz -->
                                <Link
                                    :href="`/categoria/${cat.slug}`"
                                    class="flex items-center gap-2 rounded-lg px-2 py-2 text-xs font-bold transition-colors"
                                    :class="
                                        isRootActive(cat) && !activeSubcategory
                                            ? 'text-primary-500'
                                            : 'text-content-muted hover:text-primary-500 dark:text-gray-500'
                                    "
                                >
                                    <span
                                        class="h-1 w-1 rounded-full bg-current opacity-60"
                                    ></span>
                                    Ver todo
                                </Link>

                                <!-- Subcategorías -->
                                <Link
                                    v-for="sub in cat.children"
                                    :key="sub.id"
                                    :href="`/categoria/${cat.slug}/${sub.slug}`"
                                    class="flex items-center gap-2 rounded-lg px-2 py-2 text-sm transition-colors"
                                    :class="
                                        isSubActive(sub)
                                            ? 'font-bold text-primary-500'
                                            : 'text-content-secondary hover:text-primary-500 dark:text-gray-400'
                                    "
                                >
                                    <span
                                        class="h-1.5 w-1.5 flex-shrink-0 rounded-full transition-colors"
                                        :class="
                                            isSubActive(sub)
                                                ? 'bg-primary-500'
                                                : 'bg-gray-300 dark:bg-gray-600'
                                        "
                                    ></span>
                                    {{ sub.name }}
                                </Link>
                            </div>
                        </Transition>
                    </div>
                </div>
            </aside>

            <!-- ─────────────────────────────────────────────────────
                 VITRINA DE PRODUCTOS — DERECHA (75%)
            ───────────────────────────────────────────────────────── -->
            <section class="min-w-0 flex-1 px-4 lg:px-0">
                <!-- Barra de búsqueda + ordenamiento -->
                <div
                    class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div class="relative max-w-sm flex-1">
                        <Search
                            class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400"
                        />
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Buscar en esta sección..."
                            class="w-full rounded-2xl border border-gray-200 bg-gray-50 py-2.5 pl-10 pr-4 text-sm text-content-primary transition-colors focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                        />
                    </div>
                    <select
                        v-model="sortValue"
                        @change="onSortChange"
                        class="rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                    >
                        <option value="name">Nombre A–Z</option>
                        <option value="price">Precio menor</option>
                        <option value="created_at">Más recientes</option>
                    </select>
                </div>

                <!-- Estado vacío -->
                <div
                    v-if="products.data.length === 0"
                    class="flex flex-col items-center justify-center rounded-3xl border-2 border-dashed border-gray-200 py-20 text-center dark:border-gray-700"
                >
                    <Package
                        class="mb-4 h-12 w-12 text-gray-300 dark:text-gray-600"
                    />
                    <p
                        class="font-display text-lg font-bold text-content-primary dark:text-white"
                    >
                        Sin productos en esta sección
                    </p>
                    <p class="mt-1 text-sm text-content-muted">
                        Intenta con otra categoría o amplía tu búsqueda.
                    </p>
                    <Link
                        href="/"
                        class="mt-5 rounded-2xl bg-primary-500 px-6 py-2.5 text-sm font-bold text-white transition-colors hover:bg-primary-600"
                    >
                        Ir al inicio
                    </Link>
                </div>

                <!-- Grid de productos -->
                <div
                    v-else
                    class="grid grid-cols-2 gap-4 sm:grid-cols-3 xl:grid-cols-4"
                >
                    <article
                        v-for="product in products.data"
                        :key="product.id"
                        class="group relative flex flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-lg dark:border-gray-800 dark:bg-surface-dark"
                    >
                        <!-- Imagen -->
                        <div
                            class="relative aspect-square overflow-hidden bg-gray-50 dark:bg-gray-900"
                        >
                            <img
                                v-if="product.image_url"
                                :src="product.image_url"
                                :alt="product.name"
                                class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                                loading="lazy"
                            />
                            <div
                                v-else
                                class="flex h-full w-full items-center justify-center"
                            >
                                <Package
                                    class="h-12 w-12 text-gray-300 dark:text-gray-600"
                                />
                            </div>

                            <!-- Badge stock bajo -->
                            <span
                                v-if="product.is_low_stock"
                                class="absolute left-2 top-2 rounded-full bg-red-100 px-2 py-0.5 text-xs font-bold text-red-600 dark:bg-red-900/40 dark:text-red-400"
                            >
                                Últimas unidades
                            </span>
                        </div>

                        <!-- Info -->
                        <div class="flex flex-1 flex-col gap-2 p-3">
                            <p
                                class="line-clamp-2 text-sm font-semibold leading-tight text-content-primary dark:text-white"
                            >
                                {{ product.name }}
                            </p>
                            <p
                                v-if="product.brand"
                                class="text-xs text-content-muted dark:text-gray-500"
                            >
                                {{ product.brand }}
                            </p>

                            <div
                                class="mt-auto flex items-center justify-between gap-2"
                            >
                                <span
                                    class="font-display text-base font-extrabold text-primary-500"
                                >
                                    {{ formatPrice(product.price) }}
                                </span>
                                <span
                                    class="text-xs text-content-muted dark:text-gray-500"
                                >
                                    /{{ product.unit }}
                                </span>
                            </div>

                            <!-- Botón carrito -->
                            <button
                                @click="addToCart(product)"
                                :disabled="product.stock <= 0"
                                class="flex w-full items-center justify-center gap-2 rounded-xl py-2 text-xs font-bold transition-all duration-200 active:scale-95"
                                :class="
                                    addedProductId === product.id
                                        ? 'bg-emerald-500 text-white'
                                        : product.stock <= 0
                                          ? 'cursor-not-allowed bg-gray-100 text-gray-400 dark:bg-gray-800'
                                          : 'bg-primary-500 text-white shadow-primary-sm hover:bg-primary-600'
                                "
                            >
                                <template v-if="addedProductId === product.id">
                                    <svg
                                        class="h-4 w-4"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2.5"
                                            d="M5 13l4 4L19 7"
                                        />
                                    </svg>
                                    ¡Añadido!
                                </template>
                                <template v-else-if="product.stock <= 0">
                                    Sin stock
                                </template>
                                <template v-else>
                                    <ShoppingCart class="h-3.5 w-3.5" />
                                    Añadir
                                </template>
                            </button>
                        </div>
                    </article>
                </div>

                <!-- Paginación -->
                <div
                    v-if="products.last_page > 1"
                    class="mt-8 flex flex-wrap items-center justify-center gap-2"
                >
                    <Link
                        v-for="link in products.links"
                        :key="link.label"
                        :href="link.url ?? ''"
                        :class="[
                            'rounded-xl px-3.5 py-2 text-sm font-semibold transition-colors',
                            link.active
                                ? 'bg-primary-500 text-white shadow-sm'
                                : link.url
                                  ? 'bg-white text-content-primary hover:bg-primary-50 hover:text-primary-500 dark:bg-gray-800 dark:text-white dark:hover:bg-gray-700'
                                  : 'cursor-not-allowed bg-gray-100 text-gray-300 dark:bg-gray-800 dark:text-gray-600',
                        ]"
                        :as="link.url ? 'a' : 'span'"
                        v-html="link.label"
                    />
                </div>
            </section>
        </div>

        <!-- ══════════════════════════════════════════════════════════
             SIDEBAR MÓVIL (drawer lateral)
        ══════════════════════════════════════════════════════════════ -->
        <Teleport to="body">
            <!-- Backdrop -->
            <Transition
                enter-active-class="transition-opacity ease-linear duration-300"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition-opacity ease-linear duration-300"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="isSidebarOpen"
                    @click="isSidebarOpen = false"
                    class="fixed inset-0 z-[60] bg-black/50 backdrop-blur-sm"
                />
            </Transition>

            <!-- Panel del drawer -->
            <Transition
                enter-active-class="transition ease-in-out duration-300 transform"
                enter-from-class="-translate-x-full"
                enter-to-class="translate-x-0"
                leave-active-class="transition ease-in-out duration-300 transform"
                leave-from-class="translate-x-0"
                leave-to-class="-translate-x-full"
            >
                <div
                    v-if="isSidebarOpen"
                    class="fixed inset-y-0 left-0 z-[70] flex w-[300px] max-w-full flex-col overflow-hidden bg-white shadow-2xl dark:bg-surface-dark"
                >
                    <!-- Cabecera del drawer -->
                    <div
                        class="flex items-center justify-between bg-gradient-to-r from-primary-500 to-secondary-400 px-5 py-4 text-white"
                    >
                        <div class="flex items-center gap-2">
                            <LayoutGrid class="h-5 w-5" />
                            <span class="font-display text-base font-bold"
                                >Categorías</span
                            >
                        </div>
                        <button
                            @click="isSidebarOpen = false"
                            class="rounded-full p-1.5 transition-colors hover:bg-white/20"
                        >
                            <X class="h-5 w-5" />
                        </button>
                    </div>

                    <!-- Lista de categorías -->
                    <div class="flex-1 overflow-y-auto p-4">
                        <div
                            v-for="cat in allCategories"
                            :key="cat.id"
                            class="mb-1"
                        >
                            <button
                                @click="toggleAccordion(cat.id)"
                                class="flex w-full items-center gap-3 rounded-xl px-3 py-3 text-left text-sm font-semibold transition-colors"
                                :class="
                                    isRootActive(cat)
                                        ? 'bg-primary-50 text-primary-600 dark:bg-primary-900/20'
                                        : 'text-content-primary hover:bg-gray-50 dark:text-white dark:hover:bg-gray-800'
                                "
                            >
                                <span v-if="cat.icon" class="text-lg">{{
                                    cat.icon
                                }}</span>
                                <span class="flex-1">{{ cat.name }}</span>
                                <ChevronDown
                                    class="h-4 w-4 transition-transform"
                                    :class="
                                        expandedCatId === cat.id
                                            ? 'rotate-180 text-primary-500'
                                            : ''
                                    "
                                />
                            </button>

                            <div
                                v-if="
                                    expandedCatId === cat.id &&
                                    cat.children?.length
                                "
                                class="ml-4 border-l-2 border-gray-100 pb-2 pl-3 dark:border-gray-700"
                            >
                                <Link
                                    :href="`/categoria/${cat.slug}`"
                                    @click="isSidebarOpen = false"
                                    class="block py-2 text-xs font-bold text-content-muted hover:text-primary-500"
                                    >Ver todo →</Link
                                >
                                <Link
                                    v-for="sub in cat.children"
                                    :key="sub.id"
                                    :href="`/categoria/${cat.slug}/${sub.slug}`"
                                    @click="isSidebarOpen = false"
                                    class="flex items-center gap-2 py-2 text-sm transition-colors"
                                    :class="
                                        isSubActive(sub)
                                            ? 'font-bold text-primary-500'
                                            : 'text-content-secondary hover:text-primary-500 dark:text-gray-400'
                                    "
                                >
                                    <span
                                        class="h-1.5 w-1.5 rounded-full bg-current opacity-60"
                                    ></span>
                                    {{ sub.name }}
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </AppLayout>
</template>
