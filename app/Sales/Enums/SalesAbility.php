<?php

namespace App\Sales\Enums;

/**
 * Named abilities for future RBAC. v1 gates with event.admin only.
 */
final class SalesAbility
{
    public const VIEW = 'sales.view';
    public const DASHBOARD_VIEW = 'sales.dashboard.view';
    public const PIPELINE_TYPES_VIEW = 'sales.pipeline-types.view';
    public const PIPELINE_TYPES_CREATE = 'sales.pipeline-types.create';
    public const PIPELINE_TYPES_UPDATE = 'sales.pipeline-types.update';
    public const PIPELINE_TYPES_DELETE = 'sales.pipeline-types.delete';
    public const PIPELINES_VIEW = 'sales.pipelines.view';
    public const PIPELINES_CREATE = 'sales.pipelines.create';
    public const PIPELINES_UPDATE = 'sales.pipelines.update';
    public const PIPELINES_DELETE = 'sales.pipelines.delete';
    public const DEALS_VIEW = 'sales.deals.view';
    public const DEALS_CREATE = 'sales.deals.create';
    public const DEALS_UPDATE = 'sales.deals.update';
    public const DEALS_DELETE = 'sales.deals.delete';
    public const DEALS_MOVE_STAGE = 'sales.deals.move-stage';
    public const DEALS_ASSIGN = 'sales.deals.assign';
    public const DEALS_EXPORT = 'sales.deals.export';
    public const FORMS_VIEW = 'sales.forms.view';
    public const FORMS_CREATE = 'sales.forms.create';
    public const FORMS_UPDATE = 'sales.forms.update';
    public const FORMS_DELETE = 'sales.forms.delete';
    public const FORMS_PUBLISH = 'sales.forms.publish';
    public const SUBMISSIONS_VIEW = 'sales.form-submissions.view';
    public const SUBMISSIONS_UPDATE = 'sales.form-submissions.update';
    public const SUBMISSIONS_DELETE = 'sales.form-submissions.delete';
    public const SUBMISSIONS_CONVERT = 'sales.form-submissions.convert-to-deal';
    public const SUBMISSIONS_EXPORT = 'sales.form-submissions.export';

    /** @return list<string> */
    public static function all(): array
    {
        return [
            self::VIEW,
            self::DASHBOARD_VIEW,
            self::PIPELINE_TYPES_VIEW,
            self::PIPELINE_TYPES_CREATE,
            self::PIPELINE_TYPES_UPDATE,
            self::PIPELINE_TYPES_DELETE,
            self::PIPELINES_VIEW,
            self::PIPELINES_CREATE,
            self::PIPELINES_UPDATE,
            self::PIPELINES_DELETE,
            self::DEALS_VIEW,
            self::DEALS_CREATE,
            self::DEALS_UPDATE,
            self::DEALS_DELETE,
            self::DEALS_MOVE_STAGE,
            self::DEALS_ASSIGN,
            self::DEALS_EXPORT,
            self::FORMS_VIEW,
            self::FORMS_CREATE,
            self::FORMS_UPDATE,
            self::FORMS_DELETE,
            self::FORMS_PUBLISH,
            self::SUBMISSIONS_VIEW,
            self::SUBMISSIONS_UPDATE,
            self::SUBMISSIONS_DELETE,
            self::SUBMISSIONS_CONVERT,
            self::SUBMISSIONS_EXPORT,
        ];
    }
}
