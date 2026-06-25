<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    Baby,
    Beef,
    Candy,
    ChevronDown,
    ChevronRight,
    Cigarette,
    Cookie,
    Cpu,
    Croissant,
    CupSoda,
    Dog,
    Flower2,
    Home,
    LayoutGrid,
    Milk,
    Package,
    Snowflake,
    Sparkles,
    X,
} from 'lucide-vue-next';
import { onMounted, onUnmounted, ref, watch } from 'vue';
import { useCategoryStore, type Category } from '../../Stores/categoryStore';

const categoryStore = useCategoryStore();

// ─── Desktop Mega Menu ────────────────────────────────────────────
const isMegaMenuOpen = ref(false);
const activeCategory = ref<Category | null>(null);
const megaMenuRef = ref<HTMLElement | null>(null);
let closeTimeout: ReturnType<typeof setTimeout> | null = null;

watch(
    () => categoryStore.categories,
    (cats) => {
        if (!activeCategory.value && cats.length > 0) {
            activeCategory.value = cats[0];
        }
    },
    { immediate: true },
);

function openMegaMenu() {
    if (closeTimeout) clearTimeout(closeTimeout);
    isMegaMenuOpen.value = true;
}

function startCloseTimeout() {
    closeTimeout = setTimeout(() => {
        isMegaMenuOpen.value = false;
    }, 300);
}

// Close when clicking outside
function handleClickOutside(e: MouseEvent) {
    if (megaMenuRef.value && !megaMenuRef.value.contains(e.target as Node)) {
        isMegaMenuOpen.value = false;
    }
}
onMounted(async () => {
    await categoryStore.fetchCategories();
    document.addEventListener('mousedown', handleClickOutside);
});
onUnmounted(() =>
    document.removeEventListener('mousedown', handleClickOutside),
);

// ─── Mobile Sidebar ───────────────────────────────────────────────
const isMobileMenuOpen = ref(false);
const expandedMobileId = ref<number | null>(null);

function toggleMobileAccordion(id: number) {
    expandedMobileId.value = expandedMobileId.value === id ? null : id;
}

// ─── Icon map ─────────────────────────────────────────────────────
const iconMap: Record<string, any> = {
    Package,
    CupSoda,
    Beef,
    Candy,
    Snowflake,
    Home,
    Milk,
    Sparkles,
    Dog,
    Baby,
    Croissant,
    Flower2,
    Cookie,
    Cigarette,
    Cpu,
};

function getIcon(name: string) {
    return iconMap[name] ?? Package;
}
</script>

