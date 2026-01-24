@php
    $shop = $booking->shop;
@endphp
{{ $booking->booker_name }} 様

承認期限が切れたため、以下の仮予約をキャンセルいたしました。
ご不便をおかけして申し訳ございませんが、再度ご予約をお願いいたします。
--------------------------------------------------
店舗: {{ $shop->name }}
日時: {{ $booking->start_at->setTimezone($booking->timezone)->format('Y年m月d日 H:i') }} 〜 {{ $booking->end_at->setTimezone($booking->timezone)->format('H:i') }}
メニュー: {{ $booking->menu_name }}
担当者: {{ $booking->assigned_staff_name ?? '指名なし' }}
--------------------------------------------------

またのご利用を心よりお待ちしております。
