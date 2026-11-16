<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketAssignedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $ticketId,
        public string $reference,
        public string $title,
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
        return (new MailMessage)
            ->subject("Przypisano zgłoszenie {$this->reference}")
            ->line("Zgłoszenie „{$this->title}” zostało przypisane do Ciebie.");
    }

    /**
     * @return array<string, string>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'kind' => 'assigned',
            'ticket_id' => $this->ticketId,
            'reference' => $this->reference,
            'title' => $this->title,
            'message' => "Przypisano Ci zgłoszenie {$this->reference}.",
        ];
    }
}
