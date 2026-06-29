<script setup lang="ts">
import { onMounted } from 'vue';

const props = defineProps<{
    sale: {
        id: number;
        folio: number;
        total: number;
        net_total: number;
        tax_total: number;
        discount_total: number;
        items: {
            name: string;
            quantity: number;
            price: number;
            total: number;
        }[];
        payments: { method: string; amount: number }[];
        cash_amount: number;
        card_amount: number;
        transfer_amount: number;
        cashier_name: string;
        created_at: string;
    };
}>();

const subtotalNeto = Math.round(props.sale.net_total);
const iva = Math.round(props.sale.tax_total);

function fmtPesos(cents: number): string {
    return (
        '$' +
        Math.round(cents / 100).toLocaleString('es-CL', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        })
    );
}

function paymentLabel(sale: typeof props.sale): string {
    const methods: string[] = [];
    if (sale.cash_amount > 0) methods.push('Efectivo');
    if (sale.card_amount > 0) methods.push('Tarjeta');
    if (sale.transfer_amount > 0) methods.push('Transferencia');
    return methods.join(' + ') || '—';
}

function formatDate(iso: string): string {
    const d = new Date(iso);
    return d.toLocaleDateString('es-CL', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        hour12: false,
    });
}

onMounted(() => {
    setTimeout(() => window.print(), 500);
});
</script>

<template>
    <div id="thermal-ticket" class="mx-auto w-[80mm] bg-white p-4 text-[10px]">
        <div class="text-center">
            <img
                src="/images/logo.png"
                alt="Monasterio Market"
                class="mx-auto mb-2 h-12 w-auto object-contain"
            />
            <p class="text-xs font-bold uppercase tracking-wider">
                Monasterios Market Spa
            </p>
            <p class="mt-0.5">RUT: 76.367.537-0</p>
            <p class="mt-0.5 leading-tight">
                Venta al por menor de alimentos, accesorios de teléfono y ventas
                por internet
            </p>
            <p>Código SII: 472101</p>
            <p class="mt-0.5">
                Santiago, Av. Manuel Antonio Matta 833, Local 7
            </p>
        </div>

        <div class="my-3 border-t border-dashed border-gray-400"></div>

        <div class="space-y-0.5">
            <p class="flex justify-between">
                <span>Folio:</span>
                <span class="font-bold">#{{ sale.folio }}</span>
            </p>
            <p class="flex justify-between">
                <span>Fecha:</span>
                <span>{{ formatDate(sale.created_at) }}</span>
            </p>
            <p class="flex justify-between">
                <span>Cajero:</span>
                <span>{{ sale.cashier_name }}</span>
            </p>
        </div>

        <div class="my-3 border-t border-dashed border-gray-400"></div>

        <table class="w-full">
            <thead>
                <tr>
                    <th class="pb-1 text-left font-bold">Cant</th>
                    <th class="pb-1 text-left font-bold">Descripción</th>
                    <th class="pb-1 text-right font-bold">Importe</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(item, i) in sale.items" :key="i" class="align-top">
                    <td class="py-0.5 font-mono tabular-nums">
                        {{ item.quantity }}
                    </td>
                    <td class="py-0.5">{{ item.name }}</td>
                    <td class="py-0.5 text-right font-mono tabular-nums">
                        {{ fmtPesos(item.total) }}
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="my-3 border-t border-dashed border-gray-400"></div>

        <div class="space-y-1">
            <div class="flex justify-between">
                <span>Subtotal Neto</span>
                <span class="font-mono">{{ fmtPesos(subtotalNeto) }}</span>
            </div>
            <div
                v-if="sale.discount_total > 0"
                class="flex justify-between text-red-600"
            >
                <span>Descuento</span>
                <span class="font-mono"
                    >-{{ fmtPesos(sale.discount_total) }}</span
                >
            </div>
            <div class="flex justify-between">
                <span>IVA (19%)</span>
                <span class="font-mono">{{ fmtPesos(iva) }}</span>
            </div>
            <div class="flex justify-between font-bold">
                <span>Total</span>
                <span class="font-mono text-base">{{
                    fmtPesos(sale.total)
                }}</span>
            </div>
            <div class="flex justify-between">
                <span>Método de Pago</span>
                <span class="font-mono">{{ paymentLabel(sale) }}</span>
            </div>
        </div>
    </div>
</template>

<style>
@media print {
    @page {
        margin: 0;
    }
    body {
        margin: 0;
        padding: 0;
    }
    #thermal-ticket {
        width: 80mm;
        padding: 4mm;
    }
}
</style>
