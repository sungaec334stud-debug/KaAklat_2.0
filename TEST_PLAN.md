# KaAklat test plan

2026-11-04 commit readiness: after user restarted MySQL, fresh tests/run_regression.php23 scripts and HTTP133 assertions PASS in kaaklat_acceptance_221527b78cf5; tmp/backup-unit-5bd20340eee7 retained. The earlier connection-refused attempt was an environment blocker, not counted as a pass. Latest90 PHP lints/whitespace PASS. Only fictional fixtures; no demo mutation. User authorized commit/push; private/runtime/research-output exclusions reviewed. Final Git result reported separately.

## Python static-checker execution and coverage — 2026-11-04

User-run strict audit output, confirmed in tmp/profile-premium-audit.json: findings/errors/warnings/violations/unresolved all zero. Python installation now works per user terminal output. Source inspection found no PHP support in the checker's extension list and zero eligible files under default src/app/pages roots (app exists; no premium-ui.json). Therefore classify this as successful tool execution with **zero default source coverage**, not a PHP design-audit pass. Prior inaccessible-Python status is superseded for tool setup only. Existing profile automated/HTTP/manual evidence remains scoped as recorded. No application changes or fresh application test run.

## User-reported profile acceptance — 2026-11-04

All six supplied checklist items user-reported PASS: profile information, count links,360px,768px,400% zoom and keyboard navigation. No problems reported. Classification: user-operated acceptance, not agent-observed browser evidence or full accessibility/security certification. Supersedes earlier pending statements for these named checks only. Python static audit remains unrun; separate role/status/JS-disabled manual scenarios, legacy registration timestamps, real-device/screen-reader coverage, performance and hosting not inferred. No code/data changes or automated rerun for this report.

## Latest profile evidence — 2026-11-04

FR-13/T-37:25 isolated temporary-table profile checks PASS;23 regression scripts,133 HTTP assertions (kaaklat_acceptance_a06c7aa833f0) and90 PHP lints PASS. Roles/status/version/idle, safe own-field projection, shared count scope/closed expiry exclusions, escaped output, Manila registration rollover, tab links/zero state, no-store, forged query selectors, POST refusal and safe503/retry fault injection covered. UTC initialization assertion failed before correction; new HTTP registration timestamp now verified against UTC. All7 concurrency suites/17 scenarios and existing52-check restore rerun PASS because shared connection setup changed. Existing demo records untouched. Full evidence/retained scratch names: verification/STUDENT_PROFILE_2026-11-04.md.

NOT VERIFIED: new profile visual/desktop/360/768/400%/keyboard checks (Computer Use URL-confidence policy stopped access), premium static audit (Python alias inaccessible), older registration-time provenance, performance or hosting. Do not infer these from HTTP/markup or earlier UI passes. Manual: student → My Profile → compare own fields/counts/date → follow3 tab links; pending profile/catalog remain readable; staff denied; signed-out requires login; inspect long names/navigation at360/768/400%, Tab/Shift+Tab/Enter and JS off. Report exact page/role/width/steps/expected/actual. Next feature favorites; performance verification remains outstanding.

## Latest local backup/restore evidence — 2026-11-04

FR-11/T-33 partial acceptance: CLI bundle helper implemented,15 safety checks PASS and actual52-check fictional restore PASS. Latest targeted rerun: `kaaklat_backup_src_4167dc3a3d6a` → new empty `kaaklat_restore_4167dc3a3d6a`,3.90s import-through-validation/workflow on MariaDB10.4.32. Exact12-table definitions/counts/all rows, binary digest, CHECK/FK/generated unique/rollback, original history, availability, code/cover checksums, actual protected cover HTTP, password/status/session/reset quarantine and full restored request/approval/pickup/return passed. Backup source remains unchanged; import diagnostics use a separate private directory; test server stopped. Exact resources/earlier runs in verification/BACKUP_RESTORE_2026-11-03.md. Added unit fixture tmp/backup-unit-84837f1ca9db retained.

