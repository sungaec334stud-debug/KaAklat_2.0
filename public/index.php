<?php

declare(strict_types=1);
require_once dirname(__DIR__) . '/app/bootstrap.php';
require_once dirname(__DIR__) . '/views/layout.php';
require_once dirname(__DIR__) . '/app/dashboard.php';
start_application_session();
$user = null;
if (isset($_SESSION['user_id'], $_SESSION['session_version'])) {
    $user = current_user(database_connection(application_config()['database']));
}
if ($user !== null) {
    $student = $user['role'] === 'student';
    $message = $user['account_status'] === 'pending'
        ? 'Your account is waiting for librarian activation. You can explore the catalog while you wait.'
        : ($student ? 'Find a book, send a request, and follow its progress here.' : 'Keep your library moving. Choose a task below to get started.');
    $content = '<section class="panel dashboard-hero"><div class="dashboard-kicker">Your library, connected</div><h1>Welcome back, ' . h($user['display_name']) . '</h1><p>' . h($message) . '</p><a class="button" href="/catalog.php">Explore the catalog</a></section>';
    if(in_array($user['role'],['student','librarian'],true)) {
        try {
            $counts=dashboard_counts(database_connection(application_config()['database']));
            $summaries=$student
                ? [['Pending requests',$counts['pending'],'/requests.php?status=pending'],['Ready to collect',$counts['awaiting'],'/requests.php?status=awaiting_pickup'],['Active loans',$counts['active'],'/requests.php?status=active']]
                : [['Requests to review',$counts['pending'],'/review_requests.php'],['Awaiting collection',$counts['awaiting'],'/pickups.php']];
            $content.='<div class="summary-grid" aria-label="Borrowing summary">';
            foreach($summaries as [$label,$count,$url]) { $content.='<section class="panel action-card count-card"><h2>'.h($label).'</h2><p><strong>'.h((string)$count).'</strong></p><a href="'.h($url).'">View '.h(strtolower($label)).' →</a></section>'; }
            $content.='</div>';
            if($student && $counts['overdue']>0) { $content.='<p class="flash flash-error" role="status">You have '.h((string)$counts['overdue']).' overdue loan(s), included in your active loans. Please speak with your librarian.</p>'; }
        } catch(Throwable) { $content.='<p class="flash flash-info" role="status">Your borrowing summary is temporarily unavailable. You can still open your tasks below.</p>'; }
    }
    $cards = [['Discover', 'Book catalog', 'Search by title, author or category and check available copies.', '/catalog.php', 'Browse books']];
    if ($student) {
        $cards[] = ['Your borrowing', 'My requests & loans', 'See pending requests, librarian decisions, collection deadlines and active loans.', '/requests.php', 'View my requests'];
    }
    if ($user['role'] === 'librarian') {
        $cards[] = ['Accounts', 'Student access', 'Review new student accounts and manage borrowing access.', '/students.php', 'Manage students'];
        $cards[] = ['Catalog', 'Books & copies', 'Add titles, update covers and manage physical library copies.', '/manage_books.php', 'Manage books'];
        $cards[] = ['Requests', 'Review borrowing', 'Read borrowing reasons, choose approved days or explain a rejection.', '/review_requests.php', 'Review requests'];
        $cards[] = ['Handover', 'Physical pickup', 'Check the allocated copy and record handover when the student collects it.', '/pickups.php', 'Record pickup'];
    }
    $content .= '<div class="dashboard-grid">';
    foreach ($cards as [$step, $heading, $description, $url, $label]) {
        $content .= '<section class="panel action-card"><div class="action-step">' . h($step) . '</div><h2>' . h($heading) . '</h2><p>' . h($description) . '</p><a href="' . h($url) . '">' . h($label) . ' <span aria-hidden="true">→</span></a></section>';
    }
    $content .= '</div><div class="session-actions"><span>Signed in as ' . h(ucfirst($user['role'])) . ' · ' . h(ucfirst($user['account_status'])) . '</span><form method="post" action="/logout.php"><input type="hidden" name="csrf_token" value="' . h(csrf_token()) . '"><button class="button-secondary" type="submit">Log out</button></form></div>';
    render_page('Dashboard', $content);
    exit;
}
render_page('Welcome', '<section class="panel dashboard-hero"><div class="dashboard-kicker">Welcome to your school library</div><h1>A good book is just the beginning.</h1><p>Explore the collection, request the books you need, and keep track of your borrowing in one place.</p><a class="button" href="/login.php">Log in to the library</a></section><div class="dashboard-grid"><section class="panel action-card"><div class="action-step">New here?</div><h2>Create your student account</h2><p>Register, browse the collection, and ask your librarian to activate borrowing access.</p><a href="/register.php">Create an account →</a></section><section class="panel action-card"><div class="action-step">How borrowing works</div><h2>Request. Collect. Read.</h2><p>Choose an available book and explain how long you need it. Once approved, collect it from the librarian within 48 hours. Your borrowing period starts at pickup.</p></section></div>');
