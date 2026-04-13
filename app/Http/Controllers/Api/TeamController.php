<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Views\ViewTeam;
use App\Models\Views\ViewTeamMember;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    /**
     * GET /api/teams
     */
    public function index(Request $request)
    {
        $query = ViewTeam::query();

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

        $perPage = min((int) $request->get('per_page', 15), 100);

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

        $perPage = min((int) $request->get('per_page', 20), 100);

        return response()->json($query->paginate($perPage));
    }
}
