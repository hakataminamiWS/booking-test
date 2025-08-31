@extends('app')

@section('title', 'オーナー一覧')

@section('content')
<div class="container">
    <div
        id="app"
        data-page="admin-owners-index"
        {{-- data-api-url="{{ route('api.admin.owners.index') }}" --}}
    >
    </div>
</div>
@endsection