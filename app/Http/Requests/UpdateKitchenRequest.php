<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateKitchenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $kitchenId = $this->route('kitchen');
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'code' => ['sometimes', 'string', 'max:10', 'unique:kitchens,code,' . $kitchenId],
            'status' => ['sometimes', 'string', 'in:active,inactive'],
        ];
    }
}
