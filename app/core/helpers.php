<?php

/**
 * Formats a MySQL DATETIME/date string (or null) as e.g. "Jul 31, 2026 2:35 PM".
 */
function formatDateTime(?string $value): string
{
    if (!$value) {
        return '—';
    }

    $timestamp = strtotime($value);

    if ($timestamp === false) {
        return '—';
    }

    return date('M j, Y g:i A', $timestamp);
}
