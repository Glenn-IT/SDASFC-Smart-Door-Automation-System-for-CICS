<?php

require_once __DIR__ . '/../models/User.php';

class UserController
{
    private const ROLES = ['student', 'faculty', 'staff'];

    /**
     * Returns an error string, or null on success.
     */
    public static function create(string $fullName, string $idNumber, string $rfidUid, string $role): ?string
    {
        $error = self::validate($fullName, $idNumber, $rfidUid, $role, null);

        if ($error !== null) {
            return $error;
        }

        User::create($fullName, $idNumber, $rfidUid, $role);

        return null;
    }

    /**
     * Returns an error string, or null on success.
     */
    public static function update(int $id, string $fullName, string $idNumber, string $rfidUid, string $role): ?string
    {
        if (!User::findById($id)) {
            return 'User not found.';
        }

        $error = self::validate($fullName, $idNumber, $rfidUid, $role, $id);

        if ($error !== null) {
            return $error;
        }

        User::update($id, $fullName, $idNumber, $rfidUid, $role);

        return null;
    }

    public static function toggleStatus(int $id): void
    {
        $user = User::findById($id);

        if (!$user) {
            return;
        }

        User::setStatus($id, $user['status'] === 'active' ? 'inactive' : 'active');
    }

    public static function delete(int $id): void
    {
        User::delete($id);
    }

    private static function validate(
        string $fullName,
        string $idNumber,
        string $rfidUid,
        string $role,
        ?int $excludeId
    ): ?string {
        if ($fullName === '' || $idNumber === '' || $rfidUid === '') {
            return 'Full name, ID number, and RFID UID are required.';
        }

        if (!in_array($role, self::ROLES, true)) {
            return 'Invalid role selected.';
        }

        if (User::findByIdNumber($idNumber, $excludeId)) {
            return 'A user with that ID number already exists.';
        }

        if (User::findByRfidUid($rfidUid, $excludeId)) {
            return 'That RFID UID is already assigned to another user.';
        }

        return null;
    }
}
