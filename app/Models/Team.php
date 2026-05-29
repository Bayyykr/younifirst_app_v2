<?php

namespace App\Models;

use App\Mail\TeamCreatedMail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class Team extends Model
{
    use HasFactory, SoftDeletes;

    protected static function booted()
    {
        static::created(function ($team) {
            try {
                $admins = User::where('role', 'admin')
                    ->where('notify_email', true)
                    ->where('notify_team', true)
                    ->get();

                foreach ($admins as $admin) {
                    Mail::to($admin->email)->send(new TeamCreatedMail($team));
                }
            } catch (\Exception $e) {
                Log::error(
                    'Gagal mengirim email notifikasi team: '.$e->getMessage(),
                );
            }
        });
    }

    protected $table = 'teams';

    protected $primaryKey = 'team_id';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    const CREATED_AT = 'created_at';

    const UPDATED_AT = 'updated_at';

    const DELETED_AT = 'deleted_at';

    protected $fillable = [
        'team_id',
        'team_name',
        'competition_name',
        'description',
        'max_member',
        'status',
        'rejection_reason',
        'competition_level',
        'achievement_rank',
        'photo_activity',
        'photo_certificate',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function members()
    {
        return $this->hasMany(TeamMember::class, 'team_id', 'team_id');
    }
}
