<?php

return [

    /*
    |--------------------------------------------------------------------------
    | CRON settings
    |--------------------------------------------------------------------------
    |
    | The values here indicate whether the cron is enabled
    |
    |
    */

    'close:epochs' => env('CRON_EPOCH_CLOSE'),
    'check:enotifications' => env('CRON_EPOCH_NOTIF'),
    'daily:update' => env('CRON_DAILY_UPDATE'),
    'check:nominees' => env('CRON_NOMINEE_ENABLED'),
];
