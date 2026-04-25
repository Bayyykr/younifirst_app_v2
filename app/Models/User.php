<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $primaryKey = 'user_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'firebase_uid', 'name', 'email', 'password', 'role',
        'nim', 'prodi', 'photo', 'status', 'fcm_token', 'created_at',
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

    public function suspensions()
    {
        return $this->hasMany(UserSuspension::class, 'user_id', 'user_id');
    }

    public function getActiveSuspensionAttribute()
    {
        if ($this->status !== 'suspended') return null;
        return $this->suspensions()->latest()->first();
    }

    protected $appends = ['active_suspension'];

    public function lostfoundComments()
    {
        return $this->hasMany(LostfoundComment::class, 'user_id', 'user_id');
    }
}
