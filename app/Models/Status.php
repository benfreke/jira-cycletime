<?php

namespace App\Models;

/**
 * The statuses from Jira, and the category they relate to
 */
enum Status: string
{
    case TODO = 'To Do';

    case IN_PROGRESS = 'In Progress';

    case REVIEW = 'Review';

    case UAT = 'UAT';

    case RELEASABLE = 'Releasable';

    case DONE = 'Done';

    public static function isToDoCategory(string $status): bool
    {
        return $status === Status::TODO->value;
    }

    public static function isInProgressCategory(string $status): bool
    {
        return match ($status) {
            Status::IN_PROGRESS->value, Status::REVIEW->value, Status::UAT->value => true,
            default => false
        };
    }

    public static function isDoneCategory(string $status): bool
    {
        return match ($status) {
            Status::RELEASABLE->value, Status::DONE->value => true,
            default => false
        };
    }
}
