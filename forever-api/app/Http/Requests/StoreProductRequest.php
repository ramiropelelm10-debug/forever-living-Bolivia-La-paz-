<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool 
    { 
        // Cambiado a true para permitir que los usuarios autenticados lo usen
        return true; 
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'sku'          => 'required|string|unique:products,sku',
            'name'         => 'required|string|max:255',
            'price_bs'     => 'required|numeric|min:0',
            'cc_value'     => 'required|numeric|min:0',
            'stock'        => 'required|integer|min:0',
            'image_base64' => 'nullable|string',
        ];
    }
}