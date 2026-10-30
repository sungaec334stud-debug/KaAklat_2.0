# KaAklat test plan

Version 0.1, 2026-10-29. Application scenarios are NOT RUN. SQL checks, if executed, appear in the evidence section and do not count as application passes.

## Test environments and evidence

Use only isolated databases and fictional users. Planned roles: pending student, active student A/B, suspended student, librarian A/B, administrator. Never share usable demo passwords in this file. Store runtime secrets privately and set up accounts through a secure bootstrap when implemented.

Record test date, exact code/schema revision or SHA256, database version, environment, test steps/command, actual result, and evidence path. Status values: Not run, Pass, Fail, Blocked. Pass requires execution, not source review. Changed code invalidates affected results until rerun.

## Functional scenarios

| Test | Requirements | Scenario and expected outcome | Actual result |
| --- | --- | --- | --- |
| T-01 | FR-01, FR-02, NFR-01 | Register with forged role=administrator/status=active. New account is student/pending; login permits catalog, request denied. Duplicate canonical username rejected. | Partially passed: registration/login happy path only; forged fields and duplicate cases not run |
| T-02 | FR-01, NFR-01 | Correct/incorrect passwords, logout and idle session. Generic failure; success regenerates session ID; logout and >30min idle deny reuse. | Not run |
| T-03 | FR-02, NFR-02 | Student B requests student A's history/decision endpoints; signed-out visitor requests catalog and cover. Denied without leaking records/image. | Not run |
| T-04 | FR-02 | Student/librarian attempt staff creation; admin attempts pickup/return. All denied. Last active admin cannot be removed even by two concurrent updates. | Not run |
| T-05 | FR-01, FR-02 | Librarian activates student, suspends after login. Subsequent protected access denied; librarian still able to record student's return. | Not run |
| T-06 | FR-03, NFR-04 | Search title/author, no matches, apostrophe/Unicode, availability filter, page beyond end. Correct bounded results and readable empty/error states. | Not run |
| T-07 | FR-04, NFR-03 | Create title with two authors and 3 copies. Three distinct codes, correct author links, available count 3. Reject invalid/huge/negative counts and blank title/authors. | Not run |
| T-08 | FR-04, NFR-01 | Upload valid JPEG/PNG then SVG, renamed PHP, invalid image, >2MB image, excessive pixel dimensions, and traversal filename. Only safe input accepted/re-encoded; random path; no execution/public bypass. | Not run |
| T-09 | FR-04, NFR-03 | Archive available copy, copy with live loan, title with pending request, title with live hold. Only permitted archive succeeds; blocked actions preserve history and counts. | Not run |
| T-10 | FR-05 | Eligible student submits valid request; try days 0/31/fraction, reason <10/>500, unavailable title, sixth pending and duplicate title. Only valid request accepted; no partial records. | Not run |
| T-11 | FR-06 | Librarian approves 7-day request for 10 days with note; rejects another with note. Original request retained; approved duration/decision visible; missing required note rejected. | Not run |
| T-12 | FR-05, FR-06 | Student cancels own pending/approved-uncollected request; tries another's or picked-up request. Allowed cancellations release allocation; others fail. | Not run |
| T-13 | FR-06, FR-07 | At pickup deadline minus 1 second allow eligible pickup; at exact deadline refuse. Expired record retains history and frees availability; cleanup repeat is harmless. | Not run |
| T-14 | FR-07, FR-09 | Pickup near Manila midnight, month/year boundary, and leap day. Due date is Manila pickup date + approved days; end of due date not overdue; following day overdue. | Not run |
| T-15 | FR-07 | Four active loans + one approved hold allow only pickup of existing hold; any new approval denied at five. Repeat pickup doesn't create/change another loan. | Not run |
| T-16 | FR-08, FR-09 | Return once and repeat; return while student suspended or overdue. One return event and correct availability; no fine, duplicate loan or deleted history. | Not run |
| T-17 | FR-05, FR-06, FR-07 | Existing overdue loan prevents new request, approval and pickup; return remains possible. After actual return eligibility restored if otherwise valid. | Not run |
| T-18 | FR-09 | Pending, approved, expired, cancelled, rejected, active, overdue and returned states render accurately. Student sees own reason; other students/admin-only view do not. | Not run |
| T-19 | FR-10 | Exercise each account/catalog/circulation mutation. Correct actor/time/target audit, committed with change. Inspect logs: no passwords, tokens, session IDs or reason text. | Not run |
| T-20 | FR-12, NFR-01 | Staff issues reset after identity check. Valid token works once within 30min; expired/reused token fails. Password changes; other tokens and old sessions revoked; unknown account errors generic. | Not run |

## Security and concurrency scenarios

