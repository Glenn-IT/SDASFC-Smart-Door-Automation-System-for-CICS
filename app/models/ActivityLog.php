<?php

require_once __DIR__ . '/../core/Database.php';

class ActivityLog
{
    public static function record(?int $adminId, string $action, string $description): void
    {
        $stmt = Database::getConnection()->prepare(
            'INSERT INTO activity_logs (admin_id, action, description) VALUES (?, ?, ?)'
        );
        $stmt->execute([$adminId, $action, $description]);
    }

    public static function recent(int $limit = 20): array
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT al.*, a.full_name FROM activity_logs al
             LEFT JOIN admins a ON a.id = al.admin_id
             ORDER BY al.created_at DESC LIMIT ?'
        );
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Filters: date_from, date_to (Y-m-d). Result/user_id filters used on the
     * access-log view don't map onto activity rows, so they're not applied here.
     */
    public static function filtered(array $filters): array
    {
        [$where, $params] = self::buildWhere($filters);

        $stmt = Database::getConnection()->prepare(
            "SELECT al.*, a.full_name FROM activity_logs al
             LEFT JOIN admins a ON a.id = al.admin_id
             $where
             ORDER BY al.created_at DESC"
        );
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    private static function buildWhere(array $filters): array
    {
        $clauses = [];
        $params = [];

        if (!empty($filters['date_from'])) {
            $clauses[] = 'DATE(al.created_at) >= ?';
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $clauses[] = 'DATE(al.created_at) <= ?';
            $params[] = $filters['date_to'];
        }

        $where = $clauses ? 'WHERE ' . implode(' AND ', $clauses) : '';

        return [$where, $params];
    }
}
