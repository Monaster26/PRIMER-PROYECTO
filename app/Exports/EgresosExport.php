<?php

namespace App\Exports;

use App\Models\Expense;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EgresosExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    private array $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function query()
    {
        return Expense::with('user')
            ->byDateRange($this->filters['from'] ?? null, $this->filters['to'] ?? null)
            ->ofType($this->filters['type'] ?? null)
            ->byPaymentMethod($this->filters['payment_method'] ?? null)
            ->searchBeneficiary($this->filters['beneficiary'] ?? null)
            ->byUser($this->filters['user_id'] ?? null)
            ->orderBy('date', 'desc');
    }

    public function headings(): array
    {
        return [
            'Fecha',
            'Tipo',
            'Concepto',
            'Beneficiario',
            'Método de Pago',
            'Monto',
            'Responsable',
            'Observación',
        ];
    }

    public function map($expense): array
    {
        return [
            $expense->date->format('d/m/Y'),
            Expense::TYPE_LABELS[$expense->type] ?? $expense->type,
            $expense->concept ?? '—',
            $expense->beneficiary ?? '—',
            $expense->payment_method === 'efectivo' ? 'Efectivo' : 'Transferencia',
            $expense->amount,
            $expense->user?->name ?? '—',
            $expense->observation ?? '—',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
