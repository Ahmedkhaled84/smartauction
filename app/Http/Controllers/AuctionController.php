<?php

namespace App\Http\Controllers;

use App\Models\AuctionEntry;
use App\Models\Subject;
use App\Support\AuctionContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuctionController extends Controller
{
    public function index(Request $request, AuctionContext $auctionContext)
    {
        $auctionId = $auctionContext->currentId($request);
        $subjects = Subject::with('items')
            ->where('auction_id', $auctionId)
            ->orderBy('name')
            ->get();
        $entries = AuctionEntry::with(['subject', 'items'])
            ->where('auction_id', $auctionId)
            ->orderBy('sequence_number')
            ->get();
        $usedItemCounts = DB::table('auction_entry_items')
            ->join('auction_entries', 'auction_entries.id', '=', 'auction_entry_items.auction_entry_id')
            ->where('auction_entries.auction_id', $auctionId)
            ->select('subject_item_id', DB::raw('SUM(quantity) as total'))
            ->groupBy('subject_item_id')
            ->pluck('total', 'subject_item_id')
            ->toArray();
        $entriesPayload = $entries->map(function ($entry) {
            return [
                'id' => $entry->id,
                'items' => $entry->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'quantity' => $item->pivot->quantity ?? 1,
                        'name' => $item->pivot->item_name_override ?: $item->name,
                    ];
                })->values()->all(),
            ];
        })->values()->all();
        $subjectsPayload = $subjects->map(function ($subject) {
            return [
                'id' => $subject->id,
                'name' => $subject->name,
                'number' => $subject->number,
                'items' => $subject->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                        'quantity' => $item->quantity ?? 1,
                    ];
                })->values()->all(),
            ];
        })->values()->all();

        return view('auction.index', [
            'subjects' => $subjects,
            'entries' => $entries,
            'subjectsPayload' => $subjectsPayload,
            'usedItemCounts' => $usedItemCounts,
            'entriesPayload' => $entriesPayload,
            'buyerNames' => $entries->pluck('buyer_name')->filter()->unique()->values(),
        ]);
    }

    public function store(Request $request, AuctionContext $auctionContext)
    {
        $auctionId = $auctionContext->currentId($request);
        if (!$auctionId) {
            return back()->withErrors(['sname' => 'Create or load an auction first.'])->withInput();
        }

        $data = $request->validate([
            'sname' => ['required', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['required', 'exists:subject_items,id'],
            'quantities' => ['required', 'array', 'min:1'],
            'quantities.*' => ['required', 'integer', 'min:1'],
            'item_names' => ['nullable', 'array'],
            'item_names.*' => ['nullable', 'string', 'max:255'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'buyer_name' => ['nullable', 'string', 'max:255'],
        ]);

        $subject = Subject::with('items')
            ->where('auction_id', $auctionId)
            ->where(function ($query) use ($data) {
                $query->where('name', $data['sname'])
                    ->orWhere('number', $data['sname']);
            })
            ->first();

        if (!$subject) {
            return back()->withErrors(['sname' => 'Sname must match a saved name or number.'])->withInput();
        }

        $validItemIds = $subject->items->pluck('id')->all();
        $usedItemCounts = DB::table('auction_entry_items')
            ->join('auction_entries', 'auction_entries.id', '=', 'auction_entry_items.auction_entry_id')
            ->where('auction_entries.auction_id', $auctionId)
            ->select('subject_item_id', DB::raw('SUM(quantity) as total'))
            ->groupBy('subject_item_id')
            ->pluck('total', 'subject_item_id')
            ->toArray();

        foreach ($data['items'] as $itemId) {
            if (!in_array((int) $itemId, $validItemIds, true)) {
                return back()->withErrors(['items' => 'All items must belong to the selected name/number.'])->withInput();
            }
            $itemModel = $subject->items->firstWhere('id', (int) $itemId);
            $itemTotal = (int) ($itemModel?->quantity ?? 1);
            $alreadyUsed = (int) ($usedItemCounts[$itemId] ?? 0);
            $requested = (int) ($data['quantities'][$itemId] ?? 1);
            if ($alreadyUsed + $requested > $itemTotal) {
                return back()->withErrors(['items' => 'Item quantity exceeded available stock.'])->withInput();
            }
        }

        DB::transaction(function () use ($data, $subject) {
            $nextNumber = (int) (AuctionEntry::where('auction_id', $subject->auction_id)->max('sequence_number') ?? 0) + 1;

            $entry = AuctionEntry::create([
                'sequence_number' => $nextNumber,
                'subject_id' => $subject->id,
                'auction_id' => $subject->auction_id,
                'price' => $data['price'] ?? null,
                'buyer_name' => $data['buyer_name'] ?? null,
            ]);

            $syncData = [];
            foreach ($data['items'] as $itemId) {
                $quantity = (int) ($data['quantities'][$itemId] ?? 1);
                $syncData[$itemId] = ['quantity' => max(1, $quantity)];
            }
            $entry->items()->sync($syncData);
        });

        return redirect()->route('auction.index')->with('status', 'Auction row added.');
    }

    public function edit(Request $request, AuctionEntry $auction, AuctionContext $auctionContext)
    {
        $auctionId = $auctionContext->currentId($request);
        if ($auction->auction_id !== $auctionId) {
            abort(404);
        }

        $subjects = Subject::with('items')
            ->where('auction_id', $auctionId)
            ->orderBy('name')
            ->get();
        $usedItemCounts = DB::table('auction_entry_items')
            ->join('auction_entries', 'auction_entries.id', '=', 'auction_entry_items.auction_entry_id')
            ->where('auction_entries.auction_id', $auctionId)
            ->where('auction_entry_id', '!=', $auction->id)
            ->select('subject_item_id', DB::raw('SUM(quantity) as total'))
            ->groupBy('subject_item_id')
            ->pluck('total', 'subject_item_id')
            ->toArray();

        $subjectsPayload = $subjects->map(function ($subject) {
            return [
                'id' => $subject->id,
                'name' => $subject->name,
                'number' => $subject->number,
                'items' => $subject->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                        'quantity' => $item->quantity ?? 1,
                    ];
                })->values()->all(),
            ];
        })->values()->all();

        $selectedItems = $auction->items->map(function ($item) {
            return [
                'id' => $item->id,
                'quantity' => $item->pivot->quantity ?? 1,
            ];
        })->values()->all();

        return view('auction.edit', [
            'auction' => $auction,
            'subjects' => $subjects,
            'subjectsPayload' => $subjectsPayload,
            'usedItemCounts' => $usedItemCounts,
            'selectedItems' => $selectedItems,
        ]);
    }

    public function update(Request $request, AuctionEntry $auction, AuctionContext $auctionContext)
    {
        $auctionId = $auctionContext->currentId($request);
        if ($auction->auction_id !== $auctionId) {
            abort(404);
        }

        $data = $request->validate([
            'sname' => ['required', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['required', 'exists:subject_items,id'],
            'quantities' => ['required', 'array', 'min:1'],
            'quantities.*' => ['required', 'integer', 'min:1'],
            'item_names' => ['nullable', 'array'],
            'item_names.*' => ['nullable', 'string', 'max:255'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'buyer_name' => ['nullable', 'string', 'max:255'],
        ]);

        $subject = Subject::with('items')
            ->where('auction_id', $auctionId)
            ->where(function ($query) use ($data) {
                $query->where('name', $data['sname'])
                    ->orWhere('number', $data['sname']);
            })
            ->first();

        if (!$subject) {
            return back()->withErrors(['sname' => 'Sname must match a saved name or number.'])->withInput();
        }

        $validItemIds = $subject->items->pluck('id')->all();
        $usedItemCounts = DB::table('auction_entry_items')
            ->join('auction_entries', 'auction_entries.id', '=', 'auction_entry_items.auction_entry_id')
            ->where('auction_entries.auction_id', $auctionId)
            ->where('auction_entry_id', '!=', $auction->id)
            ->select('subject_item_id', DB::raw('SUM(quantity) as total'))
            ->groupBy('subject_item_id')
            ->pluck('total', 'subject_item_id')
            ->toArray();

        foreach ($data['items'] as $itemId) {
            if (!in_array((int) $itemId, $validItemIds, true)) {
                return back()->withErrors(['items' => 'All items must belong to the selected name/number.'])->withInput();
            }
            $itemModel = $subject->items->firstWhere('id', (int) $itemId);
            $itemTotal = (int) ($itemModel?->quantity ?? 1);
            $alreadyUsed = (int) ($usedItemCounts[$itemId] ?? 0);
            $requested = (int) ($data['quantities'][$itemId] ?? 1);
            if ($alreadyUsed + $requested > $itemTotal) {
                return back()->withErrors(['items' => 'Item quantity exceeded available stock.'])->withInput();
            }
        }

        DB::transaction(function () use ($data, $subject, $auction, $validItemIds) {
            $auction->update([
                'subject_id' => $subject->id,
                'auction_id' => $auctionId,
                'price' => $data['price'] ?? null,
                'buyer_name' => $data['buyer_name'] ?? null,
            ]);

            $existingOverrides = $auction->items->mapWithKeys(function ($item) {
                return [$item->id => $item->pivot->item_name_override];
            })->toArray();

            $syncData = [];
            foreach ($data['items'] as $itemId) {
                $quantity = (int) ($data['quantities'][$itemId] ?? 1);
                $itemModel = $subject->items->firstWhere('id', (int) $itemId);
                $nameOverride = $data['item_names'][$itemId] ?? ($existingOverrides[$itemId] ?? null);

                if ($itemModel && $nameOverride && $nameOverride !== $itemModel->name) {
                    // Move the row quantity from old item to the new name.
                    $moveQty = max(1, $quantity);
                    if ($itemModel->quantity <= $moveQty) {
                        $itemModel->delete();
                    } else {
                        $itemModel->decrement('quantity', $moveQty);
                    }

                    $newItem = $subject->items()->where('name', $nameOverride)->first();
                    if ($newItem) {
                        $newItem->increment('quantity', $moveQty);
                    } else {
                        $newItem = $subject->items()->create([
                            'name' => $nameOverride,
                            'quantity' => $moveQty,
                        ]);
                    }

                    $syncData[$newItem->id] = [
                        'quantity' => $moveQty,
                        'item_name_override' => null,
                    ];
                    continue;
                }

                $syncData[$itemId] = [
                    'quantity' => max(1, $quantity),
                    'item_name_override' => $nameOverride ?: null,
                ];
            }
            $auction->items()->sync($syncData);
        });

        return redirect()->route('auction.index')->with('status', 'Auction row updated.');
    }
}
