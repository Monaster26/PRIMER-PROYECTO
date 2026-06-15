import { defineStore } from 'pinia';
import { ref } from 'vue';
import axios from 'axios';
import { useCartStore } from './cartStore';

interface Employee {
    id: number;
    name: string;
    role: string;
}

interface LoginResponse {
    success: boolean;
    token: string;
    employee: {
        id: number;
        name: string;
        role: string;
        role_name: string;
    };
}

interface ProductLookupResponse {
    found: boolean;
    product: {
        id: number;
        name: string;
        price: number;
        image_path: string | null;
        sku: string;
        effective_stock: number;
        is_low_stock: boolean;
    };
}

interface CheckoutResponse {
    success: boolean;
    order_number: string;
    total: number;
    change_due: number | null;
}

export const usePosStore = defineStore('pos', () => {
    const cartStore = useCartStore();

    const cashierId = ref<string | null>(null);
    const employeeName = ref<string | null>(null);
    const barcodeInput = ref('');
    const posModeActive = ref(false);
    const sessionToken = ref<string | null>(null);
    const error = ref<string | null>(null);

    function getEmployeeId(): number {
        return cashierId.value ? parseInt(cashierId.value.replace('CASHIER-', ''), 10) : 0;
    }

    function getAuthHeaders() {
        return {
            'X-POS-Employee': String(getEmployeeId()),
            'X-POS-Token': sessionToken.value || '',
        };
    }

    async function login(pin: string) {
        error.value = null;
        try {
            const { data: employees } = await axios.get<Employee[]>('/api/pos/employees');
            if (!employees.length) {
                error.value = 'No hay empleados activos';
                return false;
            }
            for (const employee of employees) {
                try {
                    const { data } = await axios.post<LoginResponse>('/api/pos/login', {
                        employee_id: employee.id,
                        pin,
                    });
                    if (data.success) {
                        sessionToken.value = data.token;
                        cashierId.value = 'CASHIER-' + data.employee.id;
                        employeeName.value = data.employee.name;
                        posModeActive.value = true;
                        return true;
                    }
                } catch {
                    // Try next employee
                }
            }
            error.value = 'PIN incorrecto';
            return false;
        } catch {
            error.value = 'Error de conexión con el servidor';
            return false;
        }
    }

    async function logout() {
        const eid = getEmployeeId();
        if (sessionToken.value && eid) {
            try {
                await axios.post('/api/pos/logout',
                    { employee_id: eid, session_token: sessionToken.value },
                    { headers: getAuthHeaders() },
                );
            } catch {
                // Silently fail on logout errors
            }
        }
        sessionToken.value = null;
        cashierId.value = null;
        employeeName.value = null;
        posModeActive.value = false;
        error.value = null;
    }

    async function processBarcode() {
        const barcode = barcodeInput.value;
        if (!barcode) return;
        barcodeInput.value = '';
        error.value = null;

        try {
            const { data } = await axios.get<ProductLookupResponse>(
                '/api/pos/product/lookup',
                { params: { q: barcode }, headers: getAuthHeaders() },
            );
            if (data.found && data.product) {
                cartStore.addToCart({
                    id: data.product.id,
                    name: data.product.name,
                    price: data.product.price,
                    image: data.product.image_path || undefined,
                    sku: data.product.sku,
                    stock: data.product.effective_stock,
                }, 1);
            }
        } catch (err: any) {
            error.value = err.response?.data?.message || 'Producto no encontrado';
        }
    }

    async function checkout() {
        error.value = null;
        const eid = getEmployeeId();
        if (!sessionToken.value || !eid) {
            error.value = 'Sesión POS no iniciada';
            return;
        }
        if (!cartStore.items.length) {
            error.value = 'El carrito está vacío';
            return;
        }

        const items = cartStore.items.map(item => ({
            product_id: item.product.id,
            quantity: item.quantity,
        }));

        try {
            const { data } = await axios.post<CheckoutResponse>(
                '/api/pos/checkout',
                {
                    employee_id: eid,
                    session_token: sessionToken.value,
                    items,
                    payment_method: 'cash',
                },
                { headers: getAuthHeaders() },
            );
            if (data.success) {
                cartStore.clearCart();
            }
        } catch (err: any) {
            error.value = err.response?.data?.message || 'Error al procesar la venta';
        }
    }

    return {
        cashierId,
        employeeName,
        barcodeInput,
        posModeActive,
        error,
        login,
        logout,
        processBarcode,
        checkout,
    };
});
