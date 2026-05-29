<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\Views\ViewTeam;
use App\Models\Views\ViewTeamMember;
use App\Services\FirebaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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
            ->map(
                fn ($members) => $members
                    ->take(3)
                    ->map(
                        fn ($m) => [
                            'name' => $m->user_name,
                            'encoded_name' => urlencode($m->user_name),
                        ],
                    )
                    ->values()
                    ->toArray(),
            );

        $teams = $teamsRaw->where('status', 'approved')->values()->map(
            fn ($team) => [
                'id' => $team->team_id,
                'name' => $team->team_name,
                'competition' => $team->competition_name,
                'leader_name' => $team->leader_name,
                'created_at' => $team->created_at,
                'description' => $team->description,
                'active_count' => $team->current_member_count,
                'pending_count' => $team->pending_member_count,
                'max_member' => $team->max_member,
                'status' => $team->current_member_count >= $team->max_member
                        ? 'Full'
                        : 'Open',
                'top_members' => $membersByTeam->get($team->team_id, []),
                // Report fields
                'competition_level' => $team->competition_level,
                'achievement_rank' => $team->achievement_rank,
                'photo_activity' => $team->photo_activity
                    ? asset('storage/'.$team->photo_activity)
                    : null,
                'photo_certificate' => $team->photo_certificate
                    ? asset('storage/'.$team->photo_certificate)
                    : null,
                'has_report' => ! empty($team->achievement_rank),
            ],
        );

        $pendingTeams = ViewTeam::where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        $stats = [
            'total' => $teams->count(),
            'open' => $teams->where('status', 'Open')->count(),
            'full' => $teams->where('status', 'Full')->count(),
            'pending' => $pendingTeams->count(),
            'with_report' => $teams->where('has_report', true)->count(),
        ];

        return view('admin.teams', [
            'teams' => $teams,
            'pendingTeams' => $pendingTeams,
            'stats' => $stats,
        ]);
    }

    public function respond(Request $request, $id)
    {
        $request->validate(
            [
                'action' => 'required|in:approve,reject',
                'type' => 'required|in:team,member',
                'rejection_reason' => 'required_if:action,reject|nullable|string|max:1000',
            ],
            [
                'rejection_reason.required_if' => 'Alasan penolakan wajib diisi.',
            ],
        );

        $message = DB::transaction(function () use (
            $id,
            $request,
            &$targetUser,
            &$notificationData,
        ) {
            if ($request->type === 'team') {
                $team = Team::where('team_id', $id)->firstOrFail();
                $leader = TeamMember::where('team_id', $id)
                    ->where('role', 'leader')
                    ->with('user')
                    ->first();
                $targetUser = $leader ? $leader->user : null;

                if ($request->action === 'approve') {
                    $team->update([
                        'status' => 'approved',
                        'rejection_reason' => null,
                    ]);
                    TeamMember::where('team_id', $id)
                        ->where('role', 'leader')
                        ->update([
                            'status' => 'active',
                            'rejection_reason' => null,
                        ]);
                    $statusText = 'disetujui';
                    $msg = 'Tim berhasil disetujui.';
                } else {
                    $team->update([
                        'status' => 'rejected',
                        'rejection_reason' => $request->input(
                            'rejection_reason',
                        ),
                    ]);
                    TeamMember::where('team_id', $id)
                        ->where('role', 'leader')
                        ->update([
                            'status' => 'rejected',
                            'rejection_reason' => $request->input(
                                'rejection_reason',
                            ),
                        ]);
                    $statusText = 'ditolak';
                    $msg = 'Tim telah ditolak.';
                }

                $notificationData = [
                    'title' => 'Update Status Tim',
                    'body' => "Pengajuan tim '{$team->team_name}' Anda telah {$statusText}.".
                        ($team->rejection_reason
                            ? " Alasan: {$team->rejection_reason}"
                            : ''),
                    'data' => [
                        'team_id' => (string) $team->team_id,
                        'status' => $team->status,
                        'rejection_reason' => $team->rejection_reason,
                        'type' => 'team_status_update',
                    ],
                ];

                return $msg;
            } else {
                $member = TeamMember::where('member_id', $id)
                    ->with(['user', 'team'])
                    ->firstOrFail();
                $targetUser = $member->user;
                $teamName = $member->team->team_name ?? 'Tim';

                if ($request->action === 'approve') {
                    $member->update([
                        'status' => 'active',
                        'rejection_reason' => null,
                    ]);
                    $statusText = 'disetujui';
                    $msg = 'Permohonan bergabung disetujui.';
                } else {
                    $member->update([
                        'status' => 'rejected',
                        'rejection_reason' => $request->input(
                            'rejection_reason',
                        ),
                    ]);
                    $statusText = 'ditolak';
                    $msg = 'Permohonan bergabung ditolak.';
                }

                $notificationData = [
                    'title' => 'Update Permohonan Tim',
                    'body' => "Permohonan bergabung ke tim '{$teamName}' Anda telah {$statusText}.".
                        ($member->rejection_reason
                            ? " Alasan: {$member->rejection_reason}"
                            : ''),
                    'data' => [
                        'team_id' => (string) $member->team_id,
                        'member_id' => (string) $member->member_id,
                        'status' => $member->status,
                        'rejection_reason' => $member->rejection_reason,
                        'type' => 'member_status_update',
                    ],
                ];

                return $msg;
            }
        });

        // Send Push Notification
        if (
            isset($targetUser) &&
            $targetUser->fcm_token &&
            isset($notificationData)
        ) {
            try {
                $this->firebase->sendNotification(
                    $targetUser->fcm_token,
                    $notificationData['title'],
                    $notificationData['body'],
                    $notificationData['data'],
                );
            } catch (\Throwable $e) {
                Log::warning(
                    "FCM Notification failed for user {$targetUser->user_id}: ".
                        $e->getMessage(),
                );
            }
        }

        if ($request->ajax()) {
            return response()->json([
                'message' => $message,
                'data' => $notificationData['data'] ?? null,
            ]);
        }

        return back()->with('success', $message);
    }

    public function storeReport(Request $request, $id)
    {
        $request->validate(
            [
                'achievement_rank' => 'required|string|max:50',
                'competition_level' => 'required|in:kampus,regional,nasional,internasional',
                'photo_activity' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
                'photo_certificate' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            ],
            [
                'achievement_rank.required' => 'Prestasi/juara harus diisi.',
                'competition_level.required' => 'Tingkat lomba harus dipilih.',
                'photo_activity.image' => 'File foto kegiatan harus berupa gambar.',
                'photo_activity.max' => 'Ukuran foto kegiatan maksimal 5MB.',
                'photo_certificate.image' => 'File foto sertifikat harus berupa gambar.',
                'photo_certificate.max' => 'Ukuran foto sertifikat maksimal 5MB.',
            ],
        );

        $team = Team::where('team_id', $id)->firstOrFail();

        $updateData = [
            'achievement_rank' => $request->achievement_rank,
            'competition_level' => $request->competition_level,
        ];

        // Handle foto kegiatan
        if ($request->hasFile('photo_activity')) {
            // Hapus foto lama jika ada
            if ($team->photo_activity) {
                Storage::disk('public')->delete($team->photo_activity);
            }
            $updateData['photo_activity'] = $request
                ->file('photo_activity')
                ->store('teams/activity', 'public');
        }

        // Handle foto sertifikat
        if ($request->hasFile('photo_certificate')) {
            // Hapus foto lama jika ada
            if ($team->photo_certificate) {
                Storage::disk('public')->delete($team->photo_certificate);
            }
            $updateData['photo_certificate'] = $request
                ->file('photo_certificate')
                ->store('teams/certificate', 'public');
        }

        $team->update($updateData);

        if ($request->ajax()) {
            return response()->json([
                'message' => 'Laporan hasil lomba berhasil disimpan.',
                'achievement_rank' => $team->fresh()->achievement_rank,
                'competition_level' => $team->fresh()->competition_level,
                'photo_activity' => $team->fresh()->photo_activity
                    ? asset('storage/'.$team->fresh()->photo_activity)
                    : null,
                'photo_certificate' => $team->fresh()->photo_certificate
                    ? asset('storage/'.$team->fresh()->photo_certificate)
                    : null,
            ]);
        }

        return back()->with(
            'success',
            'Laporan hasil lomba berhasil disimpan.',
        );
    }

    public function destroy(Request $request, $id)
    {
        $team = Team::where('team_id', $id)->firstOrFail();

        // Hapus file foto jika ada
        if ($team->photo_activity) {
            Storage::disk('public')->delete($team->photo_activity);
        }
        if ($team->photo_certificate) {
            Storage::disk('public')->delete($team->photo_certificate);
        }

        $team->delete(); // Soft delete

        if ($request->ajax()) {
            return response()->json(['message' => 'Tim berhasil dihapus.']);
        }

        return back()->with('success', 'Tim berhasil dihapus.');
    }
}