Fresh existing regressions22 scripts, HTTP104 assertions (kaaklat_acceptance_dbcd8ae97f2a) and84 PHP lints PASS during this increment. No demo database reset/import/mutation; no schema/UI/business change, commit/push or hosting work. Local backup instructions in docs/local-backup-restore.md. Not verified: real-demo/off-device restore, Windows ACL enforcement, OS-link fixture execution, crash/power-loss recovery, actual backup schedule/RPO, full-volume RTO, T-35 production rollback/reconciliation, performance, hosted acceptance and new visual/manual account-access acceptance. New safety tests initially failed before implementation; final targeted rerun passed after path-containment hardening.

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
| T-33 | FR-11, NFR-07         | Export SQL/files/private config, restore into separate empty local DB, validate counts, FK links, history, cover files and sign-in. Record completion <=30min and backup age <=24h or explain failure.                                   | Fictional local rehearsal PASS (52 checks, latest3.90s, 2026-11-04); real-demo/off-device/RPO routine still unverified. |
| T-34 | NFR-08                | Hosted smoke test: direct HTTPS address, redirect, PHP/PDO/GD, assigned DB version, InnoDB/FK/CHECK/UNIQUE/rollback and safe private paths. All needed capabilities work; diagnostics removed.                                           | Not run       |
| T-35 | FR-11, NFR-07, NFR-08 | Rehearse maintenance deployment and rollback with a transaction created after initial backup. Preserve it or reconcile explicitly; no blind overwrite of current loans.                                                                  | Not run       |
| T-36 | NFR-08                | Real desktop and phone browser with host JS/cookie check. Login, catalog, request, staff circulation work; blocked clients and outages documented rather than counted as passes.                                                         | Not run       |
| T-37 | FR-13, NFR-01/02/04/05 | Student-only view profile: fresh identity/status/session, safe own fields/counts, Manila date, selectors/method rejection, no-store, escaping, zero/error states, narrow/zoom/keyboard. | Automated/HTTP PASS; six supplied profile manual checks user-reported PASS (2026-11-04). Broader coverage/static audit not inferred. |

## Requirement coverage

FR-01: T01/02/05; FR-02: T01/03/04/05; FR-03: T06; FR-04: T07/08/09/25; FR-05: T10/12/17; FR-06: T11/12/13/17/21/22/23; FR-07: T13/14/15/17/22/23; FR-08: T16/24; FR-09: T14/16/18; FR-10: T19/25; FR-11: T33/35; FR-12: T20; FR-13: T37.

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

## Research PDF checks — 2026-10-31

PASS: revised PDF has 16 pages; original cover and TOC render identically; contents text/page numbers/bookmark hierarchy preserved; all original university headers and centered page numbers preserved. All 23 internal TOC annotations and 22 bookmark destinations verified against actual heading text. All pages rendered and visually reviewed, with no body overflow. Evidence: output/pdf/verification.json and REVISION_NOTES.md. Not run: manual clicks in every PDF viewer, formal participant evaluation. No application tests rerun because application code/data were unchanged.

## Librarian decisions verification — 2026-10-31

Command: C:\php-8.4.14\php.exe tests/run_regression.php — PASS, 13 scripts, connection-local temporary fixtures.
Command: C:\php-8.4.14\php.exe tests/request_decisions_concurrency_test.php — PASS, four independent-process scenarios: same request, last copy, last student allowance slot and cancellation/approval. Fresh fictional scratch DB retained: kaaklat_decision_test_133cf46ca372. Workers start at a shared scheduled time; no deterministic lock barrier was used.
PHP lint: PASS for all 41 files under app/public/views/tests. git diff --check: PASS, line-ending warnings only.
Service coverage: librarian/student/admin authorization, stale/idle sessions, CSRF, input/duration/note rules, changed duration, approval/rejection, original terms, current student status, overdue/cap/duplicate guards, archived books/copies, expiry reuse, stale decisions, own-history isolation, derived availability, audit-write rollback and omission of sensitive text from audit summaries.
Still pending: new-page authenticated desktop/360px/768px/keyboard checks; HTTP forged POST fields; exhaustive queue pagination fixtures; deterministic contention synchronization. Prior catalog manual acceptance does not verify the new pages.
Local acceptance: active student requests a book; librarian opens Dashboard > Review borrowing requests, searches student/title, approves with changed days and explanation; student checks My borrowing requests for original days/reason, approved days, explanation and Manila pickup deadline. Check catalog availability reduced by one. Repeat with rejection and verify unchanged availability. Refresh old librarian form and confirm stale decision rejected. Student/admin direct /review_requests.php must be denied. Pending students can read own history but cannot submit/cancel. Do not use real student information.


