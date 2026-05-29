<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Views\ViewAnnouncement;
use App\Services\AnnouncementNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class AnnouncementController extends Controller
{
    public function __construct(
        private readonly AnnouncementNotificationService $notificationService,
    ) {}

    public function index()
    {
        $hariMap = [
            "Sunday" => "Minggu",
            "Monday" => "Senin",
            "Tuesday" => "Selasa",
            "Wednesday" => "Rabu",
            "Thursday" => "Kamis",
            "Friday" => "Jumat",
            "Saturday" => "Sabtu",
        ];
        $bulanMap = [
            "January" => "Januari",
            "February" => "Februari",
            "March" => "Maret",
            "April" => "April",
            "May" => "Mei",
            "June" => "Juni",
            "July" => "Juli",
            "August" => "Agustus",
            "September" => "September",
            "October" => "Oktober",
            "November" => "November",
            "December" => "Desember",
        ];

        $announcements = ViewAnnouncement::query()
            ->where("creator_role", "admin")
            ->where("title", "not like", "Pengajuan %")
            ->where("title", "not like", "% disetujui")
            ->where("title", "not like", "% ditolak")
            ->orderBy("created_at", "desc")
            ->get()
            ->map(function ($announcement) use ($hariMap, $bulanMap) {
                $date = $announcement->created_at;
                $formattedDate =
                    $hariMap[$date->format("l")] .
                    ", " .
                    $date->format("d") .
                    " " .
                    $bulanMap[$date->format("F")] .
                    " " .
                    $date->format("Y");

                $publishAt = $announcement->publish_at;
                $displayPublishAt =
                    $publishAt ??
                    ($announcement->status === "publish"
                        ? $announcement->created_at
                        : null);

                return [
                    "id" => $announcement->announcement_id,
                    "title" => $announcement->title,
                    "content" => $announcement->content,
                    "status" => $announcement->status,
                    "creator_name" => $announcement->creator_name ?? "Sistem",
                    "date" => $formattedDate,
                    "file_url" => $announcement->file_url,
                    "publish_at" => $publishAt?->format("Y-m-d\TH:i"),
                    "publish_at_text" => $displayPublishAt
                        ? $displayPublishAt->format("d/m/Y H:i")
                        : "-",
                    "is_scheduled" =>
                        $announcement->status === "publish" &&
                        $publishAt &&
                        $publishAt->isFuture(),
                ];
            });

        return view("admin.announcements", compact("announcements"));
    }

    public function store(Request $request)
    {
        $request->validate([
            "title" => "required|string|max:255",
            "content" => "required|string",
            "status" => "required|in:draft,publish",
            "publish_at" => "nullable|date",
            "file" => "nullable|file|mimes:pdf,jpg,jpeg,png|max:5120",
        ]);

        $announcement = new Announcement();
        $announcement->announcement_id = "ANN" . strtoupper(Str::random(7));
        $announcement->title = $request->title;
        $announcement->content = $request->input("content");
        $announcement->status = $request->status;
        $announcement->publish_at = $request->filled("publish_at")
            ? Carbon::parse($request->publish_at, config("app.timezone"))
            : ($request->status === "publish"
                ? Carbon::now()
                : null);
        $announcement->created_by = Auth::id();
        $announcement->created_at = Carbon::now();

        if ($request->hasFile("file")) {
            $path = $request->file("file")->store("announcements", "public");
            $announcement->file = $path;
        }

        $announcement->save();
        $this->notificationService->publishAndNotify($announcement);

        $message =
            $announcement->publish_at && $announcement->publish_at->isFuture()
                ? "Pengumuman berhasil dibuat dan dijadwalkan."
                : "Pengumuman berhasil dibuat.";

        return back()->with("success", $message);
    }

    public function update(Request $request, $announcement_id)
    {
        $request->validate([
            "title" => "required|string|max:255",
            "content" => "required|string",
            "status" => "required|in:draft,publish",
            "publish_at" => "nullable|date",
            "file" => "nullable|file|mimes:pdf,jpg,jpeg,png|max:5120",
        ]);

        $announcement = Announcement::findOrFail($announcement_id);
        $announcement->title = $request->title;
        $announcement->content = $request->input("content");
        $announcement->status = $request->status;
        $announcement->publish_at = $request->filled("publish_at")
            ? Carbon::parse($request->publish_at, config("app.timezone"))
            : ($request->status === "publish"
                ? Carbon::now()
                : null);

        if ($request->hasFile("file")) {
            if ($announcement->file) {
                Storage::disk("public")->delete($announcement->file);
            }
            $path = $request->file("file")->store("announcements", "public");
            $announcement->file = $path;
        }

        $announcement->save();
        $this->notificationService->publishAndNotify($announcement);

        $message =
            $announcement->publish_at && $announcement->publish_at->isFuture()
                ? "Pengumuman berhasil diperbarui dan dijadwalkan."
                : "Pengumuman berhasil diperbarui.";

        return back()->with("success", $message);
    }

    public function destroy($announcement_id)
    {
        $announcement = Announcement::findOrFail($announcement_id);
        $announcement->delete();

        return back()->with("success", "Pengumuman berhasil dihapus.");
    }
}
