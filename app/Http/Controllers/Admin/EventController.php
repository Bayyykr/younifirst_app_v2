<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventCategory;
use App\Services\FirebaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

class EventController extends Controller
{
    protected $firebase;

    public function __construct(FirebaseService $firebase)
    {
        $this->firebase = $firebase;
    }

    /**
     * Display the Event Management dashboard for Admin.
     */
    public function index(Request $request)
    {
        $stats = [
            'total'    => Event::count(),
            'approved' => Event::whereIn('status', ['upcoming', 'ongoing', 'completed'])->count(),
            'pending'  => Event::where('status', 'pending')->count(),
            'rejected' => Event::where('status', 'rejected')->count(),
        ];

        $pendingEvents = Event::with(['category', 'creator'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        $categories = EventCategory::orderBy('name_category', 'asc')->get();

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
            'start_date_raw'=> $event->start_date->format('Y-m-d'),
            'start_time'    => $event->start_date->format('H:i') . ' WIB',
            'start_time_raw'=> $event->start_date->format('H:i'),
            'end_date'      => $event->end_date->format('d M Y'),
            'end_date_raw'  => $event->end_date->format('Y-m-d'),
            'end_time'      => $event->end_date->format('H:i') . ' WIB',
            'end_time_raw'  => $event->end_date->format('H:i'),
            'description'   => $event->description,
            'creator_name'  => $event->creator->name ?? 'System',
            'likes_count'   => $event->likes_count,
            'status'        => $event->status,
            'poster'        => $event->poster_url,
            'location'      => $event->location,
        ]);

        return view('admin.events', compact('stats', 'pendingEvents', 'categories', 'allEvents'));
    }

    public function respond(Request $request, string $event_id)
    {
        $request->validate([
            'action' => 'required|in:approve,reject'
        ]);

        $event = Event::where('event_id', $event_id)->firstOrFail();

        if ($request->action === 'approve') {
            $event->status = 'upcoming';
            $message = 'Event approved successfully.';
        } else {
            $event->status = 'rejected';
            $message = 'Event rejected successfully.';
        }

        try {
            $event->save();

            // Firebase notification (silent fail)
            try {
                $creator = $event->creator;
                if ($creator && $creator->fcm_token) {
                    $statusText = ($request->action === 'approve') ? 'disetujui' : 'ditolak';
                    $this->firebase->sendNotification(
                        $creator->fcm_token,
                        "Update Status Event",
                        "Event '{$event->title}' Anda telah {$statusText}.",
                        [
                            'event_id' => (string) $event->event_id,
                            'status'   => $event->status,
                            'type'     => 'event_status_update'
                        ]
                    );
                }
            } catch (\Throwable $e) {
                Log::warning("FCM Notification failed but proceeding: " . $e->getMessage());
            }

            if ($request->ajax()) {
                return response()->json(['message' => $message, 'status' => 'success']);
            }

            return back()->with('success', $message);

        } catch (\Throwable $e) {
            Log::error("Event Respond Error for ID {$event_id}: " . $e->getMessage());
            if ($request->ajax()) {
                return response()->json(['message' => 'Gagal memperbarui status: ' . $e->getMessage()], 500);
            }
            throw $e;
        }
    }

    public function destroy(string $event_id)
    {
        $event = Event::where('event_id', $event_id)->firstOrFail();
        $event->delete();

        if (request()->ajax()) {
            return response()->json(['message' => 'Event deleted successfully.']);
        }

        return back()->with('success', 'Event deleted successfully.');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:event_categories,category_id',
            'title'       => 'required|string|max:150',
            'description' => 'required|string',
            'start_date'  => 'required|date',
            'start_time'  => 'required',
            'end_date'    => 'required|date|after_or_equal:start_date',
            'end_time'    => 'required',
            'location'    => 'required|string|max:255',
            'poster'      => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        $start_datetime = Carbon::parse($request->start_date . ' ' . $request->start_time);
        $end_datetime   = Carbon::parse($request->end_date . ' ' . $request->end_time);

        $event = new Event();
        $event->event_id = 'EVT' . strtoupper(Str::random(7));

        $event->category_id = $request->category_id;
        $event->title       = $request->title;
        $event->description = $request->description;
        $event->start_date  = $start_datetime;
        $event->end_date    = $end_datetime;
        $event->location    = $request->location;
        $event->created_by  = auth()->id();
        $event->status      = 'upcoming';
        $event->created_at  = Carbon::now();

        if ($request->hasFile('poster')) {
            $path = $request->file('poster')->store('events', 'public');
            $event->poster = $path;
        }

        $event->save();

        if ($request->ajax()) {
            return response()->json(['message' => 'Event created successfully.', 'data' => $event]);
        }

        return back()->with('success', 'Event created successfully.');
    }

    public function update(Request $request, string $event_id)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:event_categories,category_id',
            'title'       => 'required|string|max:150',
            'description' => 'required|string',
            'start_date'  => 'required|date',
            'start_time'  => 'required',
            'end_date'    => 'required|date|after_or_equal:start_date',
            'end_time'    => 'required',
            'location'    => 'required|string|max:255',
            'poster'      => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        $event = Event::where('event_id', $event_id)->firstOrFail();

        $start_datetime = Carbon::parse($request->start_date . ' ' . $request->start_time);
        $end_datetime   = Carbon::parse($request->end_date . ' ' . $request->end_time);

        $event->category_id = $request->category_id;
        $event->title       = $request->title;
        $event->description = $request->description;
        $event->start_date  = $start_datetime;
        $event->end_date    = $end_datetime;
        $event->location    = $request->location;
        $event->updated_at  = Carbon::now();

        if ($request->hasFile('poster')) {
            if ($event->poster) {
                Storage::disk('public')->delete($event->poster);
            }
            $path = $request->file('poster')->store('events', 'public');
            $event->poster = $path;
            \Log::info("Poster updated for event {$event_id}: {$path}");
        } else {
            \Log::info("No new poster uploaded for event {$event_id}");
        }

        $event->save();

        if ($request->ajax()) {
            return response()->json(['message' => 'Event updated successfully.', 'data' => $event]);
        }

        return back()->with('success', 'Event updated successfully.');
    }
}
