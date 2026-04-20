<?php

namespace App\Models\Views;

use Illuminate\Database\Eloquent\Model;

/**
 * Read-only model untuk view_teams.
 * Sudah include: current_member_count, leader_name, leader_id.
 */
class ViewTeam extends Model
{
    protected $table = 'view_teams';
    protected $primaryKey = 'team_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $guarded = ['*'];

    protected $casts = [
        'created_at'            => 'datetime',
        'updated_at'            => 'datetime',
        'deleted_at'            => 'datetime',
        'max_member'            => 'integer',
        'current_member_count'  => 'integer',
        'pending_member_count'  => 'integer',
    ];
}
