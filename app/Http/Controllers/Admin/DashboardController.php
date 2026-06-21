<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
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
        $month = now()->month;
        $year = now()->year;

        $monthSales = Sale::whereMonth('created_at', $month)
            ->whereYear('created_at', $year);

        $totalSales = (int) $monthSales->sum('total');
        $salesCount = (clone $monthSales)->count();

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

}
