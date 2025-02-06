<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;

class CreateOrderRequest extends CustomRequest
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
            '*.certificate.template_id' => 'required',
            '*.certificate.template_src' => 'nullable|string',
            '*.certificate.orderType' => 'sometimes|string',
            '*.certificate.message' => 'nullable|string',
            '*.certificate.basket_key' => 'required|string',
            '*.certificate.product.quantity' => 'required|numeric',
            '*.certificate.product.product_id' => 'required|numeric',
            '*.certificate.product.amount' => 'required|numeric',
            '*.certificate.delivery_type' => 'required|array',
            '*.certificate.delivery_type.*' => 'required|string',
            '*.recipient.type' => 'required|string',
            '*.recipient.name' => 'required|string',
            '*.recipient.email' => 'sometimes|string|email',
            '*.recipient.msisdn' => 'sometimes|string',
            '*.time_to_send' => 'nullable|string',
            '*.utm' => 'nullable|string',
            '*.sender.name' => 'sometimes|string',
            '*.sender.email' => 'sometimes|string|email',
        ];
    }

}
