<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CashSession;
use App\Models\Expense;
use App\Models\Loss;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Promotion;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if (!$user->hasRole('admin')) {
            return redirect()->route('admin.pos');
        }

        return $this->adminDashboard();
    }

    private function adminDashboard()
    {
        $today = now()->startOfDay();
        $thisMonthStart = now()->startOfMonth();
        $lastMonthStart = now()->subMonth()->startOfMonth();
        $lastMonthEnd = now()->subMonth()->endOfMonth();

        // Ventas de hoy
        $todaySales = Sale::whereDate('created_at', $today)->sum('total') / 100;
        $todaySalesCount = Sale::whereDate('created_at', $today)->count();
        $todayProfit = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->whereDate('sales.created_at', $today)
            ->sum(DB::raw('(sale_items.price - products.cost_price) * sale_items.quantity')) / 100;
        $todayMargin = $todaySales > 0 ? round(($todayProfit / $todaySales) * 100, 1) : 0;

        // Ventas del mes actual vs mes anterior
        $thisMonthSales = Sale::whereBetween('created_at', [$thisMonthStart, now()])->sum('total') / 100;
        $lastMonthSales = Sale::whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->sum('total') / 100;
        $salesGrowth = $lastMonthSales > 0
            ? round((($thisMonthSales - $lastMonthSales) / $lastMonthSales) * 100, 1)
            : 0;

        // Tendencia últimos 30 días
        $salesTrend = Sale::selectRaw('DATE(created_at) as date, SUM(total)/100 as total, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(29)->startOfDay())
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Top 5 productos más vendidos hoy
        $topProducts = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->whereDate('sales.created_at', $today)
            ->groupBy('products.id', 'products.name')
            ->selectRaw('products.name, SUM(sale_items.quantity) as qty, SUM(sale_items.total_line)/100 as total')
            ->orderByDesc('qty')
            ->limit(5)
            ->get();

        // Métodos de pago hoy
        $paymentMethods = [
            'cash'     => Sale::whereDate('created_at', $today)->sum('cash_amount') / 100,
            'card'     => Sale::whereDate('created_at', $today)->sum('card_amount') / 100,
            'transfer' => Sale::whereDate('created_at', $today)->sum('transfer_amount') / 100,
        ];

        // Estado de caja
        $openSession = CashSession::whereNull('closed_at')
            ->whereDate('date', $today)
            ->with('user')
            ->first();

        // Alertas
        $lowStockProducts = Product::whereColumn('stock', '<=', 'min_stock')
            ->where('has_variants', false)
            ->where('is_active', true)
            ->orderBy('stock')
            ->limit(8)
           ->get(['id', 'name', 'stock', 'min_stock', 'sku']);

        $expiringBatches = ProductBatch::with('product:id,name')
            ->where('quantity', '>', 0)
            ->whereNotNull('expiration_date')
            ->where('expiration_date', '<=', now()->addDays(7))
            ->orderBy('expiration_date')
            ->limit(5)
            ->get(['id', 'product_id', 'expiration_date', 'quantity']);

        // Gastos del mes
        $monthlyExpenses = Expense::whereBetween('date', [$thisMonthStart, now()])->sum('total_expense') / 100;

        // Pérdidas del mes
        $monthlyLosses = Loss::whereBetween('date', [$thisMonthStart, now()])->sum('total_loss_value');

        // Promociones activas
        $activePromotions = Promotion::active()->count();

        return Inertia::render('admin/dashboard', [
            'role'             => 'admin',
            'todaySales'       => (int) $todaySales,
            'todaySalesCount'  => $todaySalesCount,
            'todayProfit'      => (int) $todayProfit,
            'todayMargin'      => $todayMargin,
            'thisMonthSales'   => (int) $thisMonthSales,
            'lastMonthSales'   => (int) $lastMonthSales,
            'salesGrowth'      => $salesGrowth,
            'salesTrend'       => $salesTrend,
            'topProducts'      => $topProducts,
            'paymentMethods'   => $paymentMethods,
            'openSession'      => $openSession,
            'lowStockProducts' => $lowStockProducts,
            'expiringBatches'  => $expiringBatches,
            'monthlyExpenses'  => (int) $monthlyExpenses,
            'monthlyLosses'    => (int) $monthlyLosses,
            'activePromotions' => $activePromotions,
        ]);
    }
}
