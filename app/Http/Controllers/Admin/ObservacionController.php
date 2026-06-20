<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Observacion;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ObservacionController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tipo_accion' => 'required|in:eliminar_item,limpiar_carrito',
            'producto_afectado' => 'nullable|string|max:255',
            'detalle' => 'required|string|max:5000',
        ]);

        $request->user()->observaciones()->create($validated);

        return response()->json([], 201);
    }

    public function index(Request $request)
    {
        $query = Observacion::with('user:id,name')->latest('created_at');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('fecha')) {
            $query->whereDate('created_at', $request->fecha);
        }

        return Inertia::render('admin/observaciones', [
            'observaciones' => $query->paginate(30)->withQueryString(),
            'users' => User::role('cashier')->get(['id', 'name']),
            'filters' => $request->only(['user_id', 'fecha']),
        ]);
    }
}
