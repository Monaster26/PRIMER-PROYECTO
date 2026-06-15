<?php

namespace App\Http\Controllers\Admin;
use Inertia\Inertia;

use App\Http\Controllers\Controller;
use App\Models\DailyControl;
use App\Models\Expense;
use App\Models\Loss;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MonthlySummaryController extends Controller
{
    public function index()
    {
        $year = (int) (request('year') ?? now()->year);
        $month = (int) (request('month') ?? now()->month);

        $dailyControls = DailyControl::whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get();

        $totalEfectivo = (float) $dailyControls->sum('cash_withdrawals');
        $totalTarjeta = (float) $dailyControls->sum('card_sales_z');
        $totalMercadoPago = (float) $dailyControls->sum('mercado_pago_sales');
        $ingresoBruto = $totalEfectivo + $totalTarjeta + $totalMercadoPago;

        $totalGastos = (float) Expense::whereYear('date', $year)
            ->whereMonth('date', $month)
            ->sum('amount');

        $totalPerdidas = (float) Loss::whereYear('date', $year)
            ->whereMonth('date', $month)
            ->sum('total_loss_value');

        $utilidadOperativa = $ingresoBruto - $totalGastos - $totalPerdidas;

        $months = [
            'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
            'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre',
        ];

        return Inertia::render('admin/resumen-mensual', [
            'resumen' => [
                'year' => $year,
                'month' => $month,
                'month_name' => $months[$month - 1],
                'total_efectivo' => $totalEfectivo,
                'total_tarjeta' => $totalTarjeta,
                'total_mercado_pago' => $totalMercadoPago,
                'ingreso_bruto' => $ingresoBruto,
                'total_gastos' => $totalGastos,
                'total_perdidas' => $totalPerdidas,
                'utilidad_operativa' => $utilidadOperativa,
            ],
            'available_years' => range(now()->year - 2, now()->year + 1),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        return redirect()->route('admin.resumen-mensual.index')
            ->with('error', 'El resumen mensual es generado automáticamente.');
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        return redirect()->route('admin.resumen-mensual.index')
            ->with('error', 'El resumen mensual es generado automáticamente.');
    }

    public function destroy(string $id): RedirectResponse
    {
        return redirect()->route('admin.resumen-mensual.index')
            ->with('error', 'El resumen mensual es generado automáticamente.');
    }
}
