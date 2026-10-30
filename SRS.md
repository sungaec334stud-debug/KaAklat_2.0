# KaAklat Software Requirements Specification

Version: 0.1 design draft, 2026-10-29 (Asia/Manila).
Status: Stack A selected; core workflow confirmed; remaining defaults below are proposals for review. This is a specification, not proof of implemented functionality.

## 1. Purpose and problem

Build a small hosted demonstration of a school library workflow that a beginner can understand and maintain. Students need to find physical books, request a borrowing period with a reason, and track decisions and loans. Librarians need reliable records for cataloging, approval, physical pickup and return. An administrator manages website accounts and access. The research paper must accurately distinguish planned work, implemented work and measured results.

The project addresses the proposed need for coordinated catalog and circulation records. Actual problems at the school, and any claimed time savings or error reductions, still require school evidence; they are not established by this specification.

### Objectives and success criteria

1. Demonstrate the complete student-request -> librarian-decision -> pickup -> return workflow on a free HTTPS address using fictional student data.
2. Reject unauthorized actions, a sixth allocation, duplicate checkout, and concurrent allocation of one copy; preserve loan history.
3. Support catalog entry without ISBN or scanning hardware, including multiple authors and individually tracked physical copies.
4. Make essential tasks usable at phone and desktop widths with keyboard access.
5. Restore a documented local backup, and record real test evidence in TEST_PLAN.md before describing a feature as verified.
6. Keep the research chapters consistent with the final scope. Planned evaluation of approximately 20 students and a small librarian group is not a completed study or an approved sampling design.

Presentation: approximately November 2026, exact date unknown. The supplied Chapters 1-2 PDF is the required paper template.

## 2. Approaches and selected stack

Two approaches were presented: A, plain PHP/PDO with relational storage, semantic HTML, custom CSS and minimal JavaScript; B, the same backend with Bootstrap for layout/forms. The user selected A on 2026-10-29. A keeps the code and dependencies small; responsive styling must be built and tested explicitly.

Use PHP 8.4 locally via `C:\php-8.4.14\php.exe`, XAMPP's database service after verifying its actual version, and portable InnoDB SQL for MariaDB 10.4+ / MySQL 8.0+. XAMPP's bundled PHP is a different installation (8.2.12); do not accidentally use it for version-specific checks. PHP PDO is the built-in database interface. No application framework, Composer, npm, paid API or build tool is required.

InfinityFree is the recommended free demonstration host, not yet selected through account registration or runtime testing. Its documented PHP/database support must be verified on the assigned account before relying on it. See STACK_AND_HOSTING_REVIEW.md and DEPLOYMENT.md. Provider terms may change; no unlimited capacity or permanent free-service guarantee is assumed.

Organize small HTTP entry pages under `public/`, shared security/database/business functions under `app/`, HTML templates under `views/`, and private configuration outside the public tree. Root `schema.sql` and `seed.sql` are initial database artifacts; subsequent changes are numbered SQL migrations. Do not create a framework-sized abstraction layer. Route naming, visual style and minor component structure are implementation decisions within this design.

## 3. Confirmed MVP and exclusions

User extension (2026-10-30): book records include an optional primary genre/category and year first published. Librarians can select/type category names and edit the year; blank year means unknown, valid years are 1 through the current year. The catalog displays both and combines category, A–Z displayed-title initial, title text, author text and exact-year filters with pagination retaining selections. Publication year refers to the original book publication rather than the displayed cover edition. These additions extend FR-03/FR-04; ISBN remains excluded.

Confirmed by the user:

- Three distinct roles: student, librarian, administrator.
- Catalog browsing requires login. Students register themselves; a librarian activates borrowing access.
- Students request one book and a number of days, explaining the reason. Librarians approve/reject and decide the allowed duration.
- Physical pickup starts the loan. Only librarians record pickup and return.
- No more than five approved pickups plus active loans for a student.
- Catalog entry includes title, authors, brief description, book cover and copy count. No ISBN field is required. Adding a count creates separate physical-copy records automatically.
- A hosted demonstration, with possible future school adoption after separate review.

