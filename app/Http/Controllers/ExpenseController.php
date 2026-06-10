<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Provider;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ExpenseController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Expense::with('provider')
            ->latest('date');

        if ($request->filled('from')) {
            $query->where('date', '>=', $request->date('from'));
        }
        if ($request->filled('to')) {
            $query->where('date', '<=', $request->date('to'));
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $expenses  = $query->paginate(20)->withQueryString();
        $providers = Provider::orderBy('name')->get(['id', 'name']);

        $totals = Expense::query()
            ->when($request->filled('from'), fn($q) => $q->where('date', '>=', $request->date('from')))
            ->when($request->filled('to'),   fn($q) => $q->where('date', '<=', $request->date('to')))
            ->selectRaw('
                SUM(cash_spent)     as total_cash,
                SUM(transfer_spent) as total_transfer,
                SUM(total_expense)  as grand_total
            ')->first();

        $categories = Expense::distinct()->pluck('category')->sort()->values();

        return Inertia::render('Expenses/Index', [
            'expenses'   => $expenses,
            'providers'  => $providers,
            'totals'     => $totals,
            'categories' => $categories,
            'filters'    => $request->only(['from', 'to', 'category']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'provider_id'    => 'required|exists:providers,id',
            'category'       => 'required|string|max:100',
            'cash_spent'     => 'required|integer|min:0',
            'transfer_spent' => 'required|integer|min:0',
            'date'           => 'required|date',
        ]);

        Expense::create($data);

        return back()->with('success', 'Gasto registrado correctamente.');
    }

    public function update(Request $request, Expense $expense): RedirectResponse
    {
        $data = $request->validate([
            'provider_id'    => 'required|exists:providers,id',
            'category'       => 'required|string|max:100',
            'cash_spent'     => 'required|integer|min:0',
            'transfer_spent' => 'required|integer|min:0',
            'date'           => 'required|date',
        ]);

        $expense->update($data);

        return back()->with('success', 'Gasto actualizado correctamente.');
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        $expense->delete();

        return back()->with('success', 'Gasto eliminado.');
    }
}
