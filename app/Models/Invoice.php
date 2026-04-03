<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{    
    protected $fillable = [
        'invoice_number', 'client_name', 'client_email', 'tax_rate', 'gross_total', 'net_amount'
    ];

    public function items() 
    {
        return $this->hasMany(InvoiceItem::class);
    }

    protected $casts = [
        'tax_rate'      => 'float',
        'gross_total'   => 'float',
        'net_amount'    => 'float',
    ];
}
