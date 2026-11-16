<?php

namespace App\Providers;

use App\Domain\Ticket\Events\AttachmentAdded;
use App\Domain\Ticket\Events\CommentAdded;
use App\Domain\Ticket\Events\FirstResponseRecorded;
use App\Domain\Ticket\Events\TicketAssigned;
use App\Domain\Ticket\Events\TicketPriorityChanged;
use App\Domain\Ticket\Events\TicketStatusChanged;
use App\Domain\Ticket\Events\TicketSubmitted;
use App\Listeners\NotifyAboutComment;
use App\Listeners\NotifyAssignee;
use App\Listeners\NotifyCustomerAboutStatus;
use App\Listeners\NotifyStaffAboutNewTicket;
use App\Listeners\RecordTicketHistory;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, list<class-string>>
     */
    protected $listen = [
        TicketSubmitted::class => [
            RecordTicketHistory::class,
            NotifyStaffAboutNewTicket::class,
        ],
        TicketAssigned::class => [
            RecordTicketHistory::class,
            NotifyAssignee::class,
        ],
        TicketStatusChanged::class => [
            RecordTicketHistory::class,
            NotifyCustomerAboutStatus::class,
        ],
        TicketPriorityChanged::class => [
            RecordTicketHistory::class,
        ],
        CommentAdded::class => [
            RecordTicketHistory::class,
            NotifyAboutComment::class,
        ],
        AttachmentAdded::class => [
            RecordTicketHistory::class,
        ],
        FirstResponseRecorded::class => [
            RecordTicketHistory::class,
        ],
    ];

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
