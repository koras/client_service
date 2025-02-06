<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;

/**
 * @property string Name
 * @property string Email
 * @property string Message
 * @property string Phone
 */
class SendSupportRequest extends CustomRequest
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
            'Name' => 'required|string',
            'Email' => 'required|string|email',
            'Message' => 'required|string',
            'Phone' => 'required|string',
        ];
    }

}
