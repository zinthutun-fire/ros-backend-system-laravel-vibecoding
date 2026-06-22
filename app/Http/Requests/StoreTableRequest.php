<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'table_no' => ['required', 'string', 'unique:tables,table_no'],
            'name' => ['nullable', 'string', 'max:255'],
            'capacity' => ['required', 'integer', 'min:1', 'max:50'],
            'area_id' => ['required', 'integer', 'exists:table_areas,id'],
            'status' => ['sometimes', 'string', 'in:available,occupied,ordering,payment,reserved'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
