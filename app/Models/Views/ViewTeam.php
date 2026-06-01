<?php

namespace App\Models\Views;

use Illuminate\Database\Eloquent\Model;

class ViewTeam extends Model
{
    protected $table = 'view_teams';

    protected $primaryKey = 'team_id';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $guarded = ['*'];

    protected $casts = [
        'created_at' => 'datetime',
        'update_at' => 'datetime',
        'delete_at' => 'datetime',
        'max_member' => 'integer',
        'current_member_count' => 'integer',
        'pending_member_count' => 'integer',
        'competition_level' => 'string',
        'achievement_rank' => 'string',
        'photo_activity' => 'string',
        'photo_certificate' => 'string',
        'rejection_reason' => 'string',
    ];

    public function members()
    {
        return $this->hasMany(
            ViewTeamMember::class,
            'team_id',
            'team_id',
        );
    }
}
