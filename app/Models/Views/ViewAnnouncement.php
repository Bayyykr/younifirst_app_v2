<?php

namespace App\Models\Views;

use Illuminate\Database\Eloquent\Model;

class ViewAnnouncement extends Model
{
    protected $table = "view_announcements";
    protected $primaryKey = "announcement_id";
    public $incrementing = false;
    protected $keyType = "string";
    public $timestamps = false;

    protected $guarded = ["*"];

    protected $appends = ["file_url"];

    protected $casts = [
        "created_at" => "datetime",
        "publish_at" => "datetime",
        "notified_at" => "datetime",
        "deleted_at" => "datetime",
        "file" => "string",
    ];

    protected function fileUrl(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: fn() => $this->file ? asset("storage/{$this->file}") : null,
        );
    }
}
