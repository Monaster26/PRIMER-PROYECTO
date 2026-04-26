import { defineStore } from 'pinia';
import { ref } from 'vue';
import { useCartStore } from './cartStore';

export const usePosStore = defineStore('pos', () => {
    const cartStore = useCartStore();
    
    const cashierId = ref<string | null>(null);
    const barcodeInput = ref('');
    const posModeActive = ref(false);

    function login(pin: string) {
        // Mock authentication for POS
        if (pin.length === 4) {
            cashierId.value = 'CASHIER-' + pin;
            posModeActive.value = true;
            return true;
        }
        return false;
    }

    function processBarcode() {
        if (barcodeInput.value) {
            console.log('Processing barcode:', barcodeInput.value);
            // In a real app, you'd fetch the product by barcode
            // and add it to the cartStore
            barcodeInput.value = '';
        }
    }

    function checkout() {
        // Process the POS order
        console.log('Order processed by cashier:', cashierId.value);
        cartStore.clearCart();
    }

    return {
        cashierId,
        barcodeInput,
        posModeActive,
        login,
        processBarcode,
        checkout
    };
});
