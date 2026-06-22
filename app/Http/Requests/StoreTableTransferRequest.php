<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTableTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_id' => ['required', 'integer', 'exists:orders,id'],
            'from_table_id' => ['required', 'integer', 'exists:tables,id'],
            'to_table_id' => ['required', 'integer', 'exists:tables,id', 'different:from_table_id'],
        ];
    }
}
