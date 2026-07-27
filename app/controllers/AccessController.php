<?php

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/AccessLog.php';

class AccessController
{
    /**
     * Implements the RFID tap decision flow from docs/04-access-control-flow.md:
     * an unknown UID or a non-active user is denied, any other tap is granted.
     * Returns ['access' => 'granted'|'denied', 'reason' => string].
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

        AccessLog::record((int) $user['id'], $rfidUid, 'granted', 'ok');

        return ['access' => 'granted', 'reason' => 'ok'];
    }
}
