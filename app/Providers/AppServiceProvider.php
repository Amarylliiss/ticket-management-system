<?php

namespace App\Providers;

use App\Domain\Ticket\Repositories\TicketRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentTicketRepository;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(TicketRepository::class, EloquentTicketRepository::class);
    }

    public function boot(): void
    {
        JsonResource::withoutWrapping();
    }
}