The smallest useful MVP includes login/account lifecycle, restricted catalog and cover management, request/decision/pickup/return, own history, librarian outstanding/overdue lists, essential audit records, and manual backup/restoration. These lists support the main workflow; there is no separate reporting dashboard or report-export subsystem.

Excluded now: monetary fines, barcode/QR/RFID scanning, ISBN lookup, reservations or FIFO waiting queues for unavailable books, renewals, email/SMS/push reminders, imports, advanced statistics/export reports, e-books, online payments, native apps, offline synchronization, multiple branches, and unrestricted file uploads. Faculty-specific accounts are deferred; the demonstrator uses the three confirmed roles.

Automation means automatic availability, allowance checks, due-date calculation, overdue identification and record updates. Approval, handover and receipt remain human librarian decisions. An approved copy held for pickup is part of a current request; it is not a waitlist for an unavailable book.

## 4. Roles and permissions

An administrator is not automatically a librarian. Permission checks run on the server for every protected page and action. UI visibility alone is not authorization.

| Action | Signed out | Student pending activation | Active student | Librarian | Administrator |
| --- | --- | --- | --- | --- | --- |
| Register student / sign in / redeem own reset secret | Yes | Yes | Yes | Sign in/reset | Sign in/reset |
| Browse catalog, cover and availability | No | Yes | Yes | Yes | Yes |
| Request a book | No | No | Own, if eligible | No | No |
| View student request/loan details | No | Own | Own | Operational records | No, except minimum audit metadata |
| Cancel pending/uncollected request | No | Own if existing | Own | Any with reason | No |
| Approve/reject, record pickup/return | No | No | No | Yes | No |
| Add/edit/archive catalog and physical copies, upload cover | No | No | No | Yes | No |
| Activate/suspend student borrowing account | No | No | No | Yes | Yes |
| Create/manage librarian/admin accounts | No | No | No | No | Yes |
| Issue recovery secret | No | No | No | Student accounts | Student or staff accounts |
| Read audit records | No | No | No | Own circulation event confirmation | Yes |
| Deploy/export/restore full backup | No | No | No | No | Trusted operator with hosting access, outside website UI |

Pending users can browse but cannot request. Suspended users cannot sign in; suspension must not prevent a librarian recording their returns. Preserve the last active administrator; do not allow self-demotion/suspension to remove all administrators. Staff account changes must be audited. Initial admin provisioning is a secure local/operator step, not a public registration option or a seeded shared password.

## 5. Functional requirements and acceptance criteria

2026-10-31 implementation update (supersedes older implementation summaries): FR-05 pending submission, own list and pending cancellation implemented with local automated evidence; approved cancellation remains pending. P-06 requiring a currently available copy explicitly approved by user for demo today. Five live allocations block additional submissions; pending consumes only the separate pending cap. Expired holds are excluded logically; no allocation history changed by request submission.


