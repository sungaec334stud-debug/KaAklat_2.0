# Changelog

## 2026-10-31 — Student borrowing requests

Added transactional student create/list/pending-cancel service and page, catalog/detail/dashboard links, current-role/session/CSRF/ownership checks, bounded validation and pagination. Five-pending/live allowance, duplicate title, overdue, archive and available-copy guards; expired holds excluded logically. Original terms/history preserved; safe audit atomic; no copies reserved. P-06 explicitly approved in chat. No schema migration.

PASS 12 regression scripts, PHP lint and whitespace checks; independent-process cap/title races passed in retained fictional DB kaaklat_request_test_b5596349e8e5. Authenticated UI and HTTP acceptance still pending. Existing demo data and user edits preserved.

Hosting deferred; user-operated PHP/DB/private paths/sessions/rollback/CHECK/FK checks passed, intermittent certificate warning unresolved. Cookie flags/GD processing/generated uniqueness/host locking/restore/app deployment pending. Remote diagnostics/private config/canaries/two probe tables await cleanup (see PROGRESS.md). No deployment claim. Next: local student acceptance then librarian decisions/allocation.


All dates use Asia/Manila. This records completed document changes, not application features or test results.

## [Unreleased]

- 2026-10-31: recorded explicit user approval of six borrowing defaults in SRS/progress/design/implementation-plan notes, preserving identifiers and limiting approval to stated clauses. No circulation implementation or test results inferred. Usage: 2% five-hour / 3% weekly used.

- 2026-10-31: recorded user-operated Chrome simulated 360px/768px, keyboard, login/register, catalog navigation, librarian forms and 400% zoom acceptance: all Pass, no problems. Actual-phone/contrast/concurrency/hosting verification remains pending. Corrected stale TEST_PLAN header that claimed all application scenarios were unrun. Usage: 1% five-hour and 2% weekly used.

- 2026-10-30: user-operated Chrome desktop acceptance reported Pass for all eight catalog/account-management checklist scenarios, with no observed problems. Phone/keyboard/concurrency/hosting not covered. Usage snapshot: 12% five-hour, 2% weekly used.

- Authentication hardening: fixed dummy-hash path for unknown/disabled accounts and precise username-constraint duplicate classification, keeping unrelated audit integrity failures as database errors. Added isolated hardening test to regression runner. Changed PHP lint and six non-DB scripts passed; full regression blocked by MySQL connection refusal, so database results are NOT verified. No commit/push performed.

### Regression testing — 2026-10-30

- Reproduced and fixed login clearing failed-attempt history and missing 30-minute idle session expiration. Hardened malformed credential form types. Added isolated auth regression and repeatable 10-script regression runner.
- Full details in `verification/REGRESSION_2026-10-30.md`: unit/integration tests, lint, signed-out HTTP and multipart rollback checks passed; current sample/user data preserved. Browser/accessibility/concurrency/hosting checks remain pending. Dummy password timing path and precise registration integrity-error classification remain follow-ups.
- Usage recorded: 40% five-hour / 24% weekly used at completion.

### Categories, publication years and catalog filters — 2026-10-30

- Added one optional primary genre/category and year first published to librarian add/edit forms and catalog cards/details. Category names can be selected from existing suggestions or entered by librarians; blank metadata displays Uncategorized/Unknown. Years validate as 1 through current year.
- Added combinable exact-category/exact-year, A–Z displayed-title prefix, literal title-text and author-text filters. Clear-filter link and pagination preserve filter state; alphabet matching includes leading A/The.
- Applied additive migration 001 locally after a private full-table schema/data snapshot. Existing records receive nullable fields; 20 matching real samples enriched with sourced years and editorial categories. User-added title/copies retained: current 21 visible titles, 305 copies, 17 requests, 14 loans. No schema reimport or loan mutation.
- PASS: new validation tests (failed before implementation), temporary SQL combined-filter and metadata persistence/transaction regressions, image validation tests, existing foundation/auth/catalog unit tests, PHP lint and diff checks. Read-only actual schema/sample metadata check passed; signed-out filtered catalog returned 303. Authenticated browser presentation and multi-page UI checks pending.
- Updated schema/SRS/workflow/research/source notes. Usage start/end: 30%/35% five-hour used, 22%/23% weekly used (65%/77% remaining).

