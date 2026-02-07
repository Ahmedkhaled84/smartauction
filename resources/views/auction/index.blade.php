@extends('layouts.app')

@section('content')
<div class="card shadow-sm border-0">
    <div class="card-body p-4">
        @if ($errors->any())
            <div class="alert alert-danger mb-3">
                {{ $errors->first() }}
            </div>
        @endif
        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-2 mb-3">
            <div class="d-flex flex-column flex-lg-row align-items-lg-center gap-2">
                <h2 class="h5 fw-semibold mb-0">Smart Auction Table</h2>
                <a class="btn btn-sm btn-outline-secondary" href="{{ route('export.auctions') }}">Export CSV</a>
                <button class="btn btn-sm btn-outline-primary" id="printAuction" type="button">Print Table</button>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="form-check form-switch mb-0">
                    <input class="form-check-input" type="checkbox" role="switch" id="toggleEstimate" checked>
                    <label class="form-check-label" for="toggleEstimate">Show estimate</label>
                </div>
                <input class="form-control form-control-sm w-auto" id="auctionSearch" type="search" placeholder="Search number, name, item, price, buyer">
            </div>
        </div>
        <div class="table-responsive" dir="rtl">
            <table class="table align-middle table-bordered table-resizable" id="auctionTable">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 80px;">Number</th>
                                <th>Sname</th>
                                <th>Desc</th>
                                <th class="text-center" style="width: 120px;">Price</th>
                                <th class="text-center estimate-col" style="width: 140px;">Estimate</th>
                                <th>Bname</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($entries as $entry)
                                <tr class="auction-entry-row">
                                    <td class="text-center fw-semibold">{{ $entry->sequence_number }}</td>
                                    <td>
                                        <div class="cell-display" data-cell="sname">{{ $entry->subject?->name ?? '' }}</div>
                                        <div class="cell-edit d-none">
                                            <input class="form-control form-control-sm" name="sname" list="snameOptions" value="{{ $entry->subject?->name ?? '' }}" form="edit-form-{{ $entry->id }}" required>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="cell-display" data-cell="desc">
                                        {{ $entry->items->map(function ($item) {
                                            $qty = $item->pivot->quantity ?? $item->quantity ?? 1;
                                            $name = $item->pivot->item_name_override ?: $item->name;
                                            return $qty > 1 ? ($qty . ' ' . $name) : $name;
                                        })->implode(' و ') }}
                                        </div>
                                        <div class="cell-edit d-none">
                                            <div class="border rounded-3 p-2 items-editor" data-items-editor data-entry-id="{{ $entry->id }}" data-form-id="edit-form-{{ $entry->id }}"></div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="cell-display cell-clickable" data-cell="price" data-placeholder="Write price">{{ $entry->price }}</div>
                                        <div class="cell-edit d-none">
                                            <input class="form-control form-control-sm text-center" name="price" type="number" step="0.01" min="0" value="{{ $entry->price }}" data-cell-input="price" form="edit-form-{{ $entry->id }}">
                                        </div>
                                    </td>
                                    <td class="text-center estimate-col">
                                        @php
                                            $estimateTotal = 0;
                                            $hasEstimate = false;
                                            foreach ($entry->items as $item) {
                                                if (!is_null($item->estimate)) {
                                                    $hasEstimate = true;
                                                    $qty = $item->pivot->quantity ?? $item->quantity ?? 1;
                                                    $estimateTotal += ((float) $item->estimate) * (int) $qty;
                                                }
                                            }
                                        @endphp
                                        {{ $hasEstimate ? rtrim(rtrim(number_format($estimateTotal, 2, '.', ''), '0'), '.') : '—' }}
                                    </td>
                                    <td>
                                        <div class="cell-display cell-clickable" data-cell="bname" data-placeholder="Write buyer">{{ $entry->buyer_name }}</div>
                                        <div class="cell-edit d-none">
                                            <input class="form-control form-control-sm" name="buyer_name" value="{{ $entry->buyer_name }}" data-cell-input="bname" list="buyerNames" autocomplete="off" form="edit-form-{{ $entry->id }}">
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-outline-secondary" type="button" data-edit-row>Edit</button>
                                        <button class="btn btn-sm btn-primary d-none" type="submit" form="edit-form-{{ $entry->id }}" data-save-row>Save</button>
                                        <button class="btn btn-sm btn-outline-danger d-none" type="button" data-cancel-row>Cancel</button>
                                        <form id="edit-form-{{ $entry->id }}" method="POST" action="{{ route('auction.update', $entry) }}" class="d-none">
                                            @csrf
                                            @method('PUT')
                                            @foreach ($entry->items as $item)
                                                <input type="hidden" name="items[]" value="{{ $item->id }}">
                                                <input type="hidden" name="quantities[{{ $item->id }}]" value="{{ $item->pivot->quantity ?? $item->quantity ?? 1 }}">
                                                <input type="hidden" name="item_names[{{ $item->id }}]" value="{{ $item->pivot->item_name_override ?? $item->name }}">
                                            @endforeach
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-muted text-center py-4">No auction rows yet.</td>
                                </tr>
                            @endforelse
                            <tr data-add-row-trigger>
                                <td colspan="7" class="text-center">
                                    <button class="btn btn-outline-primary" type="button" id="addRowButton">Add row</button>
                                </td>
                            </tr>
                            <tr id="addRow" class="{{ $errors->any() ? '' : 'd-none' }}">
                                <td class="text-center text-muted">Auto</td>
                                <td>
                                    <input class="form-control form-control-sm" name="sname" list="snameOptions" form="create-form" autocomplete="off" value="{{ old('sname') }}" required>
                                </td>
                                <td>
                                    <div class="border rounded-3 p-2 items-editor" data-items-editor data-entry-id="new" data-form-id="create-form"></div>
                                </td>
                                <td class="text-center">
                                    <input class="form-control form-control-sm text-center" name="price" type="number" step="0.01" min="0" form="create-form" value="{{ old('price') }}">
                                </td>
                                <td class="text-center estimate-col text-muted">—</td>
                                <td>
                                    <input class="form-control form-control-sm" name="buyer_name" list="buyerNames" autocomplete="off" form="create-form" value="{{ old('buyer_name') }}">
                                </td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-primary" type="submit" form="create-form">Save</button>
                                    <button class="btn btn-sm btn-outline-secondary" type="button" id="cancelAddRow">Cancel</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('auction.store') }}" id="create-form" class="d-none" autocomplete="off">
    @csrf
</form>

<datalist id="snameOptions">
    @foreach ($subjects as $subject)
        <option value="{{ $subject->name }}"></option>
        <option value="{{ $subject->number }}"></option>
    @endforeach
</datalist>
<datalist id="buyerNames">
    @foreach ($buyerNames as $name)
        <option value="{{ $name }}"></option>
    @endforeach
</datalist>

<script>
    window.__subjects = @json($subjectsPayload);
    window.__usedItemCounts = @json($usedItemCounts);
    window.__entries = @json($entriesPayload);
    window.__selectedItems = @json(old('items', []));
    window.__selectedQuantities = @json(old('quantities', []));
</script>
@endsection
