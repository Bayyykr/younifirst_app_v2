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

    protected $appends = ['file_url'];

    protected $casts = [
        'created_at' => 'datetime',
        'deleted_at' => 'datetime',
        'file'       => 'string',
    ];

    protected function fileUrl(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: fn () => $this->file ? \Illuminate\Support\Facades\Storage::disk('public')->url($this->file) : null,
        );
    }
}
