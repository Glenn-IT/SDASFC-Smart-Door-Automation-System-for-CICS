<?php

require_once __DIR__ . '/../models/Schedule.php';

class ScheduleController
{
    private const DAYS = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

    /**
     * Returns an error string, or null on success.
     */
    public static function create(int $userId, string $dayOfWeek, string $timeStart, string $timeEnd): ?string
    {
        $error = self::validate($userId, $dayOfWeek, $timeStart, $timeEnd, null);

        if ($error !== null) {
            return $error;
        }

        Schedule::create($userId, $dayOfWeek, $timeStart, $timeEnd);

        return null;
    }

    /**
     * Returns an error string, or null on success.
     */
    public static function update(int $id, int $userId, string $dayOfWeek, string $timeStart, string $timeEnd): ?string
    {
        if (!Schedule::findById($id)) {
            return 'Schedule not found.';
        }

        $error = self::validate($userId, $dayOfWeek, $timeStart, $timeEnd, $id);

        if ($error !== null) {
            return $error;
        }

        Schedule::update($id, $dayOfWeek, $timeStart, $timeEnd);

        return null;
    }

    public static function toggleActive(int $id): void
    {
        $schedule = Schedule::findById($id);

        if (!$schedule) {
            return;
        }

        Schedule::setActive($id, !$schedule['is_active']);
    }

    public static function delete(int $id): void
    {
        Schedule::delete($id);
    }

    private static function validate(
        int $userId,
        string $dayOfWeek,
        string $timeStart,
        string $timeEnd,
        ?int $excludeId
    ): ?string {
        if (!in_array($dayOfWeek, self::DAYS, true)) {
            return 'Invalid day selected.';
        }

        if ($timeStart === '' || $timeEnd === '') {
            return 'Start and end time are required.';
        }

        if ($timeStart >= $timeEnd) {
            return 'Start time must be before end time.';
        }

        if (Schedule::findOverlap($userId, $dayOfWeek, $timeStart, $timeEnd, $excludeId)) {
            return 'This overlaps with an existing schedule for that day.';
        }

        return null;
    }
}
