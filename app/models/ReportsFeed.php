<?php

require_once __DIR__ . '/AccessLog.php';
require_once __DIR__ . '/ActivityLog.php';

class ReportsFeed
{
    /**
     * Filters: date_from, date_to (Y-m-d), result ('granted'|'denied'), user_id.
     * When result or user_id is set, only access-log rows are returned since
     * those filters have no meaning for admin-activity rows.
     */
    public static function filtered(array $filters): array
    {
        $rows = array_map([self::class, 'fromAccessLog'], AccessLog::filtered($filters));

        if (empty($filters['result']) && empty($filters['user_id'])) {
            $activityLogs = ActivityLog::filtered([
                'date_from' => $filters['date_from'] ?? '',
                'date_to' => $filters['date_to'] ?? '',
            ]);
            $rows = array_merge($rows, array_map([self::class, 'fromActivityLog'], $activityLogs));
        }

        usort($rows, static fn (array $a, array $b) => strcmp($b['timestamp'], $a['timestamp']));

        return $rows;
    }

    private static function fromAccessLog(array $log): array
    {
        return [
            'type' => 'access',
            'timestamp' => $log['scanned_at'],
            'person' => $log['full_name'] ?? 'Unknown',
            'detail_primary' => $log['rfid_uid'],
            'result' => $log['result'],
            'detail_secondary' => $log['reason'],
        ];
    }

    private static function fromActivityLog(array $log): array
    {
        return [
            'type' => 'activity',
            'timestamp' => $log['created_at'],
            'person' => $log['full_name'] ?? 'System',
            'detail_primary' => $log['action'],
            'result' => null,
            'detail_secondary' => $log['description'],
        ];
    }
}
