<?php
namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;

/**
 * @property string certificateId
 * @property string phone
 */
class SendPinSmsRequest extends CustomRequest
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
            'certificateId' => 'required|uuid',
            'phone' => 'required|string|min:10|max:15',
        ];
    }

}
