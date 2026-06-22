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
            'tipo_accion' => 'required|in:eliminar_item,limpiar_carrito,descuadre_apertura',
            'producto_afectado' => 'nullable|string|max:255',
            'detalle' => 'required|string|max:5000',
            'monto_diferencia' => 'nullable|integer',
        ]);

        $data = $validated;
        if ($data['tipo_accion'] === 'descuadre_apertura') {
            $data['read_at'] = null;
        }

        $request->user()->observaciones()->create($data);

        return response()->json([], 201);
    }

    public function unreadCount(): JsonResponse
    {
        return response()->json([
            'count' => Observacion::unread()->count(),
        ]);
    }

    public function markAsRead(Observacion $observacion)
    {
        $observacion->update(['read_at' => now()]);

        return redirect()->back();
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
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        return Inertia::render('admin/observaciones', [
            'observaciones' => $query->paginate(30)->withQueryString(),
            'users' => User::role('cashier')->get(['id', 'name']),
            'filters' => $request->only(['user_id', 'fecha', 'estado']),
        ]);
    }

    public function update(Request $request, Observacion $observacion)
    {
        $validated = $request->validate([
            'nota_admin' => 'nullable|string|max:5000',
        ]);

        $observacion->update([
            'nota_admin' => $validated['nota_admin'],
            'estado' => 'revisado',
            'revisado_at' => now(),
            'read_at' => now(),
        ]);

        return redirect()->back();
    }
}
