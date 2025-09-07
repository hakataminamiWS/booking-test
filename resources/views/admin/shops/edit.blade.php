@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Edit Shop: {{ $shop->name }}</h1>
    <form action="{{ route('admin.shops.update', $shop->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="name" class="form-label">Shop Name</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $shop->name) }}" required>
        </div>
        <div class="mb-3">
            <label for="address" class="form-label">Address</label>
            <input type="text" class="form-control" id="address" name="address" value="{{ old('address', $shop->address) }}">
        </div>
        <div class="mb-3">
            <label for="phone_number" class="form-label">Phone Number</label>
            <input type="text" class="form-control" id="phone_number" name="phone_number" value="{{ old('phone_number', $shop->phone_number) }}">
        </div>
        <div class="mb-3">
            <label for="opening_time" class="form-label">Opening Time</label>
            <input type="time" class="form-control" id="opening_time" name="opening_time" value="{{ old('opening_time', $shop->opening_time ? $shop->opening_time->format('H:i') : '') }}">
        </div>
        <div class="mb-3">
            <label for="closing_time" class="form-label">Closing Time</label>
            <input type="time" class="form-control" id="closing_time" name="closing_time" value="{{ old('closing_time', $shop->closing_time ? $shop->closing_time->format('H:i') : '') }}">
        </div>
        <div class="mb-3">
            <label for="regular_holidays" class="form-label">Regular Holidays (comma separated)</label>
            <input type="text" class="form-control" id="regular_holidays" name="regular_holidays" value="{{ old('regular_holidays', implode(', ', (array)$shop->regular_holidays)) }}">
        </div>
        <div class="mb-3">
            <label for="reservation_acceptance_settings" class="form-label">Reservation Acceptance Settings (JSON)</label>
            <textarea class="form-control" id="reservation_acceptance_settings" name="reservation_acceptance_settings">{{ old('reservation_acceptance_settings', json_encode($shop->reservation_acceptance_settings)) }}</textarea>
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
    </form>
</div>
@endsection