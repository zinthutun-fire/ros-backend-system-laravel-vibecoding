<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tableId = $this->route('table');
        return [
            'table_no' => ['sometimes', 'string', 'unique:tables,table_no,' . $tableId],
            'name' => ['nullable', 'string', 'max:255'],
            'capacity' => ['sometimes', 'integer', 'min:1', 'max:50'],
            'area_id' => ['sometimes', 'integer', 'exists:table_areas,id'],
            'status' => ['sometimes', 'string', 'in:available,occupied,ordering,payment,reserved'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
