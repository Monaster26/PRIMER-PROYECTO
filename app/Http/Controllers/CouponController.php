<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CouponController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Coupons/Index', [
            'coupons' => Coupon::withCount('usages')->latest()->paginate(20),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code'                   => 'required|string|max:50|unique:coupons,code|regex:/^[A-Z0-9_-]+$/',
            'description'            => 'nullable|string|max:500',
            'type'                   => 'required|in:fixed,percentage',
            'value'                  => 'required|integer|min:1',
            'min_order_amount'       => 'nullable|integer|min:0',
            'max_discount_amount'    => 'nullable|integer|min:0',
            'max_uses'               => 'nullable|integer|min:1',
            'max_uses_per_customer'  => 'required|integer|min:1',
            'is_active'              => 'boolean',
            'starts_at'              => 'nullable|date',
            'expires_at'             => 'nullable|date|after_or_equal:starts_at',
            'applicable_category_ids'=> 'nullable|array',
            'applicable_category_ids.*' => 'exists:categories,id',
        ]);

        // Validar que porcentaje no supere 100
        if ($validated['type'] === 'percentage' && $validated['value'] > 100) {
            return back()->withErrors(['value' => 'El porcentaje no puede superar 100.']);
        }

        Coupon::create($validated);

        return redirect()->route('coupons.index')->with('success', 'Cupón creado exitosamente.');
    }

    public function update(Request $request, Coupon $coupon)
    {
        $validated = $request->validate([
            'is_active'   => 'boolean',
            'expires_at'  => 'nullable|date',
            'max_uses'    => 'nullable|integer|min:1',
            'description' => 'nullable|string|max:500',
        ]);

        $coupon->update($validated);

        return back()->with('success', 'Cupón actualizado.');
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->delete();
        return redirect()->route('coupons.index')->with('success', 'Cupón eliminado.');
    }
}
