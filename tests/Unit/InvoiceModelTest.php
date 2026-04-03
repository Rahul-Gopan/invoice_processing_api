<?php

namespace Tests\Unit;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_is_fillable(): void
    {
        $data = [
            'invoice_number' => 'INV-2026-0001',
            'client_name' => 'Alex Mathew',
            'client_email' => 'alex@example.com',
            'tax_rate' => 10,
            'gross_total' => 1000,
            'net_amount' => 1100,
        ];

        $invoice = Invoice::create($data);

        $this->assertEquals('INV-2026-0001', $invoice->invoice_number);
        $this->assertEquals('Alex Mathew', $invoice->client_name);
        $this->assertEquals('alex@example.com', $invoice->client_email);
        $this->assertEquals(10, $invoice->tax_rate);
        $this->assertEquals(1000, $invoice->gross_total);
        $this->assertEquals(1100, $invoice->net_amount);
    }

    public function test_invoice_casts_attributes_correctly(): void
    {
        $invoice = Invoice::create([
            'invoice_number' => 'INV-2026-0001',
            'client_name' => 'Alex Mathew',
            'tax_rate' => '10.5',
            'gross_total' => '1000.50',
            'net_amount' => '1100.55',
        ]);

        $this->assertIsFloat($invoice->tax_rate);
        $this->assertIsFloat($invoice->gross_total);
        $this->assertIsFloat($invoice->net_amount);
        $this->assertEquals(10.5, $invoice->tax_rate);
        $this->assertEquals(1000.50, $invoice->gross_total);
        $this->assertEquals(1100.55, $invoice->net_amount);
    }

    public function test_invoice_has_many_items(): void
    {
        $invoice = Invoice::create([
            'invoice_number' => 'INV-2026-0001',
            'client_name' => 'Alex Mathew',
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'title' => 'Item 1',
            'quantity' => 1,
            'unit_price' => 100,
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'title' => 'Item 2',
            'quantity' => 2,
            'unit_price' => 50,
        ]);

        $this->assertCount(2, $invoice->items);
    }

    public function test_invoice_email_is_nullable(): void
    {
        $invoice = Invoice::create([
            'invoice_number' => 'INV-2026-0001',
            'client_name' => 'Alex Mathew',
            'client_email' => null,
        ]);

        $this->assertNull($invoice->client_email);
    }

    public function test_invoice_tax_rate_defaults_to_zero(): void
    {
        $invoice = Invoice::create([
            'invoice_number' => 'INV-2026-0001',
            'client_name' => 'Alex Mathew',
        ]);

        $this->assertEquals(0, $invoice->tax_rate);
    }
}
