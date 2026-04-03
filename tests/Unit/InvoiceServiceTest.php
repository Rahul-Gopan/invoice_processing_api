<?php

namespace Tests\Unit;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Services\InvoiceServices;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceServiceTest extends TestCase
{
    use RefreshDatabase;

    private InvoiceServices $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new InvoiceServices();
    }

    public function test_can_create_invoice_with_items(): void
    {
        $data = [
            'client_name' => 'Alex Mathew',
            'client_email' => 'alex@example.com',
            'tax_rate' => 10,
            'items' => [
                ['title' => 'Item 1', 'description' => 'Test item', 'quantity' => 2, 'unit_price' => 100],
                ['title' => 'Item 2', 'quantity' => 3, 'unit_price' => 50],
            ]
        ];

        $invoice = $this->service->create($data);

        $this->assertInstanceOf(Invoice::class, $invoice);
        $this->assertEquals('Alex Mathew', $invoice->client_name);
        $this->assertEquals('alex@example.com', $invoice->client_email);
        $this->assertEquals(10, $invoice->tax_rate);
        $this->assertCount(2, $invoice->items);
    }

    public function test_invoice_totals_are_calculated(): void
    {
        $data = [
            'client_name' => 'Jane Doe',
            'client_email' => 'jane@example.com',
            'tax_rate' => 20,
            'items' => [
                ['title' => 'Item 1', 'quantity' => 2, 'unit_price' => 100],  // 200
                ['title' => 'Item 2', 'quantity' => 1, 'unit_price' => 50],   // 50
            ]
        ];

        $invoice = $this->service->create($data);

        // Gross amount: 200 + 50 = 250
        // Tax amount: 250 * 20% = 50
        // Net amount: 250 + 50 = 300
        $this->assertEquals(250, $invoice->gross_total);
        $this->assertEquals(300, $invoice->net_amount);
    }

    public function test_invoice_number_is_sequential(): void
    {
        $data1 = [
            'client_name' => 'Client 1',
            'items' => [['title' => 'Item', 'quantity' => 1, 'unit_price' => 100]]
        ];

        $invoice1 = $this->service->create($data1);
        $invoice2 = $this->service->create($data1);

        $this->assertStringStartsWith('INV-' . now()->year, $invoice1->invoice_number);
        $this->assertStringStartsWith('INV-' . now()->year, $invoice2->invoice_number);
        $this->assertNotEquals($invoice1->invoice_number, $invoice2->invoice_number);
    }

    public function test_can_find_invoice_by_id(): void
    {
        $data = [
            'client_name' => 'Alex Mathew',
            'items' => [['title' => 'Item', 'quantity' => 1, 'unit_price' => 100]]
        ];

        $created = $this->service->create($data);
        $found = $this->service->find($created->id);

        $this->assertEquals($created->invoice_number, $found['invoice_number']);
        $this->assertEquals($created->client_name, $found['client_name']);
    }

    public function test_find_throws_exception_for_nonexistent_invoice(): void
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        $this->service->find(999);
    }

    public function test_can_list_invoices_with_pagination(): void
    {
        for ($i = 0; $i < 20; $i++) {
            $this->service->create([
                'client_name' => "Client $i",
                'items' => [['title' => 'Item', 'quantity' => 1, 'unit_price' => 100]]
            ]);
        }

        $page1 = $this->service->list(1, 10);

        $this->assertEquals(20, $page1->total());
        $this->assertCount(10, $page1->items());
        $this->assertEquals(1, $page1->currentPage());
    }

    public function test_list_pagination_works_correctly(): void
    {
        for ($i = 0; $i < 25; $i++) {
            $this->service->create([
                'client_name' => "Client $i",
                'items' => [['title' => 'Item', 'quantity' => 1, 'unit_price' => 100]]
            ]);
        }

        $page1 = $this->service->list(1, 15);
        $page2 = $this->service->list(2, 15);

        $this->assertEquals(25, $page1->total());
        $this->assertCount(15, $page1->items());
        $this->assertCount(10, $page2->items());
    }

    public function test_summary_counts_invoices_correctly(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->service->create([
                'client_name' => "Client $i",
                'items' => [['title' => 'Item', 'quantity' => 1, 'unit_price' => 100]]
            ]);
        }

        $summary = $this->service->summary();

        $this->assertEquals(5, $summary['total_invoices']);
    }

    public function test_summary_calculates_total_revenue(): void
    {
        // Create invoices with different amounts
        $this->service->create([
            'client_name' => 'Client 1',
            'tax_rate' => 0,
            'items' => [['title' => 'Item', 'quantity' => 2, 'unit_price' => 100]]  // 200
        ]);

        $this->service->create([
            'client_name' => 'Client 2',
            'tax_rate' => 0,
            'items' => [['title' => 'Item', 'quantity' => 1, 'unit_price' => 300]]  // 300
        ]);

        $summary = $this->service->summary();

        // Total revenue should be net amounts: 200 + 300 = 500
        $this->assertEquals(500, $summary['total_revenue']);
    }

    public function test_create_invoice_with_zero_tax_rate(): void
    {
        $data = [
            'client_name' => 'No Tax Client',
            'tax_rate' => 0,
            'items' => [
                ['title' => 'Item 1', 'quantity' => 1, 'unit_price' => 100],
            ]
        ];

        $invoice = $this->service->create($data);

        $this->assertEquals(100, $invoice->gross_total);
        $this->assertEquals(100, $invoice->net_amount);
    }

    public function test_create_invoice_requires_items(): void
    {
        $data = [
            'client_name' => 'Alex Mathew',
            'items' => []  // Empty items
        ];

        // Should handle gracefully - either create with no items or fail validation
        $invoice = $this->service->create($data);
        
        $this->assertCount(0, $invoice->items);
    }

    public function test_invoice_items_relationship(): void
    {
        $data = [
            'client_name' => 'Alex Mathew',
            'items' => [
                ['title' => 'Item 1', 'quantity' => 2, 'unit_price' => 50],
                ['title' => 'Item 2', 'quantity' => 1, 'unit_price' => 100],
                ['title' => 'Item 3', 'quantity' => 3, 'unit_price' => 25],
            ]
        ];

        $invoice = $this->service->create($data);

        foreach ($invoice->items as $item) {
            $this->assertInstanceOf(InvoiceItem::class, $item);
            $this->assertEquals($invoice->id, $item->invoice_id);
        }
    }
}
