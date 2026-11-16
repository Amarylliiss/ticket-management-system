<?php

declare(strict_types=1);

namespace App\Policies;

use App\Infrastructure\Persistence\Eloquent\Models\AttachmentModel;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AttachmentPolicy
{
    public function download(User $user, AttachmentModel $attachment): Response
    {
        $attachment->loadMissing('ticket');

        return $user->isStaff() || $user->id === $attachment->ticket->customer_id
            ? Response::allow()
            : Response::deny('Nie masz dostępu do tego załącznika.');
    }
}
