<?php

namespace ItmoSoft\CampaignTracking\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use ItmoSoft\CampaignTracking\Models\Concerns\UsesConfiguredTable;

class CampaignConversion extends Model
{
    use UsesConfiguredTable;

    protected $guarded = [];

    protected function tableConfigKey(): string { return 'conversions'; }

    protected function casts(): array
    {
        return ['metadata' => 'array', 'converted_at' => 'datetime', 'value' => 'decimal:4'];
    }

    public function touch(): BelongsTo
    {
        return $this->belongsTo(config('itmosoft-campaign-tracking.models.touch'), 'touch_id');
    }
}