Additional executed check: expiry cleanup followed by injected final approval-audit failure rolls back old loan cancellation, new request decision and expiry audit; subsequent approval succeeds. request_decisions_test.php rerun PASS; its lint PASS.

Documentation check 2026-10-31: PROGRESS.md current-status table compared with latest decision handoff and recorded hosting/manual acceptance evidence; stale counts, approval gates and next actions corrected. No application tests rerun for this documentation-only change.

## User-reported stale-submission acceptance — 2026-11-01
User confirmed the repeated stale-submission test passed after following the same-session two-tab procedure: reject the pending request with an explanation in tab 1, then attempt approval of the same request from the unrefreshed tab 2. Recorded as user-operated acceptance, not agent-observed browser evidence. This confirms only the reported stale-submission scenario; separate changed-duration, student-visible decision and availability checks are not blanket-approved by this report.

## User-reported decision acceptance — 2026-11-01
User confirmed all three remaining targeted checks PASS: changing approved duration requires an explanation; student history preserves original terms and displays approved days, explanation and pickup deadline; approval reduces available copies by one while rejection leaves availability unchanged. Together with the separately reported stale-submission PASS, all four targeted decision checks are now user-reported PASS. These are user-operated results, not agent-observed browser evidence. New-page phone/keyboard acceptance, HTTP forged-field checks and deterministic lock-contention verification remain pending.

## Physical pickup verification — 2026-11-01
New commands: C:\php-8.4.14\php.exe tests/pickups_test.php and C:\php-8.4.14\php.exe tests/pickups_concurrency_test.php. Initial runs PASS; concurrent workers use independent processes with a shared scheduled start (not a deterministic lock barrier). Scratch DB retained kaaklat_pickup_test_53d69f9d4310. Fixtures are temporary tables or a new fictional scratch database; no reset/reimport of demo tables.
PHP lint PASS for 45 files; earlier 13 regression scripts PASS after own-history query changes. Expanded final results follow.
Not verified: new pickup page authenticated desktop/360px/768px/keyboard acceptance, HTTP forged-field end-to-end tests and deterministic contention. Computer Use previously stopped because it could not identify Chrome's URL confidently; no new browser-pass claim is made. Previous decision manual passes remain user-reported passes for those pages only.
Local checks: sign in as librarian > Record physical pickup; search an approved student/title and compare the internal code with the physical simulated copy. Confirm handover; as student check My borrowing requests for active status, pickup time and due date. Catalog availability should stay the same. Submit the old form again: original dates must remain. Expired rows must refuse handover; Release expired allocation only releases/audits the hold. Student/admin direct /pickups.php access must be denied. Returns remain pending.

Final pickup verification: 14 regression scripts PASS after expanded exact-deadline, student history/original-term, duplicate/missing/rejected-allocation and expiry-audit rollback tests. Two pickup race scenarios previously executed PASS. All 45 PHP files lint PASS. Trailing blank lines found by whitespace check were corrected. Finish usage snapshot: 51% five-hour / 74% weekly remaining.

## Student request tabs — 2026-11-01
Implemented All requests/Pending/Rejected/Awaiting pickup/Active. Active includes overdue; closed history remains in All requests. SQL filtering precedes pagination and retains session ownership. Tests cover rejected pagination (22 fixtures, pages return 21 fetch-ahead / 2), awaiting pickup excludes stale holds, empty/positive pending, overdue included in Active, invalid/array status rejection and cross-student isolation. 14-script regression PASS before final test additions; final targeted borrow_requests_test PASS; 45-file lint and changed test lint PASS. No browser/phone/keyboard pass claimed.

