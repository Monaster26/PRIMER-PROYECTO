<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CashMovement;
use App\Models\CashSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CashMovementController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|in:ingreso,retiro',
            'amount' => 'required|integer|min:100',
            'description' => 'nullable|string|max:500',
        ]);

        $session = CashSession::where('user_id', $request->user()->id)
            ->whereNull('closed_at')
            ->latest('opened_at')
            ->first();

        $movement = CashMovement::create([
            'user_id' => $request->user()->id,
            'sesion_caja_id' => $session?->id,
            'type' => $validated['type'],
            'amount' => $validated['amount'],
            'description' => $validated['description'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'movement' => $movement,
            'message' => $validated['type'] === 'ingreso'
                ? 'Ingreso registrado: +$' . number_format($validated['amount'], 0, ',', '.')
                : 'Retiro registrado: -$' . number_format($validated['amount'], 0, ',', '.'),
        ]);
    }
}
