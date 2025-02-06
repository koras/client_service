<?php

namespace App\Http\Requests;

use App\Enums\ErrorsEnum;
use Illuminate\Foundation\Http\FormRequest;

class CustomRequest extends FormRequest
{
    public function __construct(
        array $query = [],
        array $request = [],
        array $attributes = [],
        array $cookies = [],
        array $files = [],
        array $server = [],
        $content = null
    ) {
        parent::__construct($query, $request, $attributes, $cookies, $files, $server, $content);
    }

    public function messages(): array
    {
        return [
            'required' => ErrorsEnum::REQUIRED_FIELDS_MISSING->name,
            'unique' => ErrorsEnum::ALREADY_EXISTS->name,
            'string' => ErrorsEnum::INVALID_FIELDS->name,
            'boolean' => ErrorsEnum::INVALID_FIELDS->name,
            'date' => ErrorsEnum::INVALID_FIELDS->name,
            'integer' => ErrorsEnum::INVALID_FIELDS->name,
            'max' => ErrorsEnum::INVALID_FIELD_MAX->name,
            'min' => ErrorsEnum::INVALID_FIELD_MIN->name,
            'exists' => ErrorsEnum::NOT_FOUND->name,
            'in' => ErrorsEnum::INVALID_FIELDS->name,
            'regex' => ErrorsEnum::INVALID_FIELDS->name,
            'file' => ErrorsEnum::FILE_NOT_EXIST->name,
            'image' => ErrorsEnum::FILE_NOT_EXIST->name,
            'mimes' => ErrorsEnum::FILE_FORMAT_INVALID->name,
            'between' => ErrorsEnum::FILE_SIZE_EXCEEDED->name,
            'email' => ErrorsEnum::EMAIL_NOT_VALID->name,
            'size' => ErrorsEnum::FIELD_SIZE_EXCEEDED->name,
            'url' => ErrorsEnum::INVALID_URL_FORMAT->name,
            'type' => ErrorsEnum::INVALID_FIELDS->name,
        ];
    }
}
