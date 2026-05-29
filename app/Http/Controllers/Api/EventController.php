<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventLike;
use App\Models\Views\ViewEvent;
use App\Models\Views\ViewEventLike;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EventController extends Controller
{
    /**
     * GET /api/events
     */
    public function index(Request $request)
    {
        $query = ViewEvent::query();

        // -- Full-text search across title, description, location, creator name
        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(function ($qb) use ($q) {
                $qb->where('title', 'like', "%$q%")
                    ->orWhere('description', 'like', "%$q%")
                    ->orWhere('location', 'like', "%$q%")
                    ->orWhere('creator_name', 'like', "%$q%");
            });
        }

        // -- Status filter (supports: pending, upcoming, ongoing, completed, cancelled, rejected)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // -- Category filter by ID
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // -- Category filter by name (partial match)
        if ($request->filled('category_name')) {
            $query->where(
                'category_name',
                'like',
                '%'.$request->category_name.'%',
            );
        }

        // -- Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('start_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('start_date', '<=', $request->date_to);
        }

        $perPage = min((int) $request->input('per_page', 15), 100);
        $sortBy = in_array($request->input('sort_by'), [
            'created_at',
            'start_date',
            'title',
        ])
            ? $request->input('sort_by')
            : 'created_at';
        $sortDir =
            $request->input('sort_dir', 'desc') === 'asc' ? 'asc' : 'desc';

        $events = $query->orderBy($sortBy, $sortDir)->paginate($perPage);

        // Attach is_liked status for the current user
        if ($request->user()) {
            $userLikedEventIds = EventLike::where(
                'user_id',
                $request->user()->user_id,
            )
                ->whereIn('event_id', $events->pluck('event_id'))
                ->pluck('event_id')
                ->toArray();

            $events
                ->getCollection()
                ->transform(function ($event) use ($userLikedEventIds) {
                    $event->is_liked = in_array(
                        $event->event_id,
                        $userLikedEventIds,
                    );

                    return $event;
                });
        }

        return response()->json($events);
    }

    /**
     * GET /api/events/{event_id}
     */
    public function show(string $event_id, Request $request)
    {
        $event = ViewEvent::where('event_id', $event_id)->firstOrFail();

        if ($request->user()) {
            $event->is_liked = EventLike::where('event_id', $event_id)
                ->where('user_id', $request->user()->user_id)
                ->exists();
        } else {
            $event->is_liked = false;
        }

        return response()->json(['data' => $event]);
    }

    /**
     * GET /api/events/{event_id}/likes
     */
    public function likes(string $event_id, Request $request)
    {
        $perPage = min((int) $request->input('per_page', 20), 100);
        $likes = ViewEventLike::where('event_id', $event_id)
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
            'title' => 'required|string|max:50',
            'description' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'location' => 'required|string|max:255',
            'poster' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'created_by' => 'required|exists:users,user_id',
            'status' => 'nullable|in:pending,upcoming,ongoing,completed,cancelled,rejected',
            'rejection_reason' => 'required_if:status,rejected|nullable|string|max:1000',
        ]);

        $event = new Event;
        // Generate custom ID: EVT + 7 random characters (total 10)
        $event->event_id = 'EVT'.strtoupper(Str::random(7));
        $event->fill($request->except('poster'));

        if ($request->hasFile('poster')) {
            $path = $request->file('poster')->store('events', 'public');
            $event->poster = $path;
        }

        $event->status = $request->input('status', 'pending');
        $event->rejection_reason =
            $event->status === 'rejected'
                ? $request->input('rejection_reason')
                : null;
        $event->created_at = Carbon::now();
        $event->save();

        return response()->json(
            ['message' => 'Event created successfully', 'data' => $event],
            213,
        );
    }

    /**
     * PUT /api/events/{event_id}
     */
    public function update(Request $request, string $event_id)
    {
        $event = Event::where('event_id', $event_id)->firstOrFail();

        $validatedData = [
            'category_id' => 'sometimes|required|exists:event_categories,category_id',
            'title' => 'sometimes|required|string|max:50',
            'description' => 'sometimes|required|string',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date|after_or_equal:start_date',
            'location' => 'sometimes|required|string|max:255',
            'status' => 'sometimes|required|in:pending,upcoming,ongoing,completed,cancelled,rejected',
            'rejection_reason' => 'required_if:status,rejected|nullable|string|max:1000',
        ];

        // Only validate poster as image if it's a file upload
        if ($request->hasFile('poster')) {
            $validatedData['poster'] = 'image|mimes:jpeg,png,jpg|max:5120';
        } else {
            $validatedData['poster'] = 'nullable';
        }

        $validated = $request->validate($validatedData);

        $event->fill($request->except('poster'));

        if ($request->has('status') && $request->status !== 'rejected') {
            $event->rejection_reason = null;
        }

        if ($request->hasFile('poster')) {
            // Delete old poster if exists
            if ($event->poster) {
                Storage::disk('public')->delete($event->poster);
            }
            $path = $request->file('poster')->store('events', 'public');
            $event->poster = $path;
        }

        $event->updated_at = now();
        $event->save();

        return response()->json([
            'message' => 'Event updated successfully',
            'data' => $event,
        ]);
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

        return response()->json([
            'message' => 'Event deleted (soft) successfully',
        ]);
    }

    /**
     * POST /api/events/{event_id}/like
     */
    public function toggleLike(string $event_id, Request $request)
    {
        $user = $request->user();

        // 1. Verify event exists
        $event = Event::where('event_id', $event_id)->firstOrFail();

        // 2. Check if already liked
        $existingLike = EventLike::where('event_id', $event_id)
            ->where('user_id', $user->user_id)
            ->first();

        if ($existingLike) {
            // UNLIKE
            $existingLike->delete();
            $status = 'unliked';
            $isLiked = false;
        } else {
            // LIKE
            $like = new EventLike;
            $like->like_id = (string) Str::uuid();
            $like->event_id = $event_id;
            $like->user_id = $user->user_id;
            $like->created_at = Carbon::now();
            $like->save();
            $status = 'liked';
            $isLiked = true;
        }

        // 3. Get fresh total likes count
        $likesCount = EventLike::where('event_id', $event_id)->count();

        return response()->json([
            'message' => $status === 'liked'
                    ? 'Event liked successfully'
                    : 'Event unliked successfully',
            'status' => $status,
            'likes_count' => $likesCount,
            'is_liked' => $isLiked,
        ]);
    }
}
