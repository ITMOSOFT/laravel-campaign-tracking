<?php

namespace ItmoSoft\CampaignTracking\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use ItmoSoft\CampaignTracking\Models\Concerns\UsesConfiguredTable;

class CampaignTouch extends Model
{
    use UsesConfiguredTable;

    protected $guarded = [];

    protected function tableConfigKey(): string { return 'touches'; }

    protected function casts(): array
    {
        return ['metadata' => 'array', 'touched_at' => 'datetime'];
    }

    public function conversions(): HasMany
    {
        return $this->hasMany(config('itmosoft-campaign-tracking.models.conversion'), 'touch_id');
    }
}
