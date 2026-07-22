<?php

require_once __DIR__ . '/../core/Database.php';

class AccessLog
{
    public static function record(?int $userId, string $rfidUid, string $result, string $reason): void
    {
        $stmt = Database::getConnection()->prepare(
            'INSERT INTO access_logs (user_id, rfid_uid, result, reason) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$userId, $rfidUid, $result, $reason]);
    }

    public static function recent(int $limit = 20): array
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT al.*, u.full_name FROM access_logs al
             LEFT JOIN users u ON u.id = al.user_id
             ORDER BY al.scanned_at DESC LIMIT ?'
        );
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Filters: date_from, date_to (Y-m-d), result ('granted'|'denied'), user_id.
     */
    public static function filtered(array $filters): array
    {
        [$where, $params] = self::buildWhere($filters);

        $stmt = Database::getConnection()->prepare(
            "SELECT al.*, u.full_name FROM access_logs al
             LEFT JOIN users u ON u.id = al.user_id
             $where
             ORDER BY al.scanned_at DESC"
        );
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public static function todayStats(): array
    {
        $stmt = Database::getConnection()->query(
            "SELECT
                COUNT(*) AS taps,
                SUM(result = 'granted') AS granted,
                SUM(result = 'denied') AS denied
             FROM access_logs
             WHERE DATE(scanned_at) = CURDATE()"
        );
        $row = $stmt->fetch();

        return [
            'taps' => (int) ($row['taps'] ?? 0),
            'granted' => (int) ($row['granted'] ?? 0),
            'denied' => (int) ($row['denied'] ?? 0),
        ];
    }

    public static function dailyCounts(int $days = 7): array
    {
        $stmt = Database::getConnection()->prepare(
            "SELECT DATE(scanned_at) AS d, COUNT(*) AS c
             FROM access_logs
             WHERE scanned_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
             GROUP BY d"
        );
        $stmt->bindValue(1, $days - 1, PDO::PARAM_INT);
        $stmt->execute();

        $counts = [];
        foreach ($stmt->fetchAll() as $row) {
            $counts[$row['d']] = (int) $row['c'];
        }

        $series = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $series[] = [
                'label' => date('M j', strtotime($date)),
                'count' => $counts[$date] ?? 0,
            ];
        }

        return $series;
    }

    private static function buildWhere(array $filters): array
    {
        $clauses = [];
        $params = [];

        if (!empty($filters['date_from'])) {
            $clauses[] = 'DATE(al.scanned_at) >= ?';
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $clauses[] = 'DATE(al.scanned_at) <= ?';
            $params[] = $filters['date_to'];
        }

        if (!empty($filters['result'])) {
            $clauses[] = 'al.result = ?';
            $params[] = $filters['result'];
        }

        if (!empty($filters['user_id'])) {
            $clauses[] = 'al.user_id = ?';
            $params[] = $filters['user_id'];
        }

        $where = $clauses ? 'WHERE ' . implode(' AND ', $clauses) : '';

        return [$where, $params];
    }
}
