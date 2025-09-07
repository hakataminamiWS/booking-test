@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Create New Shop</h1>
    <form action="{{ route('admin.shops.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="name" class="form-label">Shop Name</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>
        <div class="mb-3">
            <label for="address" class="form-label">Address</label>
            <input type="text" class="form-control" id="address" name="address">
        </div>
        <div class="mb-3">
            <label for="phone_number" class="form-label">Phone Number</label>
            <input type="text" class="form-control" id="phone_number" name="phone_number">
        </div>
        <div class="mb-3">
            <label for="opening_time" class="form-label">Opening Time</label>
            <input type="time" class="form-control" id="opening_time" name="opening_time">
        </div>
        <div class="mb-3">
            <label for="closing_time" class="form-label">Closing Time</label>
            <input type="time" class="form-control" id="closing_time" name="closing_time">
        </div>
        <div class="mb-3">
            <label for="regular_holidays" class="form-label">Regular Holidays (comma separated)</label>
            <input type="text" class="form-control" id="regular_holidays" name="regular_holidays">
        </div>
        <div class="mb-3">
            <label for="reservation_acceptance_settings" class="form-label">Reservation Acceptance Settings (JSON)</label>
            <textarea class="form-control" id="reservation_acceptance_settings" name="reservation_acceptance_settings"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
</div>
@endsection