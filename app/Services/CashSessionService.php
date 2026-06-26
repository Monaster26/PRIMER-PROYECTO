<?php

namespace App\Services;

use App\Models\CashMovement;
use App\Models\CashSession;
use App\Models\ControlZeta;
use App\Models\Sale;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;

class CashSessionService
{
    public function getCashMovementsSummary(): array
    {
        $today = now()->toDateString();
        $movementsQuery = CashMovement::whereDate('created_at', $today);

        return [
            'total_ingresos' => (int) (clone $movementsQuery)->where('type', 'ingreso')->sum('amount'),
            'total_retiros'  => (int) (clone $movementsQuery)->where('type', 'retiro')->sum('amount'),
            'count'          => (clone $movementsQuery)->count(),
        ];
    }

    public function findOpenSession(User $user): ?CashSession
    {
        return CashSession::where('user_id', $user->id)
            ->whereNull('closed_at')
            ->latest('opened_at')
            ->first();
    }

    public function buildCierreDesglose(array $data): array
    {
        $bills = ['20k', '10k', '5k', '2k', '1k'];
        $coins = ['500' => 500, '100' => 100, '50' => 50, '10' => 10];
        $result = [];

        foreach ($bills as $denom) {
            $result[$denom] = (int) ($data["cant_{$denom}_cierre"] ?? 0);
        }

        $hasCoins = isset($data['coin_500']) || isset($data['coin_100'])
                 || isset($data['coin_50'])  || isset($data['coin_10']);

        foreach ($coins as $denom => $divisor) {
            $result[$denom] = $hasCoins
                ? (int) round(($data["coin_{$denom}"] ?? 0) / $divisor)
                : (int) ($data["cant_{$denom}_cierre"] ?? 0);
        }

        return $result;
    }

    public function calcularResumenFinanciero(CashSession $session, ?array $cierreDesglose = null): array
    {
        $endDate = $session->closed_at ?? now();

        $cashSales = Sale::where('user_id', $session->user_id)
            ->whereBetween('created_at', [$session->opened_at, $endDate])
            ->sum('cash_amount');

        $movementQuery = CashMovement::where('user_id', $session->user_id)
            ->where('sesion_caja_id', $session->id);
        $totalIngresos = (int) (clone $movementQuery)->where('type', 'ingreso')->sum('amount');
        $totalRetiros  = (int) (clone $movementQuery)->where('type', 'retiro')->sum('amount');

        $totalEfectivoCierre = $cierreDesglose
            ? self::computeTotalEfectivo($cierreDesglose)
            : $session->total_efectivo_cierre;

        $cashSalesInt = (int) ($cashSales / 100);
        $esperado = $session->opening_balance + $cashSalesInt + $totalIngresos - $totalRetiros;
        $diferencia = $totalEfectivoCierre - $esperado;

        return [
            'cashSales'            => $cashSalesInt,
            'totalIngresos'        => $totalIngresos,
            'totalRetiros'         => $totalRetiros,
            'esperado'             => $esperado,
            'diferencia'           => $diferencia,
            'totalEfectivoCierre'  => $totalEfectivoCierre,
        ];
    }

    private static function computeTotalEfectivo(array $desglose): int
    {
        $denoms = ['20k' => 20000, '10k' => 10000, '5k' => 5000, '2k' => 2000, '1k' => 1000,
                   '500' => 500, '100' => 100, '50' => 50, '10' => 10];
        $total = 0;
        foreach ($denoms as $key => $value) {
            $total += ($desglose[$key] ?? 0) * $value;
        }
        return $total;
    }

    public function actualizarControlZeta(CashSession $session, array $financial): void
    {
        ControlZeta::where('cash_session_id', $session->id)->update([
            'esperado_caja'    => $financial['esperado'],
            'efectivo_neto'    => ($session->total_efectivo_cierre + $financial['totalRetiros']) - $session->opening_balance,
            'red_compra_total' => $session->total_red_compra,
            'transferencia'    => $session->total_transferencia,
            'sobrante'         => max($financial['diferencia'], 0),
            'faltante'         => max(-$financial['diferencia'], 0),
        ]);
    }

    public function generarPdfCierre(CashSession $session, User $user, array $financial): string
    {
        $tz = 'America/Santiago';

        $pdfData = [
            'sesion_id'       => $session->id,
            'cajero'          => $user->name,
            'apertura'        => $session->opened_at->timezone($tz)->format('d/m/Y H:i'),
            'cierre'          => now($tz)->format('d/m/Y H:i'),
            'opening'         => $session->opening_balance,
            'cash_sales'      => $financial['cashSales'],
            'ingresos'        => $financial['totalIngresos'],
            'retiros'         => $financial['totalRetiros'],
            'esperado'        => $financial['esperado'],
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

        return asset("storage/{$pdfPath}");
    }
}
