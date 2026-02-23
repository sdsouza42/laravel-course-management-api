<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class BulkDeleteCoursesRequest extends FormRequest
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
        ];
    }
}
