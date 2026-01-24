@php
    $shop = $booking->shop;
@endphp
{{ $booking->booker_name }} 様

以下の内容で仮予約を受け付けました。
店舗からの確定連絡をお待ちください。
--------------------------------------------------
店舗: {{ $shop->name }}
日時: {{ $booking->start_at->format('Y年m月d日 H:i') }}
メニュー: {{ $booking->menu_name }}
金額: ¥{{ number_format($booking->menu_price) }}
--------------------------------------------------

※この時点ではまだ予約は確定しておりません。
