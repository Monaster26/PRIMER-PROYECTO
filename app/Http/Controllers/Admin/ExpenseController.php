<?php

namespace App\Http\Controllers\Admin;

use App\Exports\EgresosExport;
use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class ExpenseController extends Controller
{
    public function index()
    {
        $filters = request()->only([
            'from', 'to', 'type', 'payment_method',
            'beneficiary', 'user_id',
        ]);

        $query = Expense::with('supplier', 'user')
            ->latest('date');

        $query->byDateRange($filters['from'] ?? null, $filters['to'] ?? null)
            ->ofType($filters['type'] ?? null)
            ->byPaymentMethod($filters['payment_method'] ?? null)
            ->searchBeneficiary($filters['beneficiary'] ?? null)
            ->byUser($filters['user_id'] ?? null);

        $expenses = $query->paginate(30)->withQueryString();

        $base = Expense::query()
            ->byDateRange($filters['from'] ?? null, $filters['to'] ?? null)
            ->ofType($filters['type'] ?? null)
            ->byPaymentMethod($filters['payment_method'] ?? null)
            ->searchBeneficiary($filters['beneficiary'] ?? null)
            ->byUser($filters['user_id'] ?? null);

        $summary = [
            'total'       => (float) (clone $base)->sum('amount'),
            'efectivo'    => (float) (clone $base)->where('payment_method', 'efectivo')->sum('amount'),
            'transferencia' => (float) (clone $base)->where('payment_method', 'transferencia')->sum('amount'),
            'proveedores' => (float) (clone $base)->where('type', 'proveedor')->sum('amount'),
            'operacionales' => (float) (clone $base)->whereIn('type', [
                'servicio', 'sueldo', 'arriendo', 'gasto_comun', 'mantencion', 'impuesto', 'otros',
            ])->sum('amount'),
            'count'       => (clone $base)->count(),
        ];

        $suppliers = Supplier::orderBy('company_name')->get(['id', 'company_name']);
        $users = User::orderBy('name')->get(['id', 'name']);

        $totalTaxAmount = (int) (clone $base)->sum('tax_amount');

        return Inertia::render('admin/gastos', [
            'expenses'       => $expenses,
            'suppliers'      => $suppliers,
            'users'          => $users,
            'summary'        => $summary,
            'filters'        => $filters,
            'chart'          => $this->chartData($filters),
            'totalTaxAmount' => $totalTaxAmount,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'date'           => 'required|date',
            'type'           => 'required|string|in:' . implode(',', Expense::TYPES),
            'concept'        => 'nullable|string|max:255',
            'beneficiary'    => 'nullable|string|max:255',
            'payment_method' => 'required|string|in:efectivo,transferencia',
            'amount'         => 'required|integer|min:1',
            'observation'    => 'nullable|string|max:500',
            'receipt_file'   => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'supplier_id'    => 'nullable|exists:suppliers,id',
        ]);

        $validated['user_id'] = $request->user()->id;

        if ($request->hasFile('receipt_file')) {
            $validated['receipt_file'] = $request->file('receipt_file')
                ->store('egresos/comprobantes', 'public');
        }

        Expense::create($validated);

        return redirect()->route('admin.gastos.index', $this->filterParams($request))
            ->with('success', 'Egreso registrado correctamente.');
    }

    public function update(Request $request, Expense $expense): RedirectResponse
    {
        $validated = $request->validate([
            'date'           => 'required|date',
            'type'           => 'required|string|in:' . implode(',', Expense::TYPES),
            'concept'        => 'nullable|string|max:255',
            'beneficiary'    => 'nullable|string|max:255',
            'payment_method' => 'required|string|in:efectivo,transferencia',
            'amount'         => 'required|integer|min:1',
            'observation'    => 'nullable|string|max:500',
            'receipt_file'   => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'supplier_id'    => 'nullable|exists:suppliers,id',
        ]);

        if ($request->hasFile('receipt_file')) {
            if ($expense->receipt_file) {
                Storage::disk('public')->delete($expense->receipt_file);
            }
            $validated['receipt_file'] = $request->file('receipt_file')
                ->store('egresos/comprobantes', 'public');
        }

        $expense->update($validated);

        return redirect()->route('admin.gastos.index', $this->filterParams($request))
            ->with('success', 'Egreso actualizado correctamente.');
    }

    public function destroy(Request $request, Expense $expense): RedirectResponse
    {
        if ($expense->receipt_file) {
            Storage::disk('public')->delete($expense->receipt_file);
        }
        $expense->delete();

        return redirect()->route('admin.gastos.index', $this->filterParams($request))
            ->with('success', 'Egreso eliminado.');
    }

    public function updateTax(Request $request, Expense $expense): RedirectResponse
    {
        $validated = $request->validate([
            'tax_amount' => 'required|integer|min:0',
        ]);

        $expense->update(['tax_amount' => $validated['tax_amount']]);

        return back()->with('success', 'Impuesto actualizado.');
    }

    public function export()
    {
        $filters = request()->only([
            'from', 'to', 'type', 'payment_method',
            'beneficiary', 'user_id',
        ]);

        return Excel::download(
            new EgresosExport($filters),
            'egresos_' . now()->format('Ymd_His') . '.xlsx'
        );
    }

    public function chartData(?array $filters = null): array
    {
        $filters ??= request()->only(['from', 'to']);

        $query = Expense::query()
            ->byDateRange($filters['from'] ?? null, $filters['to'] ?? null);

        $groups = (clone $query)
            ->selectRaw('type, SUM(amount) as total')
            ->groupBy('type')
            ->pluck('total', 'type');

        $colors = [
            'proveedor'  => '#ef4444',
            'sueldo'     => '#f97316',
            'arriendo'   => '#eab308',
            'servicio'   => '#22c55e',
            'impuesto'   => '#3b82f6',
            'gasto_comun'=> '#8b5cf6',
            'mantencion' => '#ec4899',
            'otros'      => '#64748b',
        ];

        $chart = [];
        foreach (Expense::TYPES as $type) {
            $chart[$type] = [
                'label' => Expense::TYPE_LABELS[$type],
                'total' => (float) ($groups[$type] ?? 0),
                'color' => $colors[$type],
            ];
        }

        return $chart;
    }

    private function filterParams(Request $request): array
    {
        return array_filter($request->only([
            'from', 'to', 'type', 'payment_method',
            'beneficiary', 'user_id',
        ]));
    }
}
