<?php
declare(strict_types=1);

/** Keep SQL NOW() and the existing PHP-authored local DATETIME values aligned. */
function msw_sync_database_clock(mysqli $db): void {
    // Numeric offsets also work on stock XAMPP without MySQL timezone tables.
    // Long-lived workers call this again each cycle to pick up DST changes.
    $offset=date('P');
    $stmt=$db->prepare('SET SESSION time_zone=?');
    $stmt->bind_param('s',$offset);
    $stmt->execute();
    $stmt->close();
}
