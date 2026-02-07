<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuctionEntryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'auction_entry_id',
        'subject_item_id',
    ];
}
