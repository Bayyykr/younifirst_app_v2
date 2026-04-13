<?php

namespace App\Models\Views;

use Illuminate\Database\Eloquent\Model;

/**
 * Read-only model untuk view_lostfound.
 * Sudah include: name_status, reporter_name, reporter_email, reporter_nim, reporter_prodi, total_comments.
 */
class ViewLostfound extends Model
{
    protected $table = 'view_lostfound';
    protected $primaryKey = 'lostfound_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $guarded = ['*'];

    protected $casts = [
        'created_at'     => 'datetime',
        'updated_at'     => 'datetime',
        'deleted_at'     => 'datetime',
        'total_comments' => 'integer',
    ];
}
