<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class ImportBookRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->is_admin;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'volumeInfo' => ['required', 'array', new \App\Rules\RequiredTitleOrIdentifier],
            'volumeInfo.title' => 'nullable|string',
            'volumeInfo.authors' => 'nullable|array',
            'volumeInfo.industryIdentifiers' => ['nullable', 'array', new \App\Rules\UniqueBookIdentifier],
            'volumeInfo.industryIdentifiers.*.identifier' => 'distinct:ignore_case',
            'volumeInfo.imageLinks.thumbnail' => 'nullable|string',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'volumeInfo' => 'book information',
            'volumeInfo.title' => 'title',
            'volumeInfo.authors' => 'authors',
            'volumeInfo.industryIdentifiers' => 'identifiers',
            'volumeInfo.industryIdentifiers.*.identifier' => 'identifier value',
            'volumeInfo.imageLinks.thumbnail' => 'thumbnail',
        ];
    }
}
