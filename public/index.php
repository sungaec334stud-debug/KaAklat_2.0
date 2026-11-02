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
    $primaryUrl=$user['role']==='librarian'?'/review_requests.php':'/catalog.php';
    $primaryLabel=$user['role']==='librarian'?'Review borrowing requests':'Explore the catalog';
    $content = '<section class="panel dashboard-hero"><h1>Welcome back, ' . h($user['display_name']) . '</h1><p>' . h($message) . '</p><a class="button" href="' . h($primaryUrl) . '">' . h($primaryLabel) . '</a></section>';
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
    if ($user['role']==='administrator') { $content.='<section class="panel action-card"><h2>Staff access</h2><p>Create and manage librarian and administrator accounts.</p><a class="button" href="/staff.php">Manage staff accounts</a></section>'; }
    if ($user['role']==='administrator') { $content.='<section class="panel action-card"><h2>Account access and audit</h2><p><a href="/students.php">Manage students</a> · <a href="/recover_account.php">Help recover an account</a> · <a href="/audit.php">Read audit records</a></p></section>'; }
    $cards = [['Discover', 'Book catalog', 'Search by title, author or category and check available copies.', '/catalog.php', 'Browse books']];
    if ($student) {
        $cards[] = ['Your borrowing', 'My requests & loans', 'See pending requests, librarian decisions, collection deadlines and active loans.', '/requests.php', 'View my requests'];
    }
    if ($user['role'] === 'librarian') {
        $cards[] = ['Accounts', 'Student access', 'Review new student accounts and manage borrowing access.', '/students.php', 'Manage students'];
        $cards[] = ['Catalog', 'Books & copies', 'Add titles, update covers and manage physical library copies.', '/manage_books.php', 'Manage books'];
        $cards[] = ['Requests', 'Review borrowing', 'Read borrowing reasons, choose approved days or explain a rejection.', '/review_requests.php', 'Review requests'];
        $cards[] = ['Receive', 'Book returns', 'Find an outstanding loan and confirm receipt of its physical copy.', '/returns.php', 'Record a return'];
        $cards[] = ['Handover', 'Physical pickup', 'Check the allocated copy and record handover when the student collects it.', '/pickups.php', 'Record pickup'];
    }
    if ($user['role']==='librarian') {
        $order=['/review_requests.php'=>0,'/pickups.php'=>1,'/returns.php'=>2,'/catalog.php'=>3,'/manage_books.php'=>4,'/students.php'=>5];
        usort($cards,static fn(array $a,array $b):int => $order[$a[3]] <=> $order[$b[3]]);
    }
    $content .= '<div class="dashboard-grid">';
    foreach ($cards as [$step, $heading, $description, $url, $label]) {
        $content .= '<section class="panel action-card"><h2>' . h($heading) . '</h2><p>' . h($description) . '</p><a href="' . h($url) . '">' . h($label) . ' <span aria-hidden="true">→</span></a></section>';
    }
    $content .= '</div><div class="session-actions"><span>Signed in as ' . h(ucfirst($user['role'])) . ' · ' . h(ucfirst($user['account_status'])) . '</span><form method="post" action="/logout.php"><input type="hidden" name="csrf_token" value="' . h(csrf_token()) . '"><button class="button-secondary" type="submit">Log out</button></form></div>';
    render_page('Dashboard', $content);
    exit;
}
require_once dirname(__DIR__).'/views/landing.php';
render_page('Welcome', landing_html(), true);
