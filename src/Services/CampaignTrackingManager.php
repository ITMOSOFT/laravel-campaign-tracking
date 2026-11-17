<?php

namespace ItmoSoft\CampaignTracking\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use ItmoSoft\CampaignTracking\Exceptions\InvalidTrackingDataException;
use ItmoSoft\CampaignTracking\Models\CampaignConversion;
use ItmoSoft\CampaignTracking\Models\CampaignTouch;
use JsonException;

class CampaignTrackingManager
{
    public function recordTouch(array $data): CampaignTouch
    {
        Validator::make($data, [
            'campaign' => ['nullable', 'string', 'max:255'],
            'source' => ['nullable', 'string', 'max:255'],
            'medium' => ['nullable', 'string', 'max:255'],
            'term' => ['nullable', 'string', 'max:255'],
            'content' => ['nullable', 'string', 'max:255'],
            'landing_url' => ['nullable', 'string', 'max:2048'],
            'visitor_id' => ['nullable', 'string', 'max:191'],
            'session_id' => ['nullable', 'string', 'max:191'],
        ])->validate();

        $metadata = $data['metadata'] ?? [];
        $this->validateMetadata($metadata);

        return $this->touchModel()::query()->create([
            'campaign' => $data['campaign'] ?? null,
            'source' => $data['source'] ?? null,
            'medium' => $data['medium'] ?? null,
            'term' => $data['term'] ?? null,
            'content' => $data['content'] ?? null,
            'landing_url' => $data['landing_url'] ?? null,
            'visitor_id' => $data['visitor_id'] ?? null,
            'session_id' => $data['session_id'] ?? null,
            'metadata' => $metadata,
            'touched_at' => $data['touched_at'] ?? now(),
        ]);
    }

    public function recordConversion(array $data): CampaignConversion
    {
        Validator::make($data, [
            'touch_id' => ['nullable', 'integer'],
            'event' => ['required', 'string', 'max:100'],
            'external_id' => ['nullable', 'string', 'max:191'],
            'value' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
        ])->validate();

        $metadata = $data['metadata'] ?? [];
        $this->validateMetadata($metadata);

        $attributes = [
            'touch_id' => $data['touch_id'] ?? null,
            'event' => $data['event'],
            'external_id' => $data['external_id'] ?? null,
            'value' => $data['value'] ?? null,
            'currency' => isset($data['currency']) ? strtoupper($data['currency']) : null,
            'metadata' => $metadata,
            'converted_at' => $data['converted_at'] ?? now(),
        ];

        if (! empty($data['idempotency_key'])) {
            Validator::make($data, ['idempotency_key' => ['string', 'max:191']])->validate();

            return DB::transaction(function () use ($data, $attributes): CampaignConversion {
                return $this->conversionModel()::query()->firstOrCreate(
                    ['idempotency_key' => $data['idempotency_key']],
                    $attributes
                );
            });
        }

        return $this->conversionModel()::query()->create($attributes);
    }

    /**
     * Summarize touch and conversion counts and values for a campaign name.
     * Attribution modes are first_touch and last_touch at visitor level.
     */
    public function summary(string $campaign, string $mode = 'last_touch'): array
    {
        if (! in_array($mode, ['first_touch', 'last_touch'], true)) {
            throw new InvalidTrackingDataException('Attribution mode must be first_touch or last_touch.');
        }

        $touches = $this->touchModel()::query()
            ->where('campaign', $campaign)
            ->orderBy('touched_at')
            ->get();

        $visitorIds = $touches->pluck('visitor_id')->filter()->unique()->values();
        $selectedTouchIds = [];

        foreach ($visitorIds as $visitorId) {
            $visitorTouches = $touches->where('visitor_id', $visitorId);
            $selected = $mode === 'first_touch' ? $visitorTouches->first() : $visitorTouches->last();
            if ($selected) {
                $selectedTouchIds[] = $selected->getKey();
            }
        }

        $conversions = $this->conversionModel()::query()
            ->whereIn('touch_id', $selectedTouchIds)
            ->get();

        return [
            'campaign' => $campaign,
            'attribution' => $mode,
            'touches' => $touches->count(),
            'attributed_visitors' => count($selectedTouchIds),
            'conversions' => $conversions->count(),
            'conversion_value' => (float) $conversions->sum(fn ($conversion) => (float) $conversion->value),
        ];
    }

    protected function validateMetadata(mixed $metadata): void
    {
        if (! is_array($metadata)) {
            throw new InvalidTrackingDataException('Tracking metadata must be an array.');
        }

        try {
            $encoded = json_encode($metadata, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new InvalidTrackingDataException('Tracking metadata must be JSON serializable.', previous: $exception);
        }

        $max = (int) config('itmosoft-campaign-tracking.max_metadata_bytes', 16384);
        if (strlen($encoded) > $max) {
            throw new InvalidTrackingDataException("Tracking metadata exceeds the configured {$max}-byte limit.");
        }
    }

    protected function touchModel(): string
    {
        return config('itmosoft-campaign-tracking.models.touch', CampaignTouch::class);
    }

    protected function conversionModel(): string
    {
        return config('itmosoft-campaign-tracking.models.conversion', CampaignConversion::class);
    }
}
