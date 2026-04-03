<?php

namespace App\Traits;

use App\Models\Invoice;

trait CalculatesInvoiceTotals
{
    public function calculateTotals(Invoice $invoice): array
    {
        $grossAmount    = $invoice->items->sum(fn($item) => $item->quantity * $item->unit_price);
        $taxAmount      = round($grossAmount * ($invoice->tax_rate / 100), 2);
        $netAmount      = round($grossAmount + $taxAmount, 2);

        return [
            'gross_amount'  => $grossAmount,
            'tax_amount'    => $taxAmount,
            'net_amount'    => $netAmount,
        ];
    }
}
