<?php

namespace App\Finance\Enums;

enum FinanceTransactionType: string
{
    case Payment = 'payment';
    case Refund = 'refund';
    case Reversal = 'reversal';
    case OpeningBalance = 'opening_balance';
    case Adjustment = 'adjustment';
}
