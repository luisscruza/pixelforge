<?php

declare(strict_types=1);

namespace App\Enums;

enum CreditTransactionType: string
{
    case Purchase = 'purchase';
    case Hold = 'hold';
    case Release = 'release';
    case Charge = 'charge';
}
