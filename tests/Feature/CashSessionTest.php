<?php

namespace Tests\Feature;

use App\Models\CashMovement;
use App\Models\CashSession;
use App\Models\Sale;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashSessionTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $cashier;
    private User $userWithoutRole;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        $this->cashier = User::factory()->create();
        $this->cashier->assignRole('cashier');

        $this->userWithoutRole = User::factory()->create();
    }

    public function test_apertura_sesion_calcula_monto_inicial(): void
    {
        $this->actingAs($this->admin);

        $this->post(route('admin.arqueo-caja.store'), [
            'user_id' => $this->admin->id,
            'opened_at' => now(),
            'cant_20k_apertura' => 1,
            'cant_10k_apertura' => 2,
            'cant_5k_apertura' => 0,
            'cant_2k_apertura' => 0,
            'cant_1k_apertura' => 0,
            'cant_500_apertura' => 0,
            'cant_100_apertura' => 0,
            'cant_50_apertura' => 0,
            'cant_10_apertura' => 0,
            'total_red_compra' => 0,
            'total_transferencia' => 0,
            'total_retiros' => 0,
            'total_ingresos' => 0,
        ])->assertRedirect(route('admin.arqueo-caja.index'));

        $this->assertDatabaseHas('cash_sessions', [
            'user_id' => $this->admin->id,
            'total_efectivo_apertura' => 40000,
        ]);
    }

    public function test_no_se_puede_abrir_segunda_sesion_con_una_abierta(): void
    {
        $this->actingAs($this->admin);

        $datosSesion = [
            'user_id' => $this->admin->id,
            'opened_at' => now(),
            'cant_20k_apertura' => 1,
            'cant_10k_apertura' => 0,
            'cant_5k_apertura' => 0,
            'cant_2k_apertura' => 0,
            'cant_1k_apertura' => 0,
            'cant_500_apertura' => 0,
            'cant_100_apertura' => 0,
            'cant_50_apertura' => 0,
            'cant_10_apertura' => 0,
            'total_red_compra' => 0,
            'total_transferencia' => 0,
            'total_retiros' => 0,
            'total_ingresos' => 0,
        ];

        $this->post(route('admin.arqueo-caja.store'), $datosSesion);

        $this->post(route('admin.arqueo-caja.store'), $datosSesion)
            ->assertSessionHasErrors('error');
    }

    public function test_cierre_sesion_calcula_resumen_financiero(): void
    {
        $this->actingAs($this->admin);

        $this->post(route('admin.pos.open-session'), [
            'cant_20k_apertura' => 1,
            'cant_10k_apertura' => 1,
            'cant_5k_apertura' => 0,
            'cant_2k_apertura' => 0,
            'cant_1k_apertura' => 0,
            'cant_500_apertura' => 0,
            'cant_100_apertura' => 0,
            'cant_50_apertura' => 0,
            'cant_10_apertura' => 0,
        ])->assertJson(['success' => true]);

        $session = CashSession::first();

        Sale::create([
            'user_id' => $this->admin->id,
            'type' => 'pos',
            'cash_amount' => 500000,
            'total' => 500000,
            'created_at' => $session->opened_at->addMinute(),
        ]);

        CashMovement::create([
            'user_id' => $this->admin->id,
            'sesion_caja_id' => $session->id,
            'type' => 'ingreso',
            'amount' => 2000,
            'created_at' => $session->opened_at->addMinutes(2),
        ]);

        CashMovement::create([
            'user_id' => $this->admin->id,
            'sesion_caja_id' => $session->id,
            'type' => 'retiro',
            'amount' => 1000,
            'created_at' => $session->opened_at->addMinutes(2),
        ]);

        $response = $this->post(route('admin.pos.close-session'), [
            'cant_20k_cierre' => 1,
            'cant_10k_cierre' => 1,
            'cant_5k_cierre' => 0,
            'cant_2k_cierre' => 0,
            'cant_1k_cierre' => 0,
            'coin_500' => 0,
            'coin_100' => 0,
            'coin_50' => 0,
            'coin_10' => 0,
            'total_red_compra' => 0,
            'total_transferencia' => 0,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'summary' => [
                'cashSales' => 5000,
                'ingresos' => 2000,
                'retiros' => 1000,
                'esperado' => 30000 + 5000 + 2000 - 1000,
            ],
        ]);
    }

    public function test_solo_admin_y_cashier_pueden_abrir_sesion(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.arqueo-caja.store'), [
                'user_id' => $this->admin->id,
                'opened_at' => now(),
                'cant_20k_apertura' => 0,
                'cant_10k_apertura' => 0,
                'cant_5k_apertura' => 0,
                'cant_2k_apertura' => 0,
                'cant_1k_apertura' => 0,
                'cant_500_apertura' => 0,
                'cant_100_apertura' => 0,
                'cant_50_apertura' => 0,
                'cant_10_apertura' => 0,
                'total_red_compra' => 0,
                'total_transferencia' => 0,
                'total_retiros' => 0,
                'total_ingresos' => 0,
            ])
            ->assertStatus(302);

        $this->actingAs($this->cashier)
            ->post(route('admin.arqueo-caja.store'), [
                'user_id' => $this->cashier->id,
                'opened_at' => now(),
                'cant_20k_apertura' => 0,
                'cant_10k_apertura' => 0,
                'cant_5k_apertura' => 0,
                'cant_2k_apertura' => 0,
                'cant_1k_apertura' => 0,
                'cant_500_apertura' => 0,
                'cant_100_apertura' => 0,
                'cant_50_apertura' => 0,
                'cant_10_apertura' => 0,
                'total_red_compra' => 0,
                'total_transferencia' => 0,
                'total_retiros' => 0,
                'total_ingresos' => 0,
            ])
            ->assertStatus(302);

        $this->actingAs($this->userWithoutRole)
            ->post(route('admin.arqueo-caja.store'), [
                'user_id' => $this->userWithoutRole->id,
                'opened_at' => now(),
                'cant_20k_apertura' => 0,
                'cant_10k_apertura' => 0,
                'cant_5k_apertura' => 0,
                'cant_2k_apertura' => 0,
                'cant_1k_apertura' => 0,
                'cant_500_apertura' => 0,
                'cant_100_apertura' => 0,
                'cant_50_apertura' => 0,
                'cant_10_apertura' => 0,
                'total_red_compra' => 0,
                'total_transferencia' => 0,
                'total_retiros' => 0,
                'total_ingresos' => 0,
            ])
            ->assertForbidden();
    }
}
