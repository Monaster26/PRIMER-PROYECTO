import { defineStore } from 'pinia';
import { ref } from 'vue';

export interface Category {
    id: number;
    name: string;
    slug: string;
    icon?: string | null;
    parent_id: number | null;
    children: Category[];
}

export const useCategoryStore = defineStore('category', () => {
    const categories = ref<Category[]>([]);
    const isLoading = ref(false);
    const activeCategory = ref<number | null>(null);
    const selectedSubcategory = ref<string | null>(null);

    async function fetchCategories() {
        if (categories.value.length > 0) return;
        isLoading.value = true;
        try {
            const { data } = await window.axios.get('/public-categorias');
            categories.value = data;
        } finally {
            isLoading.value = false;
        }
    }

    function setActiveCategory(id: number | null) {
        activeCategory.value = id;
    }

    function selectSubcategory(name: string) {
        selectedSubcategory.value = name;
    }

    function getCategoryById(id: number): Category | undefined {
        return categories.value.find((c) => c.id === id);
    }

    const activeSubcategories = () => {
        if (!activeCategory.value) return [];
        return getCategoryById(activeCategory.value)?.children ?? [];
    };

    return {
        categories,
        isLoading,
        activeCategory,
        selectedSubcategory,
        fetchCategories,
        setActiveCategory,
        selectSubcategory,
        getCategoryById,
        activeSubcategories,
    };
});
