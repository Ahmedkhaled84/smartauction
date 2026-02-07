<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuctionEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'sequence_number',
        'subject_id',
        'auction_id',
        'price',
        'buyer_name',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function items()
    {
        return $this->belongsToMany(SubjectItem::class, 'auction_entry_items')
            ->withPivot('quantity', 'item_name_override');
    }

    public function auction()
    {
        return $this->belongsTo(Auction::class);
    }
}
