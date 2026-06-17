<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierDataSeeder extends Seeder
{
    protected array $daysMap = [
        'lunes' => 'Lunes', 'martes' => 'Martes', 'miercoles' => 'Miércoles',
        'miércoles' => 'Miércoles', 'jueves' => 'Jueves', 'viernes' => 'Viernes',
        'sabado' => 'Sábado', 'sábado' => 'Sábado', 'domingo' => 'Domingo',
    ];

    public function run(): void
    {
        $suppliers = json_decode(<<<'JSON'
[
  {"empresa":"TEQUESITOS","categoria":"TEQUEÑOS O EMPANADAS","proveedor":"ISRRAEL","dia_visita_llamada":"LUNES","tiempo_entrega":"24 horas","minimo_compra":"SIN PEDIDO MINMO","estado":"Hecho"},
  {"empresa":"AJE CHILE","categoria":"BIG UVA, FRECOLITA, SPORDAE","proveedor":"ISMAEL TEREAN","dia_visita_llamada":"LUNES","tiempo_entrega":"48 HORAS","minimo_compra":"SIN PEDIDO MINMO","estado":"Hecho"},
  {"empresa":"NESTLE","categoria":"galletas,café,","proveedor":"LUISA GUTIERREZ","dia_visita_llamada":"LUNES","tiempo_entrega":"48 HORAS","minimo_compra":"SIN PEDIDO MINMO","estado":"Hecho"},
  {"empresa":"IDEAL","categoria":"PINGUINOS,GANCITOS,","proveedor":"JAVIERA FERNANDEZ","dia_visita_llamada":"Lunes 15nal","tiempo_entrega":"48 HORAS","minimo_compra":"SIN PEDIDO MINMO","estado":"Hecho"},
  {"empresa":"COMEERCIAL KHATAR","categoria":"rikesa,maltin,mayonesa mavesa,harina pan","proveedor":"Carlos vidal","dia_visita_llamada":"MARTES","tiempo_entrega":"24 - 48 HORAS","minimo_compra":"SIN PEDIDO MINMO","estado":"Hecho"},
  {"empresa":"MARKET MANIA","categoria":"GALLETAS,ARTICULOS VENEZOLANOS CHOCOLATES","proveedor":"ADOLFO DURAN","dia_visita_llamada":"MARTES","tiempo_entrega":"48 HORAS","minimo_compra":"30 mil","estado":"Hecho"},
  {"empresa":"VIA K","categoria":"GALLETAS OREO. AFEITADORA Y PILA","proveedor":"YERKO","dia_visita_llamada":"MARTES","tiempo_entrega":"24 HORAS","minimo_compra":"15MIL","estado":"Hecho"},
  {"empresa":"DICA","categoria":"GALLETA BONO BON","proveedor":"PAOLA","dia_visita_llamada":"MIERCOLES","tiempo_entrega":"24 HORAS","minimo_compra":"SIN PEDIDO MINMO","estado":"Hecho"},
  {"empresa":"ARRIERO SAP","categoria":"RAQUETY TOCINETA CHUPETA CON LIQUEDO","proveedor":"JHONNY","dia_visita_llamada":"MIERCOLES","tiempo_entrega":"24HORAS","minimo_compra":"20MIL","estado":"Hecho"},
  {"empresa":"SOPROLE","categoria":"LACTEOS,BEBIDAS,POSTRES","proveedor":null,"dia_visita_llamada":"MIERCOLES","tiempo_entrega":"48 HORAS","minimo_compra":"54 MIL","estado":"Hecho"},
  {"empresa":"COCA-COLA","categoria":"BEBIDAS","proveedor":"MARIELA","dia_visita_llamada":"MIERCOLES/SABADO","tiempo_entrega":"48 HORAS","minimo_compra":"35 MIL","estado":"Hecho"},
  {"empresa":"ÑUBLES ALIMENTOS","categoria":"CARNE DE HAMBUERGUESA, PAPAS, QUESOS","proveedor":"CARLOS","dia_visita_llamada":"JUEVES","tiempo_entrega":"24 HORAS","minimo_compra":"10 MIL","estado":"Hecho"},
  {"empresa":"GLOBAL FOOD","categoria":"PAPELON CON LIMON,BOCADILLO,ARROZ MI SOL","proveedor":"FRANKLIN","dia_visita_llamada":"JUEVES","tiempo_entrega":null,"minimo_compra":null,"estado":"Hecho"},
  {"empresa":"ELITE","categoria":"GALLETAS , SOPAS EN SOBRE, JUMEX,SNIKER, ALOEVERA","proveedor":"GENESIS","dia_visita_llamada":"JUEVES","tiempo_entrega":"24 horas","minimo_compra":"30 MIL","estado":"Hecho"},
  {"empresa":"HYG","categoria":"platanitos,orbit,freegells,gallestas ritz,yesqueros,","proveedor":"Maria Briceño","dia_visita_llamada":"VIERNES","tiempo_entrega":"24-48 horas","minimo_compra":"40 mil","estado":"Hecho"},
  {"empresa":"DISTAR","categoria":"snickers,jumex,arizona,cuadrito de chocolate","proveedor":"ANDREA","dia_visita_llamada":"VIERNES","tiempo_entrega":"48 HORAS","minimo_compra":"40 mil","estado":"Hecho"},
  {"empresa":"BARIPOSTRESS","categoria":"TORAS FRIA, QUESILLO, PIE","proveedor":"AURIMAR VELA","dia_visita_llamada":"VIERNES","tiempo_entrega":"48 HORAS","minimo_compra":"24 UNIDADES","estado":"Hecho"},
  {"empresa":"CASTAÑO","categoria":"PAN DE MOLDE, QUEQUE, PAN DE HOT DOH","proveedor":null,"dia_visita_llamada":"VIERNES","tiempo_entrega":null,"minimo_compra":null,"estado":"Hecho"},
  {"empresa":"LUCCHETTI","categoria":"PAPS KRYSOS, CAFÉ GOL","proveedor":"FRANK","dia_visita_llamada":"VIERNES","tiempo_entrega":"48 HORAS","minimo_compra":"25 MIL","estado":"Hecho"},
  {"empresa":"ALVI","categoria":null,"proveedor":"MARILUZ","dia_visita_llamada":null,"tiempo_entrega":null,"minimo_compra":null,"estado":"Hecho"},
  {"empresa":"COLUN","categoria":null,"proveedor":null,"dia_visita_llamada":null,"tiempo_entrega":null,"minimo_compra":null,"estado":"Hecho"},
  {"empresa":"CCU","categoria":"BEBIDAS","proveedor":null,"dia_visita_llamada":null,"tiempo_entrega":null,"minimo_compra":null,"estado":"Hecho"},
  {"empresa":"CAROZZI","categoria":"pastas, alguans galletas","proveedor":"PATRICIO","dia_visita_llamada":"VIERNES","tiempo_entrega":"48 HORAS","minimo_compra":"20 MIL","estado":"Hecho"}
]
JSON, true);

        $count = 0;
        foreach ($suppliers as $item) {
            $data = [
                'company_name'       => trim($item['empresa']),
                'category'           => $this->cleanCategory($item['categoria']),
                'contact_name'       => $item['proveedor'] ? ucwords(mb_strtolower(trim($item['proveedor']))) : null,
                'visit_day'          => $this->mapVisitDay($item['dia_visita_llamada']),
                'delivery_time_hours' => $this->parseDeliveryHours($item['tiempo_entrega']),
                'minimum_order_amount' => $this->parseMinimumOrder($item['minimo_compra']),
            ];

            Supplier::updateOrCreate(
                ['company_name' => $data['company_name']],
                $data,
            );

            $count++;
        }

        $this->command->info("✔ {$count} proveedores insertados/actualizados.");
    }

    protected function cleanCategory(?string $cat): ?string
    {
        if (!$cat) return null;
        $cat = trim(preg_replace('/,\s*$/', '', $cat));
        return $cat !== '' ? mb_strtoupper($cat) : null;
    }

    protected function mapVisitDay(?string $day): ?string
    {
        if (!$day) return null;
        $day = trim(explode('/', $day)[0]);
        $day = mb_strtolower(str_replace([' ', 'nal', '15nal', '15'], '', $day));
        return $this->daysMap[$day] ?? null;
    }

    protected function parseDeliveryHours(?string $text): int
    {
        if (!$text) return 24;
        $text = str_replace(' ', '', mb_strtolower($text));
        if (preg_match('/(\d+)-(\d+)/', $text, $m)) {
            return (int) $m[2];
        }
        if (preg_match('/(\d+)/', $text, $m)) {
            return (int) $m[1];
        }
        return 24;
    }

    protected function parseMinimumOrder(?string $text): float
    {
        if (!$text) return 0;
        $text = str_replace([' ', ','], '', mb_strtolower($text));
        if (preg_match('/(\d+)/', $text, $m)) {
            return (float) $m[1];
        }
        return 0;
    }
}
