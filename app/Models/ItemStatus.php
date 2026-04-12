<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemStatus extends Model
{
    use HasFactory;

    protected $table = 'item_status';
    protected $primaryKey = 'status_id';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = ['status_id', 'name_status'];

    public function lostfoundItems()
    {
        return $this->hasMany(LostfoundItem::class, 'status_id', 'status_id');
    }
}
