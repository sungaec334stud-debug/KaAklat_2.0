# KaAklat progress

Updated: 2026-11-01 (Asia/Manila)

Status vocabulary: Planned; In progress; Implemented but unverified; Verified; Blocked.
Verified applies only to the named evidence, not to the entire application.

## Current status

| Task | Status | Evidence | Known issues | Next action |
| --- | --- | --- | --- | --- |
| Capture project direction | In progress | Stack A and implementation approved. Six demonstration borrowing defaults approved 2026-10-31; available-copy-at-request rule also approved. Login-only catalog, student activation, five-book allowance and librarian pickup/return confirmed. | Exact November presentation deadline, evaluation instrument and librarian participant count remain unresolved. Demo rules are not verified school policies. | Resolve research/evaluation details; use approved demo rules for implementation. |
| Review Chapters 1 and 2 | Implemented but unverified | Original 16-page PDF reviewed; revised PDF and revision notes saved under output/pdf with TOC links preserved. See RESEARCH_REVISION_PLAN.md. | Adviser/content acceptance and remaining reference/evaluation questions are unresolved. | Review revised paper with adviser and align later claims with actual implementation/evaluation evidence. |
| Inspect local tools | Verified | PHP 8.4.14 and existing plain PHP/PDO stack used for local tests; XAMPP MySQL started for latest database checks. All 41 PHP files linted successfully. | Tool/client versions alone do not establish hosted compatibility. | Keep explicit PHP executable; verify each deployment target separately. |
| Evaluate free hosting | In progress | InfinityFree account and user-operated compatibility probes recorded: PHP 8.4.27/extensions, MariaDB 11.4.13/InnoDB, private-file protection/read, session persistence, database connection, rollback, CHECK and FK rejection passed. | Intermittent HTTPS certificate warning; cookie flags, actual GD processing, generated uniqueness, host contention, restore and deployed application acceptance unverified. Remote diagnostics/config/probe cleanup pending. | Hosting deferred while local development continues; revisit deployment and cleanup using DEPLOYMENT.md. |
| Approve stack and workflow | Verified | User approved implementation, plain PHP/PDO/custom CSS and six borrowing demonstration defaults. | Other proposed details must not be described as institutional policy. | Continue small increments against approved requirements; pickup is implemented; next feature is returns after pickup acceptance checks. |
| SRS and normalized schema | Verified | Initial local import/count evidence retained below; subsequent catalog/request/decision tests use isolated fictional fixtures. Decision increment needs no schema migration. | Import and local tests do not prove every constraint on the host or backup restoration. | Preserve current data; run target-specific integrity and restore checks before deployment. |
| Development documentation | In progress | SRS, schema, test plan, changelog, prompt history and latest Resume here handoff record implemented scope and limitations. Top status table synchronized with current handoff. | Older dated notes are historical and may contain superseded statuses. | Use the latest handoff and dated test evidence; update documents after each increment. |
| Application implementation | In progress | Authentication, student activation/suspension, catalog/filtering, librarian catalog management, student requests/pending cancellation, librarian approval/rejection/allocation and student decision display implemented. Latest evidence: 14 regression scripts and 45-file lint passed; four decision races and two pickup races passed; audit/expiry rollback checks passed. Earlier catalog/account desktop, simulated-width and keyboard checklists were user-reported PASS. | Four targeted decision checks user-reported PASS on 2026-11-01 (duration explanation, student display, availability, stale submissions). Other request/decision browser/phone/keyboard and HTTP forged-field checks remain pending; deterministic contention checks remain. Physical pickup is implemented with local automated evidence; pickup browser acceptance, returns, approved cancellation, complete loan lists, staff administration and account recovery remain unfinished. | Finish pickup/new-screen acceptance; next feature is returns and active/overdue loan management. |
| Deployment and evaluation | Planned | Partial hosting compatibility probes recorded; local development continues by user decision. | Application is not deployed; hosted acceptance, backup/restore rehearsal, performance measurements and participant evaluation remain pending. | Complete local implementation/security checks, then deployment verification and evaluation. |

## Confirmed workflow decisions

