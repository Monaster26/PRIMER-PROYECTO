<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CashMovement;
use App\Models\CashSession;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReporteDiarioController extends Controller
{
    public function index(Request $request)
    {
        $date   = $request->input('date', today()->toDateString());
        $userId = $request->input('user_id');

        $start = $date . ' 00:00:00';
        $end   = $date . ' 23:59:59';

        // Base scopes
        $sessionQuery = CashSession::whereDate('date', $date);
        $salesQuery   = Sale::whereBetween('created_at', [$start, $end]);
        $movementQuery = CashMovement::whereBetween('created_at', [$start, $end]);
        $itemsQuery   = SaleItem::whereBetween('sales.created_at', [$start, $end]);

        if ($userId) {
            $sessionQuery->where('user_id', $userId);
            $salesQuery->where('user_id', $userId);
            $movementQuery->where('user_id', $userId);
            $itemsQuery->where('sales.user_id', $userId);
        }

        // Opening balance from cash sessions
        $openingBalance = (int) (clone $sessionQuery)->sum('opening_balance');

        // Real cash counted at closing (efectivo_cierre) — only from closed sessions
        $efectivoCierre = (int) (clone $sessionQuery)
            ->whereNotNull('closed_at')
            ->sum('total_efectivo_cierre');

        // Sales by payment method (cents → pesos)
        $sales = (clone $salesQuery)
            ->selectRaw('
                COALESCE(SUM(cash_amount),0) as cash_raw,
                COALESCE(SUM(card_amount),0) as card_raw,
                COALESCE(SUM(transfer_amount),0) as transfer_raw,
                COALESCE(SUM(total),0) as total_raw
            ')
            ->first();

        $cashSales      = (int) ($sales->cash_raw / 100);
        $cardSales      = (int) ($sales->card_raw / 100);
        $transferSales  = (int) ($sales->transfer_raw / 100);
        $grandTotal     = (int) ($sales->total_raw / 100);

        // Cash movements (already in pesos)
        $totalIngresos = (int) (clone $movementQuery)
            ->where('type', 'ingreso')
            ->sum('amount');

        $totalRetiros = (int) (clone $movementQuery)
            ->where('type', 'retiro')
            ->sum('amount');

        // Sales by category
        $byCategory = (clone $itemsQuery)
            ->selectRaw('
                categories.name as category_name,
                SUM(sale_items.quantity) as qty,
                SUM(sale_items.total_line) as total_raw
            ')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->groupBy('categories.name')
            ->orderByDesc('total_raw')
            ->get()
            ->map(fn ($item) => [
                'category' => $item->category_name,
                'qty'      => (int) $item->qty,
                'total'    => (int) ($item->total_raw / 100),
            ]);

        // Net profit (cents → pesos)
        $netProfitRaw = (clone $itemsQuery)
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->selectRaw('COALESCE(SUM((CAST(sale_items.price AS SIGNED) - CAST(products.cost_price AS SIGNED)) * sale_items.quantity),0) as profit')
            ->first()
            ->profit;

        $netProfit = (int) ($netProfitRaw / 100);

        // Users for the filter dropdown
        $users = User::role(['admin', 'cashier'])
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('admin/reporte-diario', [
            'date'       => $date,
            'userId'     => $userId ? (int) $userId : null,
            'users'      => $users,
            'cashBalance' => [
                'opening'         => $openingBalance,
                'cashSales'       => $cashSales,
                'ingresos'        => $totalIngresos,
                'withdrawals'     => $totalRetiros,
                'expected'        => $openingBalance + $cashSales + $totalIngresos - $totalRetiros,
            ],
            'digitalSales' => [
                'card'     => $cardSales,
                'transfer' => $transferSales,
                'total'    => $cardSales + $transferSales,
            ],
            'byCategory' => $byCategory,
            'summary' => [
                'grossSales'   => $grandTotal,
                'totalGeneral' => $openingBalance + $grandTotal + $totalIngresos - $totalRetiros,
                'netProfit'    => $netProfit,
            ],
        ]);
    }
}
