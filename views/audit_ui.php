<?php
declare(strict_types=1);

/** Labels supplement the original code; unknown codes must not imply an outcome. */
function audit_event_label(string $code): string
{
    return match ($code) {
        'student.activate' => 'Student account activated',
        'student.reactivate' => 'Student account reactivated',
        'student.suspend' => 'Student account suspended',
        'staff.created' => 'Staff account created',
        'staff.updated' => 'Staff account updated',
        'request.created' => 'Borrowing request submitted',
        'request.approved' => 'Borrowing request approved',
        'request.rejected' => 'Borrowing request rejected',
        'request.cancelled' => 'Pending request cancelled',
        'loan.collected' => 'Physical pickup recorded',
        'loan.returned' => 'Book return recorded',
        'loan.expired' => 'Pickup allocation expired',
        'loan.cancelled' => 'Uncollected approval cancelled',
        'account.registered' => 'Student account registered',
        'account.password_set' => 'Initial account password assigned',
        'student.correct_id' => 'Pending student ID claim corrected',
        'student.release_id' => 'Pending student ID claim released',
        'account.reset_issued' => 'Private recovery secret issued',
        'account.reset_redeemed' => 'Account recovery completed',
        'catalog.create' => 'Catalog title created',
        'catalog.edit' => 'Catalog details updated',
        'catalog.add_copies' => 'Physical copies added',
        'catalog.cover' => 'Book cover replaced',
        'catalog.archive_copy' => 'Physical copy archived',
        'catalog.archive_book' => 'Catalog title archived',
        default => 'Recorded activity',
    };
}
