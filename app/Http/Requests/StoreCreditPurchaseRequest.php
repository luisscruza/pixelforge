<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Support\Decimal;
use BcMath\Number;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreCreditPurchaseRequest extends FormRequest
{
    public const array PACKS = [5, 10, 25];

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'amount' => ['required', 'integer', Rule::in(self::PACKS)],
        ];
    }

    public function amount(): Number
    {
        return Decimal::from($this->integer('amount'));
    }
}
