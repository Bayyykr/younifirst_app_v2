<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;

    protected $table = 'teams';
    protected $primaryKey = 'team_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'update_at';

    protected $fillable = [
        'team_id', 'team_name', 'competition_name',
        'description', 'max_member', 'created_at', 'update_at', 'delete_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'update_at'  => 'datetime',
        'delete_at'  => 'datetime',
    ];

    public function members()
    {
        return $this->hasMany(TeamMember::class, 'team_id', 'team_id');
    }
}