- Pre-push review removed a previously hardcoded demo password from the authentication integration test and test-plan text. Existing-account test now requires local `KAAKLAT_TEST_PASSWORD`; it was not rerun without that secret. Private files remain ignored. Usage: 28% five-hour and 22% weekly used.

### Librarian catalog management — 2026-10-30

- Added librarian-only `/manage_books.php` with searchable/paginated title selection, create/edit forms, multiple authors (one per line), initial/additional quantities 1–100, private cover replacement, book archive and individually paginated copy archive controls. Dashboard link added; current theme and all 20 real samples preserved.
- Added `app/catalog_management.php`: server validation, POST/CSRF checks, forged-field rejection, current active librarian/session checks before image processing and under actor lock, book/copy/request/loan lock order, unique random copy codes, transactional author links/copies/audit, and up to three retries for lock conflicts. Archive refuses pending title requests or unresolved allocations, including stale holds until resolved; history remains intact. No schema migration.
- Uploads enforce genuine HTTP upload origin, actual JPEG/PNG content, 2 MB/4000×4000 bounds, GD decoding/re-encoding to JPEG, metadata removal, transparency flattening and random private filenames. Authenticated cover endpoint supports existing Gutenberg and new uploads. Failed rolled-back saves clean only the new generated file; previous covers remain. Ambiguous connection failures retain staged files for reconciliation.
- PASS: new validation/image tests; temporary-InnoDB management transaction/authorization/archive/rollback tests; real multipart upload/file rollback test; existing foundation/auth/account/catalog policy and isolated integration regressions; PHP lint and diff whitespace checks. Signed-out management GET returned 303. Browser visual/keyboard acceptance and simultaneous archive/approval races remain unverified.
- Read-only sample verification still reports 20 visible titles, 300 copies, 17 requests, 14 loans. Usage start/end: 22%/27% five-hour used and 21%/22% weekly used; 73%/78% remaining at finish.

### Real book samples and protected covers — 2026-10-30

- Replaced local demo IDs 1–20 with 20 real classics, correct author links and original descriptions sourced against Gutenberg records. Downloaded/decoded/re-encoded all 20 edition covers. Remaining unused fictional titles archived; actual active catalog count verified as 20. Private catalog backup created before transaction; 300 copies, 17 requests and 14 loans retained.
- Added source manifest, local preparation/install scripts, protected JPEG delivery, and cover display in catalog cards/details. No raw image URL exposed, ISBN added or schema migration required. These remain simulated physical holdings; no school inventory claim.
- Full PHP lint and catalog policy/SQL regression tests passed. All 20 database titles and cover images validated. Signed-out cover request returned 403. One cover visually inspected; full authenticated browser layout not checked. Usage at finish: 21% five-hour/weekly used, 79% remaining in both.

### Protected catalog — 2026-10-30

- Signed-out local catalog HTTP check returned 303. Usage start/end: 13%/15% five-hour used, 20% weekly used (85%/80% remaining at finish).

- Added `app/catalog.php`, `/catalog.php` list/detail, title/author literal search, validated search/page inputs, 12-book pages, archived-book exclusion, ordered authors, description previews/full text and derived copy counts. Inline placeholders require no public book-image path; uploaded covers are deferred.
- Authenticated pending/active students and active staff may browse; suspended/archived/revoked sessions are rejected. Added dashboard/header links, no-store catalog responses, safe error references and matching responsive styling.
- Resolved account audit rollback verification with a UNIQUE audit-code constraint in a connection-local temporary fixture; removed the rejected ALTER approach. Account integration test now passes, including session revocation and persisted rollback assertions.
- Catalog policy/input and SQL integration tests passed with temporary fictional fixtures: pagination, literal wildcard/apostrophe search, injection input, archived titles/copies and active/held/expired/returned/cancelled availability. Full PHP lint and existing unit/policy regressions passed. Browser visual acceptance remains pending. No persistent records or schema changed.

