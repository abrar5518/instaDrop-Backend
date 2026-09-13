<?php

namespace Tests\Feature;

use App\Jobs\SendQuoteNotifications;
use App\Models\QuoteRequest;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class QuoteCollectionScheduleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(ThrottleRequests::class);
        Bus::fake(); // Never send real emails or WhatsApp messages from tests.
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2030-03-30 12:00', 'Europe/London'));
    }

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();
        parent::tearDown();
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'first_name' => 'Schedule', 'last_name' => 'Test', 'email' => 'schedule-test@example.com',
            'phone' => '07123456789', 'contact_preference' => 'email', 'collection_postcode' => 'm1 1ae',
            'delivery_postcode' => 'SW1A 1AA', 'vehicle_type' => 'small_van', 'timescale' => 'scheduled_date',
            'collection_date' => '2030-03-31', 'collection_time' => '14:30', 'additional_info' => 'Automated test only',
        ], $overrides);
    }

    public function test_schedule_is_stored_and_visible_in_admin_without_enquiry_type(): void
    {
        $response = $this->postJson('/api/v1/quotes', $this->payload())->assertCreated();
        $quote = QuoteRequest::where('quote_number', $response->json('quote_number'))->firstOrFail();
        $this->assertSame('2030-03-31', $quote->collection_date);
        $this->assertSame('14:30', substr($quote->collection_time, 0, 5));
        $this->assertNull($quote->enquiry_type);
        $this->assertSame('31 Mar 2030 at 14:30 (UK time)', $quote->collection_schedule);
        $this->actingAs(User::factory()->create())->get('/admin/quotes/'.$quote->id)->assertOk()->assertSee('31 Mar 2030 at 14:30 (UK time)')->assertDontSee('Type of Enquiry');
        Bus::assertDispatchedAfterResponse(SendQuoteNotifications::class, fn ($job) => $job->quoteId === $quote->id);
    }

    public function test_missing_and_malformed_schedule_is_rejected(): void
    {
        $data = $this->payload();
        unset($data['collection_date'], $data['collection_time']);
        $this->postJson('/api/v1/quotes', $data)->assertUnprocessable()->assertJsonValidationErrors(['collection_date', 'collection_time']);
        $this->postJson('/api/v1/quotes', $this->payload(['collection_date' => '2030-02-30', 'collection_time' => '25:70']))->assertUnprocessable()->assertJsonValidationErrors(['collection_date', 'collection_time']);
        Bus::assertNothingDispatched();
    }

    public function test_past_time_and_nonexistent_uk_clock_change_time_are_rejected(): void
    {
        $this->postJson('/api/v1/quotes', $this->payload(['collection_date' => '2030-03-30', 'collection_time' => '11:30']))->assertUnprocessable()->assertJsonValidationErrors('collection_date');
        $this->postJson('/api/v1/quotes', $this->payload(['collection_date' => '2030-03-31', 'collection_time' => '01:30']))->assertUnprocessable()->assertJsonValidationErrors('collection_time');
        Bus::assertNothingDispatched();
    }

    public function test_older_requests_without_a_schedule_remain_readable(): void
    {
        $quote = new QuoteRequest(['first_name' => 'Old', 'last_name' => 'Request']);
        $this->assertSame('Not specified', $quote->collection_schedule);
    }
}
