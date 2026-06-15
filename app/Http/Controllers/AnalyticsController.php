<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AnalyticsController extends Controller
{
    /**
     * Dashboard de analítica principal.
     */
    public function dashboard(Request $request): Response
    {
        $from = $request->date('from', 'Y-m-d') ?? now()->startOfMonth();
        $to   = $request->date('to', 'Y-m-d')   ?? now();

        return Inertia::render('Analytics/Dashboard', [
            'period'           => ['from' => $from->toDateString(), 'to' => $to->toDateString()],
            'kpis'             => $this->getKpis($from, $to),
            'salesByDay'       => $this->getSalesByDay($from, $to),
            'topProducts'      => $this->getTopProducts($from, $to),
            'salesByCategory'  => $this->getSalesByCategory($from, $to),
            'salesByChannel'   => $this->getSalesByChannel($from, $to),
            'customerMetrics'  => $this->getCustomerMetrics($from, $to),
        ]);
    }

    // ─── KPIs principales ──────────────────────────────────

    private function getKpis($from, $to): array
    {
        $orders = Order::completed()
            ->whereBetween('created_at', [$from->startOfDay(), $to->endOfDay()]);

        $totalSales      = (clone $orders)->sum('total');
        $orderCount      = (clone $orders)->count();
        $totalCost       = OrderItem::whereIn('order_id', (clone $orders)->pluck('id'))
                            ->selectRaw('SUM(cost_price * quantity) as total_cost')
                            ->value('total_cost') ?? 0;

        $aov             = $orderCount > 0 ? $totalSales / $orderCount : 0;
        $grossProfit     = $totalSales - $totalCost;
        $roi             = $totalCost > 0 ? round(($grossProfit / $totalCost) * 100, 2) : 0;

        // Tasa de clientes recurrentes
        $totalCustomers  = (clone $orders)->whereNotNull('customer_id')->distinct('customer_id')->count();
        $returningCustomers = Customer::whereHas('orders', function ($q) use ($from, $to) {
            $q->completed()->whereBetween('created_at', [$from->startOfDay(), $to->endOfDay()]);
        })->where('order_count', '>', 1)->count();
        $returningRate   = $totalCustomers > 0
            ? round(($returningCustomers / $totalCustomers) * 100, 1)
            : 0;

        // Comparación con período anterior
        $prevFrom = $from->copy()->subDays($from->diffInDays($to) + 1);
        $prevTo   = $from->copy()->subDay();
        $prevSales = Order::completed()
            ->whereBetween('created_at', [$prevFrom->startOfDay(), $prevTo->endOfDay()])
            ->sum('total');

        $salesGrowth = $prevSales > 0
            ? round((($totalSales - $prevSales) / $prevSales) * 100, 1)
            : null;

        return [
            'total_sales'        => $totalSales,
            'total_sales_fmt'    => '$' . number_format($totalSales / 100, 0, ',', '.'),
            'order_count'        => $orderCount,
            'aov'                => round($aov),
            'aov_fmt'            => '$' . number_format($aov / 100, 0, ',', '.'),
            'gross_profit'       => $grossProfit,
            'gross_profit_fmt'   => '$' . number_format($grossProfit / 100, 0, ',', '.'),
            'roi_pct'            => $roi,
            'returning_rate_pct' => $returningRate,
            'sales_growth_pct'   => $salesGrowth,
        ];
    }

    // ─── Ventas por día ─────────────────────────────────────

    private function getSalesByDay($from, $to): array
    {
        return Order::completed()
            ->whereBetween('created_at', [$from->startOfDay(), $to->endOfDay()])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as orders, SUM(total) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn($row) => [
                'date'    => $row->date,
                'orders'  => $row->orders,
                'revenue' => $row->revenue,
            ])
            ->toArray();
    }

    // ─── Top productos ──────────────────────────────────────

    private function getTopProducts($from, $to, int $limit = 10): array
    {
        return OrderItem::whereIn('order_id', Order::completed()
                ->whereBetween('created_at', [$from->startOfDay(), $to->endOfDay()])
                ->pluck('id'))
            ->selectRaw('product_id, product_name, SUM(quantity) as units_sold, SUM(line_total) as revenue, SUM((line_total - cost_price * quantity)) as profit')
            ->groupBy('product_id', 'product_name')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    // ─── Ventas por categoría ───────────────────────────────

    private function getSalesByCategory($from, $to): array
    {
        return DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->join('categories', 'categories.id', '=', 'products.category_id')
            ->where('orders.status', 'completed')
            ->whereBetween('orders.created_at', [$from->startOfDay(), $to->endOfDay()])
            ->selectRaw('categories.name as category, SUM(order_items.line_total) as revenue, SUM(order_items.quantity) as units')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('revenue')
            ->get()
            ->toArray();
    }

    // ─── Ventas por canal ───────────────────────────────────

    private function getSalesByChannel($from, $to): array
    {
        return Order::completed()
            ->whereBetween('created_at', [$from->startOfDay(), $to->endOfDay()])
            ->selectRaw('sale_channel, COUNT(*) as orders, SUM(total) as revenue')
            ->groupBy('sale_channel')
            ->get()
            ->toArray();
    }

    // ─── Métricas de clientes ───────────────────────────────

    private function getCustomerMetrics($from, $to): array
    {
        $newCustomers = Customer::whereBetween('created_at', [$from->startOfDay(), $to->endOfDay()])->count();
        $topCustomers = Customer::withCount('orders')
            ->orderByDesc('total_spent')
            ->limit(5)
            ->get(['id', 'name', 'phone', 'total_spent', 'loyalty_points', 'is_vip', 'order_count'])
            ->map(fn($c) => array_merge($c->toArray(), ['total_spent_fmt' => $c->total_spent_formatted]))
            ->toArray();

        return [
            'new_customers'  => $newCustomers,
            'vip_count'      => Customer::vip()->count(),
            'top_customers'  => $topCustomers,
        ];
    }
}
