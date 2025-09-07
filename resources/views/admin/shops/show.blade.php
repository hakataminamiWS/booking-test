@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Shop Details: {{ $shop->name }}</h1>
    <p><strong>ID:</strong> {{ $shop->id }}</p>
    <p><strong>Address:</strong> {{ $shop->address }}</p>
    <p><strong>Phone:</strong> {{ $shop->phone_number }}</p>
    <p><strong>Opening Time:</strong> {{ $shop->opening_time ? $shop->opening_time->format('H:i') : 'N/A' }}</p>
    <p><strong>Closing Time:</strong> {{ $shop->closing_time ? $shop->closing_time->format('H:i') : 'N/A' }}</p>
    <p><strong>Regular Holidays:</strong> {{ implode(', ', (array)$shop->regular_holidays) }}</p>
    <p><strong>Reservation Settings:</strong> {{ json_encode($shop->reservation_acceptance_settings) }}</p>
    <p><strong>Created At:</strong> {{ $shop->created_at }}</p>
    <p><strong>Updated At:</strong> {{ $shop->updated_at }}</p>
    <p><strong>Deleted At:</strong> {{ $shop->deleted_at ?? 'N/A' }}</p>

    <a href="{{ route('admin.shops.edit', $shop->id) }}" class="btn btn-warning">Edit</a>
    <form action="{{ route('admin.shops.destroy', $shop->id) }}" method="POST" style="display:inline-block;">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger">Delete (Soft)</button>
    </form>
    @if($shop->trashed())
    <form action="{{ route('admin.shops.forceDelete', $shop->id) }}" method="POST" style="display:inline-block;">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger">Delete (Force)</button>
    </form>
    @endif
    <a href="{{ route('admin.shops.index') }}" class="btn btn-secondary">Back to List</a>
</div>
@endsection