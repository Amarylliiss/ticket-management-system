<?php

declare(strict_types=1);

namespace App\Domain\Ticket\Enums;

enum ActivityType: string
{
    case Submitted = 'submitted';
    case Assigned = 'assigned';
    case StatusChanged = 'status_changed';
    case PriorityChanged = 'priority_changed';
    case CommentAdded = 'comment_added';
    case AttachmentAdded = 'attachment_added';
    case FirstResponse = 'first_response';

    public function label(): string
    {
        return match ($this) {
            self::Submitted => 'Zgłoszenie',
            self::Assigned => 'Przypisanie',
            self::StatusChanged => 'Status',
            self::PriorityChanged => 'Priorytet',
            self::CommentAdded => 'Komentarz',
            self::AttachmentAdded => 'Załącznik',
            self::FirstResponse => 'Pierwsza odpowiedź',
        };
    }
}
