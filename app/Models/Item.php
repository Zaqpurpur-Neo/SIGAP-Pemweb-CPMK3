<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        "category_id",
        "location_id",
        "name",
        "code",
        "unit",
        "stock",
        "minimum_stock",
        "photo",
        "description",
    ];

    protected $appends = ["is_low_stock"];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function mutations(): HasMany
    {
        return $this->hasMany(Mutation::class);
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->stock <= $this->minimum_stock;
    }

    /**
     * Scope filter untuk pencarian dan filter kategori/lokasi
     */
    public function scopeFilter(
        Builder $query,
        ?string $search,
        ?string $categoryId,
        ?string $locationId,
    ): Builder {
        return $query
            ->when($search, function ($q) use ($search) {
                $q->where(function ($q2) use ($search) {
                    $q2->where("name", "like", "%{$search}%")->orWhere(
                        "code",
                        "like",
                        "%{$search}%",
                    );
                });
            })
            ->when($categoryId, fn($q) => $q->where("category_id", $categoryId))
            ->when(
                $locationId,
                fn($q) => $q->where("location_id", $locationId),
            );
    }
}
