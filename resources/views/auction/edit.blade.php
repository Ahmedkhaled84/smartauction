@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-xl-6">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h2 class="h5 fw-semibold mb-0">Edit Auction Row</h2>
                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('auction.index') }}">Back</a>
                </div>
                <form method="POST" action="{{ route('auction.update', $auction) }}" id="auctionEditForm">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label" for="sname">Sname (Field / Number)</label>
                        <input class="form-control" id="sname" name="sname" list="snameOptions" value="{{ old('sname', $auction->subject?->name ?? '') }}" required>
                        <datalist id="snameOptions">
                            @foreach ($subjects as $subject)
                                <option value="{{ $subject->name }}"></option>
                                <option value="{{ $subject->number }}"></option>
                            @endforeach
                        </datalist>
                        @error('sname')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Desc (Items)</label>
                        <div class="border rounded-3 p-2" id="itemsList">
                            <div class="text-muted small">Write a name or number to load items.</div>
                        </div>
                        <div class="text-muted small mt-2">Set quantity for each selected item.</div>
                        @error('items')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                        @error('items.*')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                        @error('quantities')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                        @error('quantities.*')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="price">Price</label>
                        <input class="form-control" id="price" name="price" type="number" step="0.01" min="0" value="{{ old('price', $auction->price) }}">
                        @error('price')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="buyer_name">Bname</label>
                        <input class="form-control" id="buyer_name" name="buyer_name" value="{{ old('buyer_name', $auction->buyer_name) }}">
                        @error('buyer_name')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <button class="btn btn-primary w-100" type="submit">Save changes</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    window.__subjects = @json($subjectsPayload);
    window.__usedItemCounts = @json($usedItemCounts);
    window.__selectedItems = @json(collect($selectedItems)->pluck('id'));
    window.__selectedQuantities = @json(collect($selectedItems)->pluck('quantity', 'id'));
</script>
@endsection
