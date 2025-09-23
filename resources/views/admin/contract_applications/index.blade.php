@extends('app')

@section('title', '契約申し込み一覧')

@section('content')
    <div
      id="app"
      data-page="admin-contract-applications-index"
      data-props="{{ json_encode([]) }}"
    ></div>
@endsection