- 2026-10-31: user approved 1–30-day durations, five pending requests, one outstanding request/allocation/loan per student/title, 48-hour pickup deadline, overdue blocking with returns allowed, and five combined approved pickups/active loans. Other proposal details were not blanket-approved.

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
### Current pickup handoff — 2026-11-01
Physical pickup service/page, librarian navigation and student checkout/due-date display implemented. No schema migration. Actual Manila pickup date + approved days; existing allocation counts once; atomic checkout/audit and expiry cleanup; repeated pickup preserves dates. Previous four decision checks remain user-reported PASS. Hosting deferred. Initial pickup integration and two independent-process race tests PASS; see latest TEST_PLAN.md for final expanded results and limitations. Scratch DB retained kaaklat_pickup_test_53d69f9d4310. No demo reset/reimport.
Next: manual pickup desktop/phone/keyboard acceptance and outstanding HTTP checks, then librarian-recorded returns and active/overdue loan management. Do not claim pickup browser acceptance or returns are complete. Earlier handoffs below are historical and superseded for pickup scope.

### Latest acceptance — 2026-11-01
All four targeted librarian-decision checks user-reported PASS: changed duration requires explanation; student sees original terms plus approved days, note and pickup deadline; approval decreases availability by one and rejection leaves it unchanged; stale second-tab submission is refused. These results supersede older pending statements for those four checks only. Phone/keyboard acceptance of new pages, HTTP forged-field checks and deterministic contention verification remain pending. Next implementation: librarian-recorded physical pickup. Hosting stays deferred.


### Librarian decisions handoff — 2026-10-31 (current)

Implemented: librarian pending queue/search and 20-row pagination, approve/reject with validated explanations, transactional allocation and expiry reuse, student-visible approved duration/deadline/note. Original terms retained; no loan clock before pickup. No migration. Hosting deferred.

Verified: 13 regression scripts; 41-file lint; whitespace check; four independent-process races (same request, last copy, last allowance slot, cancellation/approval). Scratch DB retained: kaaklat_decision_test_133cf46ca372. No demo reset or reimport. Browser/phone/keyboard and HTTP forged-field checks for new pages remain pending, as do deterministic lock-barrier tests. See latest TEST_PLAN section. Next: finish these acceptance checks, then librarian-recorded physical pickup. Do not describe pickup/returns/approved cancellation as implemented.


### Current handoff — 2026-10-31 (supersedes older notes below)

Student request submission, own paginated list and pending cancellation implemented. Active students only; 1–30 days, 10–500 character reason; five pending, duplicate outstanding title, overdue and full five-live-allocation guards. User approved P-06 requiring an available copy today. Pending requests never reserve copies; expired holds are excluded logically. No schema migration.

PASS: 12 regression scripts, PHP lint, whitespace checks. Two independent-process races passed (pending cap and duplicate title). New fictional scratch DB retained: kaaklat_request_test_b5596349e8e5. Demo tables/data preserved. New authenticated browser/phone/keyboard and HTTP forged-field checks remain unverified. Next: local student acceptance, then librarian review/approval/rejection; allocation, pickup, returns and approved cancellation remain pending.

Hosting deferred. User-operated PHP 8.4.27/extensions, MariaDB 11.4.13/InnoDB, private access deny/internal read, session persistence, PDO connection, rollback, CHECK and FK rejection passed. HTTPS intermittently presents an expired/untrusted certificate. Cookie flags, actual GD processing, generated uniqueness, host lock contention, restore and deployed app acceptance remain unverified. Hosting is not complete.

Remote cleanup pending: storage_check.php, runtime_check.php, session_check.php, database_check.php; protected hosting_db.php and canaries; kaaklat_hosting_fk_probe followed by kaaklat_hosting_rollback_probe. Keep private folder denial. No remote cleanup performed.


Latest decision — 2026-10-31: six borrowing defaults explicitly approved (SRS section 6). Prior requests for approval of these defaults are superseded. No circulation code added in this update. Next implementation: student requests/own list/cancellation and librarian approval/rejection with transactional copy allocation and cap/overdue enforcement. Hosting provider/account gate remains pending.

Latest user acceptance — 2026-10-31: Chrome simulated widths 360px/768px, keyboard navigation, login/register, catalog filters/pagination, librarian forms and 400% zoom all reported PASS; no problems. Together with the earlier eight desktop checks, this completes the supplied manual checklists for current screens. Actual-phone testing, contrast audit, concurrency and hosted acceptance remain pending; this is not full accessibility certification. Next required user decisions: approve/change borrowing defaults and select/create hosting account. No borrowing defaults approved merely by this test report.

Latest acceptance: user reported all eight Chrome desktop checklist scenarios PASS with no problems: student catalog, combined filters, availability, pagination, detail, student permission denial, librarian forms, signed-out access. Recorded in TEST_PLAN.md. Phone/keyboard/accessibility checks still pending. Authentication hardening regression passed in the preceding turn. Next: remaining phone/keyboard checks, confirm proposed borrowing defaults with the user, then build requests/decisions. Hosting provider/account and early compatibility gate remain outstanding.

Current status: MySQL restarted; all 11 regression scripts PASS including auth_hardening_test (unknown/disabled accounts, duplicate key classification, unrelated audit failure and rollback). Available-only filter added with validation, derived-count filtering before pagination and combined-filter tests; all 11 scripts rerun PASS. PHP lint PASS. Earlier connection-refusal blocker below is resolved. Documentation synchronized for implemented vs pending scope. Full authenticated desktop/mobile/keyboard browser acceptance still pending; borrowing defaults still need user approval; hosting provider/account not selected. Do not mark those complete from automated tests alone.

Current task: resolve five listed gaps sequentially. Authentication hardening implemented but database verification BLOCKED because local MySQL connection was actively refused. Start XAMPP MySQL, then run `php tests/run_regression.php` (now includes auth_hardening_test.php). New changes use fixed dummy verification for missing/disabled accounts and restrict duplicate-username translation to users INSERT + driver 1062 + named uq_users_username key. Six non-DB tests and changed-file lint passed. Next after DB verification: availability filter/browser acceptance, documentation synchronization, user confirmation of borrowing defaults, hosting setup.

Latest regression run (2026-10-30): fixed rolling login-failure retention, idle-session expiry and malformed credential types. See verification/REGRESSION_2026-10-30.md and tests/run_regression.php. Database tests use temporary fixtures; current 21 books/305 copies/17 requests/14 loans preserved. Full visual/authenticated-browser/concurrency tests remain pending; dummy verification timing and integrity-error classification noted as follow-ups. Usage remaining 60% five-hour/76% weekly.

Latest handoff — categories/year (2026-10-30): migration 001 applied to local kaaklat_dev with private snapshot. Add/edit metadata and catalog combined category/title/author/A–Z/year filters implemented. 20 samples enriched; user-added records preserved (21 visible books, 305 copies, 17 requests, 14 loans). Validation, isolated SQL/management integration and regression tests passed, lint passed, actual metadata/constraint presence verified. Authenticated visual and filter-pagination browser checks remain pending. Existing databases require migration 001 before current code; do not rerun the local migration runner. Next planned implementation remains borrowing requests/decisions after UI acceptance and policy-default confirmation. Usage remaining 65% five-hour, 77% weekly.

Latest handoff — librarian catalog management (2026-10-30): `/manage_books.php` now implements create/edit, authors, initial/additional copies, private cover upload/replacement, and guarded book/copy archiving. Dashboard link available to librarians only. No schema change or loan actions. Validation, temp-table transaction/authorization/archive/rollback and genuine multipart/file-rollback checks PASS; existing unit/integration regressions, full lint and diff checks PASS. Current 20 sample titles/covers and 300 copies/17 requests/14 loans verified unchanged. Browser visual/accessibility checks and concurrent archive/approval races remain pending. Next: try librarian forms locally, then borrow requests and librarian decisions after confirming proposed circulation defaults. Usage remaining 73% five-hour, 78% weekly.

Latest sample update: local catalog now has 20 real classic titles with author links, original descriptions and private Gutenberg edition covers; extra unused fictional entries archived. Counts verified: 20 visible titles, 300 historical copies, 17 requests, 14 loans. Covers are protected by `/cover.php`; signed-out request returned 403. See docs/sample-books-sources.md for preparation/deployment and backup details. Next: browser check and librarian editing/uploads. No loans were removed. Usage 79% remaining five-hour/weekly.

