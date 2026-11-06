<?php

namespace App\Enums;

/**
 * Signals an annotation may carry, for the filter of flagged rows and the badges of the log.
 */
enum ActivityFlag: string
{
    case SelfReview = 'self_review';
    case AfterDeadline = 'after_deadline';
    case RoleOverlap = 'role_overlap';

    public function label(): string
    {
        return match ($this) {
            self::SelfReview => 'Autorrevisión',
            self::AfterDeadline => 'Fuera de plazo',
            self::RoleOverlap => 'Doble papel',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::SelfReview => 'rejected',
            self::AfterDeadline => 'pending',
            self::RoleOverlap => 'notice',
        };
    }
}
