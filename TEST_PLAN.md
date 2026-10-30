# KaAklat test plan

Version 0.1; evidence updated 2026-10-31. Application checks have mixed implementation/verification status. Use the dated evidence below for executed automated and user-operated checks; unchecked portions of scenarios remain pending.

## Test environments and evidence

Use only isolated databases and fictional users. Planned roles: pending student, active student A/B, suspended student, librarian A/B, administrator. Never share usable demo passwords in this file. Store runtime secrets privately and set up accounts through a secure bootstrap when implemented.

Record test date, exact code/schema revision or SHA256, database version, environment, test steps/command, actual result, and evidence path. Status values: Not run, Pass, Fail, Blocked. Pass requires execution, not source review. Changed code invalidates affected results until rerun.

## Functional scenarios

| Test | Requirements         | Scenario and expected outcome                                                                                                                                                                             | Actual result                                                                                   |
| ---- | -------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ----------------------------------------------------------------------------------------------- |
| T-01 | FR-01, FR-02, NFR-01 | Register with forged role=administrator/status=active. New account is student/pending; login permits catalog, request denied. Duplicate canonical username rejected.                                      | Partially passed: registration/login happy path only; forged fields and duplicate cases not run |
| T-02 | FR-01, NFR-01        | Correct/incorrect passwords, logout and idle session. Generic failure; success regenerates session ID; logout and >30min idle deny reuse.                                                                 | Not run                                                                                         |
| T-03 | FR-02, NFR-02        | Student B requests student A's history/decision endpoints; signed-out visitor requests catalog and cover. Denied without leaking records/image.                                                           | Not run                                                                                         |
| T-04 | FR-02                | Student/librarian attempt staff creation; admin attempts pickup/return. All denied. Last active admin cannot be removed even by two concurrent updates.                                                   | Not run                                                                                         |
| T-05 | FR-01, FR-02         | Librarian activates student, suspends after login. Subsequent protected access denied; librarian still able to record student's return.                                                                   | Not run                                                                                         |
| T-06 | FR-03, NFR-04        | Search title/author, no matches, apostrophe/Unicode, availability filter, page beyond end. Correct bounded results and readable empty/error states.                                                       | Not run                                                                                         |
| T-07 | FR-04, NFR-03        | Create title with two authors and 3 copies. Three distinct codes, correct author links, available count 3. Reject invalid/huge/negative counts and blank title/authors.                                   | Not run                                                                                         |
| T-08 | FR-04, NFR-01        | Upload valid JPEG/PNG then SVG, renamed PHP, invalid image, >2MB image, excessive pixel dimensions, and traversal filename. Only safe input accepted/re-encoded; random path; no execution/public bypass. | Not run                                                                                         |
| T-09 | FR-04, NFR-03        | Archive available copy, copy with live loan, title with pending request, title with live hold. Only permitted archive succeeds; blocked actions preserve history and counts.                              | Not run                                                                                         |
| T-10 | FR-05                | Eligible student submits valid request; try days 0/31/fraction, reason <10/>500, unavailable title, sixth pending and duplicate title. Only valid request accepted; no partial records.                   | Not run                                                                                         |
| T-11 | FR-06                | Librarian approves 7-day request for 10 days with note; rejects another with note. Original request retained; approved duration/decision visible; missing required note rejected.                         | Not run                                                                                         |
| T-12 | FR-05, FR-06         | Student cancels own pending/approved-uncollected request; tries another's or picked-up request. Allowed cancellations release allocation; others fail.                                                    | Not run                                                                                         |
| T-13 | FR-06, FR-07         | At pickup deadline minus 1 second allow eligible pickup; at exact deadline refuse. Expired record retains history and frees availability; cleanup repeat is harmless.                                     | Not run                                                                                         |
| T-14 | FR-07, FR-09         | Pickup near Manila midnight, month/year boundary, and leap day. Due date is Manila pickup date + approved days; end of due date not overdue; following day overdue.                                       | Not run                                                                                         |
| T-15 | FR-07                | Four active loans + one approved hold allow only pickup of existing hold; any new approval denied at five. Repeat pickup doesn't create/change another loan.                                              | Not run                                                                                         |
| T-16 | FR-08, FR-09         | Return once and repeat; return while student suspended or overdue. One return event and correct availability; no fine, duplicate loan or deleted history.                                                 | Not run                                                                                         |
| T-17 | FR-05, FR-06, FR-07  | Existing overdue loan prevents new request, approval and pickup; return remains possible. After actual return eligibility restored if otherwise valid.                                                    | Not run                                                                                         |
| T-18 | FR-09                | Pending, approved, expired, cancelled, rejected, active, overdue and returned states render accurately. Student sees own reason; other students/admin-only view do not.                                   | Not run                                                                                         |
| T-19 | FR-10                | Exercise each account/catalog/circulation mutation. Correct actor/time/target audit, committed with change. Inspect logs: no passwords, tokens, session IDs or reason text.                               | Not run                                                                                         |
| T-20 | FR-12, NFR-01        | Staff issues reset after identity check. Valid token works once within 30min; expired/reused token fails. Password changes; other tokens and old sessions revoked; unknown account errors generic.        | Not run                                                                                         |

## Security and concurrency scenarios

Use independent database connections or processes with a barrier so operations truly overlap. The Windows PHP built-in web server is single-process and cannot by itself prove simultaneous HTTP handling. Use direct database integration tests for locking and an appropriate concurrent server/host for endpoint races. Separate browser tabs alone are not evidence that transactions overlapped.

| Test | Requirements                | Scenario and expected outcome                                                                                                                                                         | Actual result |
| ---- | --------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------- |
| T-21 | FR-06, NFR-03               | Two librarians approve different requests for one copy. Synchronize after both intend the copy. Exactly one live allocation, loser has clear conflict, no orphan audit/decision.      | Not run       |
| T-22 | FR-06, FR-07, NFR-03        | Student has 4 allocations; two librarians approve different copies simultaneously. At most 5 live allocations, one approval refused after serialized check.                           | Not run       |
| T-23 | FR-06, FR-07, NFR-03        | Expiry/cancellation races physical pickup. One valid outcome; never active loan and released copy simultaneously. Lock order avoids unhandled deadlocks; retry is bounded.            | Not run       |
| T-24 | FR-08, NFR-03               | Two simultaneous returns / repeat POST after network uncertainty. One persisted return and one audit event; repeat reports previous completion.                                       | Not run       |
| T-25 | FR-04, FR-06, FR-10, NFR-03 | Force failure after proposed mutation but before audit, or before completion of copy creation. Rollback removes all partial changes; previous image stays usable.                     | Not run       |
| T-26 | NFR-01, NFR-02              | SQL injection values in login/search/sort; stored script in title/author/reason; missing/foreign CSRF; GET mutation. No executable output, query change or unauthorized mutation.     | Not run       |
| T-27 | NFR-01                      | Six failed logins in rolling 15min, known and unknown username, including parallel failures. Account key serialized; cooldown enforced, generic response, no bypass via case changes. | Not run       |
| T-28 | NFR-01, NFR-02              | Fixation attempt; role/password/status change with another session active; inspect HTTPS cookies. Old session denied, ID rotated/revoked, required cookie attributes present.         | Not run       |
| T-29 | NFR-01, NFR-02, NFR-08      | Request config, SQL, logs, backup, upload raw path, diagnostic URL; force production error. Private files inaccessible; safe error only; removed diagnostics absent.                  | Not run       |

## Interface, performance, hosting and recovery

