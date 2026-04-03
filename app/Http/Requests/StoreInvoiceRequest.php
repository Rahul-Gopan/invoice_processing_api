<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class StoreInvoiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'client_name'           => 'required|string|max:255',
            'client_email'          => 'nullable|email|max:255',
            'tax_rate'              => 'nullable|numeric|between:0,100',
            'items'                 => 'required|array|min:1',
            'items.*.title'         => 'required|string|max:255',
            'items.*.description'   => 'nullable|string',
            'items.*.quantity'      => 'required|integer|min:1',
            'items.*.unit_price'    => 'required|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'client_name.required'          => 'The client name is required.',

            'client_email.email'            => 'The client email must be a valid email address.',

            'tax_rate.numeric'              => 'The tax rate must be a number.',
            'tax_rate.between'              => 'The tax rate must be between 0 and 100.',

            'items.required'                => 'At least one item is required.',
            'items.*.title.required'        => 'Each item must have a title.',
            'items.*.title.max'             => 'Each item title must not exceed 255 characters.',

            'items.*.description.string'    => 'Each item description must be a string.',

            'items.*.quantity.required'     => 'Each item must have a quantity.',
            'items.*.quantity.integer'      => 'Each item quantity must be an integer.',
            'items.*.quantity.min'          => 'Each item quantity must be at least 1.',
            
            'items.*.unit_price.required'   => 'Each item must have a unit price.',
            'items.*.unit_price.numeric'    => 'Each item unit price must be a number.',
            'items.*.unit_price.min'        => 'Each item unit price must be at least 0.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $validator->errors(),
            ], 422)
        );
    }
}
