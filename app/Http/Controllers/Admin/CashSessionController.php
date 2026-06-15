<?php

namespace App\Http\Controllers\Admin;
use Inertia\Inertia;

use App\Http\Controllers\Controller;
use App\Models\CashSession;
use App\Models\User;
use App\Models\ZetaReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CashSessionController extends Controller
{
    public function index(Request $request)
    {
        $query = CashSession::with('user', 'denominations');

        if ($request->user() && !$request->user()->hasRole('admin')) {
            $query->where('user_id', $request->user()->id);
        }

        $sessions = $query->orderBy('opened_at', 'desc')->paginate(30);
        $cashiers = User::role('cashier')->get();

        return Inertia::render('admin/arqueo-caja', [
            'sessions' => $sessions,
            'cashiers' => $cashiers,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'opened_at' => 'required|date',
            'opening_balance' => 'required|numeric|min:0',
            'real_balance' => 'required|numeric|min:0',
        ]);

        CashSession::create($validated);

        return redirect()->route('admin.arqueo-caja.index')->with('success', 'Sesión de caja registrada.');
    }

    public function close(Request $request, CashSession $cashSession): RedirectResponse
    {
        $validated = $request->validate([
            'closed_at' => 'required|date',
            'real_balance' => 'required|numeric|min:0',
            'total_z' => 'required|numeric|min:0',
            'transfers' => 'required|numeric|min:0',
            'pos_card_total' => 'required|numeric|min:0',
            'observations' => 'nullable|string|max:255',
        ]);

        $cashSession->update([
            'closed_at' => $validated['closed_at'],
            'real_balance' => $validated['real_balance'],
        ]);

        $date = $cashSession->date ?? \Carbon\Carbon::parse($cashSession->opened_at)->toDateString();

        ZetaReport::updateOrCreate(
            ['date' => $date, 'cashier_id' => $cashSession->user_id],
            [
                'total_z' => $validated['total_z'],
                'net_cash' => $validated['real_balance'],
                'transfers' => $validated['transfers'],
                'pos_card_total' => $validated['pos_card_total'],
                'observations' => $validated['observations'] ?? null,
                'status' => 'pending_review',
            ],
        );

        return redirect()->route('admin.arqueo-caja.index')->with('success', 'Sesión cerrada. Zeta y Control Diario actualizados.');
    }

    public function destroy(CashSession $cashSession): RedirectResponse
    {
        $cashSession->delete();

        return redirect()->route('admin.arqueo-caja.index')->with('success', 'Sesión eliminada.');
    }
}
