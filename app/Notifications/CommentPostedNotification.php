<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CommentPostedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $ticketId,
        public string $reference,
        public string $message,
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
            ->subject("Komentarz w zgłoszeniu {$this->reference}")
            ->line($this->message);
    }

    /**
     * @return array<string, string>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'kind' => 'comment',
            'ticket_id' => $this->ticketId,
            'reference' => $this->reference,
            'message' => $this->message,
        ];
    }
}
