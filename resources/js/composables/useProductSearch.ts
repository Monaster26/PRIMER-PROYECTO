import { ref, type Ref } from 'vue';

interface ProductSearchState {
    results: Ref<any[]>;
    skuResults: Ref<any[]>;
    query: Ref<string>;
    skuQuery: Ref<string>;
    searchName: (q: string) => void;
    searchSku: (q: string) => void;
    selectProduct: (product: any) => void;
    onBlur: () => void;
    onFocus: () => void;
    clearAll: () => void;
    open: Ref<boolean>;
}

export function useProductSearch(
    onSelect: (product: any) => void,
): ProductSearchState {
    const results = ref<any[]>([]);
    const skuResults = ref<any[]>([]);
    const query = ref('');
    const skuQuery = ref('');
    const open = ref(false);

    let nameTimer: ReturnType<typeof setTimeout> | null = null;
    let skuTimer: ReturnType<typeof setTimeout> | null = null;
    let blurTimer: ReturnType<typeof setTimeout> | null = null;

    function searchName(q: string) {
        if (nameTimer) clearTimeout(nameTimer);
        if (!q.trim()) {
            results.value = [];
            return;
        }
        nameTimer = setTimeout(async () => {
            try {
                const { data } = await window.axios.get(
                    window.route('admin.codigos.search-name'),
                    { params: { query: q } },
                );
                results.value = data;
                if (data.length) open.value = true;
            } catch {
                results.value = [];
            }
        }, 300);
    }

    function searchSku(q: string) {
        if (skuTimer) clearTimeout(skuTimer);
        if (!q.trim()) {
            skuResults.value = [];
            return;
        }
        skuTimer = setTimeout(async () => {
            try {
                const { data } = await window.axios.get(
                    window.route('admin.codigos.search-sku'),
                    { params: { query: q } },
                );
                skuResults.value = data ? [data] : [];
                if (data) open.value = true;
            } catch {
                skuResults.value = [];
            }
        }, 300);
    }

    function selectProduct(product: any) {
        onSelect(product);
        open.value = false;
        results.value = [];
        skuResults.value = [];
        query.value = '';
        skuQuery.value = '';
    }

    function onBlur() {
        blurTimer = setTimeout(() => {
            open.value = false;
        }, 200);
    }

    function onFocus() {
        if (blurTimer) {
            clearTimeout(blurTimer);
            blurTimer = null;
        }
    }

    function clearAll() {
        results.value = [];
        skuResults.value = [];
        open.value = false;
        query.value = '';
        skuQuery.value = '';
    }

    return {
        results,
        skuResults,
        query,
        skuQuery,
        searchName,
        searchSku,
        selectProduct,
        onBlur,
        onFocus,
        clearAll,
        open,
    };
}
