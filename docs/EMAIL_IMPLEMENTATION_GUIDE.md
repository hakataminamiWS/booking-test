# メール機能 実装・運用ガイド

本プロジェクトにおける予約システムメール機能の仕様、修正方法、およびテスト方法についてまとめます。

## 1. 実装されているメールの種類

現在、以下の4つのシチュエーション×2種類（予約者向け、オーナー向け）の計8種類のメールが実装されています。

| シチュエーション | クラス名 (Mailable) | 予約者向け View | オーナー向け View |
| :--- | :--- | :--- | :--- |
| **仮予約** | `BookingProvisionalMail` | `emails/shop/booking_provisional` | `_owner` |
| **予約確定** | `BookingConfirmedMail` | `emails/shop/booking_confirmed` | `_owner` |
| **キャンセル** | `BookingCanceledMail` | `emails/shop/booking_canceled` | `_owner` |
| **期限切れ** | `BookingProvisionalExpiredMail` | `emails/shop/booking_provisional_expired` | `_owner` |

※ ファイルパスはいずれも `resources/views/` からの相対パスです。

## 2. メールの文面修正方法

メールの件名や本文を変更したい場合は、上記の View ファイル (`.blade.php`) を編集してください。
ファイルは `resources/views/emails/shop/` ディレクトリに格納されています。

例：**予約確定メール（予約者向け）**の文面を変更する場合
=> `resources/views/emails/shop/booking_confirmed.blade.php` を編集

## 3. 送信テストの実行方法

自動テスト (`tests/Feature/EmailSendTest.php`) を利用して、実際にメール送信処理を実行・確認できます。

### 全てのメールをテストする場合
```bash
php artisan test tests/Feature/EmailSendTest.php
```

### 特定のメールのみテストする場合
`--filter` オプションを使用します。

**仮予約メールのみ:**
```bash
php artisan test --filter=test_send_provisional_mail
```

**予約確定メールのみ:**
```bash
php artisan test --filter=test_send_confirmed_mail
```

**キャンセルメールのみ:**
```bash
php artisan test --filter=test_send_canceled_mail
```

**期限切れメールのみ:**
```bash
php artisan test --filter=test_send_provisional_expired_mail
```

## 4. メール文面のプレビュー方法（送信なし）

メールを送信せずに、ブラウザ上で文面（HTML/テキスト）を確認する方法です。
`routes/web.php` に以下のような一時的な確認用ルートを追加することで、Mailableクラスのレンダリング結果を直接確認できます。

```php
// routes/web.php の末尾などに追加

use App\Models\User;
use App\Models\Shop;
use App\Models\Booking;
use App\Models\ShopBooker;
use App\Mail\Shop\BookingConfirmedMail;

Route::get('/preview-mail', function () {
    // プレビュー用のダミーデータ作成（DBには保存されません）
    // 注意: リレーションエラーが出る場合は create() を使うか、Factoryを工夫してください
    
    // 確実な方法: 一時的にDBにデータを作ってしまう（ローカル開発環境想定）
    $owner = User::factory()->create();
    $shop = Shop::create([
        'owner_user_id' => $owner->id,
        'name' => 'プレビュー美容室',
        'slug' => 'preview-shop-' . time(),
        'email' => 'shop@example.com',
        // その他必須項目 (time_slot_intervalなど) はデフォルト値を利用
    ]);
    
    $booker = ShopBooker::create([
        'shop_id' => $shop->id,
        'name' => 'プレビュー太郎',
        'contact_email' => 'booker@example.com',
    ]);

    $booking = Booking::create([
        'shop_id' => $shop->id,
        'shop_booker_id' => $booker->id,
        'status' => 'confirmed',
        'menu_name' => 'プレビューカット',
        'menu_price' => 5500,
        // その他必須項目
        'timezone' => 'Asia/Tokyo',
        'start_at' => now()->addDay(),
        'end_at' => now()->addDay()->addHour(),
        'booker_name' => $booker->name,
        'contact_email' => $booker->contact_email,
        'contact_phone' => '090-0000-0000',
    ]);

    // 確認したいメールクラスを返す
    // 第2引数を true にすればオーナー向け、false なら予約者向け
    return new BookingConfirmedMail($booking, forOwner: false);
});
```

ルート追加後、ブラウザで `http://localhost:8000/preview-mail` にアクセスすると、メールの内容が表示されます。
確認が終わったら、追加したルートは削除してください。
