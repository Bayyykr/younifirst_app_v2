<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LostfoundComment;
use App\Models\LostfoundItem;
use App\Models\Views\ViewLostfound;
use App\Models\Views\ViewLostfoundComment;
use App\Services\FirebaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LostfoundController extends Controller
{
    protected $firebase;

    public function __construct(FirebaseService $firebase)
    {
        $this->firebase = $firebase;
    }
    /**
     * Display the Lost and Found dashboard for Admin.
     */
    public function index(Request $request)
    {
        // 1. Calculate Stats using string enum status
        $stats = [
            "found" => LostfoundItem::where("status", "found")->count(),
            "lost" => LostfoundItem::where("status", "lost")->count(),
            "claimed" => LostfoundItem::where("status", "claimed")->count(),
        ];

        // 2. Fetch All Items for Client-side filtering
        $query = ViewLostfound::select(
            "view_lostfound.*",
            "u.photo AS reporter_photo_path",
        )->leftJoin(
            "users AS u",
            "view_lostfound.reporter_id",
            "=",
            "u.user_id",
        );

        if (Auth::user()->role === "satpam") {
            $query->where("view_lostfound.status", "!=", "claimed");
        }

        $items = $query
            ->orderBy("view_lostfound.created_at", "desc")
            ->get()
            ->map(function ($item) {
                $reporterPhoto = $item->reporter_photo_path
                    ? \Illuminate\Support\Facades\Storage::disk("public")->url(
                        $item->reporter_photo_path,
                    )
                    : null;
                $currentUser = Auth::user();

                return [
                    "id" => $item->lostfound_id,
                    "reporter_id" => $item->reporter_id,
                    "can_claim" =>
                        $currentUser->role === "admin" ||
                        $item->reporter_id === $currentUser->user_id,
                    "name" => $item->item_name,
                    "description" => $item->description,
                    "photo" => $item->photo_url,
                    "location" => $item->location,
                    "date" => $item->created_at->format("d F Y"),
                    "time_ago" => $item->created_at->diffForHumans(),
                    "reporter_name" => $item->reporter_name,
                    "reporter_nim" => $item->reporter_nim ?? "Mahasiswa",
                    "reporter_photo" => $reporterPhoto,
                    "comments_count" => $item->total_comments ?? 0,
                    "status" => $item->status,
                    "status_label" => match ($item->status) {
                        "lost" => "Hilang",
                        "found" => "Ditemukan",
                        "claimed" => "Diklaim",
                        default => "Unknown",
                    },
                    "status_class" => match ($item->status) {
                        "lost" => "status-danger",
                        "found" => "status-success",
                        "claimed" => "status-warning",
                        default => "status-neutral",
                    },
                ];
            });

        $firebaseConfig = [
            "apiKey" => env("FIREBASE_API_KEY"),
            "authDomain" => env("FIREBASE_PROJECT_ID") . ".firebaseapp.com",
            "databaseURL" => env(
                "FIREBASE_DATABASE_URL",
                "https://" .
                    env("FIREBASE_PROJECT_ID") .
                    "-default-rtdb.asia-southeast1.firebasedatabase.app",
            ),
            "projectId" => env("FIREBASE_PROJECT_ID"),
            "storageBucket" => env("FIREBASE_PROJECT_ID") . ".appspot.com",
        ];

        return view(
            "admin.lostfound",
            compact("stats", "items", "firebaseConfig"),
        );
    }

    /**
     * Store a new item.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            "item_name" => "required|string|max:50",
            "description" => "required|string",
            "photo" => "nullable|image|mimes:jpeg,png,jpg|max:5120",
            "location" => "required|string|max:255",
            "status" => "required|in:lost,found,claimed",
        ]);

        $item = new LostfoundItem();
        $item->lostfound_id = "LNF" . strtoupper(Str::random(7));
        $item->user_id = Auth::id(); // Admin who posts it
        $item->item_name = $validated["item_name"];
        $item->description = $validated["description"];
        $item->location = $validated["location"];
        $item->status = $validated["status"];

        if ($request->hasFile("photo")) {
            $path = $request->file("photo")->store("lostfound", "public");
            $item->photo = $path;
        }

        $item->created_at = now();
        $item->save();

        // Fetch the item from view for consistent structure
        $savedItem = ViewLostfound::where(
            "lostfound_id",
            $item->lostfound_id,
        )->first();

        return response()->json(
            [
                "message" => "Barang berhasil diposting!",
                "data" => [
                    "id" => $savedItem->lostfound_id,
                    "reporter_id" => $savedItem->reporter_id,
                    "can_claim" => true,
                    "name" => $savedItem->item_name,
                    "description" => $savedItem->description,
                    "photo" => $savedItem->photo_url,
                    "location" => $savedItem->location,
                    "date" => $savedItem->created_at->format("d F Y"),
                    "time_ago" => $savedItem->created_at->diffForHumans(),
                    "reporter_name" => $savedItem->reporter_name,
                    "reporter_nim" => $savedItem->reporter_nim ?? "Mahasiswa",
                    "reporter_photo" => Auth::user()->photo
                        ? Storage::disk("public")->url(Auth::user()->photo)
                        : null,
                    "comments_count" => 0,
                    "status" => $savedItem->status,
                    "status_label" => match ($savedItem->status) {
                        "lost" => "Hilang",
                        "found" => "Ditemukan",
                        "claimed" => "Diklaim",
                        default => "Unknown",
                    },
                    "status_class" => match ($savedItem->status) {
                        "lost" => "status-danger",
                        "found" => "status-success",
                        "claimed" => "status-warning",
                        default => "status-neutral",
                    },
                ],
            ],
            201,
        );
    }

    /**
     * Get comments for an item from the admin/satpam web session.
     */
    public function comments(string $id, Request $request)
    {
        $perPage = min((int) $request->input("per_page", 100), 100);

        $comments = ViewLostfoundComment::select(
            "view_lostfound_comments.*",
            "u.photo AS commenter_photo_path",
        )
            ->leftJoin(
                "users AS u",
                "view_lostfound_comments.user_id",
                "=",
                "u.user_id",
            )
            ->where("lostfound_id", $id)
            ->orderBy("created_at", "asc")
            ->paginate($perPage);

        $comments->getCollection()->transform(function ($comment) {
            $comment->commenter_photo = $comment->commenter_photo_path
                ? Storage::disk("public")->url($comment->commenter_photo_path)
                : null;
            $comment->time_ago = $comment->created_at->diffForHumans();

            return $comment;
        });

        return response()->json($comments);
    }

    /**
     * Add a comment from the admin/satpam web session.
     */
    public function addComment(string $id, Request $request)
    {
        $user = Auth::user();
        LostfoundItem::where("lostfound_id", $id)->firstOrFail();

        $validated = $request->validate([
            "comment" => "required|string|max:1000",
        ]);

        $comment = new LostfoundComment();
        $comment->comment_id = "CMT" . strtoupper(Str::random(7));
        $comment->lostfound_id = $id;
        $comment->user_id = $user->user_id;
        $comment->comment = $validated["comment"];
        $comment->created_at = now();
        $comment->save();

        try {
            $database = $this->firebase->getDatabase();
            if ($database) {
                $database->getReference("lostfound_comments/" . $id)->push([
                    "comment_id" => $comment->comment_id,
                    "comment" => $comment->comment,
                    "created_at" => $comment->created_at->toISOString(),
                    "commenter_name" => $user->name,
                    "commenter_photo" => $user->photo_url,
                    "user_id" => $user->user_id,
                    "time_ago" => "Baru saja",
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning(
                "Firebase RTDB admin comment sync failed: " . $e->getMessage(),
            );
        }

        return response()->json(
            [
                "message" => "Komentar berhasil dikirim.",
                "data" => [
                    "comment_id" => $comment->comment_id,
                    "lostfound_id" => $comment->lostfound_id,
                    "user_id" => $comment->user_id,
                    "comment" => $comment->comment,
                    "created_at" => $comment->created_at->toISOString(),
                    "commenter_name" => $user->name,
                    "commenter_photo" => $user->photo_url,
                    "time_ago" => "Baru saja",
                ],
            ],
            201,
        );
    }

    /**
     * Delete a comment from the admin/satpam web session.
     */
    public function deleteComment(string $id)
    {
        $user = Auth::user();
        $comment = LostfoundComment::where("comment_id", $id)->firstOrFail();

        if ($comment->user_id !== $user->user_id && $user->role !== "admin") {
            return response()->json(
                [
                    "message" =>
                        "Anda tidak memiliki akses untuk menghapus komentar ini.",
                ],
                403,
            );
        }

        $comment->delete();

        return response()->json([
            "message" => "Komentar berhasil dihapus.",
        ]);
    }

    /**
     * Resolve an item (mark as claimed/finished).
     */
    public function resolve(string $id)
    {
        $item = LostfoundItem::where("lostfound_id", $id)->firstOrFail();
        $user = Auth::user();

        if ($user->role !== "admin" && $item->user_id !== $user->user_id) {
            return response()->json(
                [
                    "message" =>
                        "Barang hanya bisa diklaim oleh pembuat postingan atau admin.",
                ],
                403,
            );
        }

        $item->status = "claimed";
        $item->updated_at = now();
        $item->save();

        // Push Notification to Item Owner
        try {
            $owner = $item->user;
            if ($owner && $owner->fcm_token) {
                $this->firebase->sendNotification(
                    $owner->fcm_token,
                    "Update Status Barang",
                    "Barang '{$item->item_name}' Anda telah ditandai sebagai Selesai/Diklaim.",
                    [
                        "lostfound_id" => (string) $item->lostfound_id,
                        "status" => $item->status,
                        "type" => "lostfound_status_update",
                    ],
                );
                Log::info(
                    "FCM: LostFound notification sent to user {$owner->user_id}",
                );
            }
        } catch (\Throwable $e) {
            Log::warning(
                "FCM: LostFound notification failed for item {$id}: " .
                    $e->getMessage(),
            );
        }

        return response()->json([
            "message" => "Status barang berhasil diperbarui menjadi selesai!",
            "status" => "claimed",
        ]);
    }

    /**
     * Delete an item.
     */
    public function destroy(string $id)
    {
        $item = LostfoundItem::where("lostfound_id", $id)->firstOrFail();
        $item->delete();

        return response()->json([
            "message" => "Data barang berhasil dihapus!",
        ]);
    }
}
