<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventCategory;
use Illuminate\Http\Request;

class EventController extends Controller
{
    /**
     * Display the Event Management dashboard for Admin.
     */
    public function index(Request $request)
    {
        // 1. Calculate Stats
        $stats = [
            'total'    => Event::count(),
            'approved' => Event::whereIn('status', ['upcoming', 'ongoing', 'completed'])->count(),
            'pending'  => Event::where('status', 'pending')->count(),
            'rejected' => Event::where('status', 'rejected')->count(),
        ];

        // 2. Fetch Pending Requests (Top Section + Dedicated View)
        $pendingEvents = Event::with(['category', 'creator'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->get();

        // 3. Fetch Categories for Filter Navigation
        $categories = EventCategory::orderBy('name_category', 'asc')->get();

        // 4. Fetch All Events for the Table with Like Count
        // Using withCount to efficiently get the number of likes
        $query = Event::with(['category', 'creator'])->withCount('likes');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%$search%")
                  ->orWhere('description', 'like', "%$search%")
                  ->orWhere('location', 'like', "%$search%")
                  ->orWhereHas('creator', function($q2) use ($search) {
                      $q2->where('name', 'like', "%$search%");
                  });
            });
        }

        if ($request->filled('category_id') && $request->category_id !== 'all') {
            $query->where('category_id', $request->input('category_id'));
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->input('status'));
        }

        $allEvents = $query->orderBy('created_at', 'desc')->get()->map(fn($event) => [
            'id'            => $event->event_id,
            'title'         => $event->title,
            'category_name' => $event->category->name_category,
            'category_id'   => $event->category_id,
            'start_date'    => $event->start_date->format('d M Y'),
            'start_time'    => $event->start_date->format('H:i') . ' WIB',
            'end_date'      => $event->end_date->format('d M Y'),
            'end_time'      => $event->end_date->format('H:i') . ' WIB',
            'creator_name'  => $event->creator->name ?? 'System',
            'likes_count'   => $event->likes_count,
            'status'        => $event->status,
            'poster'        => $event->poster,
            'location'      => $event->location,
        ]);

        return view('admin.events', compact('stats', 'pendingEvents', 'categories', 'allEvents'));
    }

    /**
     * Respond to an event request (Approve/Reject).
     */
    public function respond(string $event_id, Request $request)
    {
        $request->validate([
            'action' => 'required|in:approve,reject'
        ]);

        $event = Event::where('event_id', $event_id)->firstOrFail();

        if ($request->action === 'approve') {
            $event->status = 'upcoming'; // Moving from pending to approved (upcoming)
            $message = 'Event approved successfully.';
        } else {
            $event->status = 'rejected';
            $message = 'Event rejected successfully.';
        }

        $event->save();

        return back()->with('success', $message);
    }

    /**
     * Delete an event.
     */
    public function destroy(string $event_id)
    {
        $event = Event::where('event_id', $event_id)->firstOrFail();
        $event->delete();

        return back()->with('success', 'Event deleted successfully.');
    }
}
