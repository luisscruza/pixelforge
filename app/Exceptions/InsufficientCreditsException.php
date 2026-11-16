<?php

declare(strict_types=1);

namespace App\Exceptions;

use BcMath\Number;
use Illuminate\Http\RedirectResponse;
use RuntimeException;

final class InsufficientCreditsException extends RuntimeException
{
    public function __construct(
        public readonly Number $balance,
        public readonly Number $required,
    ) {
        parent::__construct('Not enough credits for this generation.');
    }

    public function render(): RedirectResponse
    {
        return back()->withErrors([
            'credits' => 'You need up to $'.$this->required->round(4).' for this generation but have $'.$this->balance->round(4).'. Buy credits to continue.',
        ]);
    }
}