| ID | Requirement | Testable acceptance |
| --- | --- | --- |
| FR-01 | Register, sign in/out and maintain account status. | Registration creates a student with pending status regardless of submitted role/status. Duplicate canonical username is rejected. Pending student can browse; cannot borrow. Logout invalidates the session. Suspension prevents subsequent protected requests. |
| FR-02 | Enforce roles and record ownership; administer staff. | A student changing a URL/POST ID cannot view/change another student's history. Librarian cannot create admin accounts. Admin cannot execute circulation endpoints. Last-admin protection holds under concurrent account changes. |
| FR-03 | Search/filter catalog and view book details. | Logged-in users can search title/author, filter available books and paginate. Details show title, authors, description, cover/placeholder, total active copies and available count. Signed-out requests including cover URLs do not reveal protected catalog content. |
| FR-04 | Catalog titles, authors and copies; safe cover handling. | Librarian enters N copies and gets N uniquely coded copy records. Changing a title never changes existing loan ownership. Available specific copies can be archived; live copies cannot. Multiple authors are separate rows. Invalid covers are rejected without storing executable content. |
| FR-05 | Create/cancel requests and show state. | Eligible student supplies requested days and reason; invalid/duplicate/unavailable requests fail with a useful message. Student can cancel own pending or uncollected approved request. No cancellation after pickup. |
| FR-06 | Decide requests and allocate copies safely. | Librarian approves chosen available copy and allowed days, or rejects with note. Requested terms remain visible. Different approved days require a note. Approval checks student status, cap, overdue state, request state, and copy availability in one transaction. Expired pickup releases the copy safely. |
| FR-07 | Record physical pickup and due date. | Only an unexpired approved allocation can be picked up; librarian rechecks eligibility. Due date uses actual Manila pickup date plus approved whole days. Sixth allocation is refused even with two librarians acting together. Duplicate pickup never creates a second loan. |
| FR-08 | Record physical return once. | Librarian confirms the copy and return. Return records actor/time, releases availability and retains history atomically. Retrying return reports already returned without duplicating changes. Suspended/overdue students can return books. |
| FR-09 | Show own history and operational loan lists. | Student sees only their requested terms, decision, pickup deadline, current due date and return history. Librarian can find outstanding/overdue loans and borrower. An active loan past its due date is marked overdue; returned/uncollected records are not. |
| FR-10 | Record essential audit events. | Persist actor, action, affected identifiers, timestamp and outcome for account/role changes, catalog changes, decisions, pickup, return and reset issuance/redemption. Successful mutations and their audit record commit together. Logs contain no passwords, reset secrets, session IDs or borrowing reasons. |
| FR-11 | Document and perform backup/recovery. | Operator exports database plus code/config/cover dependencies to private off-host storage and restores to a separate empty local database. Compare record counts, foreign-key integrity, sample history and cover retrieval; record elapsed time and result. |
| FR-12 | Recover accounts without email. | Authorized staff verify identity in person, issue a random single-use secret, store only its hash, and allow a password change within 30 minutes. Successful reset invalidates existing sessions and other outstanding secrets. Expired/replayed secret cannot change a password. Staff cannot view current passwords. |

## 6. Business rules

Confirmed rules have prefix C. P identifiers are retained for traceability. On 2026-10-31 the user approved these demonstration defaults: 1–30-day requested/approved periods; five pending requests; one outstanding request/allocation/loan per student/title; a 48-hour pickup deadline; overdue blocking of requests/approval/pickup while returns remain allowed; five combined approved pickups and active loans. This approves C-01, P-01, the duration clause of P-02, deadline clause of P-03 and overdue-blocking clause of P-05. Other P-rule details remain proposals. These decisions do not establish school policy or prove implemented circulation.

| ID | Rule |
| --- | --- |
| C-01 | Maximum five combined unexpired approved pickups and active unreturned loans per student. Pending requests do not consume this borrowing allowance. |
| C-02 | Librarian decides permitted duration; student supplies reason and requested duration. Loan begins at physical pickup, not approval. |
| C-03 | Librarian records pickup and return. Students cannot mark their own books returned. |
| C-04 | No fines, barcodes or ISBN requirements. Catalog access requires login. |
| P-01 | At most five pending requests and one outstanding pending/approved/active request for the same title per student. One request is for one physical copy. |
| P-02 | Requested/approved durations are integers from 1 through 30 days. Reason is 10-500 characters. Rejection or changed duration requires a decision note, 1-500 characters. |
| P-03 | Approval allocates a copy for 48 hours. At the deadline it expires. Student or librarian may cancel before pickup; librarian provides a reason. Expired/rejected/cancelled requests are retained; a new request is required. |
| P-04 | Compute overdue from Manila calendar date: pickup date + approved days is the due date, and the entire due date is allowed. An unreturned loan is overdue starting the following day. Store event timestamps in UTC, display Asia/Manila, and never derive school dates from host timezone. |
| P-05 | An overdue active loan prevents a new request, approval or pickup. It never blocks a return. Suspended students cannot pick up; librarian can cancel their uncollected allocation. |
| P-06 | Requests can be submitted only if a copy is presently available; pending requests do not reserve a copy. Availability at request time is not a guarantee of later approval. |
| P-07 | Expired uncollected allocations are released during ordinary actions, not by a background timer. Availability/allowance queries treat expired holds as unavailable to the old requester and available for reallocation even before cleanup. Any write targeting a stale allocation must cancel/release it transactionally before reuse. |
| P-08 | Catalog creation needs at least one author, title 1-255 characters, a required description 1-2000 characters, and copy count 1-100 per operation. Multiple authors are linked separately; identical names are not assumed to be identical people. Generated copy codes are unique internal identifiers. |
| P-09 | Cover is optional with placeholder. Accept staff uploads only: decoded JPEG/PNG, <=2 MB input and <=4000x4000 pixels, re-encoded under random server filenames. Do not trust extensions or client MIME types. Cover files are served through authorized access, not a public static bypass. |
| P-10 | Archive instead of deleting referenced books, copies or accounts. Do not archive a title/copy while a live allocation exists, or a title with pending requests; first resolve those requests explicitly. No physical deletion through the MVP UI. Correcting titles/names updates current history display; significant changes are audited, not represented as immutable bibliographic snapshots. |
| P-11 | Username is a unique normalized lowercase ASCII identifier, 3-32 characters using letters/digits/underscore. Collect display name and password; no email, address, birthday or phone is required for this demo. Staff verify activation/recovery against the demonstration identity roster; real-school identity processes require separate agreement. |

