<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LostfoundItem extends Model
{
    use HasFactory;

    protected $table = 'lostfound_items';
    protected $primaryKey = 'lostfound_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'lostfound_id', 'user_id', 'item_name', 'description',
        'photo', 'location', 'status_id',
        'created_at', 'updated_at', 'deleted_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function status()
    {
        return $this->belongsTo(ItemStatus::class, 'status_id', 'status_id');
    }

    public function comments()
    {
        return $this->hasMany(LostfoundComment::class, 'lostfound_id', 'lostfound_id');
    }
}
