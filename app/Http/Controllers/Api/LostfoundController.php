<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Views\ViewLostfound;
use App\Models\Views\ViewLostfoundComment;
use Illuminate\Http\Request;

class LostfoundController extends Controller
{
    /**
     * GET /api/lostfound
     */
    public function index(Request $request)
    {
        $query = ViewLostfound::query();

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(function ($qb) use ($q) {
                $qb->where('item_name', 'like', "%$q%")
                   ->orWhere('description', 'like', "%$q%")
                   ->orWhere('location', 'like', "%$q%");
            });
        }
        if ($request->filled('status_id')) $query->where('status_id', $request->status_id);

        $perPage = min((int) $request->get('per_page', 15), 100);

        return response()->json($query->orderBy('created_at', 'desc')->paginate($perPage));
    }

    /**
     * GET /api/lostfound/{lostfound_id}
     */
    public function show(string $lostfound_id)
    {
        $item = ViewLostfound::where('lostfound_id', $lostfound_id)->firstOrFail();
        return response()->json(['data' => $item]);
    }

    /**
     * GET /api/lostfound/{lostfound_id}/comments
     */
    public function comments(string $lostfound_id, Request $request)
    {
        $perPage  = min((int) $request->get('per_page', 20), 100);
        $comments = ViewLostfoundComment::where('lostfound_id', $lostfound_id)
                        ->orderBy('created_at', 'asc')
                        ->paginate($perPage);

        return response()->json($comments);
    }
}
