@extends('app')

@php
$props['errors'] = $errors->all();
@endphp

@section('content')
    <div
      id="app"
      data-page="owner-contract-apply"
      data-props="{{ json_encode($props) }}"
    ></div>
@endsection
