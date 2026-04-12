<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $primaryKey = 'user_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'name', 'email', 'password', 'role',
        'nim', 'prodi', 'photo', 'status', 'created_at',
    ];

    protected $hidden = ['password'];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function announcements()
    {
        return $this->hasMany(Announcement::class, 'created_by', 'user_id');
    }

    public function teamMembers()
    {
        return $this->hasMany(TeamMember::class, 'user_id', 'user_id');
    }

    public function events()
    {
        return $this->hasMany(Event::class, 'created_by', 'user_id');
    }

    public function eventLikes()
    {
        return $this->hasMany(EventLike::class, 'user_id', 'user_id');
    }

    public function lostfoundItems()
    {
        return $this->hasMany(LostfoundItem::class, 'user_id', 'user_id');
    }

    public function lostfoundComments()
    {
        return $this->hasMany(LostfoundComment::class, 'user_id', 'user_id');
    }
}
