<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;

class PromoCodeRequest extends CustomRequest
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
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'products.*.product.product_id' => 'required|string',
            'promo_code' => 'required|string',
        ];
    }

}
