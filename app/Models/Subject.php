<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'number',
        'phone',
        'id_number',
        'received_at',
        'auction_id',
    ];

    protected $casts = [
        'received_at' => 'date',
    ];

    public function items()
    {
        return $this->hasMany(SubjectItem::class);
    }

    public function auction()
    {
        return $this->belongsTo(Auction::class);
    }
}
