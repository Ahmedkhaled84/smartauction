<?php

namespace App\Http\Controllers;

use App\Models\AuctionEntry;
use App\Models\Subject;
use App\Support\AuctionContext;
use Illuminate\Support\Facades\Response;

class ExportController extends Controller
{
    public function subjectsCsv(AuctionContext $auctionContext)
    {
        $auctionId = $auctionContext->currentId(request());
        $subjects = Subject::with('items')
            ->where('auction_id', $auctionId)
            ->orderBy('name')
            ->get();

        return Response::streamDownload(function () use ($subjects) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Field', 'Number', 'Phone', 'ID number', 'Items', 'Date']);

            foreach ($subjects as $subject) {
                $items = $subject->items->map(function ($item) {
                    return $item->name . ' × ' . ($item->quantity ?? 1);
                })->implode(' | ');
                $date = $subject->received_at?->format('Y-m-d') ?? '';
                fputcsv($handle, [$subject->name, $subject->number, $subject->phone, $subject->id_number ?? '', $items, $date]);
            }

            fclose($handle);
        }, 'subjects.csv');
    }

    public function auctionsCsv(AuctionContext $auctionContext)
    {
        $auctionId = $auctionContext->currentId(request());
        $entries = AuctionEntry::with(['subject', 'items'])
            ->where('auction_id', $auctionId)
            ->orderBy('sequence_number')
            ->get();

        return Response::streamDownload(function () use ($entries) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Number', 'Sname', 'Desc', 'Price', 'Bname']);

            foreach ($entries as $entry) {
                $items = $entry->items->map(function ($item) {
                    $qty = $item->pivot->quantity ?? $item->quantity ?? 1;
                    return $qty . ' ' . $item->name;
                })->implode(' | ');
                fputcsv($handle, [
                    $entry->sequence_number,
                    $entry->subject?->name ?? '',
                    $items,
                    $entry->price,
                    $entry->buyer_name,
                ]);
            }

            fclose($handle);
        }, 'auction.csv');
    }
}
