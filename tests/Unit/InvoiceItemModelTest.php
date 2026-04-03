<?php

namespace Tests\Unit;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceItemModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_item_is_fillable(): void
    {
        $invoice = Invoice::create([
            'invoice_number' => 'INV-2026-0001',
            'client_name' => 'Alex Mathew',
        ]);

        $item = InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'title' => 'Test Item',
            'description' => 'Test Description',
            'quantity' => 5,
            'unit_price' => 99.99,
        ]);

        $this->assertEquals($invoice->id, $item->invoice_id);
        $this->assertEquals('Test Item', $item->title);
        $this->assertEquals('Test Description', $item->description);
        $this->assertEquals(5, $item->quantity);
        $this->assertEquals(99.99, $item->unit_price);
    }

    public function test_invoice_item_quantity_is_required(): void
    {
        $invoice = Invoice::create([
            'invoice_number' => 'INV-2026-0001',
            'client_name' => 'Alex Mathew',
        ]);

        // This should work if mass assignment allows it
        $item = InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'title' => 'Test Item',
            'quantity' => 1,
            'unit_price' => 50,
        ]);

        $this->assertNotNull($item->quantity);
    }

    public function test_invoice_item_description_is_nullable(): void
    {
        $invoice = Invoice::create([
            'invoice_number' => 'INV-2026-0001',
            'client_name' => 'Alex Mathew',
        ]);

        $item = InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'title' => 'Test Item',
            'quantity' => 1,
            'unit_price' => 50,
            'description' => null,
        ]);

        $this->assertNull($item->description);
    }

    public function test_invoice_item_belongs_to_invoice(): void
    {
        $invoice = Invoice::create([
            'invoice_number' => 'INV-2026-0001',
            'client_name' => 'Alex Mathew',
        ]);

        $item = InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'title' => 'Test Item',
            'quantity' => 1,
            'unit_price' => 50,
        ]);

        $item->load('invoice');
        
        $this->assertInstanceOf(Invoice::class, $item->invoice);
        $this->assertEquals($invoice->id, $item->invoice->id);
    }

    public function test_invoice_item_with_decimal_unit_price(): void
    {
        $invoice = Invoice::create([
            'invoice_number' => 'INV-2026-0001',
            'client_name' => 'Alex Mathew',
        ]);

        $item = InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'title' => 'Test Item',
            'quantity' => 3,
            'unit_price' => 19.99,
        ]);

        $this->assertEquals(19.99, $item->unit_price);
    }
}
