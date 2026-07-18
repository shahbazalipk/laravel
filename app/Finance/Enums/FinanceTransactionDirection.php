<?php

namespace App\Finance\Enums;

enum FinanceTransactionDirection: string
{
    case Incoming = 'incoming';
    case Outgoing = 'outgoing';
}
