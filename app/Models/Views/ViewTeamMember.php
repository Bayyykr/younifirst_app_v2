<?php

namespace App\Models\Views;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * Read-only model untuk view_team_members.
 * Sudah include: user_name, email, nim, prodi, team_name, competition_name.
 */
class ViewTeamMember extends Model
{
    protected $table = 'view_team_members';

    protected $primaryKey = 'member_id';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $guarded = ['*'];

    protected $appends = ['portfolio_url'];

    protected $casts = [
        'rejection_reason' => 'string',
    ];

    public function getPortfolioUrlAttribute()
    {
        return $this->portfolio
            ? Storage::disk('public')->url(
                $this->portfolio,
            )
            : null;
    }
}
