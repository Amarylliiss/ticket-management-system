<?php

namespace Database\Factories;

use App\Infrastructure\Persistence\Eloquent\Models\CategoryModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<CategoryModel>
 */
class CategoryModelFactory extends Factory
{
    protected $model = CategoryModel::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => ucfirst($name),
            'slug' => Str::slug($name).'-'.fake()->unique()->numerify('###'),
            'description' => fake()->sentence(),
            'is_active' => true,
        ];
    }
}
