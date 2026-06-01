<?php

namespace App\Models\Views;

use Illuminate\Database\Eloquent\Model;

class ViewLostfoundComment extends Model
{
    protected $table = 'view_lostfound_comments';
    protected $primaryKey = 'comment_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $guarded = ['*'];

    protected $casts = [
        'created_at' => 'datetime',
        'update_at'  => 'datetime',
    ];
}
