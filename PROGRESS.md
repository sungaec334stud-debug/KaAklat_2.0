# KaAklat progress

Updated: 2026-10-30 (Asia/Manila)

Status vocabulary: Planned; In progress; Implemented but unverified; Verified; Blocked.
Verified applies only to the named evidence, not to the entire application.

## Current status

| Task | Status | Evidence | Known issues | Next action |
| --- | --- | --- | --- | --- |
| Capture project direction | In progress | User confirmed librarian-recorded pickup/return; five-book allowance counts approved pickups and active loans; login-only catalog; librarian account activation; no ISBN; title/authors/description/cover/copy-count entry. Stack A selected. Presentation approximately November 2026; PDF is the required template; planned 20 students and a couple of librarians. | Exact deadline, evaluation instrument and librarian count unresolved. Pending cap, duplicate handling, 48-hour pickup expiry and 1-30-day range are recorded as proposed defaults, not school policies. | Review/confirm proposed defaults before changing them in code. |
| Review Chapters 1 and 2 | Verified | Read all 16 PDF pages as text; rendered and inspected PDF p. 5 Figure 1; findings in RESEARCH_REVISION_PLAN.md. User confirms this PDF is the required template. | Bibliography claims have not been independently audited. | Resolve evaluation requirements; revise after scope agreement. |
| Inspect local tools | Verified | Terminal PHP 8.4.14 with PDO and pdo_mysql; Git 2.47.1; XAMPP PHP 8.2.12; XAMPP database client reports MariaDB 10.4.32. Commands executed 2026-10-29. | Database client version does not prove running server version. No server connection, Apache test, or browser test performed. | Choose explicit executable paths; later verify server version and storage engines. |
| Evaluate free hosting | Verified | Official provider documentation checked on 2026-10-29; sources and limits recorded in STACK_AND_HOSTING_REVIEW.md. InfinityFree is recommended; AwardSpace free SSL restriction disqualifies it. | This verifies the source review only. No account created or deployment tested. | Select stack/host; perform early hosting smoke test. |
| Approve stack and workflow | Verified | User approved implementation; plain PHP/PDO/custom CSS selected. | Proposed business defaults still require confirmation before circulation; hosting runtime unverified. | Confirm proposed circulation defaults before borrowing increment. |
| SRS and normalized schema | Verified | User imported schema.sql and seed.sql through local XAMPP/phpMyAdmin into new database `kaaklat_dev` on 2026-10-29. Aggregate count query returned 33 users, 100 books, 300 copies, 17 requests and 14 loans, matching the fictional seed. | Import/count verification does not prove application workflows, transactions, every database constraint, backup restoration or hosted compatibility. | Begin application foundation only after design approval; later run constraint and concurrency checks. |
| Development documentation | Implemented but unverified | AGENTS.md, WORKFLOW.md, prompt.md, CHANGELOG.md, TEST_PLAN.md, DEPLOYMENT.md, research plan and implementation plan exist. | Documents require review and later updates based on real implementation/runtime evidence. | Review documents, then update per increment. |
| Application implementation | In progress | Auth, librarian student activation/suspension and protected catalog list/detail implemented. Account rollback/revocation and catalog SQL tests pass with temporary fixtures; unit/policy tests and PHP lint pass. Signed-out catalog HTTP returned 303. | Authenticated catalog browser acceptance pending. Catalog editing, cover uploads, circulation and deployment remain. | Verify catalog visually, then librarian cataloging and secure covers. |
| Deployment and evaluation | Planned | User requires hosted demonstration. | No deployed system or evaluator results. | Test a minimal hosted page/database connection early; evaluate complete workflows later. |

## Confirmed workflow decisions

- Login-required catalog; students register and need librarian activation before borrowing.
- Requests, approvals and physical pickup are distinct stages; only librarians record pickup and return.
- Approved pickups and active loans together consume the five-book allowance.
- Catalog fields: title, multiple author names, brief description, cover and copy count; no ISBN required. Test data will use well-known book titles.

These are agreed demonstration requirements, not evidence of existing institutional policy.

## Working proposals, not yet confirmed

