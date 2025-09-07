<!DOCTYPE html>
<html>
<head>
    <title>ご予約が確定しました</title>
</head>
<body>
    <h1>ご予約ありがとうございます</h1>
    <p>{{ $booking->name }}様</p>
    <p>以下の内容でご予約を承りました。</p>
    <ul>
        <li>店舗: {{ $booking->shop->name }}</li>
        <li>日時: {{ $booking->start_at }}</li>
        <li>メニュー: {{ $booking->menu->name }} ({{ $booking->menu->duration }}分)</li>
        @if($booking->staff)
            <li>担当スタッフ: {{ $booking->staff->name }}</li>
        @endif
    </ul>
    <p>ご来店を心よりお待ちしております。</p>
</body>
</html>
