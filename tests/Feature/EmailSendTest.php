<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class EmailSendTest extends TestCase
{
    /**
     * SMTP設定が正しく動作するかをテストします。
     * 
     * 使用方法:
     * php artisan test --filter=test_smtp_email_sending
     */
    public function test_smtp_email_sending(): void
    {
        echo "\n=== Email Test Start ===\n";
        
        // ▼▼▼ テスト設定：ここを変更してください ▼▼▼
        $replyToEmail = 'yoyakuowner1.netget@gmail.com'; // 返信先アドレス
        $replyToName  = '予約システム返信先（オーナーへのReply-Toのテスト）';                 // 返信先名
        $toEmail      = 'hakataminami.web.service@gmail.com';     // 送信先アドレス
        // ▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲

        try {
            // 変数を使ってメール送信
            \Illuminate\Support\Facades\Mail::mailer('smtp')->raw('Reply-To Variable Test', function($m) use ($replyToEmail, $replyToName, $toEmail) {
                $m->to($toEmail)
                  ->replyTo($replyToEmail, $replyToName)
                  ->subject('Reply-To変数化テスト - ' . now()->format('H:i:s'));
            });

            $this->assertTrue(true);
            echo "✅ Sent command executed\n";
            echo "📧 To: {$toEmail}\n";
            echo "📧 Reply-To: {$replyToName} <{$replyToEmail}>\n";
            
        } catch (\Exception $e) {
            $this->fail('Error: ' . $e->getMessage());
        }
    }
}
