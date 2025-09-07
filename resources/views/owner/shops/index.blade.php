@extends('layouts.app')

@section('content')
<div id="owner-shops-index" data-shops="{{ json_encode($shops) }}"></div>
@endsection