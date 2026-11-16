<?php

namespace App\Http\Controllers\Api;

use App\Domain\Identity\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AgentController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        abort_unless($request->user()->isStaff(), 403, 'Lista pracowników jest dostępna tylko dla zespołu.');

        $agents = User::query()
            ->whereIn('role', [UserRole::Agent->value, UserRole::Admin->value])
            ->orderBy('name')
            ->get();

        return UserResource::collection($agents);
    }
}
