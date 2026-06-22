<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTableMergeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'table_ids' => ['required', 'array', 'min:2'],
            'table_ids.*' => ['required', 'integer', 'exists:tables,id', 'distinct'],
        ];
    }
}