Latest handoff — catalog increment (2026-10-30): implementation approval is confirmed. Protected catalog list/detail now implemented at `/catalog.php`, linked from dashboards/header. Search title/authors, 12-book pages, derived active/available copies, expired-hold handling and inline local cover placeholders exist. No schema change, editing, uploads or loan actions added. Account rollback/revocation integration test now PASSES using temporary tables and fixture-only duplicate audit code (no ALTER). Catalog policy/input and SQL/availability integration tests PASS; existing foundation/auth/account policy regressions and full PHP lint PASS. Next: browser acceptance of catalog login redirects/search/detail at desktop/mobile sizes, then librarian cataloging and secure cover uploads. Historical pending-verification notes below are superseded for account rollback by this result.

Visual refresh applied to shared layout/CSS, login/registration and student-management markup. Lint and policy tests passed. Next verify desktop/mobile presentation and keyboard navigation in the browser; account transaction rollback verification remains outstanding as documented below.

Student management now has separate activation, suspension and reactivation views. Refresh `/students.php` as a librarian to select a view. Browser verification of the new navigation remains pending.

Implementation was approved by the user. Student activation/suspension is now implemented in `app/student_accounts.php` and `public/students.php`, linked from the librarian dashboard. Policy tests, existing foundation/auth unit tests and PHP lint passed. Database test reached activation, suspension, reactivation, revocation and authorization assertions; the audit-failure fixture initially failed to induce failure, then used incompatible CHECK fixtures. The final temporary-table ALTER fixture was blocked by automatic approval review and has NOT passed. Resume by safely verifying rollback and browser access before catalog work. No schema migration is required. Remaining usage at handoff: 93% five-hour, 81% weekly.


## Research revision completed — 2026-10-31

The revised Chapters 1–2 PDF is saved in output/pdf/SUNGA_CHAPTER1_2_REVISED_KaAklat.pdf. This supersedes the older table row stating that revision and bibliography checking have not begun. Both chapters, definitions, IPO diagram and retained literature/reference entries were revised. Original cover and TOC are visually unchanged; all 23 TOC links and 22 bookmarks point to the revised headings on their original pages. All 16 pages were rendered and reviewed; source/output hashes and preservation checks are in output/pdf/verification.json. The source file remains untouched.

Research next steps: adviser review, exact librarian count, approved participant/instrument procedures and consistent ISO edition/criteria; subsequent data collection and evaluation are still planned. Application work and hosting status are unchanged. Usage reached 89% before interruption; after reset continuation showed 1% used/99% remaining (weekly 24% used). No unfinished PDF authoring remains.



Additional expiry rollback injection PASS: final approval audit failure restores old allocation, pending decision and expiry audit together; successful retry then passes. Usage finish: 97% five-hour / 81% weekly remaining.


### Acceptance update — 2026-11-01
User-operated stale-submission test PASS: rejection in one tab followed by approval attempt from the unrefreshed second tab, using the same librarian session. This supersedes the pending status for that specific manual scenario only. Changed-duration explanations, student-visible decisions, availability and remaining new-page layout/keyboard checks still need separate results. No application code changed.

Final verification 2026-11-01: 14 regression scripts PASS. Expanded pickup checks include exact deadline, original/approved terms, actual student history fields, rejected/missing allocation, duplicate title and expiry-audit rollback. All 45 PHP files lint PASS. Two pickup independent-process races PASS. Browser/HTTP acceptance remains unverified. Finish usage snapshot: 51% five-hour / 74% weekly remaining. Next: manual pickup acceptance then returns/loan lists.

### Student request tabs — 2026-11-01
Student history now has All requests, Pending, Rejected, Awaiting pickup and Active tabs. Overdue stays visible in Active; closed/expired history in All requests. Ownership, filtering before pagination and selected-tab pagination are retained. Regression and targeted fixture tests PASS; visual/keyboard acceptance pending. Next remains pickup acceptance, then returns and operational loan lists.
