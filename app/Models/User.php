<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $primaryKey = 'user_id';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'firebase_uid',
        'name',
        'email',
        'password',
        'role',
        'nim',
        'prodi',
        'photo',
        'status',
        'fcm_token',
        'created_at',
        'notify_email',
        'notify_event',
        'notify_team',
        'notify_lostfound',
    ];

    protected $hidden = ['password'];

    protected $casts = [
        'created_at' => 'datetime',
        'notify_email' => 'boolean',
        'notify_event' => 'boolean',
        'notify_team' => 'boolean',
        'notify_lostfound' => 'boolean',
    ];

    public function announcements()
    {
        return $this->hasMany(Announcement::class, 'created_by', 'user_id');
    }

    public function teamMembers()
    {
        return $this->hasMany(TeamMember::class, 'user_id', 'user_id');
    }

    public function teams()
    {
        return $this->belongsToMany(
            Team::class,
            'team_members',
            'user_id',
            'team_id',
        )->withPivot('role', 'status', 'rejection_reason');
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
        if ($this->status !== 'suspended') {
            return null;
        }

        return $this->suspensions()->latest()->first();
    }

    public function getPhotoUrlAttribute()
    {
        return $this->photo
            ? Storage::disk('public')->url(
                $this->photo,
            )
            : 'https://ui-avatars.com/api/?name='.
                    urlencode($this->name).
                    '&background=3B82F6&color=fff';
    }

    protected $appends = ['active_suspension', 'photo_url'];

    public function lostfoundComments()
    {
        return $this->hasMany(LostfoundComment::class, 'user_id', 'user_id');
    }
}
