<?php

return [
    'table_prefix' => 'campaign_tracking_',
    'tables' => [
        'touches' => 'touches',
        'conversions' => 'conversions',
    ],
    'models' => [
        'touch' => ItmoSoft\CampaignTracking\Models\CampaignTouch::class,
        'conversion' => ItmoSoft\CampaignTracking\Models\CampaignConversion::class,
    ],
    'max_metadata_bytes' => 16384,
];
