<?php

namespace App\Projects\Enums;

final class ProjectAbility
{
    public const VIEW = 'projects.view';

    public const CONFIGURE = 'projects.configure';

    public const CREATE = 'projects.create';

    public const MANAGE = 'projects.manage';

    public const MANAGE_MEMBERS = 'projects.manage-members';

    public const CREATE_TASK = 'projects.create-task';

    public const EDIT_TASK = 'projects.edit-task';

    public const ASSIGN_TASK = 'projects.assign-task';

    public const CHANGE_STATUS = 'projects.change-status';

    public const MANAGE_WORKFLOWS = 'projects.manage-workflows';

    public const COMMENT = 'projects.comment';

    public const LOG_TIME = 'projects.log-time';

    public const VIEW_WORKLOAD = 'projects.view-workload';

    public const APPROVE = 'projects.approve';

    public const VIEW_FINANCIAL = 'projects.view-financial';

    public const VIEW_REPORTS = 'projects.view-reports';

    public const EXPORT = 'projects.export';

    public const VIEW_AUDIT = 'projects.view-audit';

    /** @return list<string> */
    public static function all(): array
    {
        return [
            self::VIEW,
            self::CONFIGURE,
            self::CREATE,
            self::MANAGE,
            self::MANAGE_MEMBERS,
            self::CREATE_TASK,
            self::EDIT_TASK,
            self::ASSIGN_TASK,
            self::CHANGE_STATUS,
            self::MANAGE_WORKFLOWS,
            self::COMMENT,
            self::LOG_TIME,
            self::VIEW_WORKLOAD,
            self::APPROVE,
            self::VIEW_FINANCIAL,
            self::VIEW_REPORTS,
            self::EXPORT,
            self::VIEW_AUDIT,
        ];
    }
}
