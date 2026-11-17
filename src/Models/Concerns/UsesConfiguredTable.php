<?php

namespace ItmoSoft\CampaignTracking\Models\Concerns;

trait UsesConfiguredTable
{
    abstract protected function tableConfigKey(): string;

    public function getTable(): string
    {
        $key = $this->tableConfigKey();

        return config('itmosoft-campaign-tracking.table_prefix', 'campaign_tracking_')
            .config("itmosoft-campaign-tracking.tables.{$key}", $key);
    }
}
