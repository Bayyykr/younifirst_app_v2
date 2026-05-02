<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Views\ViewTeam;
use App\Models\Views\ViewTeamMember;
use App\Models\Team;
use App\Models\TeamMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        $teams = $teamsRaw->where('status', 'approved')->values()->map(fn($team) => [
            'id'            => $team->team_id,
            'name'          => $team->team_name,
            'competition'   => $team->competition_name,
            'leader_name'   => $team->leader_name,
            'created_at'    => $team->created_at,
            'description'   => $team->description,
            'active_count'  => $team->current_member_count,
            'pending_count' => $team->pending_member_count,
            'max_member'    => $team->max_member,
            'status'        => $team->current_member_count >= $team->max_member ? 'Full' : 'Open',
            'top_members'   => $membersByTeam->get($team->team_id, []),
        ]);

        $pendingTeams = ViewTeam::where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        $stats = [
            'total' => $teams->count(),
            'open' => $teams->where('status', 'Open')->count(),
            'full' => $teams->where('status', 'Full')->count(),
            'pending' => $pendingTeams->count(),
        ];

        return view('admin.teams', [
            'teams' => $teams,
            'pendingTeams' => $pendingTeams,
            'stats' => $stats
        ]);
    }

    public function respond(Request $request, $id)
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
            'type'   => 'required|in:team,member',
        ]);

        $message = DB::transaction(function () use ($id, $request) {
            if ($request->type === 'team') {
                $team = Team::where('team_id', $id)->firstOrFail();
                if ($request->action === 'approve') {
                    $team->update(['status' => 'approved']);
                    
                    // Activate leader status
                    TeamMember::where('team_id', $id)
                        ->where('role', 'leader')
                        ->update(['status' => 'active']);
                        
                    return 'Tim berhasil disetujui.';
                } else {
                    $team->update(['status' => 'rejected']);
                    
                    // Reject leader status
                    TeamMember::where('team_id', $id)
                        ->where('role', 'leader')
                        ->update(['status' => 'rejected']);
                        
                    return 'Tim telah ditolak.';
                }
            } else {
                $member = TeamMember::where('member_id', $id)->firstOrFail();
                if ($request->action === 'approve') {
                    $member->update(['status' => 'active']);
                    return 'Permohonan bergabung disetujui.';
                } else {
                    $member->update(['status' => 'rejected']);
                    return 'Permohonan bergabung ditolak.';
                }
            }
        });

        if ($request->ajax()) {
            return response()->json(['message' => $message]);
        }

        return back()->with('success', $message);
    }

    public function destroy(Request $request, $id)
    {
        $team = Team::where('team_id', $id)->firstOrFail();
        $team->delete(); // Soft delete

        if ($request->ajax()) {
            return response()->json(['message' => 'Tim berhasil dihapus.']);
        }

        return back()->with('success', 'Tim berhasil dihapus.');
    }
}
