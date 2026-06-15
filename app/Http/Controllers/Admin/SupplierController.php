<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::orderBy('company_name')->paginate(30);

        return Inertia::render('admin/proveedores', [
            'suppliers' => $suppliers,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255|unique:suppliers,company_name',
            'category' => 'nullable|string|max:255',
            'contact_name' => 'nullable|string|max:255',
            'visit_day' => 'nullable|in:Lunes,Martes,Miércoles,Jueves,Viernes,Sábado,Domingo',
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
            'visit_day' => 'nullable|in:Lunes,Martes,Miércoles,Jueves,Viernes,Sábado,Domingo',
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