Pre-commit verification 2026-11-01: 14 regression scripts and 45-file lint PASS; four decision and two pickup independent-process race scenarios PASS. New retained scratch DBs: kaaklat_decision_test_f1dac2220538, kaaklat_pickup_test_f2d34f1487ca. Automated checks do not replace remaining browser/phone/keyboard/HTTP acceptance.

## Visual refresh verification — 2026-11-01
Shared CSS/layout and dashboard redesigned to school blue/white/gray. 14 regression scripts PASS; 45 PHP lints PASS. Existing behavior tests do not verify rendered appearance. Pending user/browser acceptance: login/register, role dashboards, catalog/details, student request tabs and librarian management/decision/pickup forms at 360/768/1280px, 400% zoom and keyboard-only; verify all text/actions fit and focus remains visible. Previous catalog/decision manual passes precede the redesign and are not visual acceptance for this change. See docs/visual-refresh.md.

Logo update 2026-11-01: blue/white open-book SVG installed in header and favicon. SVG XML validation and shared layout PHP lint PASS; git diff --check PASS. Browser visual/favicon acceptance pending. No functional regression rerun for asset/layout-only update.

## Interface enhancement verification — 2026-11-01
PASS: tests/dashboard_test.php verifies own-only student counts, pending account browsing scope, spoofed session role ignored, librarian totals, administrator/suspended rejection, expired/closed holds excluded and overdue included in active. Temporary fictional tables only.
PASS: tests/request_ui_test.php verifies invalid/array duration/reason, valid terms, status labels, one current progress step, rejected/expired terminal messaging, returned historical progress, escaped field errors and linked summary IDs.
PASS: full tests/run_regression.php (16 scripts); all 49 PHP lints; node --check public/assets/forms.js. No concurrency rerun needed for read-only presentation/count changes. SQL lifecycle and mutation guards unchanged.
NOT VERIFIED: rendered header role links and count/card navigation; actual JS focus and browser constraints; real phone/narrow/zoom/keyboard appearance; screen-reader behavior. Field-specific server mapping added for borrowing/decision forms; older forms gain optional client-native constraint errors and focused server summaries, not comprehensive new server field-error mapping.
Manual next: student/librarian/admin nav, own summary counts including expired/overdue fixtures, click each summary, requested/approved/active/terminal cards, empty tabs, wrong days/reason/note errors with preserved values, error-link focus and rejection without days, then width/zoom/keyboard and JS-disabled server checks. Existing data preserved. The prior four user decision checks precede this interface update.

## Pickup/interface acceptance — 2026-11-02
Executed: 16 regression scripts PASS; isolated real HTTP acceptance (22 assertions) PASS; 51 PHP files lint PASS; forms.js syntax PASS. Detailed evidence and limitations: verification/ACCEPTANCE_2026-11-02.md. HTTP covers workflow, roles/ownership, actual pickup/due date, unchanged availability, expiry, retries/audit, forged fields, retained invalid input and decision error mapping. In-app browser login summary autofocus and Tab/Enter link focus verified; desktop/360/768 login form inspected. Authenticated visual screens, full keyboard/phone and 400% zoom remain NOT VERIFIED. Do not infer whole-site acceptance from login-only screenshots or HTTP assertions. Run HTTP suite separately with explicit local DB access; it creates/retains a new scratch DB and temporary loopback server, never deploy router. Manual checklist in report. Next feature remains returns; no returns implemented.

## User-operated interface acceptance — 2026-11-02
User reported all 12 supplied checklist items PASS: student dashboard/cards; librarian dashboard/forms; summary count links; status/progress/empty states; 360px; 768px; 400% zoom; keyboard navigation; error-summary/field-link focus with JS enabled; invalid student inputs with JS disabled; invalid librarian decisions with JS disabled; retained values. No problems supplied (problem fields blank).
Evidence classification: user-reported manual acceptance, not agent-observed results or independent accessibility certification. These results supersede prior pending statements for the named checklist only. Actual physical-device/browser coverage, screen-reader/contrast audit, hosting, and deterministic concurrency remain separate. No new pickup-specific successful-handover test is inferred beyond prior automated/HTTP evidence.

