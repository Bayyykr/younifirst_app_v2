<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSuspension extends Model
{
    protected $fillable = [
        'user_id',
        'duration',
        'reason',
        'internal_notes',
        'ends_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
