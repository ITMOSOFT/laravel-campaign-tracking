<?php

namespace ItmoSoft\CampaignTracking\Tests\Feature;

use ItmoSoft\CampaignTracking\Exceptions\InvalidTrackingDataException;
use ItmoSoft\CampaignTracking\Services\CampaignTrackingManager;
use ItmoSoft\CampaignTracking\Tests\TestCase;

class CampaignTrackingManagerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
    }

    public function test_it_records_a_campaign_touch(): void
    {
        $touch = app(CampaignTrackingManager::class)->recordTouch([
            'campaign' => 'spring-sale',
            'source' => 'newsletter',
            'medium' => 'email',
            'visitor_id' => 'visitor-1',
            'metadata' => ['creative' => 'hero'],
        ]);

        $this->assertSame('spring-sale', $touch->campaign);
        $this->assertSame(['creative' => 'hero'], $touch->metadata);
    }

    public function test_it_records_a_conversion_with_currency(): void
    {
        $manager = app(CampaignTrackingManager::class);
        $touch = $manager->recordTouch(['campaign' => 'spring-sale', 'visitor_id' => 'visitor-1']);
        $conversion = $manager->recordConversion([
            'touch_id' => $touch->id,
            'event' => 'purchase',
            'value' => 125.50,
            'currency' => 'usd',
        ]);

        $this->assertSame('USD', $conversion->currency);
        $this->assertSame($touch->id, $conversion->touch_id);
    }

    public function test_it_is_idempotent_when_an_idempotency_key_is_supplied(): void
    {
        $manager = app(CampaignTrackingManager::class);
        $payload = ['event' => 'purchase', 'idempotency_key' => 'order-1001', 'value' => 20];
        $first = $manager->recordConversion($payload);
        $second = $manager->recordConversion($payload);

        $this->assertSame($first->id, $second->id);
        $this->assertSame(1, \ItmoSoft\CampaignTracking\Models\CampaignConversion::query()->count());
    }

    public function test_it_summarizes_last_touch_at_visitor_level(): void
    {
        $manager = app(CampaignTrackingManager::class);
        $first = $manager->recordTouch([
            'campaign' => 'spring-sale', 'source' => 'email', 'visitor_id' => 'visitor-1',
            'touched_at' => now()->subDay(),
        ]);
        $last = $manager->recordTouch([
            'campaign' => 'spring-sale', 'source' => 'social', 'visitor_id' => 'visitor-1',
            'touched_at' => now(),
        ]);
        $manager->recordConversion(['touch_id' => $first->id, 'event' => 'purchase', 'value' => 10]);
        $manager->recordConversion(['touch_id' => $last->id, 'event' => 'purchase', 'value' => 30]);

        $summary = $manager->summary('spring-sale', 'last_touch');

        $this->assertSame(2, $summary['touches']);
        $this->assertSame(1, $summary['attributed_visitors']);
        $this->assertSame(1, $summary['conversions']);
        $this->assertSame(30.0, $summary['conversion_value']);
    }

    public function test_it_rejects_an_unsupported_attribution_mode(): void
    {
        $this->expectException(InvalidTrackingDataException::class);
        app(CampaignTrackingManager::class)->summary('spring-sale', 'linear');
    }

    public function test_it_rejects_non_array_metadata(): void
    {
        $this->expectException(InvalidTrackingDataException::class);
        app(CampaignTrackingManager::class)->recordTouch(['metadata' => 'not-an-array']);
    }
}
