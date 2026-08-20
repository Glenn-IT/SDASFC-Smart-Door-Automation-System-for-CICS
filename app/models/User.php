<?php

require_once __DIR__ . '/../core/Database.php';

class User
{
    public static function all(string $sort = 'a-z', string $role = 'all', string $status = 'all', string $search = ''): array
    {
        $sql = 'SELECT * FROM users WHERE 1=1';
        $params = [];

        if ($role !== 'all' && in_array($role, ['student', 'faculty', 'staff'], true)) {
            $sql .= ' AND role = ?';
            $params[] = $role;
        }

        if ($status !== 'all' && in_array($status, ['active', 'inactive'], true)) {
            $sql .= ' AND status = ?';
            $params[] = $status;
        }

        if (trim($search) !== '') {
            $sql .= ' AND (full_name LIKE ? OR id_number LIKE ? OR rfid_uid LIKE ?)';
            $searchTerm = '%' . trim($search) . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        if ($sort === 'z-a') {
            $sql .= ' ORDER BY full_name DESC';
        } else {
            $sql .= ' ORDER BY full_name ASC';
        }

        $stmt = Database::getConnection()->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public static function count(): int
    {
        return (int) Database::getConnection()->query('SELECT COUNT(*) FROM users')->fetchColumn();
    }

    public static function countByRole(): array
    {
        $stmt = Database::getConnection()->query('SELECT role, COUNT(*) AS c FROM users GROUP BY role');

        $counts = ['student' => 0, 'faculty' => 0, 'staff' => 0];
        foreach ($stmt->fetchAll() as $row) {
            $counts[$row['role']] = (int) $row['c'];
        }

        return $counts;
    }

    public static function findById(int $id): ?array
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT * FROM users WHERE id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    public static function findByFullName(string $fullName, ?int $excludeId = null): ?array
    {
        $sql = 'SELECT * FROM users WHERE LOWER(TRIM(full_name)) = LOWER(TRIM(?))';
        $params = [trim($fullName)];

        if ($excludeId !== null) {
            $sql .= ' AND id != ?';
            $params[] = $excludeId;
        }

        $stmt = Database::getConnection()->prepare($sql . ' LIMIT 1');
        $stmt->execute($params);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    public static function findByIdNumber(string $idNumber, ?int $excludeId = null): ?array
    {
        $sql = 'SELECT * FROM users WHERE LOWER(TRIM(id_number)) = LOWER(TRIM(?))';
        $params = [trim($idNumber)];

        if ($excludeId !== null) {
            $sql .= ' AND id != ?';
            $params[] = $excludeId;
        }

        $stmt = Database::getConnection()->prepare($sql . ' LIMIT 1');
        $stmt->execute($params);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    public static function findByRfidUid(string $rfidUid, ?int $excludeId = null): ?array
    {
        $sql = "SELECT * FROM users WHERE REPLACE(LOWER(TRIM(rfid_uid)), ' ', '') = REPLACE(LOWER(TRIM(?)), ' ', '')";
        $params = [trim($rfidUid)];

        if ($excludeId !== null) {
            $sql .= ' AND id != ?';
            $params[] = $excludeId;
        }

        $stmt = Database::getConnection()->prepare($sql . ' LIMIT 1');
        $stmt->execute($params);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    public static function create(string $fullName, string $idNumber, string $rfidUid, string $role): int
    {
        $stmt = Database::getConnection()->prepare(
            'INSERT INTO users (full_name, id_number, rfid_uid, role) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$fullName, $idNumber, $rfidUid, $role]);

        return (int) Database::getConnection()->lastInsertId();
    }

    public static function update(int $id, string $fullName, string $idNumber, string $rfidUid, string $role): void
    {
        $stmt = Database::getConnection()->prepare(
            'UPDATE users SET full_name = ?, id_number = ?, rfid_uid = ?, role = ? WHERE id = ?'
        );
        $stmt->execute([$fullName, $idNumber, $rfidUid, $role, $id]);
    }

    public static function setStatus(int $id, string $status): void
    {
        $stmt = Database::getConnection()->prepare(
            'UPDATE users SET status = ? WHERE id = ?'
        );
        $stmt->execute([$status, $id]);
    }

    public static function delete(int $id): void
    {
        $stmt = Database::getConnection()->prepare('DELETE FROM users WHERE id = ?');
        $stmt->execute([$id]);
    }
}
