<?php

namespace Tests\Unit;

use App\Helpers\InvoiceNumberHelper;
use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceNumberHelperTest extends TestCase
{
    use RefreshDatabase;

    public function test_generates_invoice_number_with_current_year(): void
    {
        $number = InvoiceNumberHelper::generateInvoiceNumber();

        $this->assertStringStartsWith('INV-' . now()->year, $number);
    }

    public function test_generates_sequential_invoice_numbers(): void
    {
        $number1 = InvoiceNumberHelper::generateInvoiceNumber();
        Invoice::create([
            'invoice_number' => $number1,
            'client_name' => 'Test',
        ]);

        $number2 = InvoiceNumberHelper::generateInvoiceNumber();

        $this->assertNotEquals($number1, $number2);
        $this->assertTrue((int)substr($number2, -4) > (int)substr($number1, -4));
    }

    public function test_first_invoice_number_has_sequence_0001(): void
    {
        $number = InvoiceNumberHelper::generateInvoiceNumber();

        $this->assertStringEndsWith('-0001', $number);
    }

    public function test_increments_sequence_for_each_invoice(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $number = InvoiceNumberHelper::generateInvoiceNumber();
            Invoice::create([
                'invoice_number' => $number,
                'client_name' => "Client $i",
            ]);

            $sequence = (int)substr($number, -4);
            $this->assertEquals($i, $sequence);
        }
    }

    public function test_formats_invoice_number_correctly(): void
    {
        $number = InvoiceNumberHelper::generateInvoiceNumber();

        // Check format: INV-YYYY-NNNN
        $this->assertMatchesRegularExpression('/^INV-\d{4}-\d{4}$/', $number);
        $this->assertStringContainsString('-', $number);
    }
}
