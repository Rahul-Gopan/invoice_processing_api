<?php

namespace Tests\Feature;

use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class InvoiceCreationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function test_can_create_invoice(): void
    {        
        $response = $this->withHeaders(['X-CLIENT-KEY' => env('CLIENT_API_KEY')])
        ->postJson('/api/invoices', [
            'client_name' => "Sam Smith",
            'client_email' => "sam.smith@example.com",
            'items' => [
                ['title' => "Item 1", 'quantity' => 2, 'unit_price' => 50],
                ['title' => "Item 2", 'quantity' => 5, 'unit_price' => 100],
                ['title' => "Item 3", 'quantity' => 4, 'unit_price' => 80],
            ],
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['success', 'message', 'data' => ['invoice_number', 'items']]);

        $this->assertStringStartsWith('INV-'. now()->year, $response['data']['invoice_number']);
    }

    public function test_no_duplicate_entries_created(): void
    {
        $response = $this->withHeaders(['X-CLIENT-KEY' => env('CLIENT_API_KEY')])
        ->postJson('/api/invoices', [
            'client_name' => "Test Client",
            'client_email' => "test@example.com",
            'items' => [
                ['title' => "Item 1", 'quantity' => 2, 'unit_price' => 50],
                ['title' => "Item 2", 'quantity' => 3, 'unit_price' => 100],
            ],
        ]);

        $response->assertStatus(201);

        $invoiceNumber = $response['data']['invoice_number'];

        // Verify only one invoice exists with this number
        $this->assertEquals(1, Invoice::where('invoice_number', $invoiceNumber)->count());

        // Verify the invoice has exactly 2 items (no duplicates)
        $this->assertEquals(2, Invoice::where('invoice_number', $invoiceNumber)->first()->items()->count());
    }
}
