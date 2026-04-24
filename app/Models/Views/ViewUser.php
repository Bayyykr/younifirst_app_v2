<?php

namespace App\Models\Views;

use Illuminate\Database\Eloquent\Model;

/**
 * Read-only model untuk view_users.
 * Kolom sensitif (password, photo) sudah dikecualikan di level database view.
 */
class ViewUser extends Model
{
    protected $table = 'view_users';
    protected $primaryKey = 'user_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    // View bersifat read-only
    protected $guarded = ['*'];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function suspensions()
    {
        return $this->hasMany(\App\Models\UserSuspension::class, 'user_id', 'user_id');
    }

    public function getActiveSuspensionAttribute()
    {
        if ($this->status !== 'suspended') return null;
        return $this->suspensions()->latest()->first();
    }

    protected $appends = ['active_suspension'];
}
