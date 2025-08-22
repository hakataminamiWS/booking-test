<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>@yield('title', 'デフォルトタイトル')</title>

    @vite('resources/js/app.ts')
</head>

<body>
    @yield('content')
    @stack('scripts')
</body>

</html>
