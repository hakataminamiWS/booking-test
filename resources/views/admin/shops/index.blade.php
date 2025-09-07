@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Admin Shops Index</h1>
    <a href="{{ route('admin.shops.create') }}" class="btn btn-primary">Create New Shop</a>
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($shops as $shop)
            <tr>
                <td>{{ $shop->id }}</td>
                <td>{{ $shop->name }}</td>
                <td>
                    @if($shop->trashed())
                        <span class="badge bg-danger">Deleted</span>
                    @else
                        <span class="badge bg-success">Active</span>
                    @endif
                </td>
                <td>
                    <a href="{{ route('admin.shops.show', $shop->id) }}" class="btn btn-info">View</a>
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
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection