<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\URL;

final class Link extends Model
{
    protected $fillable = [
        'user_id',
        'code',
        'url',
        'clicks_count'
    ];

    protected $casts = [
        'clicks_count' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function clicks(): HasMany
    {
        return $this->hasMany(LinkClick::class);
    }

    public function getShortUrlAttribute(): string
    {
        return URL::route('link.redirect', $this->code);
    }

    public function incrementClicks(): void
    {
        $this->increment('clicks_count');
    }
}
