import { defineStore } from 'pinia';
import { computed, ref } from 'vue';

export interface Product {
    id: number;
    name: string;
    price: number;
    image?: string;
    images?: string[];
    badge?: string;
    rating?: number;
    reviews?: number;
    category?: string;
    description?: string;
    stock?: number;
    sku?: string;
}

export interface CartItem {
    product: Product;
    quantity: number;
}

export const useCartStore = defineStore('cart', () => {
    const items = ref<CartItem[]>([]);

    // In a real app, you'd load from localStorage or API here

    const cartCount = computed(() =>
        items.value.reduce((total, item) => total + item.quantity, 0),
    );

    const subtotal = computed(() =>
        items.value.reduce(
            (total, item) => total + item.product.price * item.quantity,
            0,
        ),
    );

    function addToCart(product: Product, quantity = 1) {
        const existingItem = items.value.find(
            (item) => item.product.id === product.id,
        );
        if (existingItem) {
            existingItem.quantity += quantity;
        } else {
            items.value.push({ product, quantity });
        }
    }

    function removeFromCart(productId: number) {
        items.value = items.value.filter(
            (item) => item.product.id !== productId,
        );
    }

    function updateQuantity(productId: number, quantity: number) {
        const item = items.value.find((item) => item.product.id === productId);
        if (item) {
            item.quantity = quantity;
            if (item.quantity <= 0) {
                removeFromCart(productId);
            }
        }
    }

    function clearCart() {
        items.value = [];
    }

    return {
        items,
        cartCount,
        subtotal,
        addToCart,
        removeFromCart,
        updateQuantity,
        clearCart,
    };
});