## Return increment verification — 2026-11-02
PASS: full regression runner (17 scripts), expanded tests/returns_test.php final rerun (temporary tables), tests/returns_concurrency_test.php (independent processes), extended tests/pickup_http_acceptance_test.php (34 HTTP assertions), PHP lint 55 files and JS syntax.
Coverage: librarian-only, CSRF/session/version/idle/inactive actor, invalid record/state, inactive/overdue borrower accepted, archives accepted without making archived titles visible, availability/active_copy_id release, unchanged checkout/due date and terms, repeat actor/time/audit, rollback keeps allocation, search/copy code, filtered 21-fetch/2-second-page pagination, ownership, returned_at/history and derived dashboard active count. HTTP tests also enforce receipt checkbox and reject extra fields, student/admin endpoint access, confirm student rendered return time and queue removal. Race same return commits one audit; return/approval yields one valid new allocation after optional retry. No deterministic contention barrier.
Retained scratch databases: kaaklat_return_test_959263a8e882, kaaklat_return_test_92187100d974, kaaklat_acceptance_1d38e48e288e. No demo mutations. Temporary HTTP server stopped in finally.
NOT VERIFIED: Returns browser visual/360/768/zoom/keyboard acceptance, archive-versus-return independent race and deployment. User-reported interface acceptance remains valid only for older screens. Usage tool unavailable.
Local acceptance: student requests book, librarian approves and records pickup. As librarian open Returns, search student/title/copy code, match physical copy and tick receipt before confirming. Student All requests should show Returned, original details and actual return time; Active tab/count falls, normal copy availability increases one. Reuse an old form: already-returned message, no changed details. Test missing receipt and student/admin URL denial. Repeat new screen narrow/zoom/keyboard. Suspended/overdue borrower still returns. Archived copy remains unavailable. Never change existing records just to make tests pass.

Returned tab 2026-11-02: targeted returns_test PASS verifies completed-only results excluding outstanding records and cross-student isolation. app/borrow_requests.php/public/requests.php lint PASS. Existing pre-pagination lifecycle filtering and pagination status links reused; browser layout not newly verified.

Critique refinement checkpoint 2026-11-02: circulation-first dashboard, historical pickup details, contextual receipt, archive caution, byte-policy guidance/autocomplete and reset links implemented. 17 regression scripts/PHP lint PASS; browser/HTTP new-copy/layout acceptance pending. Remaining items listed in CHANGELOG.md; no behavioral security policy change.

## Final critique refinements — 2026-11-02
18 regression scripts PASS; final HTTP suite 36 assertions PASS. New tests/interface_refinement_test.php validates optional context failure fallback, repeat outcome, output escaping, row error/receipt association and preserved checkbox. HTTP verifies /returns.php?q=ACCEPT-1&page=1 error link/description and checked receipt retained on invalid CSRF; existing role/ownership/forgery/lifecycle checks remain PASS. 57 PHP lints PASS before final test-only assertion addition; modified HTTP runner lint check follows. Scratch kaaklat_acceptance_2afb2ce397ca retained. Browser visual/keyboard/screen-reader and JS-on/off actual focus remain unverified. No behavioral-policy change. Check copy-specific names for pickup/expiry/archive/return; error summary links to receipt or focusable loan row only when that row is visible, otherwise generic summary without broken anchor. Optional display lookup failure unit-tested; successful receipt must remain success. Manual acceptance pending; do not claim no possible bugs.

## User-reported refinement acceptance — 2026-11-02
User reported all nine supplied checks PASS: regression suite, HTTP checks, return error/field link, search retention, successful return/history, contextual accessible action names, archive appearance, unavailable-book message and login recovery guidance. Problems observed left blank. Classification: user-operated/user-reported results, not a fresh agent test run. Supersedes prior pending status for these named checks only; broader screen-reader/device/accessibility and hosting verification remain separate.

Commit preparation 2026-11-02: fresh 18 regressions, two return concurrency scenarios and 36 HTTP assertions PASS. Initial HTTP startup failure corrected with bounded readiness polling instead of 500ms sleep. This is a test harness fix; app behavior unchanged. Scratch DBs retained; no demo reset. User authorized commit/push excluding output/ research artifacts.

