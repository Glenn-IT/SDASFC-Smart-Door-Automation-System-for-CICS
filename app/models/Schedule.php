<?php

require_once __DIR__ . '/../core/Database.php';

class Schedule
{
    public static function countsByUser(): array
    {
        $stmt = Database::getConnection()->query(
            'SELECT user_id, COUNT(*) AS total FROM schedules GROUP BY user_id'
        );

        $counts = [];
        foreach ($stmt->fetchAll() as $row) {
            $counts[(int) $row['user_id']] = (int) $row['total'];
        }

        return $counts;
    }

    public static function findByUserId(int $userId): array
    {
        $stmt = Database::getConnection()->prepare(
            "SELECT * FROM schedules WHERE user_id = ?
             ORDER BY FIELD(day_of_week, 'Mon','Tue','Wed','Thu','Fri','Sat','Sun'), time_start"
        );
        $stmt->execute([$userId]);

        return $stmt->fetchAll();
    }

    public static function findById(int $id): ?array
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT * FROM schedules WHERE id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        $schedule = $stmt->fetch();

        return $schedule ?: null;
    }

    public static function create(int $userId, string $dayOfWeek, string $timeStart, string $timeEnd): int
    {
        $stmt = Database::getConnection()->prepare(
            'INSERT INTO schedules (user_id, day_of_week, time_start, time_end) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$userId, $dayOfWeek, $timeStart, $timeEnd]);

        return (int) Database::getConnection()->lastInsertId();
    }

    public static function update(int $id, string $dayOfWeek, string $timeStart, string $timeEnd): void
    {
        $stmt = Database::getConnection()->prepare(
            'UPDATE schedules SET day_of_week = ?, time_start = ?, time_end = ? WHERE id = ?'
        );
        $stmt->execute([$dayOfWeek, $timeStart, $timeEnd, $id]);
    }

    public static function setActive(int $id, bool $isActive): void
    {
        $stmt = Database::getConnection()->prepare(
            'UPDATE schedules SET is_active = ? WHERE id = ?'
        );
        $stmt->execute([$isActive ? 1 : 0, $id]);
    }

    public static function delete(int $id): void
    {
        $stmt = Database::getConnection()->prepare('DELETE FROM schedules WHERE id = ?');
        $stmt->execute([$id]);
    }

    /**
     * Finds another active schedule for the same user/day whose time
     * range overlaps [timeStart, timeEnd). Used to prevent overlapping
     * windows for the same user.
     */
    public static function findOverlap(
        int $userId,
        string $dayOfWeek,
        string $timeStart,
        string $timeEnd,
        ?int $excludeId
    ): ?array {
        $sql = 'SELECT * FROM schedules
                WHERE user_id = ? AND day_of_week = ?
                AND time_start < ? AND time_end > ?';
        $params = [$userId, $dayOfWeek, $timeEnd, $timeStart];

        if ($excludeId !== null) {
            $sql .= ' AND id != ?';
            $params[] = $excludeId;
        }

        $stmt = Database::getConnection()->prepare($sql . ' LIMIT 1');
        $stmt->execute($params);
        $schedule = $stmt->fetch();

        return $schedule ?: null;
    }
}
