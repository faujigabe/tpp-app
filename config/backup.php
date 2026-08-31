<?php

return [
    'local_path' => env('BACKUP_LOCAL_PATH', storage_path('app/backups/daily')),
    'weekly_path' => env('BACKUP_WEEKLY_PATH'),
    'mysqldump_binary' => env('MYSQLDUMP_BINARY', 'mysqldump'),
    'mysql_binary' => env('MYSQL_BINARY', 'mysql'),
    'local_retention_days' => (int) env('BACKUP_LOCAL_RETENTION_DAYS', 14),
    'weekly_retention_days' => (int) env('BACKUP_WEEKLY_RETENTION_DAYS', 365),
    'audit_retention_years' => (int) env('AUDIT_RETENTION_YEARS', 5),
    'daily_health_max_age_hours' => (int) env('BACKUP_DAILY_MAX_AGE_HOURS', 26),
    'weekly_health_max_age_hours' => (int) env('BACKUP_WEEKLY_MAX_AGE_HOURS', 192),
];
