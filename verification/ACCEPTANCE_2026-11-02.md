# Pickup/interface acceptance — 2026-11-02

Scope: acceptance only; no returns/new features. Existing demo data preserved. New opt-in `tests/pickup_http_acceptance_test.php` creates a uniquely named fictional database, launches only a loopback PHP server with `tests/acceptance_router.php`, then stops that server in finally. Never deploy the router. The router rejects requests unless given a scratch-database environment name and loopback origin; it exposes only allowlisted existing entry pages/assets. Random fixture credentials are never printed. No private config modified. Scratch databases retained, not dropped.

Executed evidence:
- `tests/run_regression.php`: 16 scripts PASS using temporary fixtures.
- `tests/pickup_http_acceptance_test.php`: 22 assertions PASS against retained `kaaklat_acceptance_1041ca531bf5`.
- Earlier successful HTTP run retained `kaaklat_acceptance_e7521fb35d99`; an initial failed run also retained its scratch database. No cleanup authorized/performed.
- All 51 PHP files lint PASS; forms.js syntax PASS; whitespace check PASS before this report.

HTTP assertions cover four actual logins; student navigation/count links; student/admin pickup denial; invalid request field errors and retained reason; request submission; librarian navigation; changed-duration field error; approval; allocated copy code; forged CSRF and extra role input rejection; pickup; unchanged availability; due-date equality to actual Manila pickup date plus approved days; retry preserving entire loan and exactly one collection audit; student active card/decision/original terms; other student empty history; expired pickup refusal and release.

Browser evidence (in-app browser): local login page opened, desktop screenshot inspected; blank form produced field errors and focused error summary; Tab focused username summary link and Enter focused username input. Login error form inspected at 360×800 and 768×900, with readable wrapped text and usable controls in captured views. Temporary viewport restored and tab closed.

Limits: these narrow screenshots cover the login form, not authenticated dashboards/cards/librarian forms or the entire page. Full authenticated visual/keyboard acceptance, real phone, 400% zoom, screen reader, JS-disabled browser interaction and comprehensive contrast audit remain NOT VERIFIED. HTTP HTML assertions do not prove visual rendering or focus. The due-date HTTP check uses the existing due-date helper; independent midnight/calendar fixtures are in pickups_test.php. Progress/history correctness and expiry/cap ownership also have regression evidence.

Tool delays: first HTTP launch permission review timed out; retry succeeded. Viewport documentation first timed out; retry succeeded. Thus no persistent browser blockade is claimed. The initial HTTP client set CURLOPT_POSTFIELDS after CURLOPT_POST, unintentionally making later GET requests POST; corrected test option order, then all assertions passed. This was a harness defect, not an application pickup defect. No confirmed product defect fixed.

Remaining manual checklist:
1. On student/librarian/admin sessions, check nav and dashboard links/counts against own records; confirm cards/progress/empty tabs visually.
2. On isolated fictional approved pickup, confirm handover, actual Manila date/due date, unchanged availability and repeated-submit message. Verify student history.
3. Submit invalid days/reason/decision explanation; check preserved input, linked summary and Tab/Enter focus; repeat without JS.
4. Repeat authenticated screens at 360/768/1280 CSS pixels and 400% zoom; record clipping, horizontal scroll and inaccessible actions.

Next functional increment: librarian-recorded returns, after remaining pickup/interface acceptance is addressed. Hosting deferred; no commit/push.
