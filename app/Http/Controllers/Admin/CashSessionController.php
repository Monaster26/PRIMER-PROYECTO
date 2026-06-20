<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CashMovement;
use App\Models\CashSession;
use App\Models\ControlZeta;
use App\Models\Sale;
use App\Models\User;
use App\Models\ZetaReport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
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

        $filters = [
            'dia'  => $request->integer('dia'),
            'mes'  => $request->integer('mes'),
            'anio' => $request->integer('anio'),
        ];

        if ($filters['anio']) {
            $query->whereYear('opened_at', $filters['anio']);
        }
        if ($filters['mes']) {
            $query->whereMonth('opened_at', $filters['mes']);
        }
        if ($filters['dia']) {
            $query->whereDay('opened_at', $filters['dia']);
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
            'filters' => $filters,
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

        $validated['apertura_desglose'] = [
            '20k' => $validated['cant_20k_apertura'],
            '10k' => $validated['cant_10k_apertura'],
            '5k'  => $validated['cant_5k_apertura'],
            '2k'  => $validated['cant_2k_apertura'],
            '1k'  => $validated['cant_1k_apertura'],
            '500' => $validated['cant_500_apertura'],
            '100' => $validated['cant_100_apertura'],
            '50'  => $validated['cant_50_apertura'],
            '10'  => $validated['cant_10_apertura'],
        ];

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

        $cierreDesglose = [
            '20k' => $validated['cant_20k_cierre'],
            '10k' => $validated['cant_10k_cierre'],
            '5k'  => $validated['cant_5k_cierre'],
            '2k'  => $validated['cant_2k_cierre'],
            '1k'  => $validated['cant_1k_cierre'],
            '500' => $validated['cant_500_cierre'],
            '100' => $validated['cant_100_cierre'],
            '50'  => $validated['cant_50_cierre'],
            '10'  => $validated['cant_10_cierre'],
        ];

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
            'cierre_desglose' => $cierreDesglose,
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

    public function closeFromPos(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'cant_20k_cierre' => 'required|integer|min:0',
                'cant_10k_cierre' => 'required|integer|min:0',
                'cant_5k_cierre' => 'required|integer|min:0',
                'cant_2k_cierre' => 'required|integer|min:0',
                'cant_1k_cierre' => 'required|integer|min:0',
                'coin_500' => 'required|integer|min:0',
                'coin_100' => 'required|integer|min:0',
                'coin_50' => 'required|integer|min:0',
                'coin_10' => 'required|integer|min:0',
                'total_red_compra' => 'required|integer|min:0',
                'total_transferencia' => 'required|integer|min:0',
            ]);

            $user = $request->user();

            if (!$user) {
                return response()->json(['error' => 'Usuario no autenticado.'], 401);
            }

            $session = CashSession::where('user_id', $user->id)
                ->whereNull('closed_at')
                ->latest('opened_at')
                ->first();

            if (!$session) {
                return response()->json(['error' => 'No hay una sesión abierta para cerrar.'], 404);
            }

            $validated['coin_500'] ??= 0;
            $validated['coin_100'] ??= 0;
            $validated['coin_50'] ??= 0;
            $validated['coin_10'] ??= 0;

            $date = $session->date ?? now()->toDateString();

            $cashSales = Sale::where('user_id', $user->id)
                ->whereDate('created_at', $date)
                ->sum('cash_amount');

            $movementQuery = CashMovement::where('user_id', $user->id)
                ->whereDate('created_at', $date);
            $totalIngresos = (int) (clone $movementQuery)->where('type', 'ingreso')->sum('amount');
            $totalRetiros  = (int) (clone $movementQuery)->where('type', 'retiro')->sum('amount');

            $cant_500_cierre = (int) round($validated['coin_500'] / 500);
            $cant_100_cierre = (int) round($validated['coin_100'] / 100);
            $cant_50_cierre  = (int) round($validated['coin_50']  / 50);
            $cant_10_cierre  = (int) round($validated['coin_10']  / 10);

            $cierreDesglose = [
                '20k' => $validated['cant_20k_cierre'],
                '10k' => $validated['cant_10k_cierre'],
                '5k'  => $validated['cant_5k_cierre'],
                '2k'  => $validated['cant_2k_cierre'],
                '1k'  => $validated['cant_1k_cierre'],
                '500' => $cant_500_cierre,
                '100' => $cant_100_cierre,
                '50'  => $cant_50_cierre,
                '10'  => $cant_10_cierre,
            ];

            $session->update([
                'closed_at' => now(),
                'cant_20k_cierre' => $validated['cant_20k_cierre'],
                'cant_10k_cierre' => $validated['cant_10k_cierre'],
                'cant_5k_cierre' => $validated['cant_5k_cierre'],
                'cant_2k_cierre' => $validated['cant_2k_cierre'],
                'cant_1k_cierre' => $validated['cant_1k_cierre'],
                'cant_500_cierre' => $cant_500_cierre,
                'cant_100_cierre' => $cant_100_cierre,
                'cant_50_cierre'  => $cant_50_cierre,
                'cant_10_cierre'  => $cant_10_cierre,
                'total_red_compra' => $validated['total_red_compra'],
                'total_transferencia' => $validated['total_transferencia'],
                'total_ingresos' => $totalIngresos,
                'total_retiros' => $totalRetiros,
                'cierre_desglose' => $cierreDesglose,
            ]);

            $session->refresh();

            $esperado = $session->opening_balance + (int)($cashSales / 100) + $totalIngresos - $totalRetiros;
            $diferencia = $session->total_efectivo_cierre - $esperado;

            ControlZeta::where('cash_session_id', $session->id)->update([
                'esperado_caja'    => $esperado,
                'efectivo_neto'    => ($session->total_efectivo_cierre + $totalRetiros) - $session->opening_balance,
                'red_compra_total' => $session->total_red_compra,
                'transferencia'    => $session->total_transferencia,
                'sobrante'         => max($diferencia, 0),
                'faltante'         => max(-$diferencia, 0),
            ]);

            ZetaReport::updateOrCreate(
                ['date' => $date, 'cashier_id' => $user->id],
                [
                    'total_z' => $session->total_caja_esperado ?? 0,
                    'net_cash' => $session->total_efectivo_cierre ?? 0,
                    'transfers' => $session->total_transferencia,
                    'pos_card_total' => $session->total_red_compra,
                    'status' => 'pending_review',
                ],
            );

            $tz = 'America/Santiago';
            $pdfData = [
                'sesion_id'       => $session->id,
                'cajero'          => $user->name,
                'apertura'        => $session->opened_at->timezone($tz)->format('d/m/Y H:i'),
                'cierre'          => now($tz)->format('d/m/Y H:i'),
                'opening'         => $session->opening_balance,
                'cash_sales'      => (int)($cashSales / 100),
                'ingresos'        => $totalIngresos,
                'retiros'         => $totalRetiros,
                'esperado'        => $esperado,
                'efectivo_cierre' => $session->total_efectivo_cierre,
                'red_compra'      => $session->total_red_compra,
                'transferencia'   => $session->total_transferencia,
            ];

            $pdfDir = storage_path('app/public/cierres');
            if (!is_dir($pdfDir)) {
                mkdir($pdfDir, 0755, true);
            }

            $pdf = Pdf::loadView('pdf.cierre-caja', $pdfData);
            $pdfPath = "cierres/cierre-caja-{$session->id}.pdf";
            $pdf->save(storage_path("app/public/{$pdfPath}"));

            return response()->json([
                'success' => true,
                'session' => [
                    'id'               => $session->id,
                    'closed_at'        => $session->closed_at,
                    'opening_balance'  => $session->opening_balance,
                    'total_efectivo_cierre' => $session->total_efectivo_cierre,
                    'total_red_compra' => $session->total_red_compra,
                    'total_transferencia' => $session->total_transferencia,
                    'total_ingresos'   => $totalIngresos,
                    'total_retiros'    => $totalRetiros,
                    'total_caja_esperado' => $session->total_caja_esperado,
                    'diferencia_descuadre' => $session->diferencia_descuadre,
                ],
                'summary' => [
                    'cashSales'     => (int)($cashSales / 100),
                    'ingresos'      => $totalIngresos,
                    'retiros'       => $totalRetiros,
                    'esperado'      => $esperado,
                    'declarado'     => $session->total_efectivo_cierre,
                    'diferencia'    => $session->total_efectivo_cierre - $esperado,
                ],
                'pdf_url' => asset("storage/{$pdfPath}"),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Datos inválidos.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error en cierre de caja: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'error' => 'Error interno al cerrar la caja. Intenta nuevamente.',
            ], 500);
        }
    }

    public function showCloseSummary(Request $request, CashSession $cashSession)
    {
        if ($cashSession->user_id !== $request->user()->id && !$request->user()->hasRole('admin')) {
            abort(403);
        }

        if (!$cashSession->closed_at) {
            return redirect()->route('admin.pos');
        }

        $date = $cashSession->date ?? $cashSession->opened_at->toDateString();

        $cashSales = Sale::where('user_id', $cashSession->user_id)
            ->whereDate('created_at', $date)
            ->sum('cash_amount');

        $movementQuery = CashMovement::where('user_id', $cashSession->user_id)
            ->whereDate('created_at', $date);
        $totalIngresos = (int) (clone $movementQuery)->where('type', 'ingreso')->sum('amount');
        $totalRetiros  = (int) (clone $movementQuery)->where('type', 'retiro')->sum('amount');

        $esperado = $cashSession->opening_balance + (int)($cashSales / 100) + $totalIngresos - $totalRetiros;

        return Inertia::render('admin/pos-close-summary', [
            'session' => $cashSession->load('user'),
            'summary' => [
                'cashSales'  => (int)($cashSales / 100),
                'ingresos'   => $totalIngresos,
                'retiros'    => $totalRetiros,
                'esperado'   => $esperado,
                'declarado'  => $cashSession->total_efectivo_cierre,
                'diferencia' => $cashSession->total_efectivo_cierre - $esperado,
            ],
            'pdf_url' => asset("storage/cierres/cierre-caja-{$cashSession->id}.pdf"),
        ]);
    }

    public function destroy(CashSession $cashSession): RedirectResponse
    {
        $cashSession->delete();

        return redirect()->route('admin.arqueo-caja.index')->with('success', 'Sesión eliminada.');
    }
}
