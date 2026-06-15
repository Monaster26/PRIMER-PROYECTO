<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Provider;
use App\Models\ProductProviderPrice;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ProviderController extends Controller
{
    public function index(): Response
    {
        $providers = Provider::withCount(['expenses', 'products'])
            ->orderBy('name')
            ->get();

        return Inertia::render('Providers/Index', [
            'providers' => $providers,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'         => 'required|string|max:150',
            'contact_info' => 'nullable|string|max:500',
        ]);

        Provider::create($data);

        return back()->with('success', 'Proveedor creado correctamente.');
    }

    public function update(Request $request, Provider $provider): RedirectResponse
    {
        $data = $request->validate([
            'name'         => 'required|string|max:150',
            'contact_info' => 'nullable|string|max:500',
        ]);

        $provider->update($data);

        return back()->with('success', 'Proveedor actualizado correctamente.');
    }

    public function destroy(Provider $provider): RedirectResponse
    {
        $provider->delete();

        return back()->with('success', 'Proveedor eliminado.');
    }

    // ─── Price Matrix ────────────────────────────────────────────

    public function priceMatrix(): Response
    {
        // All products with their full supplier price matrix
        $products = Product::with(['providerPrices.provider'])
            ->active()
            ->orderBy('name')
            ->get()
            ->map(function ($product) {
                return [
                    'id'                 => $product->id,
                    'name'               => $product->name,
                    'sku'                => $product->sku,
                    'barcode'            => $product->barcode,
                    'cost_price'         => $product->cost_price,
                    'min_price'          => $product->min_price,
                    'max_price'          => $product->max_price,
                    'savings_gap'        => $product->savings_gap,
                    'suggested_supplier' => $product->suggested_supplier?->only(['id', 'name']),
                    'provider_prices'    => $product->providerPrices->map(fn($pp) => [
                        'id'          => $pp->id,
                        'provider_id' => $pp->provider_id,
                        'provider'    => $pp->provider->name,
                        'price'       => $pp->price,
                    ]),
                ];
            });

        $providers = Provider::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Providers/PriceMatrix', [
            'products'  => $products,
            'providers' => $providers,
        ]);
    }

    public function upsertPrice(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id'  => 'required|exists:products,id',
            'provider_id' => 'required|exists:providers,id',
            'price'       => 'required|integer|min:0',
        ]);

        ProductProviderPrice::updateOrCreate(
            ['product_id' => $data['product_id'], 'provider_id' => $data['provider_id']],
            ['price'      => $data['price']]
        );

        return back()->with('success', 'Precio actualizado en la matriz.');
    }

    public function deletePrice(ProductProviderPrice $price): RedirectResponse
    {
        $price->delete();

        return back()->with('success', 'Precio eliminado de la matriz.');
    }
}
