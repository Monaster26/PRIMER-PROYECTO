<?php

namespace App\Http\Controllers;

use App\Models\CreditInvoice;
use App\Models\CreditInvoiceItem;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CreditInvoiceController extends Controller
{
    public function index(Request $request): Response
    {
        $query = CreditInvoice::with(['customer', 'items.product'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $query->where('customer_name', 'like', '%' . $request->search . '%');
        }

        $invoices = $query->paginate(20)->withQueryString();

        $summary = [
            'total_pending' => CreditInvoice::where('status', 'pending')->sum('total_amount'),
            'total_paid'    => CreditInvoice::where('status', 'paid')->sum('total_amount'),
            'count_pending' => CreditInvoice::where('status', 'pending')->count(),
        ];

        $customers = Customer::orderBy('name')->get(['id', 'name', 'phone']);
        $products  = Product::active()->orderBy('name')->get(['id', 'name', 'price', 'sku']);

        return Inertia::render('CreditInvoices/Index', [
            'invoices'  => $invoices,
            'summary'   => $summary,
            'customers' => $customers,
            'products'  => $products,
            'filters'   => $request->only(['status', 'search']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'customer_id'   => 'nullable|exists:customers,id',
            'customer_name' => 'required|string|max:150',
            'items'         => 'required|array|min:1',
            'items.*.product_id'  => 'required|exists:products,id',
            'items.*.quantity'    => 'required|integer|min:1',
            'items.*.unit_price'  => 'required|integer|min:0',
        ]);

        $invoice = CreditInvoice::create([
            'customer_id'   => $data['customer_id'] ?? null,
            'customer_name' => $data['customer_name'],
            'status'        => 'pending',
        ]);

        foreach ($data['items'] as $item) {
            $invoice->items()->create([
                'product_id' => $item['product_id'],
                'quantity'   => $item['quantity'],
                'unit_price' => $item['unit_price'],
                // line_total computed automatically in model boot
            ]);
        }

        return back()->with('success', 'Factura de crédito creada correctamente.');
    }

    public function markPaid(CreditInvoice $invoice): RedirectResponse
    {
        $invoice->update(['status' => 'paid']);

        return back()->with('success', "Crédito de {$invoice->customer_name} marcado como pagado.");
    }

    public function destroy(CreditInvoice $invoice): RedirectResponse
    {
        $invoice->delete();

        return back()->with('success', 'Factura eliminada.');
    }
}
