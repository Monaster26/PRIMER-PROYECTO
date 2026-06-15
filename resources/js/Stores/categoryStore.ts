import { defineStore } from 'pinia';
import { ref } from 'vue';
import axios from 'axios';

export interface Category {
    id: string;
    name: string;
    emoji: string;
    icon: string;
    subcategories: string[];
    active?: boolean;
}

export const useCategoryStore = defineStore('category', () => {
    const categories = ref<Category[]>([]);
    const activeCategory = ref<string | null>(null);
    const selectedSubcategory = ref<string | null>(null);

    function setActiveCategory(id: string | null) {
        activeCategory.value = id;
    }

    function selectSubcategory(name: string) {
        selectedSubcategory.value = name;
    }

    function getCategoryById(id: string): Category | undefined {
        return categories.value.find(c => c.id === id);
    }

    const activeSubcategories = () => {
        if (!activeCategory.value) return [];
        return getCategoryById(activeCategory.value)?.subcategories ?? [];
    };

    async function fetchCategories() {
        try {
            const response = await axios.get<Category[]>('/api/categories');
            categories.value = response.data;
            if (categories.value.length > 0 && !activeCategory.value) {
                activeCategory.value = categories.value[0].id;
            }
        } catch (error) {
            console.error('Error fetching categories:', error);
        }
    }

    // Load categories immediately upon initialization
    fetchCategories();

    return {
        categories,
        activeCategory,
        selectedSubcategory,
        setActiveCategory,
        selectSubcategory,
        getCategoryById,
        activeSubcategories,
        fetchCategories,
    };
});
