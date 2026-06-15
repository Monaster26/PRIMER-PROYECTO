<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import { ChevronDown, ChevronRight, X, LayoutGrid,
    Package, CupSoda, Beef, Candy, Snowflake, Home, Milk,
    Sparkles, Dog, Baby, Croissant, Flower2, Cookie, Cigarette, Cpu
} from 'lucide-vue-next';
import { useCategoryStore } from '../../Stores/categoryStore';

const categoryStore = useCategoryStore();

// ─── Desktop Mega Menu ────────────────────────────────────────────
const isMegaMenuOpen = ref(false);
const hoveredCategoryId = ref<string | null>(null);
const megaMenuRef = ref<HTMLElement | null>(null);
let closeTimeout: ReturnType<typeof setTimeout> | null = null;

function openMegaMenu(id?: string) {
    if (closeTimeout) clearTimeout(closeTimeout);
    isMegaMenuOpen.value = true;
    if (id) hoveredCategoryId.value = id;
    else if (!hoveredCategoryId.value && categoryStore.categories.length > 0) {
        hoveredCategoryId.value = categoryStore.categories[0].id;
    }
}

function startCloseTimeout() {
    closeTimeout = setTimeout(() => {
        isMegaMenuOpen.value = false;
    }, 300);
}

const hoveredCategory = () =>
    categoryStore.categories.find(c => c.id === hoveredCategoryId.value)
    ?? categoryStore.categories[0];

// Close when clicking outside
function handleClickOutside(e: MouseEvent) {
    if (megaMenuRef.value && !megaMenuRef.value.contains(e.target as Node)) {
        isMegaMenuOpen.value = false;
    }
}
onMounted(() => document.addEventListener('mousedown', handleClickOutside));
onUnmounted(() => document.removeEventListener('mousedown', handleClickOutside));

// ─── Mobile Sidebar ───────────────────────────────────────────────
const isMobileMenuOpen = ref(false);
const expandedMobileId = ref<string | null>(null);

function toggleMobileAccordion(id: string) {
    expandedMobileId.value = expandedMobileId.value === id ? null : id;
}

// ─── Icon map ─────────────────────────────────────────────────────
const iconMap: Record<string, any> = {
    Package, CupSoda, Beef, Candy, Snowflake, Home, Milk,
    Sparkles, Dog, Baby, Croissant, Flower2, Cookie, Cigarette, Cpu,
};

function getIcon(name: string) {
    return iconMap[name] ?? Package;
}
</script>

