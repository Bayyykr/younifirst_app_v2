<?php

namespace App\Models\Views;

use Illuminate\Database\Eloquent\Model;

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
        'start_date'  => 'datetime',
        'end_date'    => 'datetime',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
        'deleted_at'  => 'datetime',
        'total_likes' => 'integer',
        'poster'      => 'string',
    ];

    protected function posterUrl(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: fn () => $this->poster ? \Illuminate\Support\Facades\Storage::disk('public')->url($this->poster) : null,
        );
    }
}
