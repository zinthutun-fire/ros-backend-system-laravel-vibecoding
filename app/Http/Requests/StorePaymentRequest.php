<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_id' => ['required', 'integer', 'exists:orders,id'],
            'type' => ['required', 'string', 'in:cash,card,split_cash_card,split_cash_cash'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'cash_portion' => ['required_if:type,split_cash_card,split_cash_cash', 'nullable', 'numeric', 'min:0'],
            'card_portion' => ['required_if:type,split_cash_card', 'nullable', 'numeric', 'min:0'],
            'tendered' => ['required_if:type,cash', 'nullable', 'numeric', 'min:0'],
        ];
    }
}
