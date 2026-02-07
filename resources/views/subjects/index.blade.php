@extends('layouts.app')

@section('content')
<div class="row g-4">
    <div class="col-12 col-xl-5">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h2 class="h5 fw-semibold mb-0">Section 1 · Create Subject</h2>
                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('export.subjects') }}">Export CSV</a>
                </div>
                <form method="POST" action="{{ route('subjects.store') }}" data-items-form autocomplete="off">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" for="name">Field name</label>
                        <input class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="number">Number</label>
                        <input class="form-control" id="number" name="number" value="{{ old('number') }}" required>
                        @error('number')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="phone">Phone</label>
                        <input class="form-control" id="phone" name="phone" value="{{ old('phone') }}">
                        @error('phone')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="id_number">ID number</label>
                        <input class="form-control" id="id_number" name="id_number" value="{{ old('id_number') }}">
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
                            <div class="input-group">
                                <input class="form-control" name="items[]" placeholder="Item name" required>
                                <input class="form-control" name="item_quantities[]" type="number" min="1" value="1" style="max-width: 120px;" required>
                                <input class="form-control" name="item_estimates[]" type="number" min="0" step="0.01" placeholder="Estimate" style="max-width: 140px;">
                            </div>
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

                    <button class="btn btn-primary w-100" type="submit">Save</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-7">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body p-4">
                <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-2 mb-3">
                    <h2 class="h5 fw-semibold mb-0">Saved Subjects</h2>
                    <input class="form-control form-control-sm w-auto" id="subjectSearch" type="search" placeholder="Search name, number, or item">
                </div>
                <div class="table-responsive">
                    <table class="table align-middle table-hover" id="subjectsTable">
                        <thead class="table-light">
                            <tr>
                                <th>Field</th>
                                <th>Number</th>
                                <th>Phone</th>
                                <th>Items</th>
                                <th>ID number</th>
                                <th>Date</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($subjects as $subject)
                                <tr>
                                    <td class="fw-semibold">{{ $subject->name }}</td>
                                    <td>{{ $subject->number }}</td>
                                    <td>{{ $subject->phone }}</td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-2">
                                            @foreach ($subject->items as $item)
                                                <span class="badge bg-primary-subtle text-primary">
                                                    {{ $item->name }} × {{ $item->quantity }}
                                                    @if (!is_null($item->estimate))
                                                        · est {{ $item->estimate }}
                                                    @endif
                                                </span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td>{{ $subject->id_number }}</td>
                                    <td>{{ $subject->received_at?->format('Y-m-d') }}</td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a class="btn btn-outline-secondary" href="{{ route('subjects.edit', $subject) }}">Edit</a>
                                            <form method="POST" action="{{ route('subjects.destroy', $subject) }}" onsubmit="return confirm('Delete this subject?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-outline-danger" type="submit">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-muted text-center py-4">No subjects saved yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
