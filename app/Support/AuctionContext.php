<?php

namespace App\Support;

use App\Models\Auction;
use Illuminate\Http\Request;

class AuctionContext
{
    public function current(Request $request): ?Auction
    {
        $id = $this->currentId($request);
        return $id ? Auction::find($id) : null;
    }

    public function currentId(Request $request): ?int
    {
        $id = $request->session()->get('auction_id');
        if ($id && Auction::whereKey($id)->exists()) {
            return $id;
        }

        $auction = Auction::orderByDesc('start_date')->first();
        if ($auction) {
            $request->session()->put('auction_id', $auction->id);
            return $auction->id;
        }

        return null;
    }
}
