<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\PendingInvoice;
use App\Models\PendingInvoiceItem;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\StockMovement;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PendingInvoiceController extends Controller
{
    public function index(Request $request): Response
    {
        $query = PendingInvoice::with('supplier', 'items.product');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('issue_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('issue_date', '<=', $request->date_to);
        }
        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('invoice_number', 'like', "%{$term}%")
                  ->orWhereHas('supplier', fn($sq) => $sq->where('company_name', 'like', "%{$term}%"));
            });
        }

        $invoices = $query->orderBy('created_at', 'desc')->paginate(30)->withQueryString();
        $suppliers = Supplier::orderBy('company_name')->get(['id', 'company_name']);
        $products = Product::active()->orderBy('name')->get(['id', 'name', 'sku', 'cost_price', 'stock', 'category_id', 'sub_category']);
        $categories = Category::with('children')->roots()->ordered()->get(['id', 'name', 'slug']);

        return Inertia::render('admin/facturas-pendientes', [
            'invoices'   => $invoices,
            'suppliers'  => $suppliers,
            'products'   => $products,
            'categories' => $categories,
            'filters'    => $request->only(['status', 'supplier_id', 'date_from', 'date_to', 'search']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'supplier_id'          => 'required|exists:suppliers,id',
            'invoice_number'       => 'nullable|string|max:255',
            'issue_date'           => 'required|date',
            'due_date'             => 'required|date|after_or_equal:issue_date',
            'delivery_date'        => 'nullable|date',
            'notes'                => 'nullable|string|max:500',
            'items'                => 'required|array|min:1',
            'items.*.product_id'   => 'nullable|exists:products,id',
            'items.*.product_name' => 'required|string|max:255',
            'items.*.category_name'=> 'nullable|string|max:255',
            'items.*.subcategory_name'=> 'nullable|string|max:255',
            'items.*.unit_cost'    => 'required|numeric|min:0',
            'items.*.quantity_ordered' => 'required|integer|min:1',
            'items.*.is_new_product' => 'required|boolean',
        ]);

        $invoice = DB::transaction(function () use ($validated) {
            $totalAmount = 0;

            $invoice = PendingInvoice::create([
                'supplier_id'      => $validated['supplier_id'],
                'invoice_number'   => $validated['invoice_number'] ?? null,
                'issue_date'       => $validated['issue_date'],
                'due_date'         => $validated['due_date'],
                'delivery_date'    => $validated['delivery_date'] ?? null,
                'notes'            => $validated['notes'] ?? null,
                'total_amount'     => 0,
                'status'           => 'pending',
            ]);

            foreach ($validated['items'] as $item) {
                $unitCost = (int) round((float) $item['unit_cost'] * 100);
                $previousCost = 0;

                if (!empty($item['product_id'])) {
                    $product = Product::find($item['product_id']);
                    $previousCost = $product ? $product->cost_price : 0;
                }

                $lineTotal = $unitCost * (int) $item['quantity_ordered'];
                $totalAmount += $lineTotal;

                PendingInvoiceItem::create([
                    'pending_invoice_id' => $invoice->id,
                    'product_id'         => $item['product_id'] ?? null,
                    'product_name'       => $item['product_name'],
                    'category_name'      => $item['category_name'] ?? null,
                    'subcategory_name'   => $item['subcategory_name'] ?? null,
                    'unit_cost'          => $unitCost,
                    'previous_cost'      => $previousCost,
                    'quantity_ordered'   => (int) $item['quantity_ordered'],
                    'is_new_product'     => (bool) ($item['is_new_product'] ?? false),
                ]);
            }

            $invoice->update(['total_amount' => $totalAmount]);

            return $invoice;
        });

        return redirect()->route('admin.facturas-pendientes.index')
            ->with('success', 'Factura registrada.');
    }

    public function update(Request $request, PendingInvoice $pendingInvoice): RedirectResponse
    {
        if ($pendingInvoice->status !== 'pending') {
            return back()->with('error', 'Solo se pueden editar facturas pendientes.');
        }

        $validated = $request->validate([
            'supplier_id'              => 'required|exists:suppliers,id',
            'invoice_number'           => 'nullable|string|max:255',
            'issue_date'               => 'required|date',
            'due_date'                 => 'required|date|after_or_equal:issue_date',
            'delivery_date'            => 'nullable|date',
            'notes'                    => 'nullable|string|max:500',
            'items'                    => 'required|array|min:1',
            'items.*.id'               => 'nullable|exists:pending_invoice_items,id',
            'items.*.product_id'       => 'nullable|exists:products,id',
            'items.*.product_name'     => 'required|string|max:255',
            'items.*.category_name'    => 'nullable|string|max:255',
            'items.*.subcategory_name' => 'nullable|string|max:255',
            'items.*.unit_cost'        => 'required|numeric|min:0',
            'items.*.quantity_ordered' => 'required|integer|min:1',
            'items.*.is_new_product'   => 'required|boolean',
        ]);

        DB::transaction(function () use ($validated, $pendingInvoice) {
            $pendingInvoice->update([
                'supplier_id'    => $validated['supplier_id'],
                'invoice_number' => $validated['invoice_number'] ?? null,
                'issue_date'     => $validated['issue_date'],
                'due_date'       => $validated['due_date'],
                'delivery_date'  => $validated['delivery_date'] ?? null,
                'notes'          => $validated['notes'] ?? null,
            ]);

            $newItemIds = collect($validated['items'])->pluck('id')->filter();
            $pendingInvoice->items()->whereNotIn('id', $newItemIds)->delete();

            $totalAmount = 0;

            foreach ($validated['items'] as $item) {
                $unitCost = (int) round((float) $item['unit_cost'] * 100);
                $previousCost = 0;

                if (!empty($item['product_id'])) {
                    $product = Product::find($item['product_id']);
                    $previousCost = $product ? $product->cost_price : 0;
                }

                $lineTotal = $unitCost * (int) $item['quantity_ordered'];
                $totalAmount += $lineTotal;

                $existingItem = !empty($item['id'])
                    ? $pendingInvoice->items()->find($item['id'])
                    : null;

                $data = [
                    'product_id'       => $item['product_id'] ?? null,
                    'product_name'     => $item['product_name'],
                    'category_name'    => $item['category_name'] ?? null,
                    'subcategory_name' => $item['subcategory_name'] ?? null,
                    'unit_cost'        => $unitCost,
                    'previous_cost'    => $previousCost,
                    'quantity_ordered' => (int) $item['quantity_ordered'],
                    'is_new_product'   => (bool) ($item['is_new_product'] ?? false),
                ];

                if ($existingItem) {
                    $existingItem->update($data);
                } else {
                    $data['pending_invoice_id'] = $pendingInvoice->id;
                    PendingInvoiceItem::create($data);
                }
            }

            $pendingInvoice->update(['total_amount' => $totalAmount]);
        });

        return redirect()->route('admin.facturas-pendientes.index')
            ->with('success', 'Factura actualizada.');
    }

    public function receive(Request $request, PendingInvoice $pendingInvoice): RedirectResponse
    {
        if ($pendingInvoice->status !== 'pending') {
            return back()->with('error', 'La factura ya fue ' . $pendingInvoice->status . '.');
        }

        $validated = $request->validate([
            'items'                     => 'required|array',
            'items.*.id'                => 'required|exists:pending_invoice_items,id',
            'items.*.quantity_received' => 'required|integer|min:0',
            'items.*.expiration_date'   => 'nullable|date|after_or_equal:today',
            'items.*.barcode'           => 'nullable|string|max:255',
            'items.*.sale_price'        => 'nullable|numeric|min:0',
            'items.*.cost_price'        => 'nullable|integer|min:0',
            'notes'                     => 'nullable|string|max:500',
        ]);

        foreach ($validated['items'] as $i => $item) {
            if (($item['quantity_received'] ?? 0) > 0 && empty($item['expiration_date'])) {
                return back()->withErrors(["items.{$i}.expiration_date" => 'La fecha de vencimiento es obligatoria cuando se reciben productos.'])->withInput();
            }
        }

        $pendingInvoice->load('items.product');

        DB::transaction(function () use ($validated, $pendingInvoice) {
            $receivedIds = collect($validated['items'])->pluck('id');
            $batchSeq = ProductBatch::where('batch_number', 'like', 'LOTE-' . now()->format('Ymd') . '-%')->count();

            foreach ($pendingInvoice->items as $item) {
                $incomingIdx = $receivedIds->search($item->id);
                $incoming = $incomingIdx !== false ? ($validated['items'][$incomingIdx] ?? null) : null;
                $receivedQty = $incoming ? (int) $incoming['quantity_received'] : $item->quantity_ordered;

                if ($receivedQty > 0) {
                    if ($item->is_new_product) {
                        $sku = 'TEMP-' . Str::random(8);
                        $salePrice = $incoming && isset($incoming['sale_price'])
                            ? (int) round((float) $incoming['sale_price'] * 100)
                            : (int) round($item->unit_cost / 0.7);

                        $categoryId = null;
                        if ($item->category_name) {
                            $cat = Category::where('name', $item->category_name)->first();
                            $categoryId = $cat?->id;
                        }
                        $categoryId ??= Category::first()?->id;

                        $product = Product::create([
                            'name'          => $item->product_name,
                            'slug'          => Str::slug($item->product_name) . '-' . Str::random(4),
                            'sku'           => $sku,
                            'category_id'   => $categoryId,
                            'cost_price'    => $item->unit_cost,
                            'price'         => $salePrice,
                            'stock'         => 0,
                            'is_active'     => true,
                        ]);

                        $item->product_id = $product->id;
                        $item->save();
                    }

                    if ($item->product_id) {
                        $product = Product::find($item->product_id);

                        if ($product) {
                            // auto-generate batch number
                            $batchSeq++;
                            $batchNumber = 'LOTE-' . now()->format('Ymd') . '-' . str_pad($batchSeq, 3, '0', STR_PAD_LEFT);

                            $expirationDate = $incoming['expiration_date'] ?? null;

                            // create batch record
                            ProductBatch::create([
                                'product_id'      => $product->id,
                                'batch_number'    => $batchNumber,
                                'quantity'        => $receivedQty,
                                'cost_price'      => $item->unit_cost,
                                'expiration_date' => $expirationDate,
                                'received_at'     => now(),
                                'notes'           => "Recepción factura #{$pendingInvoice->id}",
                            ]);

                            StockMovement::record(
                                product: $product,
                                quantityChange: $receivedQty,
                                type: 'purchase',
                                reference: $pendingInvoice,
                                unitCost: $item->unit_cost,
                                notes: "Recepción factura #{$pendingInvoice->id} (lote {$batchNumber})",
                                employeeId: auth()->id(),
                            );

                            $updates = [];
                            $newCost = $incoming && isset($incoming['cost_price'])
                                ? (int) $incoming['cost_price']
                                : $item->unit_cost;
                            if ($newCost > 0 && $newCost !== $product->cost_price) {
                                $updates['cost_price'] = $newCost;
                            }
                            if ($incoming && !empty($incoming['barcode'])) {
                                $updates['barcode'] = $incoming['barcode'];
                            }
                            if ($incoming && isset($incoming['sale_price']) && $incoming['sale_price'] > 0) {
                                $updates['price'] = (int) round((float) $incoming['sale_price'] * 100);
                            }
                            if (!empty($updates)) {
                                $product->update($updates);
                            }
                        }
                    }
                }

                $item->update(['quantity_received' => $receivedQty]);
            }

            $notes = $validated['notes'] ?? null;
            $existing = $pendingInvoice->notes;
            $pendingInvoice->update([
                'status'      => 'received',
                'received_at' => now(),
                'notes'       => $notes ? ($existing ? $existing . "\n" . $notes : $notes) : $existing,
            ]);
        });

        return redirect()->route('admin.facturas-pendientes.index')
            ->with('success', 'Factura recibida. Stock actualizado.');
    }

    public function markPaid(PendingInvoice $pendingInvoice): RedirectResponse
    {
        if ($pendingInvoice->status !== 'received') {
            return back()->with('error', 'Solo facturas recibidas pueden marcarse como pagadas.');
        }

        $pendingInvoice->update(['status' => 'paid']);

        return redirect()->route('admin.facturas-pendientes.index')
            ->with('success', 'Factura marcada como pagada.');
    }

    public function destroy(PendingInvoice $pendingInvoice): RedirectResponse
    {
        if ($pendingInvoice->status !== 'pending') {
            return back()->with('error', 'No se puede eliminar una factura que ya fue ' . $pendingInvoice->status . '.');
        }

        $pendingInvoice->delete();

        return redirect()->route('admin.facturas-pendientes.index')->with('success', 'Factura eliminada.');
    }
}
