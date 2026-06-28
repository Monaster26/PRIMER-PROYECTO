<?php

namespace App\Exports;

use App\Models\StockMovement;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StockMovementsExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    public function __construct(
        private ?string $search = null,
        private ?string $type = null,
        private ?string $from = null,
        private ?string $to = null,
    ) {}

    public function query()
    {
        return StockMovement::with('product')
            ->search($this->search)
            ->inDateRange($this->from, $this->to)
            ->when($this->type, fn($q, $type) => $q->ofType($type))
            ->orderBy('created_at', 'desc');
    }

    public function headings(): array
    {
        return [
            'ID',
            'Producto',
            'SKU',
            'Tipo',
            'Cantidad',
            'Stock Anterior',
            'Stock Posterior',
            'Notas',
            'Fecha',
        ];
    }

    public function map($movement): array
    {
        return [
            $movement->id,
            $movement->product?->name ?? '—',
            $movement->product?->sku ?? '—',
            $movement->type_name,
            $movement->quantity_change_formatted,
            number_format($movement->stock_before),
            number_format($movement->stock_after),
            $movement->notes ?? '—',
            $movement->created_at->format('d/m/Y H:i'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
