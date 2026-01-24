@php
    $shop = $booking->shop;
@endphp
{{ $booking->booker_name }} 様

以下の予約をキャンセルしました。
--------------------------------------------------
店舗: {{ $shop->name }}
日時: {{ $booking->start_at->format('Y年m月d日 H:i') }}
メニュー: {{ $booking->menu_name }}
--------------------------------------------------

またのご来店を心よりお待ちしております。
