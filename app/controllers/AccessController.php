<?php

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Schedule.php';
require_once __DIR__ . '/../models/AccessLog.php';

class AccessController
{
    /**
     * Implements the RFID tap decision flow from
     * docs/04-access-control-flow.md. Returns ['access' => 'granted'|'denied', 'reason' => string].
     */
    public static function handleScan(string $rfidUid): array
    {
        $user = User::findByRfidUid($rfidUid);

        if (!$user) {
            AccessLog::record(null, $rfidUid, 'denied', 'unknown_uid');

            return ['access' => 'denied', 'reason' => 'unknown_uid'];
        }

        if ($user['status'] !== 'active') {
            AccessLog::record((int) $user['id'], $rfidUid, 'denied', 'inactive_user');

            return ['access' => 'denied', 'reason' => 'inactive_user'];
        }

        $today = date('D');
        $now = date('H:i:s');

        $schedules = Schedule::findByUserId((int) $user['id']);
        $withinWindow = false;

        foreach ($schedules as $schedule) {
            if (!$schedule['is_active'] || $schedule['day_of_week'] !== $today) {
                continue;
            }

            if ($now >= $schedule['time_start'] && $now <= $schedule['time_end']) {
                $withinWindow = true;
                break;
            }
        }

        if (!$withinWindow) {
            AccessLog::record((int) $user['id'], $rfidUid, 'denied', 'outside_schedule');

            return ['access' => 'denied', 'reason' => 'outside_schedule'];
        }

        AccessLog::record((int) $user['id'], $rfidUid, 'granted', 'ok');

        return ['access' => 'granted', 'reason' => 'ok'];
    }
}
