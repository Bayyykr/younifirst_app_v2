<?php

namespace App\Models\Views;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * Read-only model untuk view_events.
 * Sudah include: name_category, creator_name, creator_email, total_likes.
 */
class ViewEvent extends Model
{
    protected $table = 'view_events';

    protected $primaryKey = 'event_id';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $guarded = ['*'];

    protected $appends = ['poster_url'];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'total_likes' => 'integer',
        'poster' => 'string',
        'rejection_reason' => 'string',
    ];

    protected function posterUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->poster
                ? Storage::disk('public')->url(
                    $this->poster,
                )
                : null,
        );
    }
}
