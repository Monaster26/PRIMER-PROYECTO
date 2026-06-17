import { defineStore } from 'pinia';
import { computed, ref, watch } from 'vue';

const STORAGE_KEY = 'pos_tabs';

function saveTabs(tabs: TabState[], activeIndex: number) {
    try {
        localStorage.setItem(
            STORAGE_KEY,
            JSON.stringify({ tabs, activeIndex }),
        );
    } catch (e) {
        if (e instanceof DOMException && e.name === 'QuotaExceededError') {
            console.warn('localStorage llena, no se pudo guardar el estado del POS');
        }
    }
}

function loadTabs(): { tabs: TabState[]; activeIndex: number } | null {
    try {
        const raw = localStorage.getItem(STORAGE_KEY);
        if (!raw) return null;
        return JSON.parse(raw);
    } catch {
        return null;
    }
}

export interface Product {
    id: number;
    name: string;
    sku: string;
    barcode: string | null;
    price: number;
    stock: number;
    unit: string;
    image_path: string | null;
}

export interface CartItem {
    product: Product;
    quantity: number;
}

export interface PaymentEntry {
    method: 'cash' | 'card' | 'transfer';
    amount: number | null;
}

export interface TabState {
    id: string;
    name: string;
    cart: CartItem[];
    payments: PaymentEntry[];
}

function defaultPayments(): PaymentEntry[] {
    return [
        { method: 'cash', amount: null },
        { method: 'card', amount: null },
        { method: 'transfer', amount: null },
    ];
}

export const usePosTabsStore = defineStore('posTabs', () => {
    const saved = loadTabs();
    const tabs = ref<TabState[]>(
        saved?.tabs?.length
            ? saved.tabs
            : [
                  {
                      id: '1',
                      name: 'Venta 1',
                      cart: [],
                      payments: defaultPayments(),
                  },
              ],
    );
    const activeIndex = ref(
        saved ? Math.min(saved.activeIndex, tabs.value.length - 1) : 0,
    );
    let nextId =
        saved && saved.tabs.length
            ? Math.max(...saved.tabs.map((t) => Number(t.id))) + 1
            : 2;

    const activeTab = computed(() => tabs.value[activeIndex.value]);

    // Persistir automaticamente cada mutacion
    watch(
        [tabs, activeIndex],
        () => {
            saveTabs(tabs.value, activeIndex.value);
        },
        { deep: true },
    );

    function addTab() {
        const id = String(nextId++);
        tabs.value.push({
            id,
            name: `Venta ${nextId - 1}`,
            cart: [],
            payments: defaultPayments(),
        });
        activeIndex.value = tabs.value.length - 1;
    }

    function removeTab(index: number) {
        if (tabs.value.length <= 1) return;
        tabs.value.splice(index, 1);
        if (activeIndex.value >= tabs.value.length) {
            activeIndex.value = tabs.value.length - 1;
        }
    }

    function switchTab(index: number) {
        if (index >= 0 && index < tabs.value.length) {
            activeIndex.value = index;
        }
    }

    return {
        tabs,
        activeIndex,
        activeTab,
        addTab,
        removeTab,
        switchTab,
    };
});
