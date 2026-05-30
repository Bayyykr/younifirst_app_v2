<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LostfoundItem;
use App\Models\LostfoundComment;
use App\Models\Views\ViewLostfound;
use App\Models\Views\ViewLostfoundComment;
use App\Services\FirebaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class LostfoundController extends Controller
{
    protected $firebase;

    public function __construct(FirebaseService $firebase)
    {
        $this->firebase = $firebase;
    }
    /**
     * GET /api/lostfound
     */
    public function index(Request $request)
    {
        $query = ViewLostfound::query();

        if ($request->filled("search")) {
            $q = $request->search;
            $query->where(function ($qb) use ($q) {
                $qb->where("item_name", "like", "%$q%")
                    ->orWhere("description", "like", "%$q%")
                    ->orWhere("location", "like", "%$q%");
            });
        }

        // Filter by string enum status: lost | found | claimed
        if ($request->filled("status")) {
            $query->where("status", $request->status);
        }

        $perPage = min((int) $request->input("per_page", 15), 100);

        return response()->json(
            $query->orderBy("created_at", "desc")->paginate($perPage),
        );
    }

    /**
     * GET /api/lostfound/{lostfound_id}
     */
    public function show(string $lostfound_id)
    {
        $item = ViewLostfound::where(
            "lostfound_id",
            $lostfound_id,
        )->firstOrFail();
        return response()->json(["data" => $item]);
    }

    /**
     * GET /api/lostfound/{lostfound_id}/comments
     */
    public function comments(string $lostfound_id, Request $request)
    {
        $perPage = min((int) $request->input("per_page", 20), 100);
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
            ->where("lostfound_id", $lostfound_id)
            ->orderBy("created_at", "asc")
            ->paginate($perPage);

        // Map to include full photo URL and time_ago
        $comments->getCollection()->transform(function ($comment) {
            $photoUrl = $comment->commenter_photo_path
                ? \Illuminate\Support\Facades\Storage::disk("public")->url(
                    $comment->commenter_photo_path,
                )
                : null;

            $comment->commenter_photo = $photoUrl;
            $comment->time_ago = $comment->created_at->diffForHumans();
            return $comment;
        });

        return response()->json($comments);
    }

    /**
     * POST /api/lostfound
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            "item_name" => "required|string|max:50",
            "description" => "required|string",
            "photo" => "nullable|image|mimes:jpeg,png,jpg|max:5120",
            "location" => "required|string|max:255",
            "status" => "required|in:lost,found",
        ]);

        $item = new LostfoundItem();
        // Generate custom ID: LNF + 7 random characters (total 10)
        $item->lostfound_id = "LNF" . strtoupper(Str::random(7));
        $item->fill($request->except("photo", "user_id"));
        $item->user_id = $request->user()->user_id;

        if ($request->hasFile("photo")) {
            $path = $request->file("photo")->store("lostfound", "public");
            $item->photo = $path;
        }

        $item->created_at = \Illuminate\Support\Carbon::now();
        $item->save();

        return response()->json(
            [
                "message" => "Lost/Found item created successfully",
                "data" => $item,
            ],
            201,
        );
    }

    /**
     * PUT /api/lostfound/{lostfound_id}
     */
    public function update(Request $request, string $lostfound_id)
    {
        $item = LostfoundItem::where(
            "lostfound_id",
            $lostfound_id,
        )->firstOrFail();

        $statusRule = Rule::in(["lost", "found"]);
        if (
            $request->user()->role === "admin" ||
            $item->user_id === $request->user()->user_id
        ) {
            $statusRule = Rule::in(["lost", "found", "claimed"]);
        }

        $validatedData = [
            "item_name" => "sometimes|required|string|max:50",
            "description" => "sometimes|required|string",
            "location" => "sometimes|required|string|max:255",
            "status" => ["sometimes", "required", $statusRule],
        ];

        // Only validate photo as image if it's a file upload
        if ($request->hasFile("photo")) {
            $validatedData["photo"] = "image|mimes:jpeg,png,jpg|max:5120";
        } else {
            $validatedData["photo"] = "nullable";
        }

        $validated = $request->validate($validatedData);

        $item->fill($request->except("photo", "user_id"));

        if ($request->hasFile("photo")) {
            // Delete old photo if exists
            if ($item->photo) {
                Storage::disk("public")->delete($item->photo);
            }
            $path = $request->file("photo")->store("lostfound", "public");
            $item->photo = $path;
        }

        $item->updated_at = now();
        $item->save();

        return response()->json([
            "message" => "Lost/Found item updated successfully",
            "data" => $item,
        ]);
    }

    /**
     * DELETE /api/lostfound/{lostfound_id}
     * Soft-deletes the item (sets deleted_at, excluded from view_lostfound).
     */
    public function destroy(string $lostfound_id)
    {
        $item = LostfoundItem::where(
            "lostfound_id",
            $lostfound_id,
        )->firstOrFail();
        $item->delete(); // SoftDeletes trait sets deleted_at automatically

        return response()->json([
            "message" => "Lost/Found item deleted successfully",
        ]);
    }

    /**
     * POST /api/lostfound/{lostfound_id}/comments
     */
    public function addComment(string $lostfound_id, Request $request)
    {
        $user = $request->user();

        // 1. Verify item exists
        $item = LostfoundItem::where(
            "lostfound_id",
            $lostfound_id,
        )->firstOrFail();

        // 2. Validate input
        $validated = $request->validate([
            "comment" => "required|string|max:1000",
        ]);

        // 3. Create comment
        $comment = new LostfoundComment();
        $comment->comment_id = "CMT" . strtoupper(Str::random(7));
        $comment->lostfound_id = $lostfound_id;
        $comment->user_id = $user->user_id;
        $comment->comment = $validated["comment"];
        $comment->created_at = \Illuminate\Support\Carbon::now();
        $comment->save();

        // 4. Also push to Firebase Realtime Database for realtime updates
        try {
            $database = $this->firebase->getDatabase();
            if ($database) {
                $ref = $database->getReference(
                    "lostfound_comments/" . $lostfound_id,
                );
                $ref->push([
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
            \Illuminate\Support\Facades\Log::error(
                "Firebase RTDB comment sync failed: " . $e->getMessage(),
            );
        }

        return response()->json(
            [
                "message" => "Comment added successfully",
                "data" => $comment,
            ],
            201,
        );
    }

    /**
     * PUT /api/lostfound/comments/{comment_id}
     */
    public function updateComment(string $comment_id, Request $request)
    {
        $user = $request->user();
        $comment = LostfoundComment::where(
            "comment_id",
            $comment_id,
        )->firstOrFail();

        // Only allow owner to update
        if ($comment->user_id !== $user->user_id) {
            return response()->json(["message" => "Unauthorized"], 403);
        }

        $validated = $request->validate([
            "comment" => "required|string|max:1000",
        ]);

        $comment->comment = $validated["comment"];
        $comment->update_at = now();
        $comment->save();

        return response()->json([
            "message" => "Comment updated successfully",
            "data" => $comment,
        ]);
    }

    /**
     * DELETE /api/lostfound/comments/{comment_id}
     */
    public function deleteComment(string $comment_id, Request $request)
    {
        $user = $request->user();
        $comment = LostfoundComment::where(
            "comment_id",
            $comment_id,
        )->firstOrFail();

        // Allow owner or admin to delete
        if ($comment->user_id !== $user->user_id && $user->role !== "admin") {
            return response()->json(["message" => "Unauthorized"], 403);
        }

        $comment->delete();

        return response()->json(["message" => "Comment deleted successfully"]);
    }
}
