<?php

namespace App\Models;

/**
 * The statuses from Jira, and the category they relate to
 */
enum Status: string
{
    case TODO = 'to do';

    case IN_PROGRESS = 'in progress';

    case REVIEW = 'review';

    case UAT = 'uat';

    case RELEASABLE = 'releasable';

    case DONE = 'done';

    public static function isToDoCategory(string $status): bool
    {
        return strtolower($status) === strtolower(Status::TODO->value);
    }

    public static function isInProgressCategory(string $status): bool
    {
        return match (strtolower($status)) {
            Status::IN_PROGRESS->value, Status::REVIEW->value, Status::UAT->value => true,
            default => false
        };
    }

    public static function isDoneCategory(string $status): bool
    {
        return match (strtolower($status)) {
            Status::RELEASABLE->value, Status::DONE->value => true,
            default => false
        };
    }
}
