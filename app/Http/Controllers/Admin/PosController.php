<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalePayment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PosController extends Controller
{
    public function index()
    {
        $products = Product::active()
            ->orderBy('name')
            ->get(['id', 'name', 'sku', 'barcode', 'price', 'stock', 'unit', 'image_path']);

        return Inertia::render('admin/pos', [
            'products' => $products,
        ]);
    }

    public function lookup(string $code): JsonResponse
    {
        $product = Product::active()
            ->where(function ($q) use ($code) {
                $q->where('barcode', $code)
                  ->orWhere('sku', $code);
            })
            ->first(['id', 'name', 'sku', 'barcode', 'price', 'stock', 'unit', 'image_path']);

        if (!$product) {
            return response()->json(['error' => 'Producto no encontrado'], 404);
        }

        return response()->json(['product' => $product]);
    }

    public function checkout(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'payments' => 'required|array|min:1',
            'payments.*.method' => 'required|in:cash,card,transfer',
            'payments.*.amount' => 'required|numeric|min:0',
        ]);

        $cashAmount = 0;
        $cardAmount = 0;
        $transferAmount = 0;

        foreach ($validated['payments'] as $payment) {
            $amountCents = (int) round($payment['amount'] * 100);
            match ($payment['method']) {
                'cash' => $cashAmount += $amountCents,
                'card' => $cardAmount += $amountCents,
                'transfer' => $transferAmount += $amountCents,
            };
        }

        $totalCents = 0;

        $sale = Sale::create([
            'user_id' => auth()->id(),
            'type' => 'pos',
            'cash_amount' => $cashAmount,
            'card_amount' => $cardAmount,
            'transfer_amount' => $transferAmount,
            'total' => 0,
        ]);

        foreach ($validated['items'] as $item) {
            $product = Product::findOrFail($item['product_id']);

            if ($product->stock < $item['quantity']) {
                return response()->json([
                    'error' => "Stock insuficiente para {$product->name} (disponible: {$product->stock})",
                ], 422);
            }

            $lineTotal = $product->price * $item['quantity'];

            SaleItem::create([
                'sale_id' => $sale->id,
                'product_id' => $product->id,
                'quantity' => $item['quantity'],
                'price' => $product->price,
                'total_line' => $lineTotal,
            ]);

            $product->decrement('stock', $item['quantity']);
            $totalCents += $lineTotal;
        }

        $sale->update(['total' => $totalCents]);

        foreach ($validated['payments'] as $payment) {
            SalePayment::create([
                'sale_id' => $sale->id,
                'method' => $payment['method'],
                'amount' => $payment['amount'],
            ]);
        }

        return response()->json([
            'success' => "Venta #{$sale->id} registrada correctamente.",
            'sale_id' => $sale->id,
        ]);
    }
}
