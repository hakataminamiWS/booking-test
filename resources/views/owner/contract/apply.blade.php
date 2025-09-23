@extends('app')

@section('content')
    <div
      id="app"
      data-page="owner-contract-apply"
      data-props="{{ json_encode($props) }}"
    ></div>
@endsection