Durations and pickup deadlines are deliberately separate. Approval displays allowed days and the pickup deadline. The actual due date is calculated at handover; do not promise an approval-time date that changes silently later.

## 7. Nonfunctional requirements

| ID | Measurable requirement and evidence |
| --- | --- |
| NFR-01 Security | Use password_hash(PASSWORD_DEFAULT), password_verify and rehash when needed; accept 12-72 byte passwords without arbitrary composition rules. Use parameterized queries and allowlisted sort names, server validation and context-appropriate HTML escaping. Every mutation uses POST plus CSRF token. Regenerate session ID at login and invalidate sessions after reset/role/status changes; check database role/status/session version on each protected request. Idle timeout 30 minutes; HTTPS cookies Secure, HttpOnly, SameSite=Lax. Five failed logins per normalized username in 15 minutes trigger cooldown, including unknown usernames, with generic errors. Record tests for injection, stored XSS, CSRF, role/ownership bypass, session revocation, throttling and upload rejection. Do not log secrets. |
| NFR-02 Privacy | Zero borrower/request details exposed to signed-out or other-student sessions in access tests. Covers/catalog also require authentication. Do not expose database dumps, private configs, debug traces or credentials through HTTP or Git. Use synthetic identities and request reasons for the demo. Borrowing reasons appear only to the owner and librarian, not audit logs or admin-only pages. |
| NFR-03 Integrity | InnoDB foreign keys, unique copy allocation, valid date/status constraints plus transactional service checks. Two concurrent approvals for one copy produce exactly one live allocation. Two approvals when a student already has four allocations produce at most five total. Expiry vs pickup and return vs retry produce one valid final state; rollback leaves no partial loan/audit. Preserve all loan history. |
| NFR-04 Usability | Registration, request, approval, pickup and return forms have clear labels, inline errors and confirmation. Preserve nonsensitive input after validation errors. Record observed task completion with the planned evaluators; do not invent completion times or declare acceptability before measurement. Date displays include readable day/month/year and timezone context where useful. |
| NFR-05 Accessibility | Target applicable WCAG 2.2 AA criteria: keyboard-only completion, visible focus, programmatic labels/errors, semantic headings, contrast >=4.5:1 for normal text and >=3:1 for large text, no color-only status. Check layouts at 360/768/1280 CSS pixels and zoom/reflow at 400% of a 1280px viewport. Tables may use labeled horizontal-scroll containers, but forms/actions must remain usable. This is a target, not certification. |
| NFR-06 Performance | Proposed dataset: 100 titles, 300 copies, 30 student accounts and 3 staff, five concurrent browser users. Warm local catalog server-response p95 <=2 seconds across at least 30 samples; hosted visible-page target <=5 seconds over at least 20 observed navigations, with device/network conditions recorded. Hosting/network failures are reported separately, not discarded to claim success. Targets require measurement and may be revised with evidence. |
| NFR-07 Recovery | During active work, daily manual off-host database/file backup and an extra backup before deployment/schema changes/presentation. Target maximum data loss 24 hours (RPO) and local restoration within 30 minutes (RTO), measured with an actual restoration rehearsal. Free provider backups are not assumed. |
| NFR-08 Compatibility/maintainability | Runs in current desktop/mobile browsers after actual checks; server-rendered pages and minimal application JS. Provider browser check itself requires JS/cookies. No dependency on cron, email service, worker process or paid feature. Keep modules small and document every requirement/schema change. PHP lint and relevant functional/integration checks run before declaring the corresponding increment verified. |

