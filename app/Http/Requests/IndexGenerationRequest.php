<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class IndexGenerationRequest extends FormRequest
{
    public const int MAX_WATCHED = 50;

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'watch' => ['sometimes', 'array', 'max:'.self::MAX_WATCHED],
            'watch.*' => ['uuid'],
        ];
    }

    /**
     * @return list<string>
     */
    public function watchedIds(): array
    {
        return array_values(array_filter((array) $this->input('watch', []), is_string(...)));
    }
}
