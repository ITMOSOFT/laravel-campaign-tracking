# ITMOSOFT Laravel Campaign Tracking

UTM attribution, campaign touchpoint capture, conversion recording, and first/last-touch summaries for Laravel 12 and 13.

## Requirements

- PHP 8.3+
- Laravel 12 or 13

## Installation

```bash
composer require itmosoft/laravel-campaign-tracking
php artisan vendor:publish --tag=itmosoft-campaign-tracking-config
php artisan migrate
```

## Quick start

```php
use ItmoSoft\CampaignTracking\Services\CampaignTrackingManager;

$tracking = app(CampaignTrackingManager::class);

$touch = $tracking->recordTouch([
    'campaign' => 'spring-sale',
    'source' => 'newsletter',
    'medium' => 'email',
    'visitor_id' => 'visitor-123',
    'session_id' => 'session-456',
    'landing_url' => 'https://example.com/sale',
]);

$tracking->recordConversion([
    'touch_id' => $touch->id,
    'event' => 'purchase',
    'external_id' => 'order-1001',
    'idempotency_key' => 'order-1001-purchase',
    'value' => 125.50,
    'currency' => 'USD',
]);

$summary = $tracking->summary('spring-sale', 'last_touch');
```

## Captured fields

Touchpoints can store campaign, source, medium, term, content, landing URL, visitor/session identifiers, timestamps, and application-defined JSON metadata.

Conversions can store an event name, optional touchpoint, external identifier, optional idempotency key, value, currency, timestamp, and metadata.

## Attribution

`summary($campaign, 'first_touch')` or `summary($campaign, 'last_touch')` selects one touchpoint per visitor among touchpoints recorded for that campaign and attributes conversions attached to those selected touchpoints.

This is deliberately a simple campaign-scoped visitor attribution summary, not a cross-campaign multi-touch attribution model. Anonymous touchpoints without `visitor_id` count as touches but are not included in attributed visitor counts. Conversions without a `touch_id` are not attributed by this summary.

## Configuration

`config/campaign-tracking.php` supports table prefixes, table names, model overrides, and a maximum metadata payload size.

## Production considerations

- Generate opaque visitor/session identifiers in your application; do not store raw IP addresses or unnecessary personal data by default.
- Apply consent and privacy controls before writing tracking cookies or identifiers.
- Validate conversion events server-side. Do not trust browser-submitted order values.
- Use a stable idempotency key for conversion events that may be retried.
- Add tenant scoping and authorization in your application; this package does not isolate tenants automatically.
- The package does not parse request URLs, set cookies, inject JavaScript, or provide a dashboard.

## Testing

```bash
composer install
composer test
```

## Scope boundaries

This package owns touchpoint and conversion persistence plus basic first/last-touch summaries. It does not provide campaign sending, email delivery, marketing automation, analytics dashboards, ad-platform integrations, or cross-campaign attribution models.

## License

MIT.
