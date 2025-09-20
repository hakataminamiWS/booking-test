@extends('app')

@section('title', 'オーナー権限の編集')

@section('content')
  @php
  $props = [
      'user' => $user,
      'isOwner' => $is_owner,
      'csrfToken' => csrf_token(),
  ];
  @endphp
  <div
    id="app"
    data-page="admin-users-edit"
    data-props="{{ json_encode($props) }}"
  ></div>
@endsection