| Test | Requirements          | Scenario and expected outcome                                                                                                                                                                                                            | Actual result |
| ---- | --------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------- |
| T-30 | NFR-04, NFR-05        | Complete core forms using keyboard at widths 360/768/1280 and 400% zoom; inspect labels, focus, error association, status contrast, alt text. No inaccessible controls or clipped essential content.                                     | Not run       |
| T-31 | NFR-04                | Observe actual planned participants completing tasks. Record failures/help/time without inventing responses; interview notes and questionnaire scores kept distinct.                                                                     | Not run       |
| T-32 | NFR-06                | Use 100 titles/300 copies/30 student accounts/3 staff and 5 concurrent users. Record >=30 local warm response samples and >=20 hosted navigations. Compare p95 local <=2s and hosted target <=5s, with network/device/failures recorded. | Not run       |
| T-33 | FR-11, NFR-07         | Export SQL/files/private config, restore into separate empty local DB, validate counts, FK links, history, cover files and sign-in. Record completion <=30min and backup age <=24h or explain failure.                                   | Not run       |
| T-34 | NFR-08                | Hosted smoke test: direct HTTPS address, redirect, PHP/PDO/GD, assigned DB version, InnoDB/FK/CHECK/UNIQUE/rollback and safe private paths. All needed capabilities work; diagnostics removed.                                           | Not run       |
| T-35 | FR-11, NFR-07, NFR-08 | Rehearse maintenance deployment and rollback with a transaction created after initial backup. Preserve it or reconcile explicitly; no blind overwrite of current loans.                                                                  | Not run       |
| T-36 | NFR-08                | Real desktop and phone browser with host JS/cookie check. Login, catalog, request, staff circulation work; blocked clients and outages documented rather than counted as passes.                                                         | Not run       |

## Requirement coverage

FR-01: T01/02/05; FR-02: T01/03/04/05; FR-03: T06; FR-04: T07/08/09/25; FR-05: T10/12/17; FR-06: T11/12/13/17/21/22/23; FR-07: T13/14/15/17/22/23; FR-08: T16/24; FR-09: T14/16/18; FR-10: T19/25; FR-11: T33/35; FR-12: T20.

NFR-01: T01/02/08/20/26/27/28/29; NFR-02: T03/18/26/28/29; NFR-03: T07/09/21/22/23/24/25; NFR-04: T06/30/31; NFR-05: T30; NFR-06: T32; NFR-07: T33/35; NFR-08: T29/34/35/36.

## Executed evidence

### Borrowing request increment — 2026-10-31

PASS: 12-script regression runner including tests/borrow_requests_test.php; full PHP lint and git diff --check. New tests use connection-local temporary baseline tables with fixture-only foreign keys omitted; generated uniqueness and CHECK constraints retained. Tested malformed days/reason, CSRF, staff/pending/suspended/stale-session denial, foreign ownership, five pending, duplicates, cancellation/repeats, unavailable/archive cases, overdue, live duplicate/exact-48h expiry, full allowance, unchanged availability, and forced audit rollback. No private reasons in audit.

PASS standalone tests/borrow_requests_concurrency_test.php: two processes, pending-cap and duplicate-title races. NEW fictional scratch DB retained: kaaklat_request_test_b5596349e8e5. Requires CREATE DATABASE, excluded from temporary-only regression runner. Never imports into demo DB. Initial missing-service failure and fixture setup errors corrected before passing.

NOT VERIFIED: new authenticated browser/keyboard/phone acceptance, HTTP forged-field and GET handling, future cross-operation approval/archive races. Localhost DB tests required sandbox network escalation; no approval rejection.

Hosting user-operated evidence: PHP 8.4.27/extensions, MariaDB 11.4.13/InnoDB, protected file deny/internal read, sessions, PDO, ordinary InnoDB rollback, CHECK #4025, FK #1452 passed. Temporary-table creation denied with 1044. HTTPS warning intermittent; cookie flags, actual GD processing, generated constraints, lock contention, restore and application deployment pending. Hosting deferred; diagnostics/config/canaries/two probe tables still require cleanup.


### User-operated responsive/keyboard acceptance — 2026-10-31

- User reported Chrome simulated widths 360px and 768px PASS; keyboard navigation PASS; login/register PASS; catalog filters/pagination PASS; librarian forms PASS; 400% zoom PASS; problems None.
- Evidence is the user's explicit chat report following the supplied manual instructions, not an agent-operated browser test. Exact Chrome version, revision hash and physical device were not recorded.
- Completes the supplied narrow-screen/keyboard/zoom checklist for current screens. T-30 has partial user-reported evidence, not full coverage of all widths/contrast/screens. Actual-phone, contrast measurement, concurrency and hosting checks remain pending. No claim of WCAG conformance.

### User-operated Chrome desktop acceptance — 2026-10-30

- Evidence: user explicitly reported Pass for student catalog, combined filters, available-only filter, pagination, book details, student permission denial, librarian forms, and signed-out catalog/cover access. Problems observed: None.
- Procedure: the eight-step manual checklist supplied in chat, performed by the user against the local demo in Chrome. This is user-reported acceptance evidence, not agent-operated browser execution. Browser version, viewport and exact revision hash were not recorded.
- Result: PASS for those eight desktop scenarios only. Phone layout, keyboard-only, zoom/contrast, concurrency, hosted compatibility, performance and restore checks are not covered. Do not mark all of T-03/T-06/T-07/T-08/T-09/T-30 fully passed from this report.

Latest hardening attempt: six non-database scripts passed; app/auth.php and auth_hardening_test.php lint passed. Full runner stopped at auth_regression_test.php with SQLSTATE HY000/2002 actively refused local MySQL. New duplicate/audit/rollback/unknown/disabled test exists but was not reached. Start local MySQL and rerun before marking hardening passed. Functional rejection tests do not prove equal timing.

### Regression audit — 2026-10-30

See `verification/REGRESSION_2026-10-30.md` for reproduction failures, corrections, executed unit/temporary-database/HTTP/multipart checks and limitations. Repeat CLI suite with `C:\php-8.4.14\php.exe tests/run_regression.php`. Idle expiry and rolling failure retention now have passing isolated tests; full T-02/T-27/T-28 browser and concurrency coverage remains incomplete. Credential-dependent existing-account test was not run.

### Category/year increment — 2026-10-30

- PASS `tests/catalog_filters_test.php`: initial expected undefined-function failure, then valid/invalid category IDs, A–Z input, author/title input types, malformed/injection years, optional/future year handling.
- PASS `tests/catalog_integration_test.php`: temporary categories/book fields, combinations of category/year/letter and author/title/year, unknown category/no-match year, existing literal search, archives, pagination and availability tests.
- PASS `tests/catalog_management_integration_test.php`: category/year saved and read with new book, existing edit/archive/authorization/rollback assertions still pass. Temporary fixture upgrade uses only connection-local tables. Image validation and existing foundation/auth/catalog policy tests pass; full PHP lint and whitespace check pass.
- PASS `verification/book_metadata_check.php`: actual local database has 20 populated sample metadata rows, expected combined Austen/Pride/1813/P match and FK/CHECK metadata definitions. Verification is read-only; it does not claim all constraint rejection cases executed.
- Local migration completed after private full-table JSON snapshot; existing catalog/loan counts preserved (21 visible titles, 305 copies, 17 requests, 14 loans). Signed-out filtered catalog returned 303. Browser rendering and filter persistence across UI page links still pending; no hosted or restore-rehearsal claim.

### Librarian catalog management — 2026-10-30

- PASS: `C:\php-8.4.14\php.exe tests/catalog_management_test.php`. Title/author/description validation, quantity bounds/fractions/arrays, malformed/SVG/PHP/oversized image rejection, valid PNG decoding, oversized dimensions, partial/forged upload rejection. Test initially failed before module existed.
- PASS: `C:\php-8.4.14\php.exe tests/catalog_management_integration_test.php`. Connection-local temporary InnoDB tables; students/admin/stale-session denial, forged role/CSRF rejection, two-author creation with 3 distinct copies, editing, adding copies and derived availability, whole-mutation rollback on forced audit uniqueness failure, pending-title/live-allocation archive denial, incorrect copy target denial, successful available-copy/title archive, loan-history retention and archived-title mutation denial. Fixture needed session restoration after testing revocation; rerun passed.
- PASS: genuine multipart HTTP test on temporary `127.0.0.1:8012` PHP router `tests/catalog_upload_router.php`. Submitted local JPEG with `filename=../../renamed.php` and PHP MIME: actual JPEG accepted, generated name used and output decoded. PHP source disguised as image/jpeg rejected. Duplicate audit failure on a second cover replacement restored prior cover path, retained old file and removed the new file. Router uses temporary database fixtures and deletes only its own generated test cover; server stopped afterward. Never deploy this router.
- PASS: existing foundation/auth/student/catalog unit/policy tests; catalog/student integration tests; full app/public/views/tests/bin PHP lint and `git diff --check`. Signed-out GET `/manage_books.php` returned 303. Read-only real sample check still: 20 visible books, 300 copies, 17 requests, 14 loans.
- NOT RUN: authenticated browser layout/keyboard/mobile acceptance, concurrent archive/approval races, hosting upload limits. No claim that all T-07/T-08/T-09/T-25/T-30 scenarios are fully passed.

Real book sample update: full PHP lint and catalog unit/integration tests passed. `verification/real_books_check.php` validated 20 active real titles and decoded private covers; retained counts 300 copies, 17 requests, 14 loans. Signed-out GET cover.php?id=1 returned 403. Gutenberg Pride and Prejudice cover visually inspected. Authenticated cover HTTP and full catalog visual checks remain pending. No upload security pass claimed: these are curated downloaded images only.

### Catalog and account verification — 2026-10-30

- HTTP check: signed-out GET `/catalog.php` returned 303 from the local server. Authenticated browser checks still pending.

- PASS: `C:\php-8.4.14\php.exe tests/student_accounts_integration_test.php`. Temporary users and audit tables only. Verified status transitions, forged CSRF/action rejection, staff-target denial, role/session-version enforcement, suspension revocation and atomic rollback of status/version/audit after duplicate audit failure. This supersedes the earlier blocked ALTER fixture. No persistent fixture writes.
- PASS: `C:\php-8.4.14\php.exe tests/catalog_test.php`: role/status access policy and malformed/overlong search/page inputs.
- PASS: `C:\php-8.4.14\php.exe tests/catalog_integration_test.php`: isolated temporary catalog/author/copy/request/loan tables. Search by author/title, literal percent/underscore, apostrophe/injection inputs, 12-item pagination, out-of-range empty page, absent/archived detail, archived-copy exclusion, total and available counts across active checkout, unexpired hold, >48h hold, exact 48h boundary, returned and cancelled allocations. Escaping check passed.
- PASS: all PHP syntax lint and existing foundation/auth/student policy tests. Initial catalog test failed before implementation as expected.
- NOT RUN: authenticated browser/HTTP acceptance for each role, search/detail/pagination navigation, cover placeholder visual appearance and responsive/keyboard checks. No uploaded-cover delivery exists in this increment. T-03/T-06/T-30 are not fully passed; T-05 return behavior remains deferred.

Visual refresh, 2026-10-30: syntax lint for public/views PHP and student account policy tests passed. Desktop/mobile visual inspection and keyboard checks remain NOT RUN; do not mark T-30 passed.

Student-management view separation: PHP lint and policy regression tests passed. Manually verify each status view, invalid filter fallback, pagination retaining status, and successful POST returning to the selected view; browser verification pending.

### Student account management increment - 2026-10-30