Use independent database connections or processes with a barrier so operations truly overlap. The Windows PHP built-in web server is single-process and cannot by itself prove simultaneous HTTP handling. Use direct database integration tests for locking and an appropriate concurrent server/host for endpoint races. Separate browser tabs alone are not evidence that transactions overlapped.

| Test | Requirements | Scenario and expected outcome | Actual result |
| --- | --- | --- | --- |
| T-21 | FR-06, NFR-03 | Two librarians approve different requests for one copy. Synchronize after both intend the copy. Exactly one live allocation, loser has clear conflict, no orphan audit/decision. | Not run |
| T-22 | FR-06, FR-07, NFR-03 | Student has 4 allocations; two librarians approve different copies simultaneously. At most 5 live allocations, one approval refused after serialized check. | Not run |
| T-23 | FR-06, FR-07, NFR-03 | Expiry/cancellation races physical pickup. One valid outcome; never active loan and released copy simultaneously. Lock order avoids unhandled deadlocks; retry is bounded. | Not run |
| T-24 | FR-08, NFR-03 | Two simultaneous returns / repeat POST after network uncertainty. One persisted return and one audit event; repeat reports previous completion. | Not run |
| T-25 | FR-04, FR-06, FR-10, NFR-03 | Force failure after proposed mutation but before audit, or before completion of copy creation. Rollback removes all partial changes; previous image stays usable. | Not run |
| T-26 | NFR-01, NFR-02 | SQL injection values in login/search/sort; stored script in title/author/reason; missing/foreign CSRF; GET mutation. No executable output, query change or unauthorized mutation. | Not run |
| T-27 | NFR-01 | Six failed logins in rolling 15min, known and unknown username, including parallel failures. Account key serialized; cooldown enforced, generic response, no bypass via case changes. | Not run |
| T-28 | NFR-01, NFR-02 | Fixation attempt; role/password/status change with another session active; inspect HTTPS cookies. Old session denied, ID rotated/revoked, required cookie attributes present. | Not run |
| T-29 | NFR-01, NFR-02, NFR-08 | Request config, SQL, logs, backup, upload raw path, diagnostic URL; force production error. Private files inaccessible; safe error only; removed diagnostics absent. | Not run |

## Interface, performance, hosting and recovery

| Test | Requirements | Scenario and expected outcome | Actual result |
| --- | --- | --- | --- |
| T-30 | NFR-04, NFR-05 | Complete core forms using keyboard at widths 360/768/1280 and 400% zoom; inspect labels, focus, error association, status contrast, alt text. No inaccessible controls or clipped essential content. | Not run |
| T-31 | NFR-04 | Observe actual planned participants completing tasks. Record failures/help/time without inventing responses; interview notes and questionnaire scores kept distinct. | Not run |
| T-32 | NFR-06 | Use 100 titles/300 copies/30 student accounts/3 staff and 5 concurrent users. Record >=30 local warm response samples and >=20 hosted navigations. Compare p95 local <=2s and hosted target <=5s, with network/device/failures recorded. | Not run |
| T-33 | FR-11, NFR-07 | Export SQL/files/private config, restore into separate empty local DB, validate counts, FK links, history, cover files and sign-in. Record completion <=30min and backup age <=24h or explain failure. | Not run |
| T-34 | NFR-08 | Hosted smoke test: direct HTTPS address, redirect, PHP/PDO/GD, assigned DB version, InnoDB/FK/CHECK/UNIQUE/rollback and safe private paths. All needed capabilities work; diagnostics removed. | Not run |
| T-35 | FR-11, NFR-07, NFR-08 | Rehearse maintenance deployment and rollback with a transaction created after initial backup. Preserve it or reconcile explicitly; no blind overwrite of current loans. | Not run |
| T-36 | NFR-08 | Real desktop and phone browser with host JS/cookie check. Login, catalog, request, staff circulation work; blocked clients and outages documented rather than counted as passes. | Not run |

## Requirement coverage

FR-01: T01/02/05; FR-02: T01/03/04/05; FR-03: T06; FR-04: T07/08/09/25; FR-05: T10/12/17; FR-06: T11/12/13/17/21/22/23; FR-07: T13/14/15/17/22/23; FR-08: T16/24; FR-09: T14/16/18; FR-10: T19/25; FR-11: T33/35; FR-12: T20.

NFR-01: T01/02/08/20/26/27/28/29; NFR-02: T03/18/26/28/29; NFR-03: T07/09/21/22/23/24/25; NFR-04: T06/30/31; NFR-05: T30; NFR-06: T32; NFR-07: T33/35; NFR-08: T29/34/35/36.

## Executed evidence

No complete application scenario above has been executed.

### Local account bootstrap and authentication check - 2026-10-30

- **Environment:** Local PHP 8.4.14 CLI, MariaDB 10.4.32 on `kaaklat_dev`.
- **Action:** Executed `bin/set_user_password.php demo_admin AdminPass1234!` and executed `tests/auth_integration_test.php`.
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
