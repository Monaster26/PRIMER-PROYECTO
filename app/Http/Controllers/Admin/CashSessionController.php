<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CashMovement;
use App\Models\CashSession;
use App\Models\User;
use App\Models\ZetaReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CashSessionController extends Controller
{
    public function index(Request $request)
    {
        $query = CashSession::with('user');

        if ($request->user() && !$request->user()->hasRole('admin')) {
            $query->where('user_id', $request->user()->id);
        }

        $sessions = $query->orderBy('opened_at', 'desc')->paginate(30);
        $cashiers = User::role('cashier')->get();

        // Cash movements summary for today
        $today = now()->toDateString();
        $movementsQuery = CashMovement::whereDate('created_at', $today);
        $cashMovementsSummary = [
            'total_ingresos' => (int) (clone $movementsQuery)->where('type', 'ingreso')->sum('amount'),
            'total_retiros' => (int) (clone $movementsQuery)->where('type', 'retiro')->sum('amount'),
            'count' => (clone $movementsQuery)->count(),
        ];

        return Inertia::render('admin/arqueo-caja', [
            'sessions' => $sessions,
            'cashiers' => $cashiers,
            'cashMovements' => $cashMovementsSummary,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'opened_at' => 'required|date',
            'cant_20k_apertura' => 'nullable|integer|min:0',
            'cant_10k_apertura' => 'nullable|integer|min:0',
            'cant_5k_apertura' => 'nullable|integer|min:0',
            'cant_2k_apertura' => 'nullable|integer|min:0',
            'cant_1k_apertura' => 'nullable|integer|min:0',
            'cant_500_apertura' => 'nullable|integer|min:0',
            'cant_100_apertura' => 'nullable|integer|min:0',
            'cant_50_apertura' => 'nullable|integer|min:0',
            'cant_10_apertura' => 'nullable|integer|min:0',
            'total_red_compra' => 'nullable|integer|min:0',
            'total_transferencia' => 'nullable|integer|min:0',
            'total_retiros' => 'nullable|integer|min:0',
            'total_ingresos' => 'nullable|integer|min:0',
        ]);

        $validated['cant_20k_apertura'] ??= 0;
        $validated['cant_10k_apertura'] ??= 0;
        $validated['cant_5k_apertura'] ??= 0;
        $validated['cant_2k_apertura'] ??= 0;
        $validated['cant_1k_apertura'] ??= 0;
        $validated['cant_500_apertura'] ??= 0;
        $validated['cant_100_apertura'] ??= 0;
        $validated['cant_50_apertura'] ??= 0;
        $validated['cant_10_apertura'] ??= 0;
        $validated['total_red_compra'] ??= 0;
        $validated['total_transferencia'] ??= 0;
        $validated['total_retiros'] ??= 0;
        $validated['total_ingresos'] ??= 0;

        CashSession::create($validated);

        return redirect()->route('admin.arqueo-caja.index')->with('success', 'Sesión de caja registrada.');
    }

    public function close(Request $request, CashSession $cashSession): RedirectResponse
    {
        $validated = $request->validate([
            'closed_at' => 'required|date',
            'cant_20k_cierre' => 'nullable|integer|min:0',
            'cant_10k_cierre' => 'nullable|integer|min:0',
            'cant_5k_cierre' => 'nullable|integer|min:0',
            'cant_2k_cierre' => 'nullable|integer|min:0',
            'cant_1k_cierre' => 'nullable|integer|min:0',
            'cant_500_cierre' => 'nullable|integer|min:0',
            'cant_100_cierre' => 'nullable|integer|min:0',
            'cant_50_cierre' => 'nullable|integer|min:0',
            'cant_10_cierre' => 'nullable|integer|min:0',
            'total_red_compra' => 'nullable|integer|min:0',
            'total_transferencia' => 'nullable|integer|min:0',
            'total_retiros' => 'nullable|integer|min:0',
            'total_ingresos' => 'nullable|integer|min:0',
            'observations' => 'nullable|string|max:255',
        ]);

        $cashSession->update([
            'closed_at' => $validated['closed_at'],
            'cant_20k_cierre' => $validated['cant_20k_cierre'],
            'cant_10k_cierre' => $validated['cant_10k_cierre'],
            'cant_5k_cierre' => $validated['cant_5k_cierre'],
            'cant_2k_cierre' => $validated['cant_2k_cierre'],
            'cant_1k_cierre' => $validated['cant_1k_cierre'],
            'cant_500_cierre' => $validated['cant_500_cierre'],
            'cant_100_cierre' => $validated['cant_100_cierre'],
            'cant_50_cierre' => $validated['cant_50_cierre'],
            'cant_10_cierre' => $validated['cant_10_cierre'],
            'total_red_compra' => $validated['total_red_compra'] ?? 0,
            'total_transferencia' => $validated['total_transferencia'] ?? 0,
            'total_retiros' => $validated['total_retiros'] ?? 0,
            'total_ingresos' => $validated['total_ingresos'] ?? 0,
        ]);

        $date = $cashSession->date ?? \Carbon\Carbon::parse($cashSession->opened_at)->toDateString();

        $cashSession->refresh();

        ZetaReport::updateOrCreate(
            ['date' => $date, 'cashier_id' => $cashSession->user_id],
            [
                'total_z' => $cashSession->total_caja_esperado ?? 0,
                'net_cash' => $cashSession->total_efectivo_cierre ?? 0,
                'transfers' => $cashSession->total_transferencia ?? 0,
                'pos_card_total' => $cashSession->total_red_compra ?? 0,
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
