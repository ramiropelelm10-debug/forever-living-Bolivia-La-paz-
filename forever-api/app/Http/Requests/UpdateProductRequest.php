<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
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
     */
    public function rules(): array
    {
        // Obtenemos el ID del producto que viene en la URL (api/products/{product})
        $productId = $this->route('product')->id;

        return [
            'sku'          => "sometimes|required|string|unique:products,sku,$productId",
            'name'         => 'sometimes|required|string|max:255',
            'price_bs'     => 'sometimes|required|numeric|min:0',
            'cc_value'     => 'sometimes|required|numeric|min:0',
            'stock'        => 'sometimes|required|integer|min:0',
            'image_base64' => 'nullable|string',
        ];
    }
}