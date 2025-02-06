<?php

namespace App\Http\Requests;

use App\Enums\ErrorsEnum;
use App\Enums\DeliveryTypeEnum;
use Illuminate\Contracts\Validation\ValidationRule;

class ParseFromXlsxRequest extends CustomRequest
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
            'file' => 'required|mimes:xls,xlsx|max:1024',
            'recipientType' => 'required|string',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (!in_array($this->input('recipientType'), DeliveryTypeEnum::values())) {
                $validator->errors()->add('recipientType', ErrorsEnum::INVALID_FIELDS);
            }
        });
    }
}
