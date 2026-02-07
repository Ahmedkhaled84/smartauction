<?php

namespace App\Http\Controllers;

use App\Support\AuctionContext;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request, AuctionContext $auctionContext)
    {
        $currentAuction = $auctionContext->current($request);

        return view('home', [
            'currentAuction' => $currentAuction,
        ]);
    }
}
