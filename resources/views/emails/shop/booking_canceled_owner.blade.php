@php
    $shop = $booking->shop;
@endphp
オーナー 様

以下の予約がキャンセルされました。
--------------------------------------------------
店舗: {{ $shop->name }}
日時: {{ $booking->start_at->format('Y年m月d日 H:i') }}
メニュー: {{ $booking->menu_name }}
金額: ¥{{ number_format($booking->menu_price) }}
予約者: {{ $booking->booker_name }} 様
--------------------------------------------------

管理画面で確認してください。
