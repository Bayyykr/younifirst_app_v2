<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\Views\ViewTeam;
use App\Models\Views\ViewTeamMember;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class TeamController extends Controller
{
    /**
     * GET /api/teams
     */
    public function index(Request $request)
    {
        $query = ViewTeam::where('status', 'approved');

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(function ($qb) use ($q) {
                $qb->where('team_name', 'like', "%$q%")
                   ->orWhere('competition_name', 'like', "%$q%");
            });
        }

        if ($request->filled('status')) {
            match (strtolower($request->status)) {
                'open' => $query->whereRaw('current_member_count < max_member'),
                'full' => $query->whereRaw('current_member_count >= max_member'),
                default => null,
            };
        }

        $perPage = min((int) $request->input('per_page', 15), 100);

        return response()->json($query->orderBy('created_at', 'desc')->paginate($perPage));
    }

    /**
     * GET /api/teams/{team_id}
     */
    public function show(string $team_id)
    {
        $team = ViewTeam::where('team_id', $team_id)->firstOrFail();
        return response()->json(['data' => $team]);
    }

    /**
     * GET /api/teams/{team_id}/members
     */
    public function members(string $team_id, Request $request)
    {
        $query = ViewTeamMember::where('team_id', $team_id);

        if ($request->filled('role'))   $query->where('member_role', $request->role);
        if ($request->filled('status')) $query->where('member_status', $request->status);

        $perPage = min((int) $request->input('per_page', 20), 100);

        return response()->json($query->paginate($perPage));
    }

    /**
     * POST /api/teams
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'team_name'        => 'required|string|max:50',
            'competition_name' => 'required|string|max:100',
            'description'      => 'required|string',
            'max_member'       => 'required|integer|min:1',
        ]);

        return DB::transaction(function () use ($validated, $request) {
            $team = new Team();
            // Generate custom ID: TEM + 7 random characters (total 10)
            $team->team_id = 'TEM' . strtoupper(Str::random(7));
            $team->fill($validated);
            $team->created_at = \Illuminate\Support\Carbon::now();
            $team->status = 'pending'; // Explicitly set pending
            $team->save();

            // Automatically add creator as leader
            $member = new TeamMember();
            $member->member_id = 'MEM' . strtoupper(Str::random(7));
            $member->team_id   = $team->team_id;
            $member->user_id   = $request->user()->user_id;
            $member->role      = 'leader';
            $member->status    = 'pending';
            $member->save();

            return response()->json(['message' => 'Team created successfully and waiting for admin approval', 'data' => $team], 211);
        });
    }

    /**
     * PUT /api/teams/{team_id}
     */
    public function update(Request $request, string $team_id)
    {
        $team = Team::where('team_id', $team_id)->firstOrFail();

        $validated = $request->validate([
            'team_name'        => 'sometimes|required|string|max:50',
            'competition_name' => 'sometimes|required|string|max:100',
            'description'      => 'sometimes|required|string',
            'max_member'       => 'sometimes|required|integer|min:1',
        ]);

        $team->fill($validated);
        $team->updated_at = now();
        $team->save();

        return response()->json(['message' => 'Team updated successfully', 'data' => $team]);
    }

    /**
     * DELETE /api/teams/{team_id}
     */
    public function destroy(string $team_id)
    {
        $team = Team::where('team_id', $team_id)->firstOrFail();
        $team->delete(); // SoftDeletes trait sets deleted_at automatically

        return response()->json(['message' => 'Team deleted successfully']);
    }

    /**
     * POST /api/teams/{team_id}/join
     */
    public function join(string $team_id, Request $request)
    {
        $user = $request->user();
        
        $team = Team::where('team_id', $team_id)->firstOrFail();

        if ($team->status !== 'approved') {
            return response()->json(['message' => 'You cannot join a team that is not yet approved by admin'], 422);
        }

        $viewTeam = ViewTeam::where('team_id', $team_id)->first();
        if ($viewTeam && $viewTeam->current_member_count >= $team->max_member) {
            return response()->json(['message' => 'Team is already full'], 422);
        }

        $isMember = TeamMember::where('team_id', $team_id)
            ->where('user_id', $user->user_id)
            ->exists();

        if ($isMember) {
            return response()->json(['message' => 'You are already a member or have a pending request'], 422);
        }

        $member = new TeamMember();
        $member->member_id = 'MEM' . strtoupper(Str::random(7));
        $member->team_id   = $team_id;
        $member->user_id   = $user->user_id;
        $member->role      = 'member';
        $member->status    = 'pending'; // Set to pending by default
        $member->save();

        return response()->json([
            'message' => 'Join request sent successfully. Waiting for leader approval.',
            'data'    => $member
        ], 201);
    }

    /**
     * POST /api/teams/{team_id}/members/{member_id}/respond
     */
    public function respondJoin(string $team_id, string $member_id, Request $request)
    {
        $leader = $request->user();
        
        $validated = $request->validate([
            'action' => 'required|in:accept,reject'
        ]);

        // 1. Verify requester is the LEADER of this team
        $isLeader = TeamMember::where('team_id', $team_id)
            ->where('user_id', $leader->user_id)
            ->where('role', 'leader')
            ->exists();

        if (!$isLeader) {
            return response()->json(['message' => 'Only the team leader can respond to join requests.'], 403);
        }

        // 2. Find the pending member
        $member = TeamMember::where('team_id', $team_id)
            ->where('member_id', $member_id)
            ->firstOrFail();

        if ($member->status !== 'pending') {
            return response()->json(['message' => 'This member is not in pending status.'], 422);
        }

        // 3. Handle action
        if ($validated['action'] === 'accept') {
            $member->status = 'active';
            $message = 'Member request accepted.';
        } else {
            $member->status = 'rejected';
            $message = 'Member request rejected.';
        }

        $member->save();

        return response()->json([
            'message' => $message,
            'data'    => $member
        ]);
    }
}
