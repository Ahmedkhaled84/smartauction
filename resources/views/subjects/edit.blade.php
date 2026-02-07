@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-7">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h2 class="h5 fw-semibold mb-0">Edit Subject</h2>
                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('subjects.index') }}">Back</a>
                </div>
                <form method="POST" action="{{ route('subjects.update', $subject) }}" data-items-form autocomplete="off">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label" for="name">Field name</label>
                        <input class="form-control" id="name" name="name" value="{{ old('name', $subject->name) }}" required>
                        @error('name')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="number">Number</label>
                        <input class="form-control" id="number" name="number" value="{{ old('number', $subject->number) }}" required>
                        @error('number')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="phone">Phone</label>
                        <input class="form-control" id="phone" name="phone" value="{{ old('phone', $subject->phone) }}">
                        @error('phone')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="id_number">ID number</label>
                        <input class="form-control" id="id_number" name="id_number" value="{{ old('id_number', $subject->id_number) }}">
                        @error('id_number')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <label class="form-label mb-0">Items</label>
                            <button class="btn btn-sm btn-outline-primary" type="button" data-add-item>Add item</button>
                        </div>
                        <div class="mt-2 d-grid gap-2" data-items-wrapper>
                            @foreach (old('items', $subject->items->pluck('name')->all()) as $index => $item)
                                <div class="input-group">
                                    <input class="form-control" name="items[]" value="{{ $item }}" required>
                                    <input class="form-control" name="item_quantities[]" type="number" min="1" value="{{ old('item_quantities.' . $index, $subject->items[$index]->quantity ?? 1) }}" style="max-width: 120px;" required>
                                    <input class="form-control" name="item_estimates[]" type="number" min="0" step="0.01" value="{{ old('item_estimates.' . $index, $subject->items[$index]->estimate ?? '') }}" placeholder="Estimate" style="max-width: 140px;">
                                    <button class="btn btn-outline-danger" type="button" data-remove-item>Remove</button>
                                </div>
                            @endforeach
                        </div>
                        @error('items')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                        @error('items.*')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                        @error('item_quantities')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                        @error('item_quantities.*')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                        @error('item_estimates')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                        @error('item_estimates.*')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <button class="btn btn-primary w-100" type="submit">Save changes</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
