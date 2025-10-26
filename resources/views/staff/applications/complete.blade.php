@extends('app')

@section('title', 'スタッフ登録申し込み完了')

@section('content')
    @php
        $props = [];
    @endphp
    <div id="app" data-page="StaffApplicationComplete" data-props='{{ json_encode($props) }}'></div>
@endsection
