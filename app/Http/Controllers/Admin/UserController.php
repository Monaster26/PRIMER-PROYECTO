<?php

namespace App\Http\Controllers\Admin;
use Inertia\Inertia;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function cashiers()
    {
        $cashiers = User::role('cashier')
            ->with(['cashSessions' => function ($q) {
                $q->latest('opened_at')->limit(1);
            }])
            ->get()
            ->map(function (User $user) {
                $latest = $user->cashSessions->first();
                $discrepancy = $latest ? (float) $latest->real_balance - (float) $latest->opening_balance : 0;

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'rut' => $user->rut ?? '',
                    'phone' => $user->phone ?? '',
                    'address' => $user->address ?? '',
                    'cash_status' => $latest && !$latest->closed_at ? 'Activa' : 'Cerrada',
                    'last_session' => $latest ? $latest->opened_at->format('Y-m-d H:i') : '-',
                    'discrepancy' => round($discrepancy, 2),
                ];
            });

        return Inertia::render('admin/cajeros', [
            'cashiers' => $cashiers,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255|unique:users,email',
            'password' => ['required', 'confirmed', Password::defaults()],
            'rut'      => 'nullable|string|max:20',
            'phone'    => 'required|string|max:20',
            'address'  => 'required|string|max:255',
        ]);

        $user = User::create([
            'name'              => $validated['name'],
            'email'             => $validated['email'],
            'password'          => $validated['password'],
            'email_verified_at' => now(),
            'rut'               => $validated['rut'] ?? null,
            'phone'             => $validated['phone'],
            'address'           => $validated['address'],
        ]);

        $user->assignRole('cashier');

        return redirect()->route('admin.cajeros')->with('success', 'Cajero registrado correctamente.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|max:255|unique:users,email,' . $user->id,
            'rut'     => 'nullable|string|max:20',
            'phone'   => 'required|string|max:20',
            'address' => 'required|string|max:255',
        ]);

        $user->update($validated);

        return redirect()->route('admin.cajeros')->with('success', 'Cajero actualizado correctamente.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->cashSessions()->whereNull('closed_at')->exists()) {
            return redirect()->route('admin.cajeros')
                ->with('error', 'No se puede eliminar un cajero con caja abierta. Cierra la sesión primero.');
        }

        try {
            $user->delete();
            return redirect()->route('admin.cajeros')
                ->with('success', 'Cajero eliminado correctamente.');
        } catch (QueryException) {
            return redirect()->route('admin.cajeros')
                ->with('error', 'No se puede eliminar el cajero porque tiene datos históricos asociados.');
        }
    }

    public function clients()
    {
        $clients = User::role('web_client')
            ->get()
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'rut' => $user->rut ?? '-',
                'phone' => $user->phone ?? '-',
                'address' => $user->address ?? '-',
                'total_purchases' => 0,
                'last_purchase' => '-',
            ]);

        return Inertia::render('admin/clientes', [
            'clients' => $clients,
        ]);
    }
}
