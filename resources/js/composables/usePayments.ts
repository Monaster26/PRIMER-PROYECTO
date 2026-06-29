import type { TabState } from '@/Stores/posTabsStore';
import { computed, ref, watch, type ComputedRef } from 'vue';

type BalanceState = 'exacto' | 'faltante' | 'exceso';

export function usePayments(activeTab: ComputedRef<TabState>) {
    const couponCode = ref('');
    const couponError = ref('');
    const checkoutLoading = ref(false);
    const lastSaleId = ref<number | null>(null);
    const lastDiscount = ref(0);
    const lastAppliedPromotions = ref<string[]>([]);
    const showSuccess = ref(false);

    const promoDiscount = ref(0); // centavos, desde backend
    const previewPromosLoading = ref(false);
    let promoDebounceTimer: ReturnType<typeof setTimeout> | null = null;

    watch(
        () => activeTab.value.cart,
        (cart) => {
            if (promoDebounceTimer) clearTimeout(promoDebounceTimer);
            if (cart.length === 0) {
                promoDiscount.value = 0;
                return;
            }
            previewPromosLoading.value = true;
            promoDebounceTimer = setTimeout(async () => {
                try {
                    const route = (window as any).route;
                    const token =
                        document.querySelector<HTMLMetaElement>(
                            'meta[name="csrf-token"]',
                        )?.content || '';
                    const res = await fetch(route('admin.pos.preview-promos'), {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token,
                        },
                        body: JSON.stringify({
                            items: cart.map((i) => ({
                                product_id: i.product.id,
                                quantity: i.quantity,
                            })),
                        }),
                    });
                    if (res.ok) {
                        const data = await res.json();
                        promoDiscount.value = data.discount || 0;
                    }
                } catch {
                    promoDiscount.value = 0;
                } finally {
                    previewPromosLoading.value = false;
                }
            }, 400);
        },
        { deep: true, immediate: true },
    );

    const rawTotal = computed(() =>
        activeTab.value.cart.reduce(
            (sum, item) => sum + (item.product.price / 100) * item.quantity,
            0,
        ),
    );

    const total = computed(() =>
        Math.max(0, rawTotal.value - promoDiscount.value / 100),
    );

    const totalPayments = computed(() =>
        activeTab.value.payments.reduce(
            (sum, p) => sum + (Number(p.amount) || 0),
            0,
        ),
    );

    const remaining = computed(() => total.value - totalPayments.value);
    const isBalanced = computed(
        () => Math.abs(remaining.value) < 0.01 && total.value > 0,
    );

    const balanceState = computed<BalanceState | null>(() => {
        if (total.value <= 0) return null;
        if (isBalanced.value) return 'exacto';
        if (remaining.value > 0) return 'faltante';
        return 'exceso';
    });

    const balanceClasses = computed(() => {
        switch (balanceState.value) {
            case 'exacto':
                return 'bg-success/10 text-emerald-700 font-bold';
            case 'faltante':
                return 'bg-red-50 border border-red-200 text-red-700 font-medium';
            case 'exceso':
                return 'bg-blue-50 border border-blue-200 text-blue-800 font-bold';
            default:
                return '';
        }
    });

    const canCheckout = computed(
        () =>
            !checkoutLoading.value &&
            activeTab.value.cart.length > 0 &&
            remaining.value <= 0,
    );

    function onPaymentFocus(method: string) {
        const currentPayment = activeTab.value.payments.find(
            (p) => p.method === method,
        );
        const currentAmount = Number(currentPayment?.amount) || 0;

        const others = activeTab.value.payments.filter(
            (p) => p.method !== method,
        );
        const otherSum = others.reduce(
            (sum, p) => sum + (Number(p.amount) || 0),
            0,
        );
        const nonZeroOthers = others.filter((p) => Number(p.amount) > 0);

        if (
            otherSum >= total.value &&
            currentAmount === 0 &&
            nonZeroOthers.length === 1
        ) {
            others.forEach((p) => (p.amount = null));
        }

        const freshOthers = activeTab.value.payments.filter(
            (p) => p.method !== method,
        );
        const freshOtherSum = freshOthers.reduce(
            (sum, p) => sum + (Number(p.amount) || 0),
            0,
        );
        const saldo = Math.max(0, total.value - freshOtherSum);

        if (currentPayment) {
            currentPayment.amount = saldo > 0 ? Math.round(saldo) : null;
        }
    }

    function resetCheckoutState() {
        lastSaleId.value = null;
        lastDiscount.value = 0;
        lastAppliedPromotions.value = [];
        showSuccess.value = false;
        checkoutLoading.value = false;
    }

    async function finalizeSale(csrfToken?: string): Promise<boolean> {
        if (activeTab.value.cart.length === 0) {
            alert('Agrega productos al carrito.');
            return false;
        }
        if (remaining.value > 0) {
            alert(
                'Los pagos no cubren el total. Faltan: $' +
                    remaining.value.toFixed(0),
            );
            return false;
        }

        checkoutLoading.value = true;

        const payload = {
            items: activeTab.value.cart.map((item) => ({
                product_id: item.product.id,
                quantity: item.quantity,
            })),
            payments: activeTab.value.payments.map((p) => ({
                method: p.method,
                amount: Number(p.amount),
            })),
            coupon_code: couponCode.value || undefined,
        };

        try {
            const route = (window as any).route;
            const res = await fetch(route('admin.pos.checkout'), {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || '',
                },
                body: JSON.stringify(payload),
            });

            if (!res.ok) {
                const err = await res.json();
                throw new Error(err.error || 'Error al procesar venta');
            }

            const data = await res.json();
            lastSaleId.value = data.sale_id;
            lastDiscount.value = data.discount_total || 0;
            lastAppliedPromotions.value = data.applied_promotions || [];
            showSuccess.value = true;
            return true;
        } catch (err: any) {
            if (err.message) {
                if (couponCode.value && err.message.includes('Cupón')) {
                    couponError.value = err.message;
                } else {
                    alert(err.message);
                }
            }
            return false;
        } finally {
            checkoutLoading.value = false;
        }
    }

    return {
        couponCode,
        couponError,
        checkoutLoading,
        lastSaleId,
        lastDiscount,
        lastAppliedPromotions,
        showSuccess,
        rawTotal,
        promoDiscount,
        previewPromosLoading,
        total,
        totalPayments,
        remaining,
        isBalanced,
        balanceState,
        balanceClasses,
        canCheckout,
        onPaymentFocus,
        finalizeSale,
        resetCheckoutState,
    };
}
