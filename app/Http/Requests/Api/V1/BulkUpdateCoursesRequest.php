<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class BulkUpdateCoursesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $ids = $this->query('ids');

        if (is_string($ids)) {
            $ids = array_values(array_filter(array_map('trim', explode(',', $ids)), fn (string $value): bool => $value !== ''));
        }

        $this->merge([
            'ids' => $ids,
        ]);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer', 'min:1', 'distinct'],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'required', 'string'],
            'price_cents' => ['sometimes', 'required', 'integer', 'min:0'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! $this->hasAny(['name', 'description', 'price_cents'])) {
                $validator->errors()->add(
                    'attributes',
                    'At least one updatable attribute must be provided: name, description, or price_cents.'
                );
            }
        });
    }
}
