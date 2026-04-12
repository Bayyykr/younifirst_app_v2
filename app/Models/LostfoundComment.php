<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LostfoundComment extends Model
{
    use HasFactory;

    protected $table = 'lostfound_comments';
    protected $primaryKey = 'comment_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'comment_id', 'lostfound_id', 'user_id', 'comment', 'created_at', 'update_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'update_at'  => 'datetime',
    ];

    public function item()
    {
        return $this->belongsTo(LostfoundItem::class, 'lostfound_id', 'lostfound_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
