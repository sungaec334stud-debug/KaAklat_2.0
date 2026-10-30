# Changelog

All dates use Asia/Manila. This records completed document changes, not application features or test results.

## [Unreleased]

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

