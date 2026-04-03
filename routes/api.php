<?php

use App\Http\Controllers\InvoiceController;
use Illuminate\Support\Facades\Route;

Route::middleware(['client.key', 'rate.limit'])->prefix('invoices')->group(function () {
    Route::post('/', [InvoiceController::class, 'store']);
    Route::get('/', [InvoiceController::class, 'index']);
    Route::get('/summary', [InvoiceController::class, 'summary']);
    Route::get('/find', [InvoiceController::class, 'show']);
});
