import type { CartItem, Product } from '@/Stores/posTabsStore';
import { ref } from 'vue';

// ponytail: accepts any { value: CartItem[] } shape (Ref, ComputedRef, writable proxy)
export function useCart(cart: { value: CartItem[] }, products: Product[]) {
    const scannedProductName = ref<string | null>(null);
    const scannedProductIndex = ref<number | null>(null);

    function flashScanFeedback(productName: string, index: number) {
        scannedProductName.value = productName;
        scannedProductIndex.value = index;
        setTimeout(() => {
            scannedProductName.value = null;
            scannedProductIndex.value = null;
        }, 600);
    }

    function addToCart(product: Product) {
        const existing = cart.value.find((item) => item.product.id === product.id);
        if (existing) {
            existing.quantity++;
        } else {
            cart.value.push({ product, quantity: 1 });
        }
    }

    function incrementQty(index: number) {
        if (cart.value[index].quantity < cart.value[index].product.stock) {
            cart.value[index].quantity++;
        }
    }

    function decrementQty(index: number) {
        if (cart.value[index].quantity > 1) {
            cart.value[index].quantity--;
        }
    }

    function removeItem(index: number, csrfToken?: string) {
        const item = cart.value[index];
        if (item && csrfToken) {
            const route = (window as any).route;
            fetch(route('admin.pos.observacion'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    tipo_accion: 'eliminar_item',
                    producto_afectado: item.product.name,
                    detalle: `${item.product.name} (SKU: ${item.product.sku}) x${item.quantity} — $${(item.product.price * item.quantity).toLocaleString('es-CL')}`,
                }),
            }).catch(() => {});
        }
        cart.value.splice(index, 1);
    }

    function validateStock(): boolean {
        for (const item of cart.value) {
            if (item.quantity > item.product.stock) {
                alert(
                    `Stock insuficiente para "${item.product.name}".\nDisponible: ${item.product.stock} — Solicitaste: ${item.quantity}`,
                );
                return false;
            }
        }
        return true;
    }

    async function addProductByCode(code: string, onProductFound?: () => void) {
        const trimmed = code.trim();
        if (!trimmed) return;

        const cartIdx = cart.value.findIndex(
            (item) =>
                item.product.barcode === trimmed || item.product.sku === trimmed,
        );
        if (cartIdx !== -1) {
            cart.value[cartIdx].quantity++;
            flashScanFeedback(cart.value[cartIdx].product.name, cartIdx);
            onProductFound?.();
            return;
        }

        const product = products.find(
            (p) => p.barcode === trimmed || p.sku === trimmed,
        );
        if (product) {
            const existingIdx = cart.value.findIndex(
                (item) => item.product.id === product.id,
            );
            if (existingIdx !== -1) {
                cart.value[existingIdx].quantity++;
                flashScanFeedback(product.name, existingIdx);
            } else {
                cart.value.push({ product, quantity: 1 });
                flashScanFeedback(product.name, cart.value.length - 1);
            }
            onProductFound?.();
            return;
        }

        const nameMatch = products.find(
            (p) => p.name.toLowerCase() === trimmed.toLowerCase(),
        );
        if (nameMatch) {
            const existingIdx = cart.value.findIndex(
                (item) => item.product.id === nameMatch.id,
            );
            if (existingIdx !== -1) {
                cart.value[existingIdx].quantity++;
                flashScanFeedback(nameMatch.name, existingIdx);
            } else {
                cart.value.push({ product: nameMatch, quantity: 1 });
                flashScanFeedback(nameMatch.name, cart.value.length - 1);
            }
            onProductFound?.();
            return;
        }

        try {
            const route = (window as any).route;
            const res = await fetch(route('admin.pos.lookup', trimmed));
            if (!res.ok) throw new Error('No encontrado');
            const data = await res.json();
            const p = data.product as Product;
            const existingIdx = cart.value.findIndex(
                (item) => item.product.id === p.id,
            );
            if (existingIdx !== -1) {
                cart.value[existingIdx].quantity++;
                flashScanFeedback(p.name, existingIdx);
            } else {
                cart.value.push({ product: p, quantity: 1 });
                flashScanFeedback(p.name, cart.value.length - 1);
            }
            onProductFound?.();
        } catch {
            alert('❌ Producto no encontrado: ' + trimmed);
        }
    }

    return {
        scannedProductName,
        scannedProductIndex,
        addToCart,
        incrementQty,
        decrementQty,
        removeItem,
        validateStock,
        addProductByCode,
        flashScanFeedback,
    };
}
