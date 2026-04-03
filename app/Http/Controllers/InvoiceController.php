<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseApiController;
use App\Http\Requests\StoreInvoiceRequest;
use App\Services\InvoiceServices;
use App\Traits\HandlesExceptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\JsonResponse;

class InvoiceController extends BaseApiController
{
    public function __construct(private InvoiceServices $service) {}

    public function store(StoreInvoiceRequest $request): JsonResponse
    {
        Log::info('Controller store called');

        try {
            $invoice = $this->service->create($request->validated());
            return $this->successResponse($invoice, 'Invoice created successfully', 201);
        } catch (\Exception $e) {
            return $this->failureResponse('Failed to create invoice', 400, $e->getMessage());
        }
    }

    public function show(Request $request): JsonResponse
    {
        try {
            $request->validate(['invoice_id' => 'required|integer|exists:invoices,id']);
            return $this->successResponse($this->service->find($request->input('invoice_id')), 'Invoice retrieved successfully');
        } catch (\Exception $e) {
            return $this->failureResponse('Failed to find invoice', 400, $e->getMessage());
        }
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $data = $this->service->list(
                (int) $request->input('page', 1),
                (int) $request->input('per_page', 15)
            );
            return $this->paginationResponse($data, 'Invoices retrieved successfully');
        } catch (\Exception $e) {
            return $this->failureResponse('Failed to list invoices', 400, $e->getMessage());
        }
    }

    public function summary(): JsonResponse
    {
        try {
            return $this->successResponse($this->service->summary(), 'Invoice summary retrieved successfully');
        } catch (\Exception $e) {

            return $this->failureResponse('Failed to retrieve invoice summary', 400, $e->getMessage());
        }
    }
}