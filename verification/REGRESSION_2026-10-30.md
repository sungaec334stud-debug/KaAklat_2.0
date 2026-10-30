# Regression results — 2026-10-30 (Asia/Manila)

Scope: implemented authentication, account lifecycle, catalog/metadata filters, librarian catalog management and covers. Circulation requests/pickup/return are not implemented and are outside this run. Worktree contained the user's uncommitted category/year increment; preserved.

## Reproduced defects and corrections

1. Successful login erased rolling-window failed attempts. New isolated auth regression initially failed with `Successful login must preserve rolling failure history`. Removed success cleanup and made expiry boundary exclusive as specified. Correct password stays blocked after five recent failures and works once failures expire.
2. Protected requests did not enforce the specified idle timeout. After correcting the first defect, the same test failed at `Idle session must expire`. Login now records activity and current-user lookup expires sessions at 30 minutes. Pre-existing sessions without an activity timestamp get their initial timestamp on first access.
3. Malformed array username/password form values could be cast to strings and produce PHP warnings. Input boundaries now reject non-string credentials safely; form fields preserve only strings. Isolated malformed username test passes.

## Passed checks

- `php tests/run_regression.php` is the repeatable runner: 6 unit/policy/image scripts and 4 temporary-database integration/regression scripts. Fails immediately on a failing child. The old credential-dependent `auth_integration_test.php` is deliberately excluded: it needs a private existing-account password and writes real throttle state. New auth tests use random credentials and temporary tables.
- Account activation/suspension/reactivation, role/session-version/CSRF denial, status mutation/audit rollback; catalog search/literal wildcard handling, metadata filters, pagination, archived exclusion and loan-derived availability; book/author/copy mutations, archive guards and rollback; image bytes/dimensions/upload-origin validation.
- Full PHP lint in app/public/views/tests/bin/verification and `git diff --check` passed.
- Actual localhost signed-out HTTP: catalog/list/detail/filtered catalog, manage_books and students returned 303; cover returned 403.
- Isolated multipart server: valid JPEG named `../../fake.php` re-encoded under random JPEG filename; PHP source named fake.jpg rejected; audit failure during replacement preserved old file and removed new staged file. Temporary server stopped.
- Read-only local data checks retained 21 visible titles, 305 copies, 17 requests, 14 loans; 20 real sample titles/covers and their metadata verified.

## Limits and follow-up

Authenticated full-browser journeys, phone/keyboard/zoom/contrast checks, true simultaneous races, hosted runtime, restoration rehearsal and performance targets were not run. HTTP checks are not visual acceptance. No claim of a bug-free or production-ready system. Further authentication hardening remains: unknown/disabled-login accounts currently lack the dummy password-verification path specified in SCHEMA.md; timing-resistance testing is outstanding. Registration currently maps any SQLSTATE 23000 to duplicate username, which may conceal another integrity failure; targeted constraint-error classification remains a follow-up.

Usage: start 37%/23% used, finish 40%/24% used for five-hour/weekly windows.