## Local service/documentation housekeeping — 2026-11-02
Read-only configured PDO connection PASS; MariaDB 10.4.32 on port 3306. PHP port 8000 was not listening; started existing local server without deleted log redirections. GET /health.php HTTP 200 with status ready; GET /login.php HTTP 200. No database restart, import, configuration change, schema change or demo mutation. Earlier unavailable result not reproduced; original cause not established. Current SRS/progress summaries synchronized with recorded scope. No new regression, concurrency or visual pass inferred.

## Approved-uncollected cancellation — 2026-11-02
Implemented student-own and active-librarian cancellation in app/approved_cancellation.php, student history and existing librarian pickup queue. Librarian explanation 1–500 characters; students pending/active may cancel their own approval as permitted by the role matrix; suspended sessions remain denied, while librarians may cancel their allocations. UTC actor/time/kind/note stored in existing loans fields; approval/terms/history retained. Expired holds use expiry state/audit, not voluntary cancellation. No migration. Sensitive explanations never included in audit payloads. Existing user/book/copy/request/loan order and transactional retries retained.
Verification: 19-script regression PASS; expanded cancellation temporary-fixture test PASS; three independent-process races PASS (duplicate cancellation, cancellation/pickup, cancellation/approval), shared scheduled start rather than deterministic lock barrier. Scratch kaaklat_cancel_test_c68d3cdb03f1 retained. Extended HTTP suite 45 assertions PASS: roles/CSRF, forms, field link, escaped retained explanation, search context, staff/student cancellation, history, released availability and refused pickup. Scratch kaaklat_acceptance_45781cfe6e84 retained; test server stopped. PHP lint 60 files PASS; whitespace check PASS. Tests preserve demo data. No browser-control tool exposed in this session; new visual/360/768/400%/keyboard checks NOT VERIFIED. Prior user passes do not cover these new controls. Archive-versus-cancellation race not executed.
Manual: student requests fictional title; librarian approves; student Awaiting pickup > Cancel allocation. Confirm closed history in All requests, original terms preserved, allowance/pickup count decreases and copy becomes available. Repeat with librarian Pickups > Cancel this allocation; blank explanation must fail with link/retained search, valid explanation visible only to owner/staff. Try old pickup form after cancellation and cancellation after pickup: both refused. Repeat controls at narrow sizes/zoom/keyboard. No new cancellation tab; closed records remain in All requests.

## Regression verification — 2026-11-03 (Asia/Manila)
Executed against current uncommitted work: tests/run_regression.php PASS (19 scripts); tests/approved_cancellation_concurrency_test.php PASS (three independent-process races: duplicate cancellation, cancellation/pickup, cancellation/approval); tests/pickup_http_acceptance_test.php PASS (45 assertions). PHP lint PASS (60 files); node --check public/assets/forms.js PASS; git diff --check PASS (line-ending normalization warnings only). Temporary fixtures/new fictional scratch databases only; no demo reset/import/deletion. Retained kaaklat_cancel_test_1f8e07072496 and kaaklat_acceptance_5208de6ed0fb; temporary HTTP server stopped by harness. No defect detected by these checks. Visual/keyboard/new cancellation manual acceptance, deterministic lock-barrier testing, archive race and hosting not tested. No commit/push.

## User-reported cancellation acceptance — 2026-11-03
User reported: "visual and keyboard acceptance all passed" following the approved-uncollected cancellation checklist and regression verification. Recorded as user-operated visual/keyboard acceptance for the new cancellation controls; not agent-observed verification or full accessibility certification. No problems reported. Supersedes pending visual/keyboard acceptance statements for these controls only. Hosting, broader device/screen-reader/contrast verification, deterministic lock barriers and archive race remain separate. No code change or automated rerun. Next functional increment: administrator staff-account management under the SRS; hosting deferred.

