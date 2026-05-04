<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Views\ViewTeam;
use App\Models\Views\ViewTeamMember;
use App\Models\Team;
use App\Models\TeamMember;
use App\Services\FirebaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TeamController extends Controller
{
    protected $firebase;

    public function __construct(FirebaseService $firebase)
    {
        $this->firebase = $firebase;
    }

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

        $message = DB::transaction(function () use ($id, $request, &$targetUser, &$notificationData) {
            if ($request->type === 'team') {
                $team = Team::where('team_id', $id)->firstOrFail();
                $leader = TeamMember::where('team_id', $id)->where('role', 'leader')->with('user')->first();
                $targetUser = $leader ? $leader->user : null;
                
                if ($request->action === 'approve') {
                    $team->update(['status' => 'approved']);
                    TeamMember::where('team_id', $id)->where('role', 'leader')->update(['status' => 'active']);
                    $statusText = 'disetujui';
                    $msg = 'Tim berhasil disetujui.';
                } else {
                    $team->update(['status' => 'rejected']);
                    TeamMember::where('team_id', $id)->where('role', 'leader')->update(['status' => 'rejected']);
                    $statusText = 'ditolak';
                    $msg = 'Tim telah ditolak.';
                }

                $notificationData = [
                    'title' => "Update Status Tim",
                    'body'  => "Pengajuan tim '{$team->team_name}' Anda telah {$statusText}.",
                    'data'  => [
                        'team_id' => (string) $team->team_id,
                        'type'    => 'team_status_update'
                    ]
                ];

                return $msg;
            } else {
                $member = TeamMember::where('member_id', $id)->with(['user', 'team'])->firstOrFail();
                $targetUser = $member->user;
                $teamName = $member->team->team_name ?? 'Tim';

                if ($request->action === 'approve') {
                    $member->update(['status' => 'active']);
                    $statusText = 'disetujui';
                    $msg = 'Permohonan bergabung disetujui.';
                } else {
                    $member->update(['status' => 'rejected']);
                    $statusText = 'ditolak';
                    $msg = 'Permohonan bergabung ditolak.';
                }

                $notificationData = [
                    'title' => "Update Permohonan Tim",
                    'body'  => "Permohonan bergabung ke tim '{$teamName}' Anda telah {$statusText}.",
                    'data'  => [
                        'team_id' => (string) $member->team_id,
                        'type'    => 'member_status_update'
                    ]
                ];

                return $msg;
            }
        });

        // Send Push Notification
        if (isset($targetUser) && $targetUser->fcm_token && isset($notificationData)) {
            try {
                $this->firebase->sendNotification(
                    $targetUser->fcm_token,
                    $notificationData['title'],
                    $notificationData['body'],
                    $notificationData['data']
                );
            } catch (\Throwable $e) {
                Log::warning("FCM Notification failed for user {$targetUser->user_id}: " . $e->getMessage());
            }
        }

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