- Visual refresh (2026-10-30): forest-green header, warm paper background, serif headings, consistent buttons/forms/alerts, focused authentication cards, account-management tabs, student cards, status badges and responsive stacking. Shared header navigation and footer added. No account-policy or schema changes. PHP lint and student policy regression tests passed; browser visual verification pending.

- Separated librarian student management into Activate pending students, Suspend active students and Reactivate suspended students views. Status filter is allowlisted and parameterized; pagination and POST redirects retain the selected view. Usage: 9% five-hour and 19% weekly used.

### Student account management - 2026-10-30

- Added librarian-only `/students.php`, pending-first student list with pagination, and Activate/Suspend/Reactivate forms. Added librarian dashboard link.
- Status updates validate CSRF and transitions; lock actor/target rows in numeric order; recheck active librarian role and session version; reject staff targets; increment student session version and commit audit atomically. No schema changes.
- Policy tests passed after initial expected missing-module failure. Foundation/auth unit tests and PHP lint passed. Integration verification is incomplete: temporary-table fixture setup and failure injection required corrections; final ALTER fixture run was rejected by automatic approval review over possible persistent-table risk. Rollback remains unverified. Browser checks not run.
- Usage checked at start/end: 5% to 7% used in five-hour window; weekly 18% to 19% used.

### Added - 2026-10-30

- Local CLI password bootstrap script `bin/set_user_password.php` and backing function `set_user_password()` in `app/auth.php` for setting account passwords with 12-72 character validation, session-version rotation, rate-limit clearing, and audit logging.
- `tests/auth_integration_test.php` verifying database authentication for valid users and rejection of invalid passwords and non-existent accounts.
- Boundary assertions in `tests/auth_unit_test.php` for password length restrictions (11, 12, 72, 73 chars).

### Fixed - 2026-10-30

- Fixed literal `\n` inside single-quoted SQL strings across `app/auth.php` that caused SQL syntax error 1064 in MariaDB during registration, throttle, and audit queries.
- Registration failures now create a random reference and write the exception class and message to the PHP server error log. In the `local` environment only, the page also shows the escaped database diagnostic to make setup troubleshooting possible. Production mode keeps the database detail private and shows only the reference.

### Verification - 2026-10-30

- PHP syntax lint passed for every current file in `app/`, `public/`, `views/` and `tests/` after the registration diagnostic change.
- User browser check: `http://127.0.0.1:8000/health.php` returned `{"status":"ready"}`. The local PHP application can connect to `kaaklat_dev`; the outstanding registration failure occurs later in its transaction.
- Codex usage snapshot: 58% used in the current 12-hour window; ordinary usage remained available. This is a monitoring observation, not an application test.
- Follow-up usage snapshot: 68% used in the same 12-hour window; ordinary usage remained available. Recorded before the remaining allowance becomes low.
- Follow-up usage snapshot: 74% used in the same 12-hour window; ordinary usage remained available. Recorded because the remaining allowance is now low.
- Follow-up usage snapshot: 81% used in the same 12-hour window; ordinary usage remained available. Recorded because the remaining allowance is low.
- Follow-up usage snapshot: 86% used in the same 12-hour window; ordinary usage remained available. Recorded because the remaining allowance is very low.
- Follow-up usage snapshot: 91% used in the same 12-hour window; ordinary usage remained available. Recorded because the remaining allowance is critically low.
- User browser check: a fresh fictional student account `test_student_04` was registered and then signed in. The dashboard correctly stated that librarian activation is required before borrowing. This verifies only the registration and successful-login happy path.

### Added - 2026-10-29

