<?php

namespace App\Models\Views;

use Illuminate\Database\Eloquent\Model;

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