- At most five pending requests; one pending/approved/active request per student/title; one request receives one copy on approval.
- Approved copies are held for 48 hours. A new request is required after expiry. Student may cancel pending/uncollected request; librarian may cancel an uncollected approval with a reason.
- Requested and approved durations are whole days 1-30. Due date begins at physical pickup in Asia/Manila; students can see the final approved terms.
- No reservations/FIFO waitlists, renewals, external messages, imports or advanced reports in the initial MVP unless required by the adviser.
- Book-cover uploads are now required. Proposal: staff-only JPEG/PNG, size/dimension limits, decoded and re-encoded images with random filenames and no script execution in the image directory. No arbitrary file uploads.
- Fictional student accounts and simulated loan records; book metadata may use well-known titles.

## Resume here

Latest regression run (2026-10-30): fixed rolling login-failure retention, idle-session expiry and malformed credential types. See verification/REGRESSION_2026-10-30.md and tests/run_regression.php. Database tests use temporary fixtures; current 21 books/305 copies/17 requests/14 loans preserved. Full visual/authenticated-browser/concurrency tests remain pending; dummy verification timing and integrity-error classification noted as follow-ups. Usage remaining 60% five-hour/76% weekly.

Latest handoff — categories/year (2026-10-30): migration 001 applied to local kaaklat_dev with private snapshot. Add/edit metadata and catalog combined category/title/author/A–Z/year filters implemented. 20 samples enriched; user-added records preserved (21 visible books, 305 copies, 17 requests, 14 loans). Validation, isolated SQL/management integration and regression tests passed, lint passed, actual metadata/constraint presence verified. Authenticated visual and filter-pagination browser checks remain pending. Existing databases require migration 001 before current code; do not rerun the local migration runner. Next planned implementation remains borrowing requests/decisions after UI acceptance and policy-default confirmation. Usage remaining 65% five-hour, 77% weekly.

Latest handoff — librarian catalog management (2026-10-30): `/manage_books.php` now implements create/edit, authors, initial/additional copies, private cover upload/replacement, and guarded book/copy archiving. Dashboard link available to librarians only. No schema change or loan actions. Validation, temp-table transaction/authorization/archive/rollback and genuine multipart/file-rollback checks PASS; existing unit/integration regressions, full lint and diff checks PASS. Current 20 sample titles/covers and 300 copies/17 requests/14 loans verified unchanged. Browser visual/accessibility checks and concurrent archive/approval races remain pending. Next: try librarian forms locally, then borrow requests and librarian decisions after confirming proposed circulation defaults. Usage remaining 73% five-hour, 78% weekly.

Latest sample update: local catalog now has 20 real classic titles with author links, original descriptions and private Gutenberg edition covers; extra unused fictional entries archived. Counts verified: 20 visible titles, 300 historical copies, 17 requests, 14 loans. Covers are protected by `/cover.php`; signed-out request returned 403. See docs/sample-books-sources.md for preparation/deployment and backup details. Next: browser check and librarian editing/uploads. No loans were removed. Usage 79% remaining five-hour/weekly.

Latest handoff — catalog increment (2026-10-30): implementation approval is confirmed. Protected catalog list/detail now implemented at `/catalog.php`, linked from dashboards/header. Search title/authors, 12-book pages, derived active/available copies, expired-hold handling and inline local cover placeholders exist. No schema change, editing, uploads or loan actions added. Account rollback/revocation integration test now PASSES using temporary tables and fixture-only duplicate audit code (no ALTER). Catalog policy/input and SQL/availability integration tests PASS; existing foundation/auth/account policy regressions and full PHP lint PASS. Next: browser acceptance of catalog login redirects/search/detail at desktop/mobile sizes, then librarian cataloging and secure cover uploads. Historical pending-verification notes below are superseded for account rollback by this result.

Visual refresh applied to shared layout/CSS, login/registration and student-management markup. Lint and policy tests passed. Next verify desktop/mobile presentation and keyboard navigation in the browser; account transaction rollback verification remains outstanding as documented below.

Student management now has separate activation, suspension and reactivation views. Refresh `/students.php` as a librarian to select a view. Browser verification of the new navigation remains pending.

Implementation was approved by the user. Student activation/suspension is now implemented in `app/student_accounts.php` and `public/students.php`, linked from the librarian dashboard. Policy tests, existing foundation/auth unit tests and PHP lint passed. Database test reached activation, suspension, reactivation, revocation and authorization assertions; the audit-failure fixture initially failed to induce failure, then used incompatible CHECK fixtures. The final temporary-table ALTER fixture was blocked by automatic approval review and has NOT passed. Resume by safely verifying rollback and browser access before catalog work. No schema migration is required. Remaining usage at handoff: 93% five-hour, 81% weekly.