<template>
    <!-- ═══════════════════════════════════════════════════════════
         DESKTOP MEGA MENU
    ════════════════════════════════════════════════════════════════ -->
    <div
        ref="megaMenuRef"
        class="relative hidden lg:block"
        @mouseleave="startCloseTimeout"
        @mouseenter="openMegaMenu()"
    >
        <!-- Trigger Button -->
        <button
            @mouseenter="openMegaMenu()"
            class="flex items-center gap-2 rounded-full bg-primary-500 px-4 py-2 text-sm font-bold text-white shadow-primary-sm transition-all duration-200 hover:bg-primary-600"
        >
            <LayoutGrid class="h-4 w-4" />
            Categorías
            <ChevronDown
                class="h-4 w-4 transition-transform duration-200"
                :class="{ 'rotate-180': isMegaMenuOpen }"
            />
        </button>

        <!-- Invisible Bridge (Protects the gap between button and menu) -->
        <div
            v-if="isMegaMenuOpen"
            class="absolute left-0 top-full z-40 h-4 w-full bg-transparent"
        ></div>

        <!-- Mega Menu Panel -->
        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0 translate-y-4"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 translate-y-2"
        >
            <div
                v-if="isMegaMenuOpen"
                @mouseenter="openMegaMenu()"
                class="absolute left-0 z-50 mt-3 flex w-[760px] overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-2xl dark:border-gray-800 dark:bg-surface-dark"
                style="top: 100%"
            >
                <!-- Left: Category List -->
                <div
                    class="w-56 flex-shrink-0 border-r border-gray-100 bg-gradient-to-b from-secondary-50 to-white py-3 dark:border-gray-800 dark:from-gray-900 dark:to-gray-900"
                >
                    <p
                        class="px-4 py-2 text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-500"
                    >
                        Departamentos
                    </p>
                    <button
                        v-for="parent in categoryStore.categories.filter(c => !c.parent_id)"
                        :key="parent.id"
                        @mouseenter="activeCategory = parent"
                        class="group flex w-full items-center gap-3 px-4 py-2.5 text-sm font-semibold transition-all duration-150"
                        :class="
                            activeCategory?.id === parent.id
                                ? 'bg-primary-500 text-white'
                                : 'text-content-primary hover:bg-primary-50 hover:text-primary-500 dark:text-content-inverted dark:hover:bg-gray-800'
                        "
                    >
                        <span class="text-base leading-none">{{ parent.icon }}</span>
                        <span class="truncate">{{ parent.name }}</span>
                        <ChevronRight class="ml-auto h-4 w-4 opacity-60" />
                    </button>
                </div>

                <!-- Right: Subcategories Grid -->
                <div class="max-h-[480px] flex-1 overflow-y-auto p-6">
                    <template v-if="activeCategory">
                        <div class="mb-5 flex items-center gap-3">
                            <span class="text-3xl">{{ activeCategory.icon }}</span>
                            <h3
                                class="font-display text-lg font-extrabold text-primary-500"
                            >
                                {{ activeCategory.name }}
                            </h3>
                        </div>

                        <div class="grid grid-cols-2 gap-x-6 gap-y-1">
                            <Link
                                v-for="sub in activeCategory.children"
                                :key="sub.id"
                                :href="`/categoria/${activeCategory.slug}/${sub.slug}`"
                                @click="isMegaMenuOpen = false"
                                class="group flex items-center gap-2 rounded-xl px-3 py-2 text-sm text-content-secondary transition-all duration-150 hover:bg-accent-100 hover:text-content-primary dark:text-gray-400 dark:hover:bg-accent-900/30 dark:hover:text-accent-300"
                            >
                                <span
                                    class="h-1.5 w-1.5 flex-shrink-0 rounded-full bg-secondary-300 transition-colors group-hover:bg-accent-500"
                                ></span>
                                {{ sub.name }}
                            </Link>
                        </div>

                        <!-- Footer CTA -->
                        <div
                            class="mt-6 border-t border-gray-100 pt-4 dark:border-gray-800"
                        >
                            <Link
                                :href="`/categoria/${activeCategory.slug}`"
                                @click="isMegaMenuOpen = false"
                                class="inline-flex items-center gap-2 text-sm font-bold text-primary-500 transition-colors hover:text-primary-600"
                            >
                                Ver todo en {{ activeCategory.name }}
                                <ChevronRight class="h-4 w-4" />
                            </Link>
                        </div>
                    </template>
                </div>
            </div>
        </Transition>
    </div>

    <!-- ═══════════════════════════════════════════════════════════
         MOBILE: Hamburger Trigger (shown from Navbar)
    ════════════════════════════════════════════════════════════════ -->
    <button
        @click="isMobileMenuOpen = true"
        class="flex items-center gap-2 rounded-full bg-primary-500 px-3 py-2 text-sm font-bold text-white transition-all hover:bg-primary-600 lg:hidden"
    >
        <LayoutGrid class="h-4 w-4" />
        <span class="hidden sm:inline">Categorías</span>
    </button>

    <!-- ═══════════════════════════════════════════════════════════
         MOBILE SIDEBAR OVERLAY
    ════════════════════════════════════════════════════════════════ -->
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
                v-if="isMobileMenuOpen"
                @click="isMobileMenuOpen = false"
                class="fixed inset-0 z-[60] bg-black/50 backdrop-blur-sm"
            />
        </Transition>

        <!-- Slide-over Panel -->
        <Transition
            enter-active-class="transition ease-in-out duration-300 transform"
            enter-from-class="-translate-x-full"
            enter-to-class="translate-x-0"
            leave-active-class="transition ease-in-out duration-300 transform"
            leave-from-class="translate-x-0"
            leave-to-class="-translate-x-full"
        >
            <div
                v-if="isMobileMenuOpen"
                class="fixed inset-y-0 left-0 z-[70] flex w-[320px] max-w-full flex-col overflow-hidden bg-white shadow-2xl dark:bg-surface-dark"
            >
                <!-- Sidebar Header -->
                <div
                    class="flex flex-shrink-0 items-center justify-between bg-gradient-to-r from-primary-500 to-secondary-400 px-5 py-4 text-white"
                >
                    <div class="flex items-center gap-2">
                        <LayoutGrid class="h-5 w-5" />
                        <span class="font-display text-lg font-bold"
                            >Categorías</span
                        >
                    </div>
                    <button
                        @click="isMobileMenuOpen = false"
                        class="rounded-full p-1.5 transition-colors hover:bg-white/20"
                    >
                        <X class="h-5 w-5" />
                    </button>
                </div>

                <!-- Accordion List -->
                <div class="flex-1 overflow-y-auto">
                    <div
                        v-for="cat in categoryStore.categories.filter(c => !c.parent_id)"
                        :key="cat.id"
                        class="border-b border-gray-100 dark:border-gray-800"
                    >
                        <!-- Category Header -->
                        <button
                            @click="toggleMobileAccordion(cat.id)"
                            class="flex w-full items-center gap-3 px-5 py-4 text-left transition-colors hover:bg-primary-50 dark:hover:bg-gray-800"
                            :class="
                                expandedMobileId === cat.id
                                    ? 'bg-primary-50 dark:bg-gray-800'
                                    : ''
                            "
                        >
                            <span class="text-xl">{{ cat.icon }}</span>
                            <span
                                class="flex-1 text-sm font-bold text-content-primary dark:text-content-inverted"
                                :class="
                                    expandedMobileId === cat.id
                                        ? 'text-primary-500'
                                        : ''
                                "
                            >
                                {{ cat.name }}
                            </span>
                            <ChevronDown
                                class="h-4 w-4 text-content-muted transition-transform duration-200"
                                :class="
                                    expandedMobileId === cat.id
                                        ? 'rotate-180 text-primary-500'
                                        : ''
                                "
                            />
                        </button>

                        <!-- Subcategories Accordion -->
                        <Transition
                            enter-active-class="transition-all ease-out duration-200 overflow-hidden"
                            enter-from-class="max-h-0 opacity-0"
                            enter-to-class="max-h-[500px] opacity-100"
                            leave-active-class="transition-all ease-in duration-150 overflow-hidden"
                            leave-from-class="max-h-[500px] opacity-100"
                            leave-to-class="max-h-0 opacity-0"
                        >
                            <div
                                v-if="expandedMobileId === cat.id"
                                class="bg-gray-50 pb-2 dark:bg-gray-900"
                            >
                                <Link
                                    v-for="sub in cat.children"
                                    :key="sub.id"
                                    :href="`/categoria/${cat.slug}/${sub.slug}`"
                                    @click="isMobileMenuOpen = false"
                                    class="flex items-center gap-3 px-7 py-2.5 text-sm text-content-secondary transition-colors hover:bg-accent-50 hover:text-primary-500 dark:text-gray-400 dark:hover:bg-accent-900/20"
                                >
                                    <span
                                        class="h-1.5 w-1.5 flex-shrink-0 rounded-full bg-secondary-300"
                                    ></span>
                                    {{ sub.name }}
                                </Link>
                                <Link
                                    :href="`/categoria/${cat.slug}`"
                                    @click="isMobileMenuOpen = false"
                                    class="mt-1 flex items-center gap-2 px-7 py-3 text-sm font-bold text-primary-500 transition-colors hover:text-primary-600"
                                >
                                    Ver todo →
                                </Link>
                            </div>
                        </Transition>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
