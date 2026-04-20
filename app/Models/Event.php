<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $table = 'events';
    protected $primaryKey = 'event_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'event_id', 'category_id', 'title', 'description',
        'start_date', 'end_date', 'location', 'poster',
        'created_by', 'status', 'created_at', 'updated_at', 'deleted_at',
    ];
    
    protected $appends = ['poster_url'];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date'   => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'poster'     => 'string',
    ];

    public function category()
    {
        return $this->belongsTo(EventCategory::class, 'category_id', 'category_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    public function likes()
    {
        return $this->hasMany(EventLike::class, 'event_id', 'event_id');
    }

    protected function posterUrl(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: fn () => $this->poster ? \Illuminate\Support\Facades\Storage::disk('public')->url($this->poster) : null,
        );
    }
}