## Staff management verification — 2026-11-03
Implemented app/staff_accounts.php and public/staff.php, admin navigation/dashboard, CLI bootstrap/private Windows password input and optional interactive mode in existing password tool. No migration/demo provisioning. Temporary-fixture staff tests cover roles, idle/stale/suspended actors, CSRF, canonical duplicates, disabled hash creation, invalid roles/student conversion refusal, last usable admin (NULL hash excluded), stale updates/retry, atomic audit failure, reset revocation, search/21-fetch-ahead pagination and self-demotion session removal. Bootstrap validation/duplicate refusal and successful creation tested only in temporary/scratch fixtures. Two independent-process races PASS: simultaneous bootstrap and competing self-suspension preserving one usable admin; shared scheduled start, not deterministic lock barrier. Scratch kaaklat_staff_test_5d0d91c0379f retained. Extended HTTP suite 56 assertions PASS in scratch kaaklat_acceptance_e67a5ac88c78; temporary server stopped. Initial HTTP staff test encountered router allowlist omission, corrected and rerun PASS. Existing pre-change 20 regression scripts passed; final regression/lint rerun below. Browser tools unavailable: new staff visual/keyboard/narrow/zoom and actual Windows console secret-entry acceptance NOT VERIFIED. Bootstrap not executed on demo DB. Next secure staff-assisted account recovery after acceptance.

Final staff verification: 20 regression scripts PASS after password/reset changes; 70 PHP files lint PASS (app/public/views/tests/bin); 56 HTTP assertions and two staff races PASS as recorded above. Latest presentation-only missing-row error-link guard and private-helper UTF-8 output change linted separately; real console entry remains unverified.

## User-reported administration acceptance — 2026-11-03
User reported: "The administration testing all passed", referring to the supplied staff-account checklist: administrator page, fictional librarian creation, private password entry, librarian login and staff-page denial, suspension/session revocation, suspended-login denial, reactivation/fresh login, 360px/768px, 400% zoom and keyboard/validation. Recorded as user-operated acceptance, not agent-observed browser results or full accessibility certification. No problems reported. This supersedes earlier pending UI/private-console acceptance for these named checks only. Bootstrap was observed by the user to refuse because an existing usable administrator was present; successful production/demo bootstrap is not inferred. No code changes or automated rerun. Next: secure staff-assisted account recovery; hosting remains deferred. No commit/push.

## Implemented-feature audit — 2026-11-03
20 regression scripts, six concurrency suites/15 independent-process scenarios, 81 HTTP assertions, 71 PHP lints, JS syntax and whitespace checks PASS. Read-only demo integrity counts all zero for mismatched loan/copy title, nonapproved loan and duplicate unresolved copy. Fixed confirmed embedded-NUL password validation error; new failing-before/passing-after unit test and HTTP registration coverage. Expanded registration/student activation/catalog multipart/cover/archive/logout HTTP checks. Representative coverage, not proof of no bugs. Administrator student activation/suspension remains an SRS gap (existing page/service librarian-only); recovery/admin audit browsing unfinished. No new browser tools available, no fresh visual/keyboard acceptance. Detailed evidence, retained scratch DBs and limitations: verification/AUDIT_2026-11-03.md. Demo data preserved; no commit/push; hosting deferred.

## Account-access verification — 2026-11-03
Implemented administrator student activation/suspension/reactivation through dedicated authorization (shared librarian catalog guard unchanged); staff-assisted issuance/redemption and admin read-only audit metadata. New tests/account_access_test.php uses temporary tables and checks admin activation, CSRF/identity/role/session/version, digest storage, reissue, expiry, replay, status preservation, issuance/redemption audit-failure rollback, audit authorization/filter/pagination and exclusion of summary fields. Full regression runner now 21 scripts PASS. Two independent-process recovery races PASS: same-secret redemption and reissue/redemption; shared scheduled starts, not deterministic lock barriers. Scratch kaaklat_recovery_test_d362ca216926 retained.
Extended HTTP suite 101 assertions PASS in kaaklat_acceptance_0f966f5250bc: admin student actions, privileged URL guards, librarian staff-recovery denial, identity/CSRF, delivery/digest match, one-time display, secret not reflected in invalid forms, password mismatch, redemption/replay/new-password sign-in, audit role/read-only/filter/forged-page restrictions and sensitive fixture-summary exclusion. Initial test token lookup on empty student list corrected to reuse its valid existing administrator session token. Test server stopped. Previous circulation/catalog/staff checks remained PASS. No demo reset/import/deletion or real-account recovery.
New pages visual/keyboard/360/768/400%/JS-disabled acceptance NOT VERIFIED; no browser-control tools exposed. User-reported older page checks remain scoped to those pages. Hosting still deferred; real hosted secure-cookie/HTTPS acceptance unverified. Final lint/whitespace result follows. Manual checklist in WORKFLOW.md. Next: account-access manual acceptance, then outstanding SRS backup/restore, performance and deployment/evaluation work.

