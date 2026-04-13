<?php

namespace App\Models\Views;

use Illuminate\Database\Eloquent\Model;

/**
 * Read-only model untuk view_lostfound_comments.
 * Sudah include: item_name, item_location, commenter_name, commenter_email, nim, prodi.
 */
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
