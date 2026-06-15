<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DailyControl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DailyControlController extends Controller
{
    public function index()
    {
        $controls = DailyControl::orderBy('date', 'desc')->paginate(30);

        return Inertia::render('admin/control-diario', [
            'controls' => $controls,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'date' => 'required|date|unique:daily_controls,date',
            'cash_withdrawals' => 'required|numeric|min:0',
            'card_sales_z' => 'required|numeric|min:0',
            'mercado_pago_sales' => 'required|numeric|min:0',
        ]);

        DailyControl::create($validated);

        return redirect()->route('admin.control-diario.index')->with('success', 'Control diario registrado.');
    }

    public function update(Request $request, DailyControl $dailyControl): RedirectResponse
    {
        $validated = $request->validate([
            'cash_withdrawals' => 'required|numeric|min:0',
            'card_sales_z' => 'required|numeric|min:0',
            'mercado_pago_sales' => 'required|numeric|min:0',
        ]);

        $dailyControl->update($validated);

        return redirect()->route('admin.control-diario.index')->with('success', 'Control diario actualizado.');
    }

    public function destroy(DailyControl $dailyControl): RedirectResponse
    {
        $dailyControl->delete();

        return redirect()->route('admin.control-diario.index')->with('success', 'Control diario eliminado.');
    }
}
