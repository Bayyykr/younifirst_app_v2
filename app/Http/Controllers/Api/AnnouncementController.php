<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Views\ViewAnnouncement;
use Illuminate\Http\Request;

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
}
