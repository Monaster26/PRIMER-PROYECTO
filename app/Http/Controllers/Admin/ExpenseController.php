<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ExpenseController extends Controller
{
    public function index()
    {
        $month = (int) (request('month') ?? now()->month);
        $year = (int) (request('year') ?? now()->year);

        $expenses = Expense::with('supplier')
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->orderBy('date', 'desc')
            ->paginate(30);

        $suppliers = Supplier::orderBy('company_name')->get();

        $baseQuery = Expense::whereMonth('date', $month)->whereYear('date', $year);

        $summary = [
            'total'    => (float) $baseQuery->sum('total_expense'),
            'cash'     => (float) (clone $baseQuery)->sum('cash_spent'),
            'transfer' => (float) (clone $baseQuery)->sum('transfer_spent'),
            'count'    => (clone $baseQuery)->count(),
        ];

        $months = [
            'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
            'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre',
        ];

        return Inertia::render('admin/gastos', [
            'expenses'  => $expenses,
            'suppliers' => $suppliers,
            'month'     => $month,
            'year'      => $year,
            'month_name' => $months[$month - 1],
            'summary'   => $summary,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'date'            => 'required|date',
            'supplier_id'     => 'nullable|exists:suppliers,id',
            'category'        => 'required|string|max:255',
            'cash_spent'      => 'nullable|integer|min:0',
            'transfer_spent'  => 'nullable|integer|min:0',
            'concept'         => 'nullable|string|max:255',
        ]);

        $validated['cash_spent'] ??= 0;
        $validated['transfer_spent'] ??= 0;

        Expense::create($validated);

        return redirect()->route('admin.gastos.index', [
            'month' => now()->month,
            'year'  => now()->year,
        ])->with('success', 'Gasto registrado.');
    }

    public function update(Request $request, Expense $expense): RedirectResponse
    {
        $validated = $request->validate([
            'date'            => 'required|date',
            'supplier_id'     => 'nullable|exists:suppliers,id',
            'category'        => 'required|string|max:255',
            'cash_spent'      => 'nullable|integer|min:0',
            'transfer_spent'  => 'nullable|integer|min:0',
            'concept'         => 'nullable|string|max:255',
        ]);

        $validated['cash_spent'] ??= 0;
        $validated['transfer_spent'] ??= 0;

        $expense->update($validated);

        return redirect()->route('admin.gastos.index', [
            'month' => request('month', now()->month),
            'year'  => request('year', now()->year),
        ])->with('success', 'Gasto actualizado.');
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        $expense->delete();

        return redirect()->route('admin.gastos.index', [
            'month' => request('month', now()->month),
            'year'  => request('year', now()->year),
        ])->with('success', 'Gasto eliminado.');
    }
}
