@extends('layouts.app')

@section('content')
    <div id="app"
         data-page="Owner/Bookings/Index"
         data-props='{
            "shop": {{ Js::from($shop) }},
            "menus": {{ Js::from($menus) }},
            "staffs": {{ Js::from($staffs) }}
         }'
    ></div>
@endsection