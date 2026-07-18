<?php

namespace App\Finance\Enums;

final class FinanceAbility
{
    public const VIEW = 'finance.view';

    public const CONFIGURE = 'finance.configure';

    public const MANAGE_ACCOUNTS = 'finance.manage-accounts';

    public const VIEW_SENSITIVE = 'finance.view-sensitive';

    public const MANAGE_INCOME = 'finance.manage-income';

    public const MANAGE_EXPENSES = 'finance.manage-expenses';

    public const APPROVE = 'finance.approve';

    public const PAY = 'finance.pay';

    public const REFUND = 'finance.refund';

    public const MANAGE_BUDGETS = 'finance.manage-budgets';

    public const RECONCILE = 'finance.reconcile';

    public const VIEW_REPORTS = 'finance.view-reports';

    public const EXPORT = 'finance.export';

    public const VIEW_AUDIT = 'finance.view-audit';

    /** @return list<string> */
    public static function all(): array
    {
        return [
            self::VIEW,
            self::CONFIGURE,
            self::MANAGE_ACCOUNTS,
            self::VIEW_SENSITIVE,
            self::MANAGE_INCOME,
            self::MANAGE_EXPENSES,
            self::APPROVE,
            self::PAY,
            self::REFUND,
            self::MANAGE_BUDGETS,
            self::RECONCILE,
            self::VIEW_REPORTS,
            self::EXPORT,
            self::VIEW_AUDIT,
        ];
    }
}
