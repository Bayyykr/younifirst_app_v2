<?php

namespace App\Models\Views;

use Illuminate\Database\Eloquent\Model;

/**
 * Read-only model untuk view_announcements.
 * Sudah include: creator_name, creator_email, creator_role.
 */
class ViewAnnouncement extends Model
{
    protected $table = 'view_announcements';
    protected $primaryKey = 'announcement_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $guarded = ['*'];

    protected $casts = [
        'created_at' => 'datetime',
    ];
}
