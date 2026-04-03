<?php 

namespace App\Services;

use App\Helpers\InvoiceNumberHelper;
use App\Models\Invoice;
use App\Traits\CalculatesInvoiceTotals;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InvoiceServices
{
    use CalculatesInvoiceTotals;

    public function create(array $data): Invoice
    {


        return DB::transaction(function () use ($data) {

            $invoiceNumber = InvoiceNumberHelper::generateInvoiceNumber();
            
            $invoice = Invoice::create([
                'invoice_number'    => $invoiceNumber,
                'client_name'       => $data['client_name'],
                'client_email'      => $data['client_email'] ?? null,
                'tax_rate'          => $data['tax_rate'] ?? 0,
            ]);

            foreach ($data['items'] as $item) {
                $invoice->items()->create($item);
            }
            
            $invoice->load('items');
            $totals = $this->calculateTotals($invoice);
            $invoice->updateQuietly([
                'gross_total'   => $totals['gross_amount'],
                'net_amount'    => $totals['net_amount'],
            ]);

            Cache::forget('invoice_summary');

            return $invoice->fresh()->load('items');
        });

    }

    public function find(int $id): array
    {
        $invoice = Invoice::with('items')->findOrFail($id);
        return array_merge($invoice->toArray(), $this->calculateTotals($invoice));
    }

    public function list(int $page, $per_page): LengthAwarePaginator
    {
        return Invoice::with('items')->paginate($per_page, ['*'], 'page', $page);

    }

    public function summary(): array 
    {
        return Cache::remember('invoice_summary', 60, fn() => [
            'total_invoices'    => Invoice::count(),
            'total_revenue'     => Invoice::with('items')->get()->sum(fn($invoice) => 
                                    $this->calculateTotals($invoice)['total']),
        ]);
    }
}