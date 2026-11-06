<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Retention of the activity log
    |--------------------------------------------------------------------------
    |
    | activity:prune deletes the annotations older than retention_years, the
    | failed logins older than failed_login_retention_days, empties the IP and
    | the user agent after network_data_retention_days and deletes the read
    | notifications older than read_notification_retention_days.
    |
    */

    'retention_years' => (int) env('ACTIVITY_RETENTION_YEARS', 5),

    'failed_login_retention_days' => (int) env('ACTIVITY_FAILED_LOGIN_RETENTION_DAYS', 365),

    'network_data_retention_days' => (int) env('ACTIVITY_NETWORK_DATA_RETENTION_DAYS', 365),

    'read_notification_retention_days' => (int) env('ACTIVITY_READ_NOTIFICATION_RETENTION_DAYS', 180),

    'page_size' => 10,

];
