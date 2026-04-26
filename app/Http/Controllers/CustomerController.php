<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController extends Controller
{
    public function index(Request $request): Response
    {
        $customers = Customer::when($request->search, fn($q) => $q->search($request->search))
            ->when($request->boolean('vip'), fn($q) => $q->vip())
            ->orderByDesc('total_spent')
            ->paginate(25)
            ->withQueryString();

        return Inertia::render('Customers/Index', [
            'customers' => $customers,
            'filters'   => $request->only(['search', 'vip']),
        ]);
    }

    public function show(Customer $customer): Response
    {
        return Inertia::render('Customers/Show', [
            'customer' => $customer->load(['orders' => fn($q) => $q->with('items')->latest()->limit(10)]),
        ]);
    }

    /**
     * Registro rápido de cliente desde el POS (un clic).
     */
    public function quickRegister(Request $request): JsonResponse
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'phone'          => 'nullable|string|max:20',
            'document_number'=> 'nullable|string|max:20',
            'email'          => 'nullable|email|max:255',
        ]);

        // Prevenir duplicados por teléfono
        if ($request->phone) {
            $existing = Customer::byPhone($request->phone)->first();
            if ($existing) {
                return response()->json(['customer' => $existing, 'created' => false]);
            }
        }

        $customer = Customer::create([
            'name'            => $request->name,
            'phone'           => $request->phone,
            'email'           => $request->email,
            'document_number' => $request->document_number,
            'acquisition_channel' => 'pos',
        ]);

        return response()->json(['customer' => $customer, 'created' => true], 201);
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'phone'          => 'nullable|string|max:20',
            'document_number'=> 'nullable|string|max:20',
            'email'          => 'nullable|email|max:255',
            'birth_date'     => 'nullable|date',
        ]);

        $customer->update($validated);

        return back()->with('success', 'Cliente actualizado.');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();
        return redirect()->route('customers.index')->with('success', 'Cliente eliminado.');
    }

    /**
     * Historial de compras con paginación.
     */
    public function orderHistory(Customer $customer): Response
    {
        return Inertia::render('Customers/OrderHistory', [
            'customer' => $customer,
            'orders'   => $customer->orders()->with('items')->latest()->paginate(15),
        ]);
    }
}
