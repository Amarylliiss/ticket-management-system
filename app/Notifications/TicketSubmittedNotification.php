<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketSubmittedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $ticketId,
        public string $reference,
        public string $title,
        public string $priority,
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
            ->subject("Nowe zgłoszenie {$this->reference}")
            ->line("Klient zgłosił: „{$this->title}”.");
    }

    /**
     * @return array<string, string>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'kind' => 'submitted',
            'ticket_id' => $this->ticketId,
            'reference' => $this->reference,
            'title' => $this->title,
            'priority' => $this->priority,
            'message' => "Nowe zgłoszenie {$this->reference}: {$this->title}.",
        ];
    }
}
