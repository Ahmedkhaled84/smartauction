<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Support\AuctionContext;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SubjectController extends Controller
{
    public function index(Request $request, AuctionContext $auctionContext)
    {
        $auctionId = $auctionContext->currentId($request);
        $subjects = Subject::with('items')
            ->where('auction_id', $auctionId)
            ->latest()
            ->get();

        return view('subjects.index', [
            'subjects' => $subjects,
        ]);
    }

    public function store(Request $request, AuctionContext $auctionContext)
    {
        $auctionId = $auctionContext->currentId($request);
        if (!$auctionId) {
            return back()->withErrors(['name' => 'Create or load an auction first.'])->withInput();
        }

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('subjects', 'name')->where(fn ($query) => $query->where('auction_id', $auctionId)),
            ],
            'number' => ['required', 'string', 'max:255', 'unique:subjects,number'],
            'phone' => ['nullable', 'string', 'max:50'],
            'id_number' => ['nullable', 'string', 'max:255', 'unique:subjects,id_number'],
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['required', 'string', 'max:255'],
            'item_quantities' => ['required', 'array', 'min:1'],
            'item_quantities.*' => ['required', 'integer', 'min:1'],
            'item_estimates' => ['nullable', 'array'],
            'item_estimates.*' => ['nullable', 'numeric', 'min:0'],
        ]);

        $subject = Subject::create([
            'name' => $data['name'],
            'number' => $data['number'],
            'phone' => $data['phone'],
            'id_number' => $data['id_number'] ?? null,
            'received_at' => now()->toDateString(),
            'auction_id' => $auctionId,
        ]);

        foreach ($data['items'] as $index => $itemName) {
            $quantity = (int) ($data['item_quantities'][$index] ?? 1);
            $estimate = $data['item_estimates'][$index] ?? null;
            $subject->items()->create([
                'name' => $itemName,
                'quantity' => max(1, $quantity),
                'estimate' => is_numeric($estimate) ? (float) $estimate : null,
            ]);
        }

        return redirect()->route('subjects.index')->with('status', 'Saved successfully.');
    }

    public function edit(Request $request, Subject $subject, AuctionContext $auctionContext)
    {
        $auctionId = $auctionContext->currentId($request);
        if ($subject->auction_id !== $auctionId) {
            abort(404);
        }

        $subject->load('items');

        return view('subjects.edit', [
            'subject' => $subject,
        ]);
    }

    public function update(Request $request, Subject $subject, AuctionContext $auctionContext)
    {
        $auctionId = $auctionContext->currentId($request);
        if ($subject->auction_id !== $auctionId) {
            abort(404);
        }

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('subjects', 'name')
                    ->ignore($subject->id)
                    ->where(fn ($query) => $query->where('auction_id', $auctionId)),
            ],
            'number' => ['required', 'string', 'max:255', 'unique:subjects,number,' . $subject->id],
            'phone' => ['nullable', 'string', 'max:50'],
            'id_number' => ['nullable', 'string', 'max:255', 'unique:subjects,id_number,' . $subject->id],
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['required', 'string', 'max:255'],
            'item_quantities' => ['required', 'array', 'min:1'],
            'item_quantities.*' => ['required', 'integer', 'min:1'],
            'item_estimates' => ['nullable', 'array'],
            'item_estimates.*' => ['nullable', 'numeric', 'min:0'],
        ]);

        $subject->update([
            'name' => $data['name'],
            'number' => $data['number'],
            'phone' => $data['phone'],
            'id_number' => $data['id_number'] ?? null,
        ]);

        $subject->items()->delete();
        foreach ($data['items'] as $index => $itemName) {
            $quantity = (int) ($data['item_quantities'][$index] ?? 1);
            $estimate = $data['item_estimates'][$index] ?? null;
            $subject->items()->create([
                'name' => $itemName,
                'quantity' => max(1, $quantity),
                'estimate' => is_numeric($estimate) ? (float) $estimate : null,
            ]);
        }

        return redirect()->route('subjects.index')->with('status', 'Subject updated.');
    }

    public function destroy(Request $request, Subject $subject, AuctionContext $auctionContext)
    {
        $auctionId = $auctionContext->currentId($request);
        if ($subject->auction_id !== $auctionId) {
            abort(404);
        }

        $subject->delete();

        return redirect()->route('subjects.index')->with('status', 'Subject deleted.');
    }
}