<template>
    <!-- ═══════════════════════════════════════════════════════════
         DESKTOP MEGA MENU
    ════════════════════════════════════════════════════════════════ -->
    <div ref="megaMenuRef" class="hidden lg:block relative" @mouseleave="startCloseTimeout" @mouseenter="openMegaMenu()">

        <!-- Trigger Button -->
        <button
            @mouseenter="openMegaMenu()"
            class="flex items-center gap-2 px-4 py-2 rounded-full text-sm font-bold text-white bg-primary-500 hover:bg-primary-600 transition-all duration-200 shadow-primary-sm"
        >
            <LayoutGrid class="w-4 h-4" />
            Categorías
            <ChevronDown class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': isMegaMenuOpen }" />
        </button>

        <!-- Invisible Bridge (Protects the gap between button and menu) -->
        <div v-if="isMegaMenuOpen" class="absolute h-4 w-full top-full left-0 z-40 bg-transparent"></div>

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
                class="absolute left-0 mt-3 w-[760px] bg-white dark:bg-surface-dark rounded-3xl shadow-2xl border border-gray-100 dark:border-gray-800 overflow-hidden z-50 flex"
                style="top: 100%"
            >
                <!-- Left: Category List -->
                <div class="w-56 bg-gradient-to-b from-secondary-50 to-white dark:from-gray-900 dark:to-gray-900 border-r border-gray-100 dark:border-gray-800 py-3 flex-shrink-0">
                    <p class="px-4 py-2 text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-500">Departamentos</p>
                    <button
                        v-for="cat in categoryStore.categories"
                        :key="cat.id"
                        @mouseenter="openMegaMenu(cat.id)"
                        @click="hoveredCategoryId = cat.id"
                        class="w-full flex items-center gap-3 px-4 py-2.5 text-sm font-semibold transition-all duration-150 group"
                        :class="hoveredCategoryId === cat.id
                            ? 'bg-primary-500 text-white'
                            : 'text-content-primary dark:text-content-inverted hover:bg-primary-50 dark:hover:bg-gray-800 hover:text-primary-500'"
                    >
                        <span class="text-base leading-none">{{ cat.emoji }}</span>
                        <span class="truncate">{{ cat.name }}</span>
                        <ChevronRight class="w-4 h-4 ml-auto opacity-60" />
                    </button>
                </div>

                <!-- Right: Subcategories Grid -->
                <div class="flex-1 p-6 overflow-y-auto max-h-[480px]">
                    <template v-if="hoveredCategory()">
                        <div class="flex items-center gap-3 mb-5">
                            <span class="text-3xl">{{ hoveredCategory()!.emoji }}</span>
                            <h3 class="text-lg font-display font-extrabold text-primary-500">
                                {{ hoveredCategory()!.name }}
                            </h3>
                        </div>

                        <div class="grid grid-cols-2 gap-x-6 gap-y-1">
                            <Link
                                v-for="sub in hoveredCategory()!.subcategories"
                                :key="sub"
                                :href="`/catalog?category=${hoveredCategory()!.id}&sub=${encodeURIComponent(sub)}`"
                                @click="isMegaMenuOpen = false"
                                class="flex items-center gap-2 py-2 px-3 rounded-xl text-sm text-content-secondary dark:text-gray-400 hover:bg-accent-100 hover:text-content-primary dark:hover:bg-accent-900/30 dark:hover:text-accent-300 transition-all duration-150 group"
                            >
                                <span class="w-1.5 h-1.5 rounded-full bg-secondary-300 group-hover:bg-accent-500 flex-shrink-0 transition-colors"></span>
                                {{ sub }}
                            </Link>
                        </div>

                        <!-- Footer CTA -->
                        <div class="mt-6 pt-4 border-t border-gray-100 dark:border-gray-800">
                            <Link
                                :href="`/catalog?category=${hoveredCategory()!.id}`"
                                @click="isMegaMenuOpen = false"
                                class="inline-flex items-center gap-2 text-sm font-bold text-primary-500 hover:text-primary-600 transition-colors"
                            >
                                Ver todo en {{ hoveredCategory()!.name }}
                                <ChevronRight class="w-4 h-4" />
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
        class="lg:hidden flex items-center gap-2 px-3 py-2 rounded-full text-sm font-bold text-white bg-primary-500 hover:bg-primary-600 transition-all"
    >
        <LayoutGrid class="w-4 h-4" />
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
                class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[60]"
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
                class="fixed inset-y-0 left-0 w-[320px] max-w-full bg-white dark:bg-surface-dark shadow-2xl z-[70] flex flex-col overflow-hidden"
            >
                <!-- Sidebar Header -->
                <div class="flex items-center justify-between px-5 py-4 bg-gradient-to-r from-primary-500 to-secondary-400 text-white flex-shrink-0">
                    <div class="flex items-center gap-2">
                        <LayoutGrid class="w-5 h-5" />
                        <span class="font-display font-bold text-lg">Categorías</span>
                    </div>
                    <button @click="isMobileMenuOpen = false" class="p-1.5 rounded-full hover:bg-white/20 transition-colors">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <!-- Accordion List -->
                <div class="flex-1 overflow-y-auto">
                    <div
                        v-for="cat in categoryStore.categories"
                        :key="cat.id"
                        class="border-b border-gray-100 dark:border-gray-800"
                    >
                        <!-- Category Header -->
                        <button
                            @click="toggleMobileAccordion(cat.id)"
                            class="w-full flex items-center gap-3 px-5 py-4 text-left transition-colors hover:bg-primary-50 dark:hover:bg-gray-800"
                            :class="expandedMobileId === cat.id ? 'bg-primary-50 dark:bg-gray-800' : ''"
                        >
                            <span class="text-xl">{{ cat.emoji }}</span>
                            <span class="flex-1 text-sm font-bold text-content-primary dark:text-content-inverted" :class="expandedMobileId === cat.id ? 'text-primary-500' : ''">
                                {{ cat.name }}
                            </span>
                            <ChevronDown
                                class="w-4 h-4 text-content-muted transition-transform duration-200"
                                :class="expandedMobileId === cat.id ? 'rotate-180 text-primary-500' : ''"
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
                            <div v-if="expandedMobileId === cat.id" class="pb-2 bg-gray-50 dark:bg-gray-900">
                                <Link
                                    v-for="sub in cat.subcategories"
                                    :key="sub"
                                    :href="`/catalog?category=${cat.id}&sub=${encodeURIComponent(sub)}`"
                                    @click="isMobileMenuOpen = false"
                                    class="flex items-center gap-3 px-7 py-2.5 text-sm text-content-secondary dark:text-gray-400 hover:text-primary-500 hover:bg-accent-50 dark:hover:bg-accent-900/20 transition-colors"
                                >
                                    <span class="w-1.5 h-1.5 rounded-full bg-secondary-300 flex-shrink-0"></span>
                                    {{ sub }}
                                </Link>
                                <Link
                                    :href="`/catalog?category=${cat.id}`"
                                    @click="isMobileMenuOpen = false"
                                    class="flex items-center gap-2 px-7 py-3 mt-1 text-sm font-bold text-primary-500 hover:text-primary-600 transition-colors"
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
