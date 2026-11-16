<?php

namespace App\Providers;

use App\Infrastructure\Persistence\Eloquent\Models\AttachmentModel;
use App\Infrastructure\Persistence\Eloquent\Models\CategoryModel;
use App\Infrastructure\Persistence\Eloquent\Models\TicketModel;
use App\Models\User;
use App\Policies\AttachmentPolicy;
use App\Policies\CategoryPolicy;
use App\Policies\TicketPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::policy(TicketModel::class, TicketPolicy::class);
        Gate::policy(CategoryModel::class, CategoryPolicy::class);
        Gate::policy(AttachmentModel::class, AttachmentPolicy::class);

        Gate::before(function (User $user) {
            return $user->isAdmin() ? true : null;
        });

        Gate::define('viewStatistics', fn (User $user): bool => $user->isStaff());
    }
}
