<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ControlZeta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ControlZetaController extends Controller
{
    public function index(Request $request)
    {
        $query = ControlZeta::query();

        if ($request->filled('mes')) {
            $query->whereMonth('fecha_apertura', $request->mes);
        }
        if ($request->filled('anio')) {
            $query->whereYear('fecha_apertura', $request->anio);
        }

        return Inertia::render('admin/z-mensual', [
            'registros' => $query->orderBy('fecha_apertura')->get(),
            'filters'   => $request->only(['mes', 'anio']),
        ]);
    }

    public function update(Request $request, ControlZeta $controlZeta): JsonResponse
    {
        $validated = $request->validate([
            'red_compra_neto' => 'integer|min:0',
            'porcentaje_banco' => 'integer|min:0',
            'observaciones' => 'nullable|string|max:5000',
        ]);

        $controlZeta->update($validated);

        return response()->json(['success' => true]);
    }
}