The current [W3C WCAG 2.2 recommendation](https://www.w3.org/TR/WCAG22/) is the accessibility reference; conformance claims require an appropriate audit. Performance targets describe a demonstration dataset, not school-wide capacity.

## 8. Workflows and failure handling

### Registration and activation

Validate registration -> create pending student -> sign in -> browse -> librarian checks identity and activates. Duplicate username or invalid password yields field errors. Forged role/status is ignored. Suspension/recovery revokes existing sessions; activation grants borrowing only after server state reflects it.

### Request and approval

Student selects an available title, requests days and gives reason -> system checks eligibility -> pending request. Librarian reviews -> locks student/request/copy, rechecks policy and availability, then records approval/allocation or rejection. Another student may get the last copy first; the later attempt shows a conflict and makes no partial change. Retried approval must not create another assignment. Policy failures leave request pending unless the librarian separately rejects it.

### Pickup, expiry and return

Before deadline: librarian confirms identity and physical copy -> rechecks eligibility -> records pickup and due date. At/after deadline: refuse pickup and release expired allocation; user needs a new request. Return locks the existing loan/allocation, records return and audit, and releases availability in one transaction. Retried return is harmless. A temporarily lost network response is resolved by refreshing recorded state before repeating any action.

### Catalog and cover

Librarian enters a title and linked authors and N copies; save in one transaction. Covers require safe decoding/re-encoding. If cover processing fails, no corrupt image path is saved and the form reports the error; if DB save fails after image staging, remove only that new staged image. Existing covers remain until the replacement commits. Increasing inventory adds physical copies; reducing it selects available copies to archive. No count edit can erase an active loan.

### Recovery and deployment

Use nonpublic backups. Restore into a separate empty database, check expected counts/constraints/sample history/covers, then record evidence. Deployment uses an announced maintenance window if existing users/data are present; protect all mutating endpoints during migration. Do not overwrite live data with seed.sql. Rollback must account for transactions since the backup; blindly restoring old data can lose valid loans.

## 9. Dependencies, assumptions and unresolved items

- DB server version, InnoDB constraints, PDO, GD/image support, HTTPS, session cookies, private-file protection, upload limits and backup import need actual local/host verification.
- Internet reliability at presentation is unknown. Prepare a local fallback; offline synchronization is outside scope.
- Host selection/account signup remains unperformed. No real school/student data may be inferred from test data.
- Approved borrowing clauses are enumerated in section 6; other P-rule details remain proposals. Exact school policies are not supplied.
- Exact November presentation date, exact librarian participant count, research instrument, ISO/IEC 25010 edition/subset, sampling justification, consent/approval process, and evidence of school interviews remain open. Do not delay safe design drafting to invent answers.
- Original research references require source checking before chapter rewriting. Automated local tests exist in TEST_PLAN.md; participant surveys and hosted deployment results remain pending.
- Actual adoption, lost/damaged procedures, retention/deletion obligations and operational ownership require a separate school-approved phase. For this demo an unreturned copy stays on its loan; no invented fine or lost-item resolution workflow.

## 10. Traceability and delivery boundaries

SCHEMA.md describes storage and concurrency; schema.sql/seed.sql are the initial database artifacts. TEST_PLAN.md maps every FR/NFR to unexecuted and executed checks. WORKFLOW.md guides implementation phases; DEPLOYMENT.md separates provider documentation from runtime evidence. AGENTS.md governs future agents. PROGRESS.md records current truth, CHANGELOG.md historical changes, and prompt.md only actual prompts. RESEARCH_REVISION_PLAN.md maps paper changes before any rewriting.

Current implementation includes authentication, librarian student activation/suspension, catalog browsing/filtering, catalog editing/copy management, protected cover handling and audit for these mutations. Local automated evidence is recorded in TEST_PLAN.md. Staff administration, recovery, circulation requests/decisions/pickup/returns, complete history, restore rehearsal, hosting and participant evaluation remain pending. Full authenticated browser/accessibility/concurrency acceptance is not yet complete.
