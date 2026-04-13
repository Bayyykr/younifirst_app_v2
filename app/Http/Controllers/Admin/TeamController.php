<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Views\ViewTeam;
use App\Models\Views\ViewTeamMember;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function index()
    {
        $teamsRaw = ViewTeam::orderBy('created_at', 'desc')->get();

        // Ambil semua active member sekaligus → group by team_id di PHP
        $membersByTeam = ViewTeamMember::where('member_status', 'active')
            ->get()
            ->groupBy('team_id')
            ->map(fn($members) => $members
                ->take(3)
                ->map(fn($m) => [
                    'name'         => $m->user_name,
                    'encoded_name' => urlencode($m->user_name),
                ])
                ->values()
                ->toArray()
            );

        $teams = $teamsRaw->map(fn($team) => [
            'id'            => $team->team_id,
            'name'          => $team->team_name,
            'competition'   => $team->competition_name,
            'active_count'  => $team->current_member_count,
            'pending_count' => $team->pending_member_count,
            'max_member'    => $team->max_member,
            'status'        => $team->current_member_count >= $team->max_member ? 'Full' : 'Open',
            'top_members'   => $membersByTeam->get($team->team_id, []),
        ]);

        return view('admin.teams', compact('teams'));
    }
}
