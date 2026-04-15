<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Views\ViewEvent;
use App\Models\Views\ViewEventLike;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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

    /**
     * POST /api/events
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:event_categories,category_id',
            'title'       => 'required|string|max:50',
            'description' => 'required|string',
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after_or_equal:start_date',
            'location'    => 'required|string|max:255',
            'poster'      => 'nullable|string',
            'created_by'  => 'required|exists:users,user_id',
            'status'      => 'nullable|in:upcoming,ongoing,completed,cancelled',
        ]);

        $event = new Event();
        // Generate custom ID: EVT + 7 random characters (total 10)
        $event->event_id = 'EVT' . strtoupper(Str::random(7));
        $event->fill($validated);
        $event->created_at = now();
        $event->save();

        return response()->json(['message' => 'Event created successfully', 'data' => $event], 213);
    }

    /**
     * PUT /api/events/{event_id}
     */
    public function update(Request $request, string $event_id)
    {
        $event = Event::where('event_id', $event_id)->firstOrFail();

        $validated = $request->validate([
            'category_id' => 'sometimes|required|exists:event_categories,category_id',
            'title'       => 'sometimes|required|string|max:50',
            'description' => 'sometimes|required|string',
            'start_date'  => 'sometimes|required|date',
            'end_date'    => 'sometimes|required|date|after_or_equal:start_date',
            'location'    => 'sometimes|required|string|max:255',
            'poster'      => 'nullable|string',
            'status'      => 'sometimes|required|in:upcoming,ongoing,completed,cancelled',
        ]);

        $event->fill($validated);
        $event->updated_at = now();
        $event->save();

        return response()->json(['message' => 'Event updated successfully', 'data' => $event]);
    }

    /**
     * DELETE /api/events/{event_id}
     */
    public function destroy(string $event_id)
    {
        $event = Event::where('event_id', $event_id)->firstOrFail();
        $event->delete(); // This could be a soft delete if handled by model, but we set deleted_at manually otherwise

        // If not using SoftDeletes trait, we set it manually
        $event->deleted_at = now();
        $event->save();

        return response()->json(['message' => 'Event deleted (soft) successfully']);
    }
}
