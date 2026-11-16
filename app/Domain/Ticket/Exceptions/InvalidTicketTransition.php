<?php

declare(strict_types=1);

namespace App\Domain\Ticket\Exceptions;

use DomainException;

final class InvalidTicketTransition extends DomainException {}
