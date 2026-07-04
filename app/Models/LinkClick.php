<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class LinkClick extends Model
{
    public const string|null UPDATED_AT = null;
    public const string CREATED_AT = 'clicked_at';

    protected $fillable = [
        'link_id',
        'ip_address',
        'user_agent',
        'clicked_at'
    ];

    protected $casts = [
        'clicked_at' => 'datetime'
    ];

    public function link(): BelongsTo
    {
        return $this->belongsTo(Link::class);
    }
}
