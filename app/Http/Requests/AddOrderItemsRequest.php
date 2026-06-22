<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddOrderItemsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.menu_item_id' => ['required', 'integer', 'exists:menu_items,id'],
            'items.*.qty' => ['required', 'integer', 'min:1', 'max:100'],
            'items.*.note' => ['nullable', 'string', 'max:500'],
            'items.*.modifiers' => ['nullable', 'array'],
            'items.*.modifiers.*.modifier_id' => ['sometimes', 'integer', 'exists:menu_item_modifiers,id'],
            'items.*.modifiers.*.name' => ['sometimes', 'string', 'max:255'],
            'items.*.modifiers.*.price_adjustment' => ['sometimes', 'numeric', 'min:0'],
        ];
    }
}
