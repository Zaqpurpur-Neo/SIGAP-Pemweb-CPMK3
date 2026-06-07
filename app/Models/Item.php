<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
}
