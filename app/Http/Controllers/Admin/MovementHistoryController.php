<?php

namespace App\Http\Controllers\Admin;

use App\Exports\StockMovementsExport;
use App\Http\Controllers\Controller;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class MovementHistoryController extends Controller
{
    public function index()
    {
        $movements = StockMovement::with('product', 'reference')
            ->search(request('search'))
            ->inDateRange(request('from'), request('to'))
            ->when(request('type'), fn($q, $type) => $q->ofType($type))
            ->orderBy('created_at', 'desc')
            ->paginate(50)
            ->withQueryString();

        $summary = [
            'total_entries' => (int) StockMovement::search(request('search'))
                ->inDateRange(request('from'), request('to'))
                ->when(request('type'), fn($q, $type) => $q->ofType($type))
                ->where('quantity_change', '>', 0)
                ->sum('quantity_change'),
            'total_exits' => (int) StockMovement::search(request('search'))
                ->inDateRange(request('from'), request('to'))
                ->when(request('type'), fn($q, $type) => $q->ofType($type))
                ->where('quantity_change', '<', 0)
                ->sum('quantity_change'),
        ];

        return Inertia::render('admin/movimientos-stock', [
            'movements' => $movements,
            'summary'   => $summary,
            'filters'   => request(['search', 'type', 'from', 'to']),
        ]);
    }

    public function exportExcel()
    {
        return Excel::download(
            new StockMovementsExport(
                search: request('search'),
                type: request('type'),
                from: request('from'),
                to: request('to'),
            ),
            'movimientos-inventario.xlsx'
        );
    }
}
