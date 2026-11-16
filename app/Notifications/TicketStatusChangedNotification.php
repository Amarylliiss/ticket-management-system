<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Domain\Ticket\Enums\TicketStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketStatusChangedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $ticketId,
        public string $reference,
        public string $title,
        public string $status,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $label = TicketStatus::from($this->status)->label();

        return (new MailMessage)
            ->subject("Status zgłoszenia {$this->reference}")
            ->line("Zgłoszenie „{$this->title}” ma teraz status: {$label}.");
    }

    /**
     * @return array<string, string>
     */
    public function toArray(object $notifiable): array
    {
        $label = TicketStatus::from($this->status)->label();

        return [
            'kind' => 'status',
            'ticket_id' => $this->ticketId,
            'reference' => $this->reference,
            'title' => $this->title,
            'status' => $this->status,
            'message' => "Zgłoszenie {$this->reference} zmieniło status na „{$label}”.",
        ];
    }
}
