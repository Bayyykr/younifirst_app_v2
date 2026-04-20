<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LostfoundItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'lostfound_items';
    protected $primaryKey = 'lostfound_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    const DELETED_AT = 'deleted_at';

    protected $fillable = [
        'lostfound_id', 'user_id', 'item_name', 'description',
        'photo', 'location', 'status',
        'created_at', 'updated_at',
    ];

    protected $appends = ['photo_url'];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'photo'      => 'string',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function comments()
    {
        return $this->hasMany(LostfoundComment::class, 'lostfound_id', 'lostfound_id');
    }

    protected function photoUrl(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: fn () => $this->photo ? \Illuminate\Support\Facades\Storage::disk('public')->url($this->photo) : null,
        );
    }
}
