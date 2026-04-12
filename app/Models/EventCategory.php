<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventCategory extends Model
{
    use HasFactory;

    protected $table = 'event_categories';
    protected $primaryKey = 'category_id';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = ['category_id', 'name_category'];

    public function events()
    {
        return $this->hasMany(Event::class, 'category_id', 'category_id');
    }
}