- Initial project requirements and MVP scope in `SRS.md` (FR-01 through FR-12; NFR-01 through NFR-08).
- Normalized database documentation in `SCHEMA.md`, executable initial DDL in `schema.sql`, and fictional non-login seed fixtures in `seed.sql`.
- Development guidance in `AGENTS.md`, `WORKFLOW.md`, `DEPLOYMENT.md`, and `TEST_PLAN.md`.
- Research scope map in `RESEARCH_REVISION_PLAN.md` and free-hosting review in `STACK_AND_HOSTING_REVIEW.md`.
- This changelog, an append-only prompt log template, and an implementation plan.

### Implemented but not fully verified - 2026-10-29

- First PHP application foundation: private local configuration loader, PDO connection factory, HTML escaping, server-side required-text validation, CSRF tokens, one-time flash messages, session configuration, shared layout, local-only health endpoint, and basic responsive CSS.
- Student account entry points: public home page, registration, login and logout routes. Registration always creates a pending `student` account; client-supplied role/status is not accepted. Passwords use PHP password hashing. Login implements session-ID regeneration, current account/session-version lookup and a rolling failed-login throttle structure.
- Reusable checks: `tests/foundation_test.php`, `tests/auth_unit_test.php`, and `verification/database_connection_check.php`.

### Verification - 2026-10-29

- PHP syntax lint passed for every created PHP file.
- `tests/foundation_test.php` passed.
- `tests/auth_unit_test.php` passed.
- Database-backed checks did not run in the Codex sandbox: its PHP process is denied access to the local MySQL socket, although the user verified phpMyAdmin access separately.
- Browser smoke test did not run: the Codex sandbox terminates the local PHP development-server process after its command completes. The user must run the server in their own PowerShell or VS Code terminal for browser testing.

### Verified document checks - 2026-10-29

- PDF Chapters 1-2: page text reviewed; the conceptual-framework page was rendered and inspected. See `RESEARCH_REVISION_PLAN.md`.
- Local tool inventory: PHP 8.4.14 CLI with PDO MySQL; XAMPP PHP 8.2.12; MariaDB 10.4.32 client; Git 2.47.1. This does not verify a running XAMPP database or a hosted runtime.
- Hosting claims: official-source documentation review completed. No provider account or deployment was created.
- Static SQL structure check: `schema.sql` contains 11 table definitions and no `DROP`/`TRUNCATE`; `seed.sql` contains 11 inserts in one explicit transaction and no destructive statements. This is not a database import or constraint test.
- Local XAMPP/phpMyAdmin import of `schema.sql` and `seed.sql` into new database `kaaklat_dev`; fixture counts matched: 33 users, 100 books, 300 copies, 17 requests and 14 loans.

### Not verified

- No database-backed browser workflow, hosted site, complete functional/security test suite, backup restoration, participant interview, questionnaire response, or research evaluation has been completed.


## 2026-10-31 — Chapters 1 and 2 rewritten

Created output/pdf/SUNGA_CHAPTER1_2_REVISED_KaAklat.pdf from the preserved source PDF. Rewrote both chapters, conceptual framework and references against the actual plain PHP/PDO demonstration and current implementation status. Preserved the cover, contents text/page numbers, 16-page structure, university headers and page numbers. All 23 TOC annotations and 22 bookmarks were structurally verified against destination headings; rendered pages visually reviewed. See output/pdf/REVISION_NOTES.md and verification.json. No application/database changes or commit.

Usage snapshot before interruption: 89% five-hour used. Continuation after reset: 1% used/99% remaining, weekly 24% used. Deliverable complete; next research step is adviser review of the evaluation edition, criteria, instrument and participant procedures. Do not invent evaluation results. Next software increment remains librarian review/approval/rejection, after student request acceptance.

## Librarian decision increment — 2026-10-31

Implemented librarian pending queue/search with bounded 20-row pages, approval/rejection forms, original terms and current eligibility/availability. Approval assigns the lowest eligible internal copy, holds it for 48 hours, and leaves pickup/due date unset. Changed duration and rejection require a student-visible explanation (1–500 characters). Student history shows approved days, deadline and note; pending students may read their own history, while mutations remain active-student only. Actor/student, book, copies, requests and loans are locked in documented order. Expired selected allocations are released and audited atomically with approval. No schema migration; demo data preserved; hosting deferred.

