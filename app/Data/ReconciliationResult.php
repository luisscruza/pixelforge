<?php

declare(strict_types=1);

namespace App\Data;

final readonly class ReconciliationResult
{
    public function __construct(
        public int $resubmitted,
        public int $reconciled,
        public int $polled,
    ) {
        //
    }
}
