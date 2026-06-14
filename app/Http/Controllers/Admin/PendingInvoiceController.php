<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PendingInvoice;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PendingInvoiceController extends Controller
{
    public function index()
    {
        $invoices = PendingInvoice::with('supplier')->orderBy('due_date')->paginate(30);
        $suppliers = Supplier::orderBy('company_name')->get();

        return Inertia::render('admin/facturas-pendientes', [
            'invoices' => $invoices,
            'suppliers' => $suppliers,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'invoice_number' => 'nullable|string|max:255',
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:issue_date',
            'total_amount' => 'required|numeric|min:0',
            'status' => 'required|in:pending,partially_paid,paid',
        ]);

        PendingInvoice::create($validated);

        return redirect()->route('admin.facturas-pendientes.index')->with('success', 'Factura registrada.');
    }

    public function update(Request $request, PendingInvoice $pendingInvoice): RedirectResponse
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'invoice_number' => 'nullable|string|max:255',
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:issue_date',
            'total_amount' => 'required|numeric|min:0',
            'status' => 'required|in:pending,partially_paid,paid',
        ]);

        $pendingInvoice->update($validated);

        return redirect()->route('admin.facturas-pendientes.index')->with('success', 'Factura actualizada.');
    }

    public function destroy(PendingInvoice $pendingInvoice): RedirectResponse
    {
        $pendingInvoice->delete();

        return redirect()->route('admin.facturas-pendientes.index')->with('success', 'Factura eliminada.');
    }
}
