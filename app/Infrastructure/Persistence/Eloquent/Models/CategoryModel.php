<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Database\Factories\CategoryModelFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug', 'description', 'is_active'])]
class CategoryModel extends Model
{
    /** @use HasFactory<CategoryModelFactory> */
    use HasFactory;

    protected $table = 'categories';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(TicketModel::class, 'category_id');
    }

    protected static function newFactory(): CategoryModelFactory
    {
        return CategoryModelFactory::new();
    }
}
