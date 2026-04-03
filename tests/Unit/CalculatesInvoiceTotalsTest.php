<?php

namespace Tests\Unit;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Traits\CalculatesInvoiceTotals;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalculatesInvoiceTotalsTest extends TestCase
{
    use RefreshDatabase;
    use CalculatesInvoiceTotals;

    public function test_calculates_gross_amount_correctly(): void
    {
        $invoice = Invoice::create([
            'invoice_number' => 'INV-2026-0001',
            'client_name' => 'Test Client',
            'tax_rate' => 0,
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'title' => 'Item 1',
            'quantity' => 2,
            'unit_price' => 100,
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'title' => 'Item 2',
            'quantity' => 3,
            'unit_price' => 50,
        ]);

        $invoice->load('items');
        $totals = $this->calculateTotals($invoice);

        // (2 * 100) + (3 * 50) = 200 + 150 = 350
        $this->assertEquals(350, $totals['gross_amount']);
    }

    public function test_calculates_tax_correctly_with_rate(): void
    {
        $invoice = Invoice::create([
            'invoice_number' => 'INV-2026-0001',
            'client_name' => 'Test Client',
            'tax_rate' => 20,  // 20% tax
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'title' => 'Item 1',
            'quantity' => 1,
            'unit_price' => 100,
        ]);

        $invoice->load('items');
        $totals = $this->calculateTotals($invoice);

        // Tax: 100 * 20% = 20
        $this->assertEquals(20, $totals['tax_amount']);
    }

    public function test_calculates_net_amount_correctly(): void
    {
        $invoice = Invoice::create([
            'invoice_number' => 'INV-2026-0001',
            'client_name' => 'Test Client',
            'tax_rate' => 10,  // 10% tax
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'title' => 'Item 1',
            'quantity' => 1,
            'unit_price' => 100,
        ]);

        $invoice->load('items');
        $totals = $this->calculateTotals($invoice);

        // Gross: 100, Tax: 10, Net: 110
        $this->assertEquals(110, $totals['net_amount']);
    }

    public function test_calculates_with_zero_tax_rate(): void
    {
        $invoice = Invoice::create([
            'invoice_number' => 'INV-2026-0001',
            'client_name' => 'Test Client',
            'tax_rate' => 0,
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'title' => 'Item 1',
            'quantity' => 2,
            'unit_price' => 50,
        ]);

        $invoice->load('items');
        $totals = $this->calculateTotals($invoice);

        $this->assertEquals(100, $totals['gross_amount']);
        $this->assertEquals(0, $totals['tax_amount']);
        $this->assertEquals(100, $totals['net_amount']);
    }

    public function test_calculates_with_high_tax_rate(): void
    {
        $invoice = Invoice::create([
            'invoice_number' => 'INV-2026-0001',
            'client_name' => 'Test Client',
            'tax_rate' => 100,  // 100% tax
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'title' => 'Item 1',
            'quantity' => 1,
            'unit_price' => 100,
        ]);

        $invoice->load('items');
        $totals = $this->calculateTotals($invoice);

        // Gross: 100, Tax: 100, Net: 200
        $this->assertEquals(100, $totals['gross_amount']);
        $this->assertEquals(100, $totals['tax_amount']);
        $this->assertEquals(200, $totals['net_amount']);
    }

    public function test_handles_decimal_prices(): void
    {
        $invoice = Invoice::create([
            'invoice_number' => 'INV-2026-0001',
            'client_name' => 'Test Client',
            'tax_rate' => 15,
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'title' => 'Item 1',
            'quantity' => 3,
            'unit_price' => 19.99,
        ]);

        $invoice->load('items');
        $totals = $this->calculateTotals($invoice);

        // Gross: 3 * 19.99 = 59.97
        // Tax: 59.97 * 15% = 8.9955 ≈ 9.00
        // Net: 59.97 + 9.00 = 68.97
        $this->assertEquals(59.97, $totals['gross_amount']);
        $this->assertEquals(9.00, $totals['tax_amount']);
        $this->assertEquals(68.97, $totals['net_amount']);
    }

    public function test_handles_multiple_items_with_varying_quantities(): void
    {
        $invoice = Invoice::create([
            'invoice_number' => 'INV-2026-0001',
            'client_name' => 'Test Client',
            'tax_rate' => 5,
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'title' => 'Item 1',
            'quantity' => 5,
            'unit_price' => 10,
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'title' => 'Item 2',
            'quantity' => 2,
            'unit_price' => 25,
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'title' => 'Item 3',
            'quantity' => 1,
            'unit_price' => 100,
        ]);

        $invoice->load('items');
        $totals = $this->calculateTotals($invoice);

        // Gross: (5*10) + (2*25) + (1*100) = 50 + 50 + 100 = 200
        // Tax: 200 * 5% = 10
        // Net: 200 + 10 = 210
        $this->assertEquals(200, $totals['gross_amount']);
        $this->assertEquals(10, $totals['tax_amount']);
        $this->assertEquals(210, $totals['net_amount']);
    }

    public function test_empty_invoice_items(): void
    {
        $invoice = Invoice::create([
            'invoice_number' => 'INV-2026-0001',
            'client_name' => 'Test Client',
            'tax_rate' => 10,
        ]);

        $invoice->load('items');
        $totals = $this->calculateTotals($invoice);

        $this->assertEquals(0, $totals['gross_amount']);
        $this->assertEquals(0, $totals['tax_amount']);
        $this->assertEquals(0, $totals['net_amount']);
    }
}
