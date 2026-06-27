<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CashSession;
use App\Models\User;
use App\Services\CashSessionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CashSessionController extends Controller
{
    public function __construct(
        protected CashSessionService $cashSessionService
    ) {}

    public function index(Request $request)
    {
        $query = CashSession::with('user');

        if ($request->user() && !$request->user()->hasRole('admin')) {
            $query->where('user_id', $request->user()->id);
        }

        $filters = [
            'dia'        => $request->integer('dia'),
            'mes'        => $request->integer('mes'),
            'anio'       => $request->integer('anio'),
            'cashier_id' => $request->integer('cashier_id'),
        ];

        if ($filters['cashier_id']) {
            $query->where('user_id', $filters['cashier_id']);
        }

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

        return Inertia::render('admin/arqueo-caja', [
            'sessions' => $sessions,
            'cashiers' => $cashiers,
            'cashMovements' => $this->cashSessionService->getCashMovementsSummary(),
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

        $user = User::findOrFail($validated['user_id']);
        $openSession = $this->cashSessionService->findOpenSession($user);
        if ($openSession) {
            return back()->withErrors(['error' => 'Ya existe una sesión abierta para este usuario.'])->withInput();
        }

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

        $cierreDesglose = $this->cashSessionService->buildCierreDesglose($validated);

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

        return redirect()->route('admin.arqueo-caja.index')->with('success', 'Sesión cerrada correctamente.');
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

            $session = $this->cashSessionService->findOpenSession($user);

            if (!$session) {
                return response()->json(['error' => 'No hay una sesión abierta para cerrar.'], 404);
            }

            $cierreDesglose = $this->cashSessionService->buildCierreDesglose($validated);
            $financial = $this->cashSessionService->calcularResumenFinanciero($session, $cierreDesglose);

            $session->update([
                'closed_at' => now(),
                'cant_20k_cierre' => $cierreDesglose['20k'],
                'cant_10k_cierre' => $cierreDesglose['10k'],
                'cant_5k_cierre'  => $cierreDesglose['5k'],
                'cant_2k_cierre'  => $cierreDesglose['2k'],
                'cant_1k_cierre'  => $cierreDesglose['1k'],
                'cant_500_cierre' => $cierreDesglose['500'],
                'cant_100_cierre' => $cierreDesglose['100'],
                'cant_50_cierre'  => $cierreDesglose['50'],
                'cant_10_cierre'  => $cierreDesglose['10'],
                'total_red_compra' => $validated['total_red_compra'],
                'total_transferencia' => $validated['total_transferencia'],
                'total_ingresos' => $financial['totalIngresos'],
                'total_retiros'  => $financial['totalRetiros'],
                'cierre_desglose' => $cierreDesglose,
            ]);

            $session->refresh();

            $this->cashSessionService->actualizarControlZeta($session, $financial);

            $pdfUrl = $this->cashSessionService->generarPdfCierre($session, $user, $financial);

            return response()->json([
                'success' => true,
                'session' => [
                    'id'               => $session->id,
                    'closed_at'        => $session->closed_at,
                    'opening_balance'  => $session->opening_balance,
                    'total_efectivo_cierre' => $session->total_efectivo_cierre,
                    'total_red_compra' => $session->total_red_compra,
                    'total_transferencia' => $session->total_transferencia,
                    'total_ingresos'   => $financial['totalIngresos'],
                    'total_retiros'    => $financial['totalRetiros'],
                    'total_caja_esperado' => $session->total_caja_esperado,
                    'diferencia_descuadre' => $session->diferencia_descuadre,
                ],
                'summary' => [
                    'cashSales'     => $financial['cashSales'],
                    'ingresos'      => $financial['totalIngresos'],
                    'retiros'       => $financial['totalRetiros'],
                    'esperado'      => $financial['esperado'],
                    'declarado'     => $session->total_efectivo_cierre,
                    'diferencia'    => $financial['diferencia'],
                ],
                'pdf_url' => $pdfUrl,
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

        $financial = $this->cashSessionService->calcularResumenFinanciero($cashSession);

        return Inertia::render('admin/pos-close-summary', [
            'session' => $cashSession->load('user'),
            'summary' => [
                'cashSales'  => $financial['cashSales'],
                'ingresos'   => $financial['totalIngresos'],
                'retiros'    => $financial['totalRetiros'],
                'esperado'   => $financial['esperado'],
                'declarado'  => $cashSession->total_efectivo_cierre,
                'diferencia' => $financial['diferencia'],
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
