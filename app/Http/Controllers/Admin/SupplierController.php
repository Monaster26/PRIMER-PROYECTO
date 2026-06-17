<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');

        $query = Supplier::orderByRaw("CASE WHEN visit_day IS NULL THEN 1 ELSE 0 END")
            ->orderByRaw("FIELD(visit_day, 'Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo','Todos los días')")
            ->orderBy('company_name');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('company_name', 'like', "%{$search}%")
                  ->orWhere('contact_name', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%")
                  ->orWhere('visit_day', 'like', "%{$search}%");
            });
        }

        $suppliers = $query->paginate(30);

        return Inertia::render('admin/proveedores', [
            'suppliers' => $suppliers,
            'search' => $search,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255|unique:suppliers,company_name',
            'category' => 'nullable|string|max:255',
            'contact_name' => 'nullable|string|max:255',
            'visit_day' => 'nullable|in:Lunes,Martes,Miércoles,Jueves,Viernes,Sábado,Domingo,Todos los días',
            'delivery_time_hours' => 'required|integer|min:1',
            'minimum_order_amount' => 'required|numeric|min:0',
        ]);

        Supplier::create($validated);

        return redirect()->route('admin.proveedores.index')->with('success', 'Proveedor registrado.');
    }

    public function update(Request $request, Supplier $supplier): RedirectResponse
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255|unique:suppliers,company_name,' . $supplier->id,
            'category' => 'nullable|string|max:255',
            'contact_name' => 'nullable|string|max:255',
            'visit_day' => 'nullable|in:Lunes,Martes,Miércoles,Jueves,Viernes,Sábado,Domingo,Todos los días',
            'delivery_time_hours' => 'required|integer|min:1',
            'minimum_order_amount' => 'required|numeric|min:0',
        ]);

        $supplier->update($validated);

        return redirect()->route('admin.proveedores.index')->with('success', 'Proveedor actualizado.');
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        $supplier->delete();

        return redirect()->route('admin.proveedores.index')->with('success', 'Proveedor eliminado.');
    }
}
