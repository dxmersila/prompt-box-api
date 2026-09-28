<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ItemIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'page' => [
                'nullable',
                'integer',
                'min:1',
            ],

            'search' => [
                'nullable',
                'string',
                'max:100',
            ],

            'category' => [
                'nullable',
                'string',
            ],

            'tag' => [
                'nullable',
                'string',
            ],

            'access_type' => [
                'nullable',
                Rule::in([
                    'free',
                    'paid',
                ]),
            ],

            'sort' => [
                'nullable',
                Rule::in([
                    'latest',
                    'oldest',
                    'title',
                ]),
            ],
        ];
    }
}
