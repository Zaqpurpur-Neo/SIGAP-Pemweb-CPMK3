<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mutation extends Model
{
    protected $fillable = [
        "item_id",
        "type",
        "quantity",
        "date",
        "note",
        "user_id",
    ];

    protected $casts = [
        "date" => "date",
        "type" => "string",
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
