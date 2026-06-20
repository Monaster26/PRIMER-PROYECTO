<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CashSession;
use App\Models\ControlZeta;
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

        $user = auth()->user();

        // Admin siempre pasa sin bloqueo; cajero debe tener caja abierta
        $hasOpenSession = $user->hasRole('admin') ? true :
            CashSession::where('user_id', $user->id)
                ->whereNull('closed_at')
                ->latest('opened_at')
                ->exists();

        return Inertia::render('admin/pos', [
            'products' => $products,
            'hasOpenSession' => $hasOpenSession,
        ]);
    }

    public function openSession(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'cant_20k_apertura' => 'required|integer|min:0',
            'cant_10k_apertura' => 'required|integer|min:0',
            'cant_5k_apertura' => 'required|integer|min:0',
            'cant_2k_apertura' => 'required|integer|min:0',
            'cant_1k_apertura' => 'required|integer|min:0',
            'cant_500_apertura' => 'required|integer|min:0',
            'cant_100_apertura' => 'required|integer|min:0',
            'cant_50_apertura' => 'required|integer|min:0',
            'cant_10_apertura' => 'required|integer|min:0',
        ]);

        // Calcular total en servidor (seguridad: no confiar en el cliente)
        $total =
            ($validated['cant_20k_apertura'] * 20000) +
            ($validated['cant_10k_apertura'] * 10000) +
            ($validated['cant_5k_apertura'] * 5000) +
            ($validated['cant_2k_apertura'] * 2000) +
            ($validated['cant_1k_apertura'] * 1000) +
            ($validated['cant_500_apertura'] * 500) +
            ($validated['cant_100_apertura'] * 100) +
            ($validated['cant_50_apertura'] * 50) +
            ($validated['cant_10_apertura'] * 10);

        $aperturaDesglose = [
            '20k' => $validated['cant_20k_apertura'],
            '10k' => $validated['cant_10k_apertura'],
            '5k'  => $validated['cant_5k_apertura'],
            '2k'  => $validated['cant_2k_apertura'],
            '1k'  => $validated['cant_1k_apertura'],
            '500' => $validated['cant_500_apertura'],
            '100' => $validated['cant_100_apertura'],
            '50'  => $validated['cant_50_apertura'],
            '10'  => $validated['cant_10_apertura'],
        ];

        $session = CashSession::create([
            'user_id'          => $request->user()->id,
            'opened_at'        => now(),
            'opening_balance'  => $total,
            'apertura_desglose'=> $aperturaDesglose,
            ...$validated,
        ]);

        // total_efectivo_apertura se calcula automáticamente en el modelo (saving event)

        ControlZeta::create([
            'cash_session_id' => $session->id,
            'fecha_apertura'  => now(),
            'cajero'          => $request->user()->name,
        ]);

        return response()->json([
            'success' => true,
            'session' => [
                'id' => $session->id,
                'opening_balance' => $session->opening_balance,
                'opened_at' => $session->opened_at,
            ],
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
