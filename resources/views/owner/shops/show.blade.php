@extends('layouts.app')

@section('content')
<div id="owner-shops-show" data-shop="{{ $shop->toJson() }}"></div>
@endsection