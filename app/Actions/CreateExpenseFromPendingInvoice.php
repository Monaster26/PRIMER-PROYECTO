<?php

namespace App\Actions;

use App\Models\Expense;
use App\Models\PendingInvoice;

class CreateExpenseFromPendingInvoice
{
    public function execute(PendingInvoice $invoice): Expense
    {
        if (Expense::where('origin_type', 'invoice')
                  ->where('origin_id', $invoice->id)
                  ->exists()) {
            throw new \RuntimeException(
                'Ya existe un egreso generado para esta factura.'
            );
        }

        return Expense::create([
            'date'           => today(),
            'type'           => 'proveedor',
            'concept'        => 'Pago factura '
                . ($invoice->invoice_number ?? "#{$invoice->id}"),
            'beneficiary'    => $invoice->supplier?->company_name,
            'payment_method' => 'transferencia',
            'amount'         => $invoice->total_amount,
            'tax_amount'     => $invoice->tax_amount ?? 0,
            'observation'    => 'Generado automáticamente desde Factura Pendiente'
                . " #{$invoice->id}"
                . ($invoice->notes ? " | {$invoice->notes}" : ''),
            'supplier_id'    => $invoice->supplier_id,
            'user_id'        => auth()->id(),
            'origin_type'    => 'invoice',
            'origin_id'      => $invoice->id,
        ]);
    }
}
