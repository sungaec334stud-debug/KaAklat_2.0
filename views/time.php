<?php
declare(strict_types=1);

// Display only. Circulation deadline and due-date calculations stay in services.
function display_manila_time(string $utc, string $format = 'M j, Y g:i a'): string
{
    return (new DateTimeImmutable($utc, new DateTimeZone('UTC')))
        ->setTimezone(new DateTimeZone('Asia/Manila'))->format($format);
}
