<?php

namespace App\Models;

use App\Mail\EventCreatedMail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class Event extends Model
{
    use HasFactory;

    protected static function booted()
    {
        static::created(function ($event) {
            try {
                $admins = User::where('role', 'admin')
                    ->where('notify_email', true)
                    ->where('notify_event', true)
                    ->get();

                foreach ($admins as $admin) {
                    Mail::to($admin->email)->send(new EventCreatedMail($event));
                }
            } catch (\Exception $e) {
                Log::error(
                    'Gagal mengirim email notifikasi event: '.
                        $e->getMessage(),
                );
            }
        });
    }

    protected $table = 'events';

    protected $primaryKey = 'event_id';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'event_id',
        'category_id',
        'title',
        'description',
        'start_date',
        'end_date',
        'location',
        'poster',
        'created_by',
        'status',
        'rejection_reason',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $appends = ['poster_url'];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'poster' => 'string',
    ];

    public function category()
    {
        return $this->belongsTo(
            EventCategory::class,
            'category_id',
            'category_id',
        );
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    public function likes()
    {
        return $this->hasMany(EventLike::class, 'event_id', 'event_id');
    }

    protected function posterUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->poster
                ? Storage::disk('public')->url(
                    $this->poster,
                )
                : null,
        );
    }
}
