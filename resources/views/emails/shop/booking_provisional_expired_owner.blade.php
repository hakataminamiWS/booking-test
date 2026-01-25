@php
    $shop = $booking->shop;
@endphp
オーナー 様

以下の仮予約が承認期限切れのため、自動キャンセルされました。
--------------------------------------------------
店舗: {{ $shop->name }}
日時: {{ $booking->start_at->setTimezone($booking->timezone)->format('Y年m月d日 H:i') }} 〜 {{ $booking->end_at->setTimezone($booking->timezone)->format('H:i') }}
メニュー: {{ $booking->menu_name }}
金額: ¥{{ number_format($booking->menu_price) }}
担当者: {{ $booking->assigned_staff_name ?? '指名なし' }}
予約者: {{ $booking->booker_name }} 様
メールアドレス: {{ $booking->contact_email }}
電話番号: {{ $booking->contact_phone }}
--------------------------------------------------

@if ($booking->note_from_booker)
[お客様からのメモ]
{{ $booking->note_from_booker }}

@endif
管理画面で確認してください。

▼ 店舗ダッシュボードはこちら ▼
{{ route('owner.shops.dashboard', $shop) }}
