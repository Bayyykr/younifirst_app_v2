<?php

namespace App\Models\Views;

use Illuminate\Database\Eloquent\Model;

/**
 * Read-only model untuk view_event_likes.
 * Sudah include: event_title, event_status, user_name, user_email, nim, prodi.
 */
class ViewEventLike extends Model
{
    protected $table = 'view_event_likes';
    protected $primaryKey = 'like_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $guarded = ['*'];

    protected $casts = [
        'liked_at' => 'datetime',
    ];
}
