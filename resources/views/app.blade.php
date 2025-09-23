<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>@yield('title', 'デフォルトタイトル')</title>

    @vite(['resources/css/app.css', 'resources/js/app.ts'])
</head>

<body>
    @if (session('success'))
        <div
            style="background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; padding: 10px; margin-bottom: 10px;">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div
            style="background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; padding: 10px; margin-bottom: 10px;">
            {{ session('error') }}
        </div>
    @endif
    @if (session('status'))
        <div
            style="background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; padding: 10px; margin-bottom: 10px;">
            {{ session('status') }}
        </div>
    @endif
    @yield('content')
    @stack('scripts')
</body>

</html>
