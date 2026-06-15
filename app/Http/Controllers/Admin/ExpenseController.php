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

        $summary = [
            'total' => (float) Expense::whereMonth('date', $month)->whereYear('date', $year)->sum('amount'),
            'cash'  => (float) Expense::whereMonth('date', $month)->whereYear('date', $year)->where('payment_method', 'cash_box')->sum('amount'),
            'transfer' => (float) Expense::whereMonth('date', $month)->whereYear('date', $year)->where('payment_method', 'bank_transfer')->sum('amount'),
            'count' => Expense::whereMonth('date', $month)->whereYear('date', $year)->count(),
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
            'date'           => 'required|date',
            'supplier_id'    => 'nullable|exists:suppliers,id',
            'payment_method' => 'required|in:cash_box,bank_transfer',
            'amount'         => 'required|numeric|min:0',
            'concept'        => 'required|string|max:255',
        ]);

        Expense::create($validated);

        return redirect()->route('admin.gastos.index', [
            'month' => now()->month,
            'year'  => now()->year,
        ])->with('success', 'Gasto registrado.');
    }

    public function update(Request $request, Expense $expense): RedirectResponse
    {
        $validated = $request->validate([
            'date'           => 'required|date',
            'supplier_id'    => 'nullable|exists:suppliers,id',
            'payment_method' => 'required|in:cash_box,bank_transfer',
            'amount'         => 'required|numeric|min:0',
            'concept'        => 'required|string|max:255',
        ]);

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
