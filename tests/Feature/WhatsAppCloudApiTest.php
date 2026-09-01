<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\QuoteRequest;
use App\Models\WhatsAppNotificationLog;
use App\Services\WhatsAppCloudApiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WhatsAppCloudApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_whatsapp_service_sends_template_using_graph_v25_and_phone_id()
    {
        Http::fake([
            'https://graph.facebook.com/v25.0/1349924141534825/messages' => Http::response([
                'messaging_product' => 'whatsapp',
                'contacts' => [['input' => '447852502775', 'wa_id' => '447852502775']],
                'messages' => [['id' => 'wamid.HBgMNDQ3ODUyNTAyNzc1FQIAERgSQjE2ODg0OTIxMzA1NUY5MkU5AA==']],
            ], 200),
        ]);

        $service = new WhatsAppCloudApiService();
        $result = $service->sendTemplate(
            '447852502775',
            'new_inquiry_admin_alert',
            ['Sarah Mitchell', '+447123456789', 'sarah@test.com', 'Business Enquiry', 'Test Message'],
            'en'
        );

        $this->assertTrue($result['success']);
        $this->assertEquals('wamid.HBgMNDQ3ODUyNTAyNzc1FQIAERgSQjE2ODg0OTIxMzA1NUY5MkU5AA==', $result['message_id']);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://graph.facebook.com/v25.0/1349924141534825/messages'
                && $request->hasHeader('Authorization', 'Bearer EAAVToOfKKlEBSSJxC6uZBAHUMHfvEl9k55w6ISHF0CZAkmpaDLJwBTYYTtJxLivv92QYgrom89NiJpjF6zwM17LqpN8rvv3XOPlrmnM11n0ypcceySvf0Hwigjar31w2aqS6n9hqHR6Q0TUug9LcsQ7Skx0sEK5PxqqUjWjv0fvD6os4N6VvRkAqExHQZDZD')
                && $request['template']['name'] === 'new_inquiry_admin_alert'
                && $request['to'] === '447852502775';
        });
    }

    public function test_whatsapp_service_respects_disabled_feature_toggle()
    {
        Config::set('whatsapp.enabled', false);

        Http::fake();

        $service = new WhatsAppCloudApiService();
        $result = $service->sendTemplate(
            '447852502775',
            'new_inquiry_admin_alert',
            ['Sarah', '07123', 's@t.com', 'Sub', 'Msg']
        );

        $this->assertFalse($result['success']);
        Http::assertNothingSent();
    }

    public function test_idempotency_log_records_successful_meta_message_id()
    {
        Http::fake([
            '*' => Http::response([
                'messages' => [['id' => 'wamid.TEST12345']],
            ], 200),
        ]);

        $log = WhatsAppNotificationLog::create([
            'notification_type' => 'new_inquiry',
            'source_record_id'  => 99,
            'recipient'         => '447852502775',
            'template_name'     => 'new_inquiry_admin_alert',
            'status'            => 'pending',
            'attempt_count'     => 1,
        ]);

        $service = new WhatsAppCloudApiService();
        $res = $service->sendTemplate('447852502775', 'new_inquiry_admin_alert', ['a', 'b', 'c', 'd', 'e']);

        if ($res['success']) {
            $log->update([
                'status' => 'sent',
                'meta_message_id' => $res['message_id'],
                'sent_at' => now(),
            ]);
        }

        $this->assertDatabaseHas('whatsapp_notification_logs', [
            'notification_type' => 'new_inquiry',
            'source_record_id'  => 99,
            'status'            => 'sent',
            'meta_message_id'   => 'wamid.TEST12345',
        ]);
    }
}
