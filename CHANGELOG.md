# Changelog

All dates use Asia/Manila. This records completed document changes, not application features or test results.

## [Unreleased]

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