Final account-access syntax checks: 79 PHP files PASS; forms.js syntax and whitespace checks PASS. Student manager idle-session validation added to match other mutations; existing isolated fixture sessions updated to supply last_activity; affected final regression rerun follows.

Final rerun after student-manager idle guard: all 21 regression scripts PASS.

## Full local system verification — 2026-11-03
Fresh current-tree run: 21 regression scripts, all seven concurrency suites/17 scenarios, 101 HTTP assertions, 79 PHP lints, JS syntax and whitespace PASS. Actual local health ready HTTP200. Five read-only demo integrity counts zero, including due-date equality and missing approved allocation. No new defect detected by executed checks; no application fix required. Demo data/current work preserved; no commit/push. Evidence, scratch names and limits in verification/FULL_LOCAL_CHECK_2026-11-03.md. New recovery/audit/admin-student visual/keyboard acceptance, hosting, performance and restore rehearsal still unverified; no guarantee of zero bugs. Next manual acceptance then operational SRS verification.

## Shared interface refinement — 2026-11-03
Impeccable-guided blue/white/gray refinement applied through shared CSS/layout and dashboard/auth presentation; no business rules, routes, submitted fields, schema or demo data changed. Desktop sidebar, wrapping mobile/tablet navigation, consistent type/spacing/forms/tabs/status/caution states and quieter task surfaces. Final headless92 captures across23 role/page combinations at1366/768/360/320 CSS px: no horizontal overflow. Actual invalid-request summary autofocus and Tab/Enter link-to-reason PASS. Nine principal color pairs >=4.5:1. Representative screenshots inspected in two bounded rounds. 21 regression scripts and101 HTTP assertions PASS. Broader manual/400%-zoom/device/screen-reader/JS-off acceptance not inferred. Impeccable engine/detector unavailable; references used directly. Full evidence: verification/DESIGN_REFINEMENT_2026-11-03.md. Hosting deferred; no commit/push. Next user visual acceptance, then outstanding operational SRS checks.

## Landing imagery/motion — 2026-11-03
Signed-out welcome redesigned with two optimized local generated photographs (illustrative, not school photos), login/register actions, four-step borrowing guide and bounded700ms hero animation. Dedicated landing view/scoped CSS; authenticated dashboard branch retained, no new JS or business/data/schema change.21 regression scripts and104 HTTP assertions PASS; four-width headless renders without horizontal overflow, image loading, reduced-motion:none, JS-disabled visibility and keyboard skip-to-main PASS. Images total218930 bytes; source provenance in docs/landing-images.md. Lower lazy-loaded image visually confirmed after scrolling. No actual400%/real-device/full accessibility certification claim. Evidence verification/LANDING_2026-11-03.md. Hosting deferred; no commit/push.

2026-11-03: temporary tmp/design-review artifacts deleted at user request after runtime reference check. Earlier review results remain historical evidence; raw screenshots/metrics there no longer retained. Public website assets preserved.

## Commit preparation — 2026-11-03
User authorized commit/push of all today's implementation. Fresh21-script regression,104 HTTP assertions,80 PHP lints, JS syntax and all seven concurrency suites/17 scenarios PASS. Scratch names: request_2d69cb8e1fdd, decision_407db3aef9fe, pickup_986ac7bd3cf3, return_6133309bf66d, cancel_871d488ba947, staff_4c3a21d13c1b, recovery_54d0d2682b23 (each with existing kaaklat_*_test_ prefix); HTTP kaaklat_acceptance_ff72fd321a80. No demo reset/import. Scope includes staff/admin student access, recovery/audit, UI/landing imagery, tests/docs and confirmed NUL validation fix. Private runtime files and separate output/ research artifacts excluded. Earlier no-commit notes are historical; final commit/push result verified through Git separately.
