import { computed, nextTick, reactive, ref } from 'vue';

export interface UltimaSesion {
    cierre_desglose: Record<string, number> | null;
    total_efectivo_cierre: number;
    cerrado_por: string;
    cerrado_at: string;
}

type Denomination = {
    key: string;
    label: string;
    value: number;
    directInput: boolean;
};

const denominations: readonly Denomination[] = [
    { key: '20k', label: '$20.000', value: 20000, directInput: false },
    { key: '10k', label: '$10.000', value: 10000, directInput: false },
    { key: '5k', label: '$5.000', value: 5000, directInput: false },
    { key: '2k', label: '$2.000', value: 2000, directInput: false },
    { key: '1k', label: '$1.000', value: 1000, directInput: false },
    { key: '500', label: '$500', value: 500, directInput: true },
    { key: '100', label: '$100', value: 100, directInput: true },
    { key: '50', label: '$50', value: 50, directInput: true },
    { key: '10', label: '$10', value: 10, directInput: true },
] as const;

export function useSession(
    csrfToken: () => string,
    hasOpenSession: boolean,
    ultimaSesion: { value: UltimaSesion | null },
) {
    const storedOpen = localStorage.getItem('pos_session_opened');
    if (storedOpen === 'true' && !hasOpenSession) {
        localStorage.removeItem('pos_session_opened');
    }
    const sessionOpened = ref(
        localStorage.getItem('pos_session_opened') === 'true' || hasOpenSession,
    );
    const sessionOpening = ref(false);
    const sessionOpenError = ref('');

    const bills = computed(() => denominations.filter((d) => !d.directInput));
    const coins = computed(() => denominations.filter((d) => d.directInput));

    const billQtys = reactive<Record<string, number | null>>({
        '20k': null,
        '10k': null,
        '5k': null,
        '2k': null,
        '1k': null,
    });
    const coinAmounts = reactive<Record<string, number | null>>({
        '500': null,
        '100': null,
        '50': null,
        '10': null,
    });
    const coinErrors = reactive<Record<string, string | null>>({});

    const showDiscrepancyModal = ref(false);
    const discrepancyData = ref<{
        requiere_justificacion: boolean;
        diferencia: number;
        ultimo_cierre_monto: number;
        ultimo_cierre_desglose: Record<string, number> | null;
        nuevo_apertura_monto: number;
    } | null>(null);
    const discrepancyReason = ref('');

    const desgloseAnterior = computed(
        () =>
            discrepancyData.value?.ultimo_cierre_desglose ??
            ultimaSesion.value?.cierre_desglose ??
            null,
    );

    const totalOpening = computed(() => {
        let t = 0;
        for (const d of denominations) {
            if (d.directInput) {
                t += Number(coinAmounts[d.key] || 0);
            } else {
                t += (Number(billQtys[d.key]) || 0) * d.value;
            }
        }
        return t;
    });

    const hasCoinErrors = computed(() =>
        Object.values(coinErrors).some((v) => v !== null),
    );

    const fmt = (v: number | undefined | null) =>
        v != null
            ? '$' + v.toLocaleString('es-CO', { minimumFractionDigits: 0 })
            : '$0';

    function formatCoin(val: number | null): string {
        if (val === null || val === undefined) return '';
        return val.toLocaleString('es-CL');
    }

    function onCoinInput(e: Event, key: string) {
        coinErrors[key] = null;
        const raw = (e.target as HTMLInputElement).value.replace(/\D/g, '');
        const num = raw ? parseInt(raw, 10) : null;
        coinAmounts[key] = num;
        (e.target as HTMLInputElement).value = num
            ? num.toLocaleString('es-CL')
            : '';
    }

    function validateCoin(key: string) {
        const d = denominations.find((c) => c.key === key);
        if (!d) return;
        const val = coinAmounts[key];
        if (val !== null && val > 0 && val % d.value !== 0) {
            coinErrors[key] = `Debe ser múltiplo de ${d.label}`;
        } else {
            coinErrors[key] = null;
        }
    }

    function focusNext(e: Event) {
        const form = (e.target as HTMLElement).closest('form');
        if (!form) return;
        const inputs = Array.from(
            form.querySelectorAll<HTMLInputElement>(
                'input[type="number"], input[inputmode="numeric"]',
            ),
        );
        const idx = inputs.indexOf(e.target as HTMLInputElement);
        if (idx >= 0 && idx < inputs.length - 1) inputs[idx + 1].focus();
    }

    function submitOpenSession(focusScanner: () => void) {
        sessionOpening.value = true;
        sessionOpenError.value = '';
        const route_ = (window as any).route;

        const body: Record<string, any> = {};
        for (const d of denominations) {
            if (d.directInput) {
                const amount = Number(coinAmounts[d.key]) || 0;
                body[`cant_${d.key}_apertura`] = Math.round(amount / d.value);
            } else {
                body[`cant_${d.key}_apertura`] = Number(billQtys[d.key]) || 0;
            }
        }

        fetch(route_('admin.pos.open-session'), {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
            },
            body: JSON.stringify(body),
        })
            .then((res) => {
                if (!res.ok)
                    return res.json().then((err) => {
                        if (err.requiere_justificacion) {
                            discrepancyData.value = err;
                            showDiscrepancyModal.value = true;
                            throw new Error();
                        }
                        throw new Error(err.message || 'Error al abrir caja');
                    });
                return res.json();
            })
            .then(() => {
                sessionOpened.value = true;
                localStorage.setItem('pos_session_opened', 'true');
                nextTick(() => focusScanner());
            })
            .catch((err) => {
                if (err.message) sessionOpenError.value = err.message;
            })
            .finally(() => {
                sessionOpening.value = false;
            });
    }

    function confirmOpenWithDiscrepancy(focusScanner: () => void) {
        sessionOpening.value = true;
        const route_ = (window as any).route;
        const body: Record<string, any> = {};
        for (const d of denominations) {
            if (d.directInput) {
                body[`cant_${d.key}_apertura`] = Math.round(
                    (Number(coinAmounts[d.key]) || 0) / d.value,
                );
            } else {
                body[`cant_${d.key}_apertura`] = Number(billQtys[d.key]) || 0;
            }
        }
        body.observacion = discrepancyReason.value;

        fetch(route_('admin.pos.open-session'), {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
            },
            body: JSON.stringify(body),
        })
            .then((res) => {
                if (!res.ok)
                    return res.json().then((err) => {
                        throw new Error(err.message || 'Error al abrir caja');
                    });
                return res.json();
            })
            .then(() => {
                showDiscrepancyModal.value = false;
                sessionOpened.value = true;
                localStorage.setItem('pos_session_opened', 'true');
                nextTick(() => focusScanner());
            })
            .catch((err) => {
                sessionOpenError.value = err.message;
            })
            .finally(() => {
                sessionOpening.value = false;
            });
    }

    return {
        sessionOpened,
        sessionOpening,
        sessionOpenError,
        denominations,
        bills,
        coins,
        billQtys,
        coinAmounts,
        coinErrors,
        desgloseAnterior,
        totalOpening,
        hasCoinErrors,
        showDiscrepancyModal,
        discrepancyData,
        discrepancyReason,
        fmt,
        formatCoin,
        onCoinInput,
        validateCoin,
        focusNext,
        submitOpenSession,
        confirmOpenWithDiscrepancy,
    };
}
