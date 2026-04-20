<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Announcement extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'announcements';
    protected $primaryKey = 'announcement_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    const DELETED_AT = 'deleted_at';

    protected $fillable = [
        'announcement_id', 'title', 'content', 'file', 'created_by', 'created_at',
    ];

    protected $appends = ['file_url'];

    protected $casts = [
        'created_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    protected function fileUrl(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: fn () => $this->file ? \Illuminate\Support\Facades\Storage::disk('public')->url($this->file) : null,
        );
    }
}