- `php tests/student_accounts_test.php`: passed permitted transitions and rejection of invalid transitions and unauthorized roles.
- Existing foundation and auth unit tests passed. PHP lint passed across app/public/views/tests.
- `php tests/student_accounts_integration_test.php`: incomplete. Uses connection-local temporary tables to shadow users/audit_events. Status, revocation, CSRF and authorization assertions executed before the failure-injection stage. First audit-failure fixture did not force an error; subsequent CHECK fixtures failed compatibility checks. Final temporary audit-column rename fixture remains unexecuted because automatic approval review rejected the ALTER operation over possible persistent-table risk. Do not mark rollback or the integration suite passed.
- Browser role denial, pagination, POST redirects and end-to-end student suspension still require manual verification. T-05 remains incomplete; return handling is outside this increment.

No complete application scenario above has been executed.

### Local account bootstrap and authentication check - 2026-10-30

- **Environment:** Local PHP 8.4.14 CLI, MariaDB 10.4.32 on `kaaklat_dev`.
- **Action:** Set the demo administrator password using the local bootstrap script (credential omitted) and executed `tests/auth_integration_test.php`. The test now reads its credential from the local `KAAKLAT_TEST_PASSWORD` environment variable.
- **Observed:** CLI successfully validated username and length boundaries, updated `password_hash`, incremented `session_version` to 2, recorded `account.password_set` in `audit_events`, and cleared failures. `tests/auth_integration_test.php` verified authentication succeeds for `demo_admin` with valid credentials, rejects invalid password, and rejects non-existent username.
- **Result:** Pass for local CLI account bootstrap and database authentication.
- **Next action:** Verify login through the web interface using `demo_admin`.

### Local registration and database check - 2026-10-30

- **Environment:** User-operated PHP 8.4 development server at `127.0.0.1:8000`, local `kaaklat_dev` database.
- **Action:** Requested `health.php` in a browser, then created fresh fictional account `test_student_04` and signed in.
- **Observed:** `{"status":"ready"}`; dashboard showed "You are now signed in" and stated that librarian activation is required before borrowing.
- **Result:** Pass for PHP-to-database readiness and the registration/successful-login happy path. T-01 is only partially passed because forged role/status, duplicate username, catalog and borrowing denial cases were not executed.
- **Evidence:** User-provided browser screenshot, 2026-10-30 (Asia/Manila).
- **Next action:** Run the remaining T-01 cases. If the earlier intermittent failure returns, capture the local diagnostic without passwords or database credentials.

### Design artifact check - 2026-10-29

- **Action:** Static PowerShell inspection of `schema.sql` and `seed.sql`.
- **Observed:** `schema.sql` has 11 `CREATE TABLE` statements and no `DROP` or `TRUNCATE`; `seed.sql` has 11 `INSERT` statements, one `START TRANSACTION`, one `COMMIT`, and no `DROP` or `TRUNCATE`.
- **Result:** Pass for this limited structural check only.
- **Limit:** MariaDB/MySQL did not parse or execute either SQL file. A temporary isolated MariaDB server started, but sandbox policy denied both TCP and named-pipe client connections. No existing XAMPP database or school data was accessed.

Importing a schema is not a functional, security, load, restoration or concurrency test of the application.

### Local schema and seed import - 2026-10-29

- **Environment:** User-operated phpMyAdmin on local XAMPP database `kaaklat_dev`.
- **Action:** Imported `schema.sql`, then `seed.sql`, into a newly created database. Executed the aggregate count query supplied in this test plan.
- **Observed:** `users` = 33, `books` = 100, `book_copies` = 300, `borrow_requests` = 17, `loans` = 14.
- **Result:** Pass for initial schema/seed import and fixture counts.
- **Evidence:** User-provided phpMyAdmin screenshot, 2026-10-29 (Asia/Manila).
- **Limit:** This does not execute functional, security, concurrent-transaction, backup/restore, accessibility, performance, hosted-runtime or application-PHP tests.

## Result entry template

```text
Test ID:
Date/time/timezone:
Environment and actual database version:
Code/schema revision or SHA256:
Setup and exact action/command:
Expected:
Actual:
Status (Pass/Fail/Blocked):
Evidence path (no secrets/private borrower data):
Issue and next action:
```
