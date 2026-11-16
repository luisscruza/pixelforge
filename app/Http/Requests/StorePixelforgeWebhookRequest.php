<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class StorePixelforgeWebhookRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id' => ['required', 'string', 'starts_with:evt_', 'max:255'],
            'type' => ['required', 'string', 'max:255'],
            'data' => ['present', 'array'],
            'data.object' => ['nullable', 'array'],
            'data.object.id' => ['nullable', 'string', 'max:255'],
        ];
    }
}
