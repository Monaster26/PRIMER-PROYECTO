<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\CashSession;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->hasRole('admin')) {
            return $this->adminDashboard();
        }

        return $this->cashierDashboard($user);
    }

    private function adminDashboard()
    {
        $month = now()->month;
        $year = now()->year;

        $monthSales = Sale::whereMonth('date', $month)
            ->whereYear('date', $year)
            ->with('items')
            ->get();

        $totalSales = (int) round($monthSales->sum(fn($s) => $s->items->sum('subtotal')) * 100);
        $salesCount = $monthSales->count();

        $totalProducts = Product::count();
        $activeCategories = Category::count();
        $lowStockCount = Product::whereColumn('stock', '<=', 'min_stock')->where('has_variants', false)->count();

        $lowStockProducts = Product::whereColumn('stock', '<=', 'min_stock')
            ->where('has_variants', false)
            ->orderBy('stock')
            ->limit(5)
            ->get(['id', 'name', 'stock', 'min_stock']);

        $recentSales = Sale::with('cashier', 'items.product')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $categories = Category::withCount('products as product_count')->orderBy('name')->get();

        return Inertia::render('admin/dashboard', [
            'role' => 'admin',
            'stats' => [
                'total_sales' => $totalSales,
                'sales_count' => $salesCount,
                'total_products' => $totalProducts,
                'active_categories' => $activeCategories,
                'low_stock_count' => $lowStockCount,
            ],
            'recent_sales' => $recentSales,
            'low_stock_products' => $lowStockProducts,
            'categories' => $categories,
        ]);
    }

    private function cashierDashboard($user)
    {
        $today = now()->toDateString();

        $todaySales = Sale::whereDate('date', $today)
            ->where('cashier_id', $user->id)
            ->with('items')
            ->get();

        $todayTotal = (int) round($todaySales->sum(fn($s) => $s->items->sum('subtotal')) * 100);
        $todayCount = $todaySales->count();

        $openSession = CashSession::where('user_id', $user->id)
            ->whereNull('closed_at')
            ->first();

        $recentSales = Sale::with('items.product')
            ->where('cashier_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return Inertia::render('admin/dashboard', [
            'role' => 'cashier',
            'stats' => [
                'today_sales' => $todayTotal,
                'today_count' => $todayCount,
                'has_open_session' => $openSession !== null,
                'session_opened_at' => $openSession?->opened_at,
            ],
            'recent_sales' => $recentSales,
        ]);
    }
}
