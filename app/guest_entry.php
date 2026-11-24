<?php
declare(strict_types=1);

/** Entry forms belong to guests; validate identity before rendering or processing old forms. */
function redirect_authenticated_entry(): void
{
    if (!isset($_SESSION['user_id'], $_SESSION['session_version'])) { return; }
    try {
        $user=current_user(database_connection(application_config()['database']));
    } catch (Throwable) {
        // Do not show a mixed authenticated/guest page when identity cannot be verified.
        error_log('Guest entry session verification unavailable.');
        http_response_code(503);
        header('Content-Type: text/plain; charset=UTF-8');
        exit('Unable to verify your session. Please reload this page.');
    }
    if ($user!==null) { redirect('/'); }
}