Executed: 13 regression scripts PASS; 41 PHP files lint PASS; git diff --check PASS (line-ending warnings only). Four independent-process races PASS: same request, last copy, last allowance slot, cancellation versus approval. Scratch database retained: kaaklat_decision_test_133cf46ca372. Database initially refused connections; existing XAMPP MySQL started for tests.

Not verified: authenticated HTTP forged-field tests and desktop/phone/keyboard acceptance of the new forms; deterministic lock-contention/barrier coverage beyond simultaneous worker starts. Automated service tests cover audit rollback of decision/allocation, expiry reuse, archives, stale decisions, roles/session, original terms, availability and ownership. Do not infer full security or deployment acceptance.

Next: complete these acceptance gaps, then librarian-recorded physical pickup. No pickup, returns or approved cancellation implemented. Existing research output and edits preserved. No commit/push performed.


Additional verification: injected final approval-audit failure after expiry cleanup; PASS — old expired allocation remained unresolved, current request remained pending, no expiry audit survived rollback. Then successful retry passed. Changed test lint passed. Usage finish snapshot: 3% used / 97% remaining in five-hour window; 19% used / 81% remaining weekly. This is not a usage-limit stop.

## Documentation synchronization — 2026-10-31
Updated PROGRESS.md top status table to match the latest librarian-decision handoff: 13 regression scripts, 41-file lint, four decision races, remaining browser/HTTP checks, deferred hosting and physical pickup next. Corrected outdated implementation approval, defaults, hosting-account/probe and research revision statements. Historical notes and existing work retained. Documentation-only; no application tests rerun.

## Acceptance update — 2026-11-01
User reported same-session two-tab rejection-versus-stale-approval check PASS. TEST_PLAN.md records the scope. Other new-screen manual checks remain pending. No application code changed or automated tests rerun.

## Decision acceptance update — 2026-11-01
Recorded user-reported PASS for changed-duration explanations, student-visible original/approved terms and pickup deadline, and approval/rejection availability effects. All four targeted decision checks, including stale submissions, now reported PASS. Phone/keyboard and other unverified checks remain pending. Documentation-only; no automated tests rerun.

## Physical pickup — 2026-11-01
Added librarian pickup queue/search with bounded pagination, allocated copy code, approved days, Manila deadline and explicit expired state. POST handover records checkout and due date on the existing loan, with current role/session and borrower checks, overdue/duplicate/allowance/archive guards and atomic audit. Repeated pickup preserves dates; expiry cleanup commits separately as a successful expired outcome. Student history shows actual checkout time and due date. No schema migration; hosting remains deferred. Current demo data and previous research/code work preserved. No commit/push.

Verification: previous 13 regression scripts passed after history/UI changes; pickup integration and two independent-process races passed (same allocation; expiry/reallocation versus stale pickup). Scratch DB retained kaaklat_pickup_test_53d69f9d4310. PHP lint passed for 45 files. Final expanded test results follow in TEST_PLAN.md.

Final pickup results: 14 regression scripts and 45-file lint PASS; two independent-process pickup races PASS; expanded exact-deadline, original/history field and expiry-audit rollback checks PASS. Whitespace cleanup applied. Browser/HTTP acceptance remains pending. Usage finish: 51% five-hour, 74% weekly remaining; no usage-limit stop.

## Student request tabs — 2026-11-01
Added Pending, Rejected, Awaiting pickup and Active tabs using existing theme, plus All requests to retain access to cancelled/expired/returned history. Active includes overdue loans with their derived overdue label. Status is allowlisted; ownership is enforced before filtering; SQL filters before bounded pagination; page links retain status. New submissions redirect to Pending; cancellations keep selected tab. No schema change or demo-data modification.
Verification: 14 regression scripts PASS; added positive Pending/overdue Active fixtures then reran borrow_requests_test PASS. 45-file lint PASS, changed test lint PASS. Browser visual/keyboard checks not performed. A test-only fixture initially collided with an occupied copy; corrected the fictional fixture and reran successfully. No application integrity rule weakened.

