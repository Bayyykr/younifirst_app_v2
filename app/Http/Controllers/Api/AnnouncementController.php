<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Views\ViewAnnouncement;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class AnnouncementController extends Controller
{
    /**
     * GET /api/announcements
     */
    public function index(Request $request)
    {
        $query = ViewAnnouncement::where('status', 'publish')
                               ->where('creator_role', 'admin')
                               ->where('title', 'not like', 'Pengajuan %')
                               ->where('title', 'not like', '% disetujui')
                               ->where('title', 'not like', '% ditolak');

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(function ($qb) use ($q) {
                $qb->where('title', 'like', "%$q%")
                   ->orWhere('content', 'like', "%$q%");
            });
        }

        $perPage = min((int) $request->get('per_page', 15), 100);

        return response()->json($query->orderBy('created_at', 'desc')->paginate($perPage));
    }

    /**
     * GET /api/announcements/{announcement_id}
     */
    public function show(string $announcement_id)
    {
        $announcement = ViewAnnouncement::where('announcement_id', $announcement_id)
                                       ->where('status', 'publish')
                                       ->where('creator_role', 'admin')
                                       ->where('title', 'not like', 'Pengajuan %')
                                       ->where('title', 'not like', '% disetujui')
                                       ->where('title', 'not like', '% ditolak')
                                       ->firstOrFail();
        return response()->json(['data' => $announcement]);
    }

    /**
     * POST /api/announcements
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'      => 'required|string|max:100',
            'content'    => 'required|string',
            'file'       => 'nullable|file|max:10240', // 10MB max
            'created_by' => 'required|exists:users,user_id',
        ]);

        $announcement = new Announcement();
        // Generate custom ID: ANN + 7 random characters (total 10)
        $announcement->announcement_id = 'ANN' . strtoupper(Str::random(7));
        $announcement->fill($request->except('file'));

        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('announcements', 'public');
            $announcement->file = $path;
        }

        $announcement->created_at = now();
        $announcement->save();

        return response()->json(['message' => 'Announcement created successfully', 'data' => $announcement], 212);
    }

    /**
     * PUT /api/announcements/{announcement_id}
     */
    public function update(Request $request, string $announcement_id)
    {
        $announcement = Announcement::where('announcement_id', $announcement_id)->firstOrFail();

        $validatedData = [
            'title'   => 'sometimes|required|string|max:100',
            'content' => 'sometimes|required|string',
        ];

        // Only validate file if it's a file upload
        if ($request->hasFile('file')) {
            $validatedData['file'] = 'file|max:10240';
        } else {
            $validatedData['file'] = 'nullable';
        }

        $validated = $request->validate($validatedData);

        $announcement->fill($request->except('file'));

        if ($request->hasFile('file')) {
            // Delete old file if exists
            if ($announcement->file) {
                Storage::disk('public')->delete($announcement->file);
            }
            $path = $request->file('file')->store('announcements', 'public');
            $announcement->file = $path;
        }

        $announcement->updated_at = now();
        $announcement->save();

        return response()->json(['message' => 'Announcement updated successfully', 'data' => $announcement]);
    }

    /**
     * DELETE /api/announcements/{announcement_id}
     * Soft-deletes the announcement (sets deleted_at, excluded from view_announcements).
     */
    public function destroy(string $announcement_id)
    {
        $announcement = Announcement::where('announcement_id', $announcement_id)->firstOrFail();
        $announcement->delete(); // SoftDeletes trait sets deleted_at automatically

        return response()->json(['message' => 'Announcement deleted successfully']);
    }
}
