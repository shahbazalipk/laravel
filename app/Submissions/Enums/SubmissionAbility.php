<?php

namespace App\Submissions\Enums;

final class SubmissionAbility
{
    public const VIEW = 'submissions.view';

    public const CONFIGURE = 'submissions.configure';

    public const MANAGE = 'submissions.manage';

    public const CHANGE_STAGE = 'submissions.change-stage';

    public const ASSIGN_REVIEWERS = 'submissions.assign-reviewers';

    public const REVIEW = 'submissions.review';

    public const DECIDE = 'submissions.decide';

    public const REVISE = 'submissions.request-revision';

    public const CONVERT_SPEAKER = 'submissions.convert-speaker';

    public const MANAGE_ONBOARDING = 'submissions.manage-onboarding';

    public const MANAGE_CONTRACTS = 'submissions.manage-contracts';

    public const VIEW_FINANCIAL = 'submissions.view-financial';

    public const MANAGE_TRAVEL = 'submissions.manage-travel';

    public const VIEW_SENSITIVE = 'submissions.view-sensitive';

    public const EXPORT = 'submissions.export';

    /** @return list<string> */
    public static function all(): array
    {
        return [
            self::VIEW, self::CONFIGURE, self::MANAGE, self::CHANGE_STAGE, self::ASSIGN_REVIEWERS,
            self::REVIEW, self::DECIDE, self::REVISE, self::CONVERT_SPEAKER, self::MANAGE_ONBOARDING,
            self::MANAGE_CONTRACTS, self::VIEW_FINANCIAL, self::MANAGE_TRAVEL, self::VIEW_SENSITIVE, self::EXPORT,
        ];
    }
}
