<?php

namespace App\Helpers;

use App\Models\Invoice;

class InvoiceNumberHelper
{
    public static function generateInvoiceNumber(): string 
    {
        $year = now()->year;

        $last = Invoice::whereYear('created_at', $year)
                ->lockForUpdate()
                ->orderByDesc('id')
                ->first();

        $sequence = $last ? ((int) substr($last->invoice_number, -4)) + 1 : 1;

        return sprintf('INV-%d-%04d', $year, $sequence);
    }
}