## Pre-commit verification — 2026-11-01
User authorized review, commit and push of application/documentation changes; output/ research artifacts excluded. Fresh checks: 14 regression scripts PASS; 45 PHP files lint PASS; four decision concurrency scenarios and two pickup concurrency scenarios PASS. Fictional scratch databases retained: kaaklat_decision_test_f1dac2220538 and kaaklat_pickup_test_f2d34f1487ca. Staged filenames and secret patterns reviewed: private config/runtime/logs/backups/temporary artifacts excluded; no secret-pattern matches. Trailing test-file blank lines corrected. Remaining browser/phone/keyboard and HTTP acceptance limitations remain documented. No demo data reset/reimport.

## Blue/white/gray visual refresh — 2026-11-01
Replaced overlapping green-theme CSS with a consistent navy/blue, white and gray portal design. Added role-specific dashboard task cards, explanatory welcome/borrowing guidance, current-page navigation, skip-to-content, consistent forms/buttons, clearer selected tabs and responsive catalog/card layouts. No new dependency or data/schema change. Design notes: docs/visual-refresh.md.
Executed: 14 regression scripts PASS (isolated fixtures), 45 PHP files lint PASS. Browser desktop/mobile/keyboard/zoom acceptance for the redesign remains pending; no new visual/accessibility pass claimed. No commit/push.

## KaAklat logo — 2026-11-01
Created repo-native public/assets/kaaklat-logo.svg: blue rounded tile, white/pale-blue open book, K-inspired page stroke. Added shared header mark and SVG favicon; live brand text remains accessible, decorative header image has empty alt, fixed dimensions prevent layout shift. No external images/fonts/dependencies or official school seal implied. XML validation, changed layout PHP lint and whitespace checks PASS. Browser logo/favicon rendering acceptance pending. No commit/push.

## Navigation, summaries and borrowing interface — 2026-11-01
Implemented all eight interface areas: database-verified role navigation; linked student-own/global librarian dashboard counts; textual status badges; request progress with rejected/expired/closed explanations; compact authenticated covers and original-reason details on student cards; separate approve/reject action panels naming student/title; linked field errors and focusable summary; actionable empty states.
Counts exclude expired/closed allocations and Active includes overdue. Student summary links select their matching request tab. Navigation adds no permissions; all endpoints retain authorization. Returned progress represents historical data only; no return action added. No schema migration or demo mutations.
Validation scope: borrowing request and decision server errors associate with fields where identifiable; other policy/CSRF errors remain summary-level. Shared optional forms.js adds field-linked feedback for native constraints on existing POST forms, preserves values and focuses summaries. Server validation remains authoritative. Broader server-side field mapping in older catalog/auth forms is not claimed complete.
Executed: 16 regression scripts PASS (including dashboard ownership/roles/expiry and request UI validation/progress/escaping); 49 PHP files lint PASS; node --check public/assets/forms.js PASS. Browser desktop/mobile/keyboard and actual JS focus/validation interactions remain unverified. Existing visual refresh/logo work preserved. No commit/push.
Usage start 35% five-hour / 72% weekly remaining; finish snapshot 18% / 69%. Handoff: visually check role navigation wrap, counts/links, request card covers/progress, error links/focus with JS on/off, reject versus changed-duration errors, empty states, 360/768/1280px and 400% zoom. No destructive cleanup. Hosting deferred; returns remain next functional increment after acceptance.

## Interface commit preparation — 2026-11-01
User requested commit/push. Fresh 16-script regression, 49-file PHP lint, forms.js syntax and whitespace checks PASS. Include theme/logo, role navigation, counts and borrowing UX; exclude output/ and private/runtime artifacts. Browser visual/keyboard/phone/focus acceptance remains pending; older server forms retain some generic errors. No demo data changed.
