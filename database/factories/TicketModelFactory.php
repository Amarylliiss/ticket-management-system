<?php

namespace Database\Factories;

use App\Domain\Ticket\Enums\TicketPriority;
use App\Domain\Ticket\Enums\TicketStatus;
use App\Infrastructure\Persistence\Eloquent\Models\CategoryModel;
use App\Infrastructure\Persistence\Eloquent\Models\TicketModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<TicketModel>
 */
class TicketModelFactory extends Factory
{
    protected $model = TicketModel::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'reference' => sprintf('TCK-%06d', fake()->unique()->numberBetween(100000, 899999)),
            'title' => fake()->sentence(5),
            'description' => fake()->paragraph(),
            'category_id' => CategoryModel::factory(),
            'priority' => TicketPriority::Medium,
            'status' => TicketStatus::Open,
            'customer_id' => User::factory(),
            'assignee_id' => null,
        ];
    }
}
