<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Views\ViewAnnouncement;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AnnouncementController extends Controller
{
    /**
     * GET /api/announcements
     */
    public function index(Request $request)
    {
        $query = ViewAnnouncement::query();

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
        $announcement = ViewAnnouncement::where('announcement_id', $announcement_id)->firstOrFail();
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
            'file'       => 'nullable|string', // Assuming binary/string logic is handled
            'created_by' => 'required|exists:users,user_id',
        ]);

        $announcement = new Announcement();
        // Generate custom ID: ANN + 7 random characters (total 10)
        $announcement->announcement_id = 'ANN' . strtoupper(Str::random(7));
        $announcement->fill($validated);
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

        $validated = $request->validate([
            'title'   => 'sometimes|required|string|max:100',
            'content' => 'sometimes|required|string',
            'file'    => 'nullable|string',
        ]);

        $announcement->update($validated);

        return response()->json(['message' => 'Announcement updated successfully', 'data' => $announcement]);
    }

    /**
     * DELETE /api/announcements/{announcement_id}
     */
    public function destroy(string $announcement_id)
    {
        $announcement = Announcement::where('announcement_id', $announcement_id)->firstOrFail();
        $announcement->delete();

        return response()->json(['message' => 'Announcement deleted successfully']);
    }
}
