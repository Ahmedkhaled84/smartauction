<?php

namespace App\Http\Controllers;

use App\Models\Auction;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AuctionSessionController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date'],
        ]);

        $start = Carbon::parse($data['start_date']);
        $end = Carbon::parse($data['end_date']);
        $code = $this->generateCode($start, $end);

        if (!$code) {
            return response()->json([
                'message' => 'Unable to generate a unique code for this date.',
            ], 422);
        }

        $auction = Auction::create([
            'code' => $code,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
        ]);

        $request->session()->put('auction_id', $auction->id);

        return response()->json([
            'code' => $auction->code,
        ]);
    }

    public function load(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:20'],
        ]);

        $auction = Auction::where('code', $data['code'])->first();
        if (!$auction) {
            return response()->json([
                'message' => 'Auction code not found.',
            ], 404);
        }

        $request->session()->put('auction_id', $auction->id);

        return response()->json([
            'code' => $auction->code,
        ]);
    }

    public function codes()
    {
        $codes = Auction::orderByDesc('start_date')
            ->get(['code', 'start_date', 'end_date'])
            ->map(function (Auction $auction) {
                return [
                    'code' => $auction->code,
                    'start_date' => $auction->start_date?->format('Y-m-d'),
                    'end_date' => $auction->end_date?->format('Y-m-d'),
                ];
            });

        return response()->json([
            'codes' => $codes,
        ]);
    }

    private function generateCode(Carbon $start, Carbon $end): ?string
    {
        $base = sprintf(
            '%d.%d-%d-%s',
            $start->day,
            $end->day,
            $end->month,
            $end->format('y')
        );

        if (!Auction::where('code', $base)->exists()) {
            return $base;
        }

        for ($suffix = 1; $suffix <= 9; $suffix++) {
            $candidate = $base . '-' . $suffix;
            if (!Auction::where('code', $candidate)->exists()) {
                return $candidate;
            }
        }

        return null;
    }
}
