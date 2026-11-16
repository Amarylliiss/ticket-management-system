<?php

declare(strict_types=1);

namespace App\Policies;

use App\Infrastructure\Persistence\Eloquent\Models\CategoryModel;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): Response
    {
        return $user->isAdmin()
            ? Response::allow()
            : Response::deny('Kategorie może zmieniać tylko administrator.');
    }

    public function update(User $user, CategoryModel $category): Response
    {
        return $this->create($user);
    }

    public function delete(User $user, CategoryModel $category): Response
    {
        return $this->create($user);
    }
}
