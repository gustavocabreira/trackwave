<?php

declare(strict_types=1);

namespace App\Http\Requests\Project;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class IndexProjectRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'page' => ['sometimes', 'integer', 'min:1'],
            'name' => ['sometimes', 'string'],
            'per_page' => ['sometimes', 'integer', 'min:1'],
            'order_by' => ['string', 'required_with:direction', Rule::in(['id', 'name'])],
            'direction' => ['string', 'required_with:order_by', Rule::in(['asc', 'desc'])],
        ];
    }
}
