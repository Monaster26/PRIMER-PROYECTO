<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Check, Download, LogOut } from 'lucide-vue-next';

const props = defineProps<{
    session: any;
    summary: any;
    pdf_url: string;
}>();

const fmt = (v: number) =>
    '$' + v.toLocaleString('es-CO', { minimumFractionDigits: 0 });

function formatDate(iso: string) {
    if (!iso) return '';
    const d = new Date(iso);
    return d.toLocaleDateString('es-CL', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

function downloadPdf() {
    window.open(props.pdf_url, '_blank');
}

function logout() {
    localStorage.removeItem('pos_session_opened');
    router.post(route('logout'));
}
</script>

<template>
    <div
        class="flex min-h-screen items-center justify-center bg-gray-100 px-4 dark:bg-gray-900"
    >
        <div
            class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl dark:bg-gray-800"
        >
            <!-- Success header -->
            <div
                class="rounded-xl bg-emerald-50 p-4 text-center dark:bg-emerald-900/20"
            >
                <Check class="mx-auto h-10 w-10 text-emerald-500" />
                <p
                    class="mt-2 text-base font-bold text-emerald-700 dark:text-emerald-300"
                >
                    Caja cerrada exitosamente
                </p>
                <p class="mt-1 text-xs text-content-muted">
                    Reporte Z{{ String(session.id).padStart(8, '0') }} —
                    {{ formatDate(session.closed_at) }}
                </p>
            </div>

            <!-- Summary -->
            <div class="mt-5 space-y-2 text-sm">
                <div
                    class="flex justify-between border-b border-gray-100 pb-1.5 dark:border-gray-700"
                >
                    <span class="text-content-muted">Monto Apertura</span>
                    <span
                        class="font-bold text-content-primary dark:text-white"
                        >{{ fmt(session.opening_balance) }}</span
                    >
                </div>
                <div
                    class="flex justify-between border-b border-gray-100 pb-1.5 dark:border-gray-700"
                >
                    <span class="text-content-muted">+ Ventas Efectivo</span>
                    <span
                        class="font-bold text-emerald-600 dark:text-emerald-400"
                        >{{ fmt(summary.cashSales) }}</span
                    >
                </div>
                <div
                    class="flex justify-between border-b border-gray-100 pb-1.5 dark:border-gray-700"
                >
                    <span class="text-content-muted">+ Ingresos</span>
                    <span
                        class="font-bold text-emerald-600 dark:text-emerald-400"
                        >{{ fmt(summary.ingresos) }}</span
                    >
                </div>
                <div
                    class="flex justify-between border-b border-gray-100 pb-1.5 dark:border-gray-700"
                >
                    <span class="text-content-muted">- Retiros</span>
                    <span
                        class="font-bold text-orange-600 dark:text-orange-400"
                        >{{ fmt(summary.retiros) }}</span
                    >
                </div>
                <div
                    class="flex justify-between border-b border-gray-100 pb-1.5 font-bold dark:border-gray-700"
                >
                    <span class="text-content-muted">Esperado en Caja</span>
                    <span class="text-primary-600 dark:text-primary-400">{{
                        fmt(summary.esperado)
                    }}</span>
                </div>
                <div
                    class="flex justify-between border-b border-gray-100 pb-1.5 dark:border-gray-700"
                >
                    <span class="text-content-muted">Efectivo Declarado</span>
                    <span
                        class="font-bold text-content-primary dark:text-white"
                        >{{ fmt(summary.declarado) }}</span
                    >
                </div>
                <div
                    class="flex justify-between border-b border-gray-100 pb-1.5 dark:border-gray-700"
                >
                    <span class="text-content-muted">Redcompra</span>
                    <span
                        class="font-bold text-content-primary dark:text-white"
                        >{{ fmt(session.total_red_compra) }}</span
                    >
                </div>
                <div
                    class="flex justify-between border-b border-gray-100 pb-1.5 dark:border-gray-700"
                >
                    <span class="text-content-muted">Transferencias</span>
                    <span
                        class="font-bold text-content-primary dark:text-white"
                        >{{ fmt(session.total_transferencia) }}</span
                    >
                </div>

                <div
                    class="flex justify-between pt-1 text-base font-black"
                    :class="
                        summary.diferencia < 0
                            ? 'text-red-600'
                            : summary.diferencia > 0
                              ? 'text-emerald-600'
                              : 'text-content-muted'
                    "
                >
                    <span>Diferencia Total</span>
                    <span>
                        <template v-if="summary.diferencia < 0"
                            >Faltante:
                            {{ fmt(Math.abs(summary.diferencia)) }}</template
                        >
                        <template v-else-if="summary.diferencia > 0"
                            >Sobrante: +{{ fmt(summary.diferencia) }}</template
                        >
                        <template v-else>$0 (Cuadrado)</template>
                    </span>
                </div>
            </div>

            <!-- Actions -->
            <div class="mt-6 space-y-3">
                <button
                    @click="downloadPdf"
                    class="flex w-full items-center justify-center gap-2 rounded-xl bg-primary-600 py-3 text-sm font-bold text-white shadow-sm transition-colors hover:bg-primary-700"
                >
                    <Download class="h-4 w-4" />
                    Descargar Comprobante PDF
                </button>

                <button
                    @click="logout"
                    class="flex w-full items-center justify-center gap-2 rounded-xl border border-gray-200 py-3 text-sm font-bold text-danger transition-colors hover:bg-red-50 dark:border-gray-700 dark:hover:bg-red-900/10"
                >
                    <LogOut class="h-4 w-4" />
                    Cerrar Sesión
                </button>
            </div>
        </div>
    </div>
</template>
