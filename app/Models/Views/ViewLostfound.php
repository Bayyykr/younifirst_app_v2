<?php

namespace App\Models\Views;

use Illuminate\Database\Eloquent\Model;

class ViewLostfound extends Model
{
    protected $table = 'view_lostfound';
    protected $primaryKey = 'lostfound_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $guarded = ['*'];

    protected $appends = ['photo_url'];

    protected $casts = [
        'created_at'     => 'datetime',
        'updated_at'     => 'datetime',
        'deleted_at'     => 'datetime',
        'total_comments' => 'integer',
        'status'         => 'string',
        'photo'          => 'string',
    ];

    protected function photoUrl(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: fn () => $this->photo ? \Illuminate\Support\Facades\Storage::disk('public')->url($this->photo) : null,
        );
    }
}
