import { defineStore } from 'pinia';
import { ref } from 'vue';

export interface Category {
    id: string;
    name: string;
    emoji: string;
    icon: string;
    subcategories: string[];
}

export const categoriesData: Category[] = [
    {
        id: 'abarrotes',
        name: 'ABARROTES',
        emoji: '🥫',
        icon: 'Package',
        subcategories: [
            'Aceite y Vinagres',
            'Alimento para Niños',
            'Arroz',
            'Azúcar y Endulzantes',
            'Café, Té e Infusiones',
            'Enlatados y Granos',
            'Esencias y Condimentos',
            'Fideos e Instantáneos',
            'Harinas y Sal',
            'Postres, Cremas y Untables',
            'Saborizantes y Leche en Polvo',
            'Salsas y Mayonesas',
        ],
    },
    {
        id: 'bebidas',
        name: 'BEBIDAS',
        emoji: '🥤',
        icon: 'CupSoda',
        subcategories: [
            'Aguas y Aguas Saborizadas',
            'Bebidas en Polvo',
            'Bebidas Saborizadas',
            'Deportivas',
            'Energéticas',
            'Gaseosas',
            'Té y Néctar',
            'Café (Juan Valdez, Marley, Nescafé)',
        ],
    },
    {
        id: 'cecinas',
        name: 'CECINAS',
        emoji: '🥩',
        icon: 'Beef',
        subcategories: ['Cecina por Kilo', 'Embutidos', 'Envasados'],
    },
    {
        id: 'confites',
        name: 'CONFITES',
        emoji: '🍬',
        icon: 'Candy',
        subcategories: [
            'Cereales, Avenas y Granolas',
            'Chicles y Caramelos',
            'Chocolates',
            'Galletas',
            'Golosinas',
            'Gomitas',
        ],
    },
    {
        id: 'congelados',
        name: 'CONGELADOS',
        emoji: '❄️',
        icon: 'Snowflake',
        subcategories: [
            'Frutas y Verduras',
            'Hamburguesas y Churrascos',
            'Helados',
            'Pollo',
        ],
    },
    {
        id: 'hogar',
        name: 'HOGAR',
        emoji: '🏠',
        icon: 'Home',
        subcategories: [
            'Bolsas de Basura',
            'Bolsas de Regalo',
            'Cocina',
            'Desechables',
            'Encendedores',
            'Pilas',
            'Útiles',
            'Utilitarios',
            'Velas',
        ],
    },
    {
        id: 'lacteos',
        name: 'LÁCTEOS',
        emoji: '🥛',
        icon: 'Milk',
        subcategories: ['Leches', 'Mantequillas', 'Quesos'],
    },
    {
        id: 'limpieza',
        name: 'LIMPIEZA',
        emoji: '🧼',
        icon: 'Sparkles',
        subcategories: [
            'Antigrasas, Baño, Vidrios',
            'Aromatizantes',
            'Cloros y Gel',
            'Insecticidas',
            'Lavado',
            'Lavalozas',
            'Limpiadores Crema',
            'Limpia Pisos',
            'Papel Higiénico',
            'Toallas y Servilletas',
            'Utensilios',
        ],
    },
    {
        id: 'mascotas',
        name: 'MASCOTAS',
        emoji: '🐶',
        icon: 'Dog',
        subcategories: ['Gatos', 'Perros'],
    },
    {
        id: 'mundo-bebe',
        name: 'MUNDO BEBÉ',
        emoji: '👶',
        icon: 'Baby',
        subcategories: ['Pañales', 'Toallitas'],
    },
    {
        id: 'panaderia',
        name: 'PANADERÍA',
        emoji: '🍞',
        icon: 'Croissant',
        subcategories: ['Panes', 'Queques', 'Tortillas y Wrap'],
    },
    {
        id: 'perfumeria',
        name: 'PERFUMERÍA',
        emoji: '💄',
        icon: 'Flower2',
        subcategories: [
            'Accesorios',
            'Afeitado',
            'Algodones y Alcohol',
            'Cosméticos',
            'Cremas Corporales',
            'Desodorantes',
            'Higiene Bucal',
            'Jabón de Baño',
            'Pañuelos y Toallas Desmaquillantes',
            'Perfumes',
            'Shampoo y Acondicionador',
            'Talco y Zapatos',
            'Tinturas',
            'Toallas y Protectores',
            'Tratamientos Capilares',
        ],
    },
    {
        id: 'snacks',
        name: 'SNACKS',
        emoji: '🍟',
        icon: 'Cookie',
        subcategories: [
            'Aceitunas',
            'Cabritas y Ramitas',
            'Chicharrón y Tocino',
            'De Todito',
            'Maní y Frutos Secos',
            'Nachos y Tortillas',
            'Papas',
            'Platanitos',
            'Takis',
        ],
    },
    {
        id: 'tabaqueria',
        name: 'TABAQUERÍA',
        emoji: '🚬',
        icon: 'Cigarette',
        subcategories: ['Cigarros', 'Papelillo', 'Tabacos', 'Vaporizadores'],
    },
    {
        id: 'tecnologia',
        name: 'TECNOLOGÍA',
        emoji: '🔌',
        icon: 'Cpu',
        subcategories: ['Audífonos', 'Cargadores', 'Chip Telefónico'],
    },
];

export const useCategoryStore = defineStore('category', () => {
    const categories = ref<Category[]>(categoriesData);
    const activeCategory = ref<string | null>(null);
    const selectedSubcategory = ref<string | null>(null);

    function setActiveCategory(id: string | null) {
        activeCategory.value = id;
    }

    function selectSubcategory(name: string) {
        selectedSubcategory.value = name;
    }

    function getCategoryById(id: string): Category | undefined {
        return categories.value.find((c) => c.id === id);
    }

    const activeSubcategories = () => {
        if (!activeCategory.value) return [];
        return getCategoryById(activeCategory.value)?.subcategories ?? [];
    };

    return {
        categories,
        activeCategory,
        selectedSubcategory,
        setActiveCategory,
        selectSubcategory,
        getCategoryById,
        activeSubcategories,
    };
});
