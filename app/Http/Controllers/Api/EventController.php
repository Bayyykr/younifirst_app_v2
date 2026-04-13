<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Views\ViewEvent;
use App\Models\Views\ViewEventLike;
use Illuminate\Http\Request;

class EventController extends Controller
{
    /**
     * GET /api/events
     */
    public function index(Request $request)
    {
        $query = ViewEvent::query();

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(function ($qb) use ($q) {
                $qb->where('title', 'like', "%$q%")
                   ->orWhere('description', 'like', "%$q%")
                   ->orWhere('location', 'like', "%$q%");
            });
        }
        if ($request->filled('status'))      $query->where('status', $request->status);
        if ($request->filled('category_id')) $query->where('category_id', $request->category_id);

        $perPage = min((int) $request->get('per_page', 15), 100);

        return response()->json($query->orderBy('created_at', 'desc')->paginate($perPage));
    }

    /**
     * GET /api/events/{event_id}
     */
    public function show(string $event_id)
    {
        $event = ViewEvent::where('event_id', $event_id)->firstOrFail();
        return response()->json(['data' => $event]);
    }

    /**
     * GET /api/events/{event_id}/likes
     */
    public function likes(string $event_id, Request $request)
    {
        $perPage = min((int) $request->get('per_page', 20), 100);
        $likes   = ViewEventLike::where('event_id', $event_id)
                       ->orderBy('liked_at', 'desc')
                       ->paginate($perPage);

        return response()->json($likes);
    }
}
