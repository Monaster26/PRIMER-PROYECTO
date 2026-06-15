<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ZetaReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ZetaReportController extends Controller
{
    public function index()
    {
        $reports = ZetaReport::with('cashier')->orderBy('date', 'desc')->paginate(30);
        $cashiers = User::role('cashier')->get();

        return Inertia::render('admin/zeta', [
            'reports' => $reports,
            'cashiers' => $cashiers,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'date' => 'required|date|unique:zeta_reports,date',
            'cashier_id' => 'required|exists:users,id',
            'total_z' => 'required|numeric|min:0',
            'net_cash' => 'required|numeric|min:0',
            'transfers' => 'required|numeric|min:0',
            'pos_card_total' => 'required|numeric|min:0',
            'observations' => 'nullable|string',
            'status' => 'required|in:pending_review,audited',
        ]);

        ZetaReport::create($validated);

        return redirect()->route('admin.zeta.index')->with('success', 'Reporte Zeta registrado. Control Diario actualizado automáticamente.');
    }

    public function update(Request $request, ZetaReport $zetaReport): RedirectResponse
    {
        $validated = $request->validate([
            'total_z' => 'required|numeric|min:0',
            'net_cash' => 'required|numeric|min:0',
            'transfers' => 'required|numeric|min:0',
            'pos_card_total' => 'required|numeric|min:0',
            'observations' => 'nullable|string',
            'status' => 'required|in:pending_review,audited',
        ]);

        $zetaReport->update($validated);

        return redirect()->route('admin.zeta.index')->with('success', 'Reporte Zeta actualizado. Control Diario actualizado automáticamente.');
    }

    public function destroy(ZetaReport $zetaReport): RedirectResponse
    {
        $zetaReport->delete();

        return redirect()->route('admin.zeta.index')->with('success', 'Reporte Zeta eliminado.');
    }
}
