## Staff dashboard parity correction — 2026-11-13

The earlier redesign completion statement overstated staff-dashboard parity. Corrected the librarian and administrator dashboards to the accepted layout: whole-card summaries, two-column work/account panels, protected book covers, librarian student verification and own recent desk activity, administrator recent account activity and active staff access. All values come from bounded read-only queries. Librarians see only their own allowlisted circulation activity; administrators gain no circulation permissions. Existing student dashboard, role checks, About content, pagination and pilot records remain unchanged. No schema changes, commit or push; hosting deferred.

Verification: 36 regression scripts passed before the final administrator presentation adjustment; final affected dashboard/design tests, PHP lint and 416 workflow HTTP assertions passed. Final browser rerun covered 248 role/theme/viewport combinations with zero runtime exceptions and zero sampled contrast failures. Final librarian/administrator dark screenshots were visually inspected. Strict static UI audit reported zero findings. Actual 400% browser zoom, physical-device and screen-reader acceptance remain manual; no claim of a bug-free system. Usage tool unavailable.

Evidence: verification/STAFF_DASHBOARD_PARITY_2026-11-13.md.

# Changelog

## 2026-11-13 — Catalog card alignment

Catalog row alignment corrected with seven shared natural-height CSS subgrid tracks and one optional-badge container. Covers, titles, badge blocks, authors, publication metadata, descriptions and available counts align within each visual row without fixed text heights or additional clipping. Server queries, status/availability rules, cover permissions, links, sorting, filters and pagination unchanged. Focused browser reproduction initially measured150.77px drift; final10 Light/Dark viewport cases(1440/1000/768/360/320px) measured0px, with no overflow/clipped text and a JavaScript-disabled CSS check passing. Existing96 authenticated catalog browser combinations and focused status/view tests passed; PHP lint passed. No pilot records changed, no commit/push. Earlier redesign work remains preserved.

## 2026-11-13 — Accepted three-role design refinement

Applied all accepted recommendation areas across the maintained application: grouped navigation with the approved line icons; compact shared headings/search/cards; real role-scoped dashboard summaries and student deadline/catalog preview; sectioned native book editor; clearer audit labels; refined staff, profile, recovery, request, About and logout presentation. All current About information is retained byte-for-byte. Original workflow forms/fields, security, history, protected covers, System/Light/Dark and pagination design are preserved. Verified36 regression scripts,409 workflow HTTP assertions,373 pagination HTTP checks,440 browser layout combinations(248 theme+78 workspace+24 editor+90 pagination),30 workspace interactions,28 no-JS page navigations,12 theme unit checks and121 PHP lints; zero browser runtime exceptions, no sampled theme contrast failures and zero strict static UI findings. All pilot table row fingerprints match the initial baseline; no pilot test accounts/records added. Actual400% zoom, real-device/screen-reader acceptance and updated performance/restore/evaluation remain pending. No schema or circulation mutation/lock changes; independent-process concurrency suites not rerun. Usage tool unavailable. No commit/push; hosting deferred. Evidence: verification/DESIGN_REVIEW_IMPLEMENTATION_2026-11-13.md.

## 2026-11-12 — Covers, alignment and student detail cards

Fixed missing circulation cover presentation and alignment: review/pickup/return rows and selected cards now use the existing authenticated cover endpoint instead of title initials, with an honest missing/archived/failed-image fallback. Selected covers load eagerly; list covers remain lazy. Centered managed-catalog category/availability headers and values, fixed oversized no-cover placeholders, and padded the catalog pagination container without changing buttons/renderer/styles. Active/suspended student detail cards now show the school ID to librarians, registration and recorded activation/suspension/reactivation dates in Manila time. Dates derive from existing audit events; unavailable history says Not recorded. Existing administrator ID restriction retained. No schema, mutation/lock/audit policy or pilot data changes. Verified35 regression scripts,390 workflow HTTP checks,373 pagination HTTP checks,248 theme layouts,90 pagination browser layouts/28 keyboard-no-JS navigations and36 focused cover/alignment/history layouts. Browser suites reported zero runtime exceptions; sampled theme contrast had no failures. All128 PHP files linted; strict static UI audit zero findings. Pilot table rows/definitions/covers/config match the fresh baseline. Actual400% zoom, real-device/screen-reader acceptance and new performance measurements remain unverified. Usage tool unavailable. No commit/push; hosting deferred. See verification/COVERS_ALIGNMENT_STUDENT_DETAILS_2026-11-12.md.

## Manage books layout correction — 2026-11-12

Corrected Manage books to the approved server-rendered list/editor layout: catalog records, protected covers, categories and derived availability on the left; the selected book’s original edit/copy/cover/archive forms on the right. Narrow layouts stack. Search, page and selected-book context survive navigation and successful writes. No new mutation permissions, schema, circulation logic or editable availability/copy counter. Pagination renderer/style unchanged. Verified33 regression scripts,390 workflow HTTP assertions,373 pagination HTTP checks,24 focused browser layouts and90 pagination browser layouts with28 keyboard/no-JS navigations; zero browser runtime exceptions. All124 PHP files linted; strict static UI audit zero findings. Pilot accounts/catalog/covers/config unchanged. Exact full-row baseline comparison detected concurrent existing-account activity(two new requests,three approvals and their audit/allocation records); that activity was preserved. No test accounts added to pilot; structural schema unchanged ignoring AUTO_INCREMENT values. Actual400% zoom, real-device and screen-reader acceptance remain unverified. Usage tool unavailable. No commit/push; hosting deferred. See verification/MANAGED_CATALOG_LAYOUT_2026-11-12.md.

## 2026-11-12 — Approved workspace design

Applied the approved twelve-page workspace design: compact search bars, record directories with focused action panels, condensed audit records, protected book thumbnails, a student identity card, recovery guidance, quieter request cards, an editorial About page and a compact logout confirmation. Existing pagination renderer and stylesheet are unchanged. Original server forms/fields, routes, permissions, CSRF, validators and circulation services are preserved; JavaScript moves existing record nodes and does not clone forms or fetch records. Narrow screens begin with the list. Verified32 regression scripts,763 HTTP checks(390 workflow+373 pagination),248 theme/role layout combinations,90 pagination combinations with28 keyboard/no-JS navigations,78 focused workspace layouts with30 interaction checks,122 PHP lints and a strict static audit with zero findings. Browser suites reported zero runtime exceptions; the theme sweep found no sampled contrast failures. All pilot table rows/definitions, covers and private configuration match the pre-redesign snapshot. Actual400% zoom, real-device/screen-reader acceptance and refreshed performance timings remain unverified. Usage tool unavailable. No commit/push; hosting deferred. See verification/WORKSPACE_REFRESH_2026-11-12.md.

## 2026-11-12 — Audited behavior-preserving simplification

Completed the five audited simplification areas without a rewrite: removed two unused return-date assignments, reused catalog_like for unified search, shared student summary items/overdue markup with existing page labels, shared display-only UTC/Manila formatting, and consolidated low-level Chrome protocol plumbing across three browser suites. Original24 presentation snapshots remain byte-for-byte identical. Verified32 regression scripts,763 HTTP checks(390 workflow+373 pagination),434 browser combinations(248 theme+96 catalog+90 pagination),28 pagination keyboard/no-JS navigations,12 theme-controller checks and122 PHP lints. All three browser suites reported zero runtime exceptions; the theme suite's sampled contrast checks had no failures. Browser protocol error/response-matching checks passed. Pilot table rows/definitions, covers and configuration match the pre-refactor baseline; no test accounts/data added to pilot. No schema, authorization, CSRF, circulation mutation, locking, deadline math or CSS changes. Actual400% zoom/real-device/screen-reader and fresh performance checks remain unverified. Usage tool unavailable. No commit/push; hosting deferred. See verification/SIMPLIFICATION_2026-11-12.md.

## 2026-11-12 — Shared numbered list pagination

Shared numbered pagination now covers student request history, student accounts, staff accounts, audit records, request reviews, pickups, returns, managed books and physical copies, using the catalog's existing renderer/style. Each list has accurate filtered totals, a current-page marker, bounded first/last/nearby numbers, Previous/Next and preserved search/status/book context. Out-of-range page links clamp to the last valid page; empty lists show0 and page1of1. Existing page sizes, role/ownership restrictions and mutation handlers remain unchanged. Verified31 regression scripts,390 existing HTTP checks,373 additional pagination HTTP checks and90 Chrome role/theme/viewport combinations, including28 native keyboard navigations with JavaScript disabled and zero runtime exceptions. Pilot rows/schema/covers/config match the pre-change baseline. Actual400% zoom, real phones, screen readers and new query performance remain unverified. Usage tool unavailable. No commit/push; hosting deferred. See verification/SHARED_PAGINATION_2026-11-12.md.

## 2026-11-12 — Catalog sorting, filters and unavailable titles

Implemented catalog discovery: four deterministic sort orders, title-or-author search, inclusive/open year ranges with legacy exact-year compatibility, three availability choices for every authenticated role, distinct result totals, removable filters and preserved results/detail/back context. Native GET controls work without JavaScript; existing availability and request authorization remain authoritative. No schema or pilot data migration. Verified30 regression scripts,390 HTTP checks,115 PHP lints and96 authenticated Chrome role/theme/viewport combinations with zero runtime exceptions; keyboard/error focus and JS-disabled apply/removal/clear checked. Fifteen final read-only pilot catalog requests passed. Original accounts/rows,86 books/273 copies,87 cover files and configuration preserved; one independently registered/activated student during work was retained, bringing accounts to4. Actual400% zoom, real-device/screen-reader acceptance and updated performance timings remain unverified. Usage tool unavailable. No commit/push; hosting deferred. See verification/CATALOG_DISCOVERY_2026-11-12.md.

## 2026-11-12 — Real-title catalog expansion

Added38 researched real titles (two per each of19 categories),127 simulated copy records and38 protected source covers. Pilot totals86 books/273 copies; prior data preserved. Actual audit-failure rollback and repeated-import refusal rehearsed in separate scratch resources. Corrected imported cover filenames to the existing protected endpoint format, without weakening security. Final306 HTTP checks and276 browser geometry/image combinations passed. Public bibliography/manifest and detailed evidence in verification/CATALOG_EXPANSION_2026-11-12.md. No application/schema/policy change or commit/push; hosting deferred.

## 2026-11-12 — Remove unreferenced Lantern Valley demo catalog

2026-11-12: user-authorized Lantern Valley demo cleanup completed. Removed80 archived, unreferenced fictional titles,240 DEMO copies and80 author links in one audited transaction after a verified full pilot backup. Remaining48 books/146 copies; accounts/passwords/history/authors/categories/covers and visible catalog availability unchanged.38 post-removal HTTP/preservation checks passed. Earlier128/386 totals are historical. See verification/LANTERN_DEMO_REMOVAL_2026-11-12.md. No code/schema change or commit/push; hosting deferred.

## 2026-11-11 — Protected project cleanup

Saved source consolidation in local commit412e40a, then relocated temporary artifacts, research outputs, catalog import material, old pilot/rollback copies and older backups to a protected archive outside the repository. Kept maintained source/tests/docs/operator tools and current pilot configuration/covers/sessions/latest backup. Added cleanup inventory and verification evidence; no feature/business-rule/database changes, no database deletion and no push. Full isolated regression, HTTP, concurrency, theme/browser and fictional restore checks executed. See verification/PROJECT_CLEANUP_2026-11-11.md.

## 2026-11-11 — One maintained source for the local pilot

Consolidated runtime into the main repository while keeping existing kaaklat_pilot records/passwords unchanged. Added guarded start-local.ps1 and CLI config checker; active private config/cookie copied from pilot, covers synchronized exactly, sessions copied to private/pilot-sessions. Old main private config/covers preserved for rollback; old pilot code snapshot retained and launcher forwarded. Verified full pilot backup and source/data preservation; no seed/import/demo merge. Updated setup guidance. No commit/push; hosting deferred. Evidence: verification/PILOT_CONSOLIDATION_2026-11-11.md.

## 2026-11-10 — Repository README

Added README.md as the GitHub entry guide: role permissions/workflows, registration/ID approval, borrowing defaults, native appearance keyboard controls, existing private-pilot versus fresh-clone setup, account tools, private data/backups, verification scope, troubleshooting and linked project documents. No credentials, pilot files, schema or application behavior changes. Documentation links/anchors/code fences and whitespace checked; no database tests rerun for documentation-only work.

## 2026-11-10 — Catalog-only pilot prepared

Prepared separate local kaaklat_pilot database/application with preserved catalog, copies, authors, categories and referenced covers; empty accounts/circulation/security/audit tables. Full original demo backup verified, original data/config unchanged. Isolated cookie/session directory, restricted new pilot/backup permissions, loopback8010 smoke checks passed. User handles actual tester accounts. No source feature changes, demo deletion, hosting, commit or push. See verification/PILOT_PREPARATION_2026-11-10.md.

## 2026-11-10 — User-operated local acceptance

Recorded user-reported passes for five independent sessions, simultaneous catalog/search/filter/pagination, phone Light/Dark, staff themes,400% zoom and keyboard navigation. No problems reported; no new measured browser timings or full accessibility certification inferred. Hosting explicitly deferred to a later phase. Controlled regular-data backup/isolated restore is the next local checkpoint. Documentation only; code/data unchanged. Prior implementation/performance checkpoint pushed as2411253.

## 2026-11-10 — Shared navigation and appearance

Applied approved preview navigation: compact navy sidebar, utility separation and visible scrollable navigation at narrow widths. Added read-only authenticated Settings with native System default/Light/Dark cards; shared early theme initialization, browser-local persistence, device-change handling, blocked-storage feedback and JavaScript-free system fallback. Extended canonical CSS tokens across shared controls, statuses, forms, catalog/history and signed-out screens. Preserved role workflow routes, server checks, schema and regular data. Verification and remaining manual limits: verification/THEME_NAVIGATION_2026-11-10.md. Hosting deferred; no commit/push.

## 2026-11-05 — Login and registration refresh

Updated the two account pages with short field hints, clearer headings and dedicated guidance for librarian approval and account recovery.

## 2026-11-05 — Student ID registration and librarian approval

Added school ID validation and an additive uniqueness constraint. Librarians now confirm an in-person check before activating and can correct or release pending claims. The audit records the operator and time without exposing the ID. Existing accounts retain their status and borrowing history.

## 2026-11-04 — Approved campus portal design

Applied approved blue/white/gray campus mockup through shared CSS/layout: navy directory sidebar, crisp system typography, decorative route icons, contextual topbar, shared linked count strip, task cards and coordinated catalog/forms/history/profile/landing. Preserved all backend services, queries, form fields, permissions, routes and data. Fixed browser-observed narrow checkbox-error compression with normal-flow labels. Added shared presentation helper,28 focused checks and semantic role-navigation HTTP coverage. Updated DESIGN and shared UX owner map.

24 regressions,181 HTTP assertions,93 PHP lints and seven concurrency suites17 scenarios PASS. Actual representative fixture-browser screens and keyboard error links observed; fresh review found no important defects. Scoped static checker has one reviewed dynamic-fragment false positive, PHP unsupported. Actual400% zoom/JS-disabled/full-state acceptance remains manual. Exact evidence/resources: verification/CAMPUS_PORTAL_ACCEPTANCE.md. No demo reset, dependencies, schema changes, commit or push. Hosting deferred.

## 2026-10-31 — Student borrowing requests

Added transactional student create/list/pending-cancel service and page, catalog/detail/dashboard links, current-role/session/CSRF/ownership checks, bounded validation and pagination. Five-pending/live allowance, duplicate title, overdue, archive and available-copy guards; expired holds excluded logically. Original terms/history preserved; safe audit atomic; no copies reserved. P-06 explicitly approved in chat. No schema migration.

PASS 12 regression scripts, PHP lint and whitespace checks; independent-process cap/title races passed in retained fictional DB kaaklat_request_test_b5596349e8e5. Authenticated UI and HTTP acceptance still pending. Existing demo data and user edits preserved.

Hosting deferred; user-operated PHP/DB/private paths/sessions/rollback/CHECK/FK checks passed, intermittent certificate warning unresolved. Cookie flags/GD processing/generated uniqueness/host locking/restore/app deployment pending. Remote diagnostics/private config/canaries/two probe tables await cleanup (see PROGRESS.md). No deployment claim. Next: local student acceptance then librarian decisions/allocation.


All dates use Asia/Manila. This records completed document changes, not application features or test results.

## [Unreleased]

- 2026-11-04: recorded successful user installation of Python3.15.2 and strict checker execution with zero findings. Corrected audit interpretation: default scan covered zero eligible source files, and the checker excludes PHP. No PHP UI/security certification inferred. Application work/data preserved; documentation-only update, no application test rerun, commit or push.

- 2026-11-04: recorded user-operated My Profile information/count links/360px/768px/400% zoom/keyboard acceptance: all PASS, no problems reported. Named checks only; Python static audit and broader coverage not inferred. No application changes, test rerun, commit or push.

- 2026-11-04: added read-only own student profile with safe basic fields, Manila registration display, pending/active explanation, shared own borrowing counts/history links, student navigation and scoped responsive definition-list styling. Fixed confirmed SYSTEM/+08 default timestamp mismatch by setting new application connections to UTC; existing timestamps not modified and legacy registration provenance needs review.25 profile checks/23 regressions/133 HTTP assertions/90 lints/17 race scenarios and52-check restore PASS. Browser/keyboard/zoom blocked by Computer Use URL-confidence policy; premium static audit blocked by inaccessible Python. Existing backup/research/demo work preserved; hosting deferred, no commit/push. Favorites next feature; performance remains pending.

- 2026-11-04 (work begun 2026-11-03): added CLI-only local backup bundles (SQL, matching covers/config/working source, checksums), private credential transport/cleanup, new-only path guards and beginner operator/restore-quarantine guidance. Actual fictional source/restore rehearsal52 checks PASS,3.30s on MariaDB10.4.32;22 regressions/104 HTTP assertions/84 PHP lints PASS. No demo-data mutation, route/schema change, deployment, off-device/real-demo recoverability claim, commit or push. Exact retained scratch resources and failed harness checkpoints documented in verification/BACKUP_RESTORE_2026-11-03.md; hosting deferred.

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

## Acceptance checkpoint — 2026-11-02
See verification/ACCEPTANCE_2026-11-02.md. Added isolated real-HTTP pickup acceptance runner/router. 16 regression scripts PASS; HTTP 22 assertions PASS in kaaklat_acceptance_1041ca531bf5; earlier HTTP run retained kaaklat_acceptance_e7521fb35d99. No demo data changes, private config changes, returns or new feature. Test client GET/POST order corrected after initial harness failure. 51 PHP lints and JS syntax PASS. In-app browser login desktop/360/768 screenshots inspected; error summary focus and Tab/Enter field link verified. Authenticated visual/keyboard and 400% zoom remain unverified. Temporary server stopped/browser viewport restored/tab closed. No commit/push.

Acceptance usage snapshot: start 98% five-hour/59% weekly remaining; finish 86%/57%. Not a usage-limit stop. Remaining authenticated visual/400%/full keyboard checklist explicitly retained.

## User acceptance checklist — 2026-11-02
Recorded user-reported PASS for all 12 dashboard/forms/counts/status/responsive/zoom/keyboard/JS-on-off validation/value-preservation checklist items; no problems supplied. Updated progress handoff and test evidence. No application changes or automated rerun. Next feature: librarian-recorded returns; hosting remains deferred.

## Librarian-recorded returns — 2026-11-02
Implemented app/returns.php and public/returns.php with active outstanding loan list, 20-row pages plus fetch-ahead, literal title/student/copy search, Manila pickup/due dates, derived overdue labels and receipt-confirmation checkbox. Librarian navigation/dashboard link added; student history displays actual return time and completed status. No migration, counters, replacement loans or history deletion. Inactive/overdue borrowers and archived copies/titles can return; archived catalog remains unavailable. Repeat returns preserve actor/time with no duplicate audit. Sorted actor/borrower -> book -> copy -> request -> loan locks, post-lock UTC, bounded transient retries, atomic audit/return.
Executed: 17 regression scripts PASS; expanded return pagination/dashboard/history tests PASS; two independent-process return races PASS; extended real HTTP workflow PASS (34 assertions); 55 PHP files lint PASS and forms.js syntax PASS. Scratch DBs retained kaaklat_return_test_959263a8e882, kaaklat_return_test_92187100d974 and kaaklat_acceptance_1d38e48e288e. Initial DB connection refused; started existing XAMPP MySQL and reran successfully. No demo reset/reimport. Existing acceptance/research edits preserved.
Limitations: new Returns screen visual/phone/keyboard acceptance not run; no deterministic worker lock barrier (shared scheduled start); no hosted verification. Previous user interface passes do not cover returns. Usage tool unavailable at start/finish; no percentage claimed. No commit/push. Next small SRS increment after return acceptance: cancellation of approved uncollected allocations (remaining FR-05), followed by remaining staff administration/recovery requirements. Hosting deferred.

## Returned student tab — 2026-11-02
Added Returned to student request status tabs and server allowlist/derived lifecycle filter. Existing ownership, bounded SQL pagination, status-preserving page links and returned date/history retained. Targeted returns_test PASS with returned-only and other-student isolation assertions; changed application PHP lint PASS. Visual acceptance pending. Design helper context launcher unavailable; existing theme reused. No migration/demo changes/commit/push.

## Critique presentation refinements — 2026-11-02
Applied librarian circulation-first dashboard action/order; collected loan pickup deadlines moved into labeled historical details; return success includes trusted book/copy context; copy/archive-book matched controls receive caution styling/consequence text; auth autocomplete and registration UTF-8 byte guidance; catalog/pickup reset links. Existing routes/permissions/transaction rules unchanged. 17 regression scripts PASS; PHP lint PASS. Browser visual acceptance pending. Remaining critique items: complete accessible object-specific action names, row-associated return errors, persistent truthful recovery guidance, unavailable-book CTA clarification and full archive-action consistency review. No commit/push; no demo mutations. User requested execution of critique plan; these remaining refinements must not be described as complete.

## Completed critique refinements — 2026-11-02
Finished contextual accessible pickup/expiry/return/archive labels; consistent caution archive controls; row-targeted return error summary and receipt association, retained checked state and search/page query; truthful login librarian-help copy; unavailable catalog action replaced with current-availability explanation. No routes, authorization, CSRF, receipt policy, transaction or circulation rule changed. Optional receipt context loaded through return_success_message callback: any context exception falls back to generic success after committed return; no misleading failure. New views/return_ui.php contains escaped presentation helpers.
Verification: 18 regression scripts PASS; interface_refinement_test exercises failed optional context, repeated-result fallback, malicious title/error escaping, row/receipt IDs and retained state; extended HTTP 36 assertions PASS including authorization, forged fields, error links/state/query and successful lifecycle. 57 PHP files lint PASS. Retained scratch DBs kaaklat_acceptance_d92acc1c08d0 and kaaklat_acceptance_2afb2ce397ca. No demo mutations. Browser visual/keyboard/screen-reader acceptance not executed for these refinements. Earlier user passes preserved, not extended to new code. No commit/push. Hosting deferred.
Next local checks: contextual buttons using keyboard/screen-reader button list, receipt errors JS on/off and preserved search/checked state, generic/contextual return success, unavailable book message and archive caution at narrow/zoom. Next feature approved-uncollected cancellation.

Refinement usage snapshots: start 95% five-hour/68% weekly remaining; finish 93%/67%. Not a usage-limit stop.

## Refinement acceptance confirmation — 2026-11-02
Recorded user-reported PASS for all nine refinement checklist checks, no problems reported. Includes successful return/history, field link/search, accessible action names, archive appearance and unavailable/recovery text, plus user-run regression/HTTP. Documentation-only; no tests rerun or code changes. Next functional increment: approved-uncollected request cancellation.

## Commit verification — 2026-11-02
User authorized all application/documentation/test changes committed and pushed, excluding separate output/ research artifacts as before. Fresh verification: 18 regressions PASS; two return concurrency scenarios PASS (scratch kaaklat_return_test_afe8caed91ef); final HTTP 36 checks PASS (scratch kaaklat_acceptance_a5868b73455d). Slow machine exposed fixed 500ms test-server startup assumption; test harness now polls loopback readiness for at most 45 seconds, then rerun passed. No application behavior change from this harness fix. Scope/secret-pattern and whitespace checks passed before this final staging. Remaining new-screen visual limits remain documented.

## 2026-11-02 — Status synchronization and local service check
Updated SRS current requirement/deployment summary and progress table/handoff; labeled older checkpoints historical. Read-only PDO check passed; started absent local PHP listener; health ready and login HTTP 200. No application/database/configuration changes. Hosting deferred; no commit/push.

## 2026-11-02 — Approved-uncollected cancellation
Added atomic, audited student-own/librarian cancellation using existing loan cancellation fields; current-session/ownership/role checks, expiry distinction, repeat safety and copy/allowance release. Existing pending cancellation preserved. Student history shows cancellation details; librarian pickup queue supplies required explanation with error link, escaped retained input and search context. No migration/data reset. 19 regression scripts, 45 HTTP assertions, 60-file lint and three independent-process cancellation races PASS. Browser acceptance remains pending; hosting deferred. No commit/push.

2026-11-03: reran 19 regression scripts, three cancellation race scenarios, 45 HTTP assertions, 60 PHP lints, JS syntax and whitespace checks; all PASS. No application changes. Manual cancellation acceptance remains pending; hosting deferred.

2026-11-03: recorded user-reported visual and keyboard acceptance PASS for approved-uncollected cancellation controls. No code changes or new automated checks; no full accessibility/hosting certification. Next: administrator staff-account management.

## 2026-11-03 — Administrator staff accounts
Added admin-only staff list/create/role/status management, last usable administrator concurrency protection, atomic audits/session/reset revocation, safe self-change and disabled-password guidance. Added CLI-only initial-admin bootstrap and private interactive Windows password input; legacy password tool callers retained. No schema/demo data changes; no bootstrap against demo. Unit/integration/concurrency/HTTP evidence and UI/terminal limitations in TEST_PLAN.md. Hosting deferred; no commit/push. Next staff-assisted recovery.

2026-11-03: recorded user-reported PASS for supplied administration checklist, including private password assignment, access/session controls and responsive/keyboard checks. No code changes or automated rerun; next staff-assisted recovery.

2026-11-03 audit: rejected embedded NUL in new passwords before PHP hashing; added regression and broader HTTP registration/student-account/catalog/upload/logout coverage. 20 scripts/15 races/81 HTTP assertions/71 PHP lints PASS. Recorded admin student-permission SRS gap and unverified areas in verification/AUDIT_2026-11-03.md.

## 2026-11-03 — Account access and audit completion
Closed admin student-management permission gap without broadening circulation/catalog guards. Added staff-assisted 30-minute single-use recovery, atomic reset/session/audit handling and one-time private delivery; password choice preserves account status. Added read-only admin audit metadata browser with filters/pagination, no sensitive free-text payload. New navigation/dashboard/login guidance. No schema migration or demo mutations. 21 regression scripts, 101 HTTP assertions and two recovery races PASS. New UI/browser acceptance pending; hosting deferred. No commit/push.

2026-11-03: full local verification rerun PASS (21 regression scripts, 17 races, 101 HTTP assertions, 79 PHP lints, JS syntax/whitespace). Health ready; five read-only demo integrity checks zero. No new app changes. Limitations/evidence in verification/FULL_LOCAL_CHECK_2026-11-03.md.

2026-11-03: modernized shared interface in existing school palette; desktop sidebar, responsive navigation, clearer forms/task hierarchy and consistent status/focus/caution styles. DESIGN.md and verification/DESIGN_REFINEMENT_2026-11-03.md record scope and evidence. 21 regressions/101 HTTP assertions PASS; final92 headless width captures without horizontal overflow. No business/data/schema changes. Manual acceptance remains scoped.

2026-11-03: added signed-out library imagery and bounded reduced-motion-aware animation, dedicated landing view/CSS and four-step guide. Local optimized generated images, no runtime external dependency.21 regressions/104 HTTP assertions PASS; actual headless responsive/motion/JS-off/skip-link checks recorded in verification/LANDING_2026-11-03.md. Authenticated dashboards/data preserved.

2026-11-03: removed unused temporary design-review folder at user request; no runtime dependency or public assets removed.

## Local college textbook collection

- Added nine course categories, 27 verified textbook records, 81 demo copy records and 27 private cover images to the local database. Added a bibliographic source manifest with separate course evidence where confirmed.

## School ID on My Profile

- Display the signed-in student's school ID in the private, view-only profile. Show an explicit message when the ID is missing.

## Dashboard task directory

- Grouped existing student, librarian, and administrator dashboard actions into role-specific task lists with responsive layouts.

- Simplified the role dashboards by removing task lists that duplicated main navigation. Kept linked borrowing totals, brought student notices forward, and highlighted Staff accounts for administrators.

- Applied the approved dashboard mockup with personalized banners and role-specific previews from existing records.

- Added compact expandable student request rows and a separate Overdue filter. Updated student dashboard and profile links to match the two loan views.

- Added the supplied Universidad de Dagupan seal and name to the shared site header.

- Restored the KaAklat header logo and placed the university seal beside the university name below it.
# 2026-11-09 — Public About KaAklat page

Added public read-only About page and shared guest/role navigation entry with existing branding/styles. Includes demonstration purpose, officially sourced UdD introduction with labeled mission/vision summary and borrowing guide. Unprovided developer credits omitted. No schema or business-rule changes. 24 regression scripts, expanded HTTP suite, 88 PHP lints and whitespace checks PASS; guest 360/1440px layout and keyboard skip link verified. Remaining visual/accessibility limits and failed checkpoints recorded in TEST_PLAN.md. No deployment/commit/push.

### Developer information — 2026-11-09

Added user-supplied Emmanuel C. Sunga name, third-year Computer Science/Universidad de Dagupan description, developer role, email mailto link and original supplied photo (102980 bytes) to About. Original download preserved; public asset is public by explicit user request. No unprovided adviser/team/portfolio credits invented. Changed views/about.php and scoped app.css only plus the local JPEG asset. PHP view lint and existing 28 portal presentation checks PASS; guest browser 360x800 and 1440x900 inspected, photo loaded at intrinsic 1080px and correct mailto destination confirmed, mobile horizontal overflow false. Temporary viewport reset. Full regression/HTTP suite not rerun for this static content addition; prior About results are historical. Screen-reader/real-device/zoom checks not run. No schema, authentication or borrowing changes; no commit/push/deployment.

### Shared logout confirmation — 2026-11-09

Moved Log out from the dashboard-only form into shared signed-in navigation (all roles, including pending students). GET /logout.php displays a no-store confirmation page; Stay signed in returns to the dashboard without ending the session. Yes, log out requires POST, valid CSRF and confirm=yes before clearing the session and redirecting to login. No JavaScript dependency, external dialog, schema or circulation change.

Verification: preimplementation HTTP test failed on Logout GET requests confirmation. First implementation run found the pending-student test depended on the removed dashboard form token; changed fixture token retrieval to the confirmation form. Final full HTTP suite PASS, retained scratch kaaklat_acceptance_4c5d6e3ed15a. Existing campus assertions now require shared logout links. 24 regression scripts PASS; 88 PHP lints PASS; forms.js syntax and whitespace PASS. IAB fictional active-student fixture kaaklat_acceptance_028db9acd4a3: About navigation opens confirmation, Stay signed in returns to authenticated dashboard, keyboard Return confirms logout and shows login success message. Mobile 360x800 and desktop 1440x900 inspected; mobile overflow false. Browser viewport reset and temporary port8033 server stopped. No real account signout/data mutation. Real devices, screen readers, 400% zoom and other-role visual checks not run. JS-disabled behavior is supported by native links/forms, but browser JS-disabled mode was not separately exercised. No commit/push/deployment.

### Full local review — 2026-11-09

24 regressions, complete HTTP suite, seven concurrency suites/17 scenarios, 52 fictional restore checks, 96 PHP lints, JS syntax and whitespace PASS. Browser 34 page/role combinations at each of 360/1440px (68 views), no overflow/visible runtime errors; five demo inconsistency counts zero, health ready. No blocking application defect found. Added ignore rules for generated audit and research outputs, preserving files. Full commands, scratch resources, failed read-only helper checkpoints and limits: verification/FULL_LOCAL_CHECK_2026-11-09.md. Ready for scoped commit/normal push; none executed. Hosted/performance/real-device/screen-reader/400%-zoom/real-demo recovery checks remain separate.

### Numbered catalog pagination — 2026-11-09

Requirement: direct numbered page selection in the book catalog for every authorized role. Added same-filter total count, effective page/total pages metadata, semantic bounded-number view and responsive 44px links. Existing 12-book size and filter/search/availability/access semantics retained. Valid page requests beyond matches now clamp to the last page, including page1 for zero matches.

Tests first: tests/catalog_integration_test.php failed on missing total before implementation. Final tests/run_regression.php PASS (25 scripts, including new pagination view test). tests/pickup_http_acceptance_test.php PASS including new tests/catalog_pagination_http_checks.php: fictional 25-book filtered collection, student/pending/librarian/admin page2 count/current state/every-filter retention, page10000 clamp to page3 and empty matches reset. Retained scratch kaaklat_acceptance_3159047dd2a8. Same-filter SQL tests verify archived exclusion, availability, combined filters and literal search. View tests cover first/middle/last/single pages, bounded navigation up to10000 pages, meaningful accessible labels and preserved escaped search strings. PHP8.4.14 lint app/public/views/tests/bin:99 files PASS; git diff --check PASS (line-ending warnings only).

Actual IAB: all four account states at360px and1440px show Page2of3/current2 without horizontal overflow. Active student keyboard Return on Go to page3 loaded final single-book page and retained title filter. Mobile and administrator desktop screenshots inspected. Temporary viewport reset; loopback port8035 stopped. No demo-data changes, new concurrency test, hosting/load benchmark, screen-reader/400%-zoom/real-device or explicit JS-disabled browser check. Normal native links need no JS. Count adds a filtered read query; large-data performance remains unmeasured. No migration/commit/push. New fixtures are fictional only; unrelated collection manifest preserved.

### Catalog pagination centering correction — 2026-11-09

User screenshot identified off-center controls. Root cause reproduced at1920px: inherited p max-width74ch constrained the summary despite flex-basis100%, and later generic nav[aria-label$=pages] set justify-content:flex-start. Initial controls midpoint was129.24px right of navigation midpoint. Removed summary width cap/centered text and placed nav.catalog-pagination center/gap rule after generic pagination styles. Added stylesheet cache version. No query/markup/access/policy change.

Verified actual browser at1920/1440/360px: controls midpoint error below0.01px, summary on a separate line, no horizontal overflow. Corrected administrator desktop screenshot inspected; keyboard focus visible. Pagination presentation test, layout PHP lint and git diff --check PASS. Full regression/HTTP suite not rerun for this CSS-only correction; previous functional evidence retained. Earlier browser sweep checked overflow and missed this visual alignment defect. Temporary viewport reset, fictional preview server stopped. No commit/push.

### Student catalog status labels — 2026-11-09

Requirement: small personal request/loan labels on individual catalog cards, visible without opening a book. Added catalog_own_request_states (one batch for up to12 visible IDs, current DB student identity), allowlisted label view and scoped wrapping using existing badges. Pending, awaiting pickup, active and overdue only; closed/expired/returned/other-owner records omitted. Manila due date and exact48-hour expiry follow existing rules. No schema/service mutation, persisted status, staff circulation grant, demo-data change or polling.

Tests first: catalog_status_test.php failed before implementation because label view was absent. Final tests/run_regression.php PASS26 scripts. New temporary-table tests cover four states, due today, exact pickup expiry, returned/cancelled/rejected exclusion, visible IDs, ownership between students, forged session role, pending status, suspended and stale sessions, and unknown label exclusion. Full tests/pickup_http_acceptance_test.php PASS including new catalog_status_http_checks.php: correct label attached to each of four cards, no label for returned/other-owner book, other student sees only own label and staff see none. Retained fictional scratch kaaklat_acceptance_184555770a66. PHP8.4.14 lint app/public/views/tests/bin102 files and git diff --check PASS (line-ending warnings only).

Actual IAB student fixture at1440px and360px: all four labels, no horizontal overflow/clipping; desktop/mobile screenshots inspected. Pending fixture with no requests and librarian/admin showed zero labels at360px. Temporary viewport reset; port8036 preview stopped. No real-device/screen-reader/400%-zoom/explicit JS-disabled browser, load benchmark, concurrency or hosting rerun for this read-only increment. Prior pagination checks remain in suite. No commit/push; unrelated manifest and prior uncommitted pagination work preserved.

### Separate Chapters 1 and 2 document — 2026-11-09
Created output/documents/KaAklat_Chapters_1_2.docx from the user-supplied SE1 System/KaAklat_Chapters_1_2_3_Methodology_Revised.docx. Preserved title page, school header, chapter text and figure; excluded Chapter 3 and later material. Added a real Word TOC restricted to Chapters 1–2 and refreshed it in Microsoft Word. Verified all internal TOC links resolve, chapter text matches the source, header appears on all 12 pages, and all 12 rendered pages were visually inspected. Removed a trailing blank page. Packaged renderer failed because LibreOffice is absent; used Word PDF export and bundled Poppler for visual QA instead. Original file untouched; no application tests needed or run. Next: user/adviser review of the separate copy.

### Synthesis and contextual research gap — 2026-11-09
Applied the user-approved shorter synthesis and explicit contextual research gap to output/documents/KaAklat_Chapters_1_2.docx. Exactly three synthesis paragraphs replaced; preceding text verified unchanged. Word refreshed TOC page numbers and exported 12 pages; bundled Poppler rendered all pages. Pixel comparison found changes only on pages 2, 11 and 12, which were visually reviewed; remaining pages matched prior reviewed renders. Internal TOC anchors resolve and all pages retain the school header. Original three-chapter document unchanged; pre-edit extract retained at tmp/chapters12/before_synthesis.docx. No application tests run for this document-only edit. Next: adviser review of gap wording.

## 2026-11-10 — Local catalog performance

Added an operator-run isolated benchmark and loopback-only router. Final fixture has 100 fictional titles, 300 copies, 30 students, three staff, 183 requests and 123 loans. All 900 HTML samples validated with zero failures across normal browsing, search, combined filters, availability and pagination. Highest final five-session scenario p95 was 284.106 ms against the 2,000 ms target. Saved raw timings, environment details and EXPLAIN plans. No query, schema or index change was warranted. PHP lint and all 26 regression scripts passed. Five interactive browser users, hosted page timing and production capacity remain unverified.

### References for Chapters 1 and 2 — 2026-11-11
Created output/documents/KaAklat_Chapters_1_2_With_References.docx because the existing extract was locked for writing. Added 19 cited bibliography entries copied from the supplied three-chapter original, excluding uncited ISO 2011 and WCAG entries. Preserved the shortened synthesis and school header. Word refreshed the TOC, now including References on numbered page 11; final length 14 physical pages. Word export and bundled Poppler rendering passed; original 12 pages pixel-identical except updated TOC; contents and two reference pages visually reviewed. Source bibliographic claims/URLs transferred, not independently researched. Initial in-place write failed due to lock; separate copy succeeded. No application changes/tests. Next: use the new copy for review.

### Conceptual framework arrow visibility — 2026-11-11
Created output/documents/KaAklat_Chapters_1_2_Clear_Arrows.docx and companion KaAklat_Conceptual_Framework_Clear_Arrows.png. Built-in image editing darkened the two forward arrows and output-to-input feedback loop; labels visually verified. Prompt requested exact existing wording/layout/pastel boxes with bold charcoal arrows only. DOCX ZIP comparison confirms only word/media/image2.png changed; original references copy preserved. Word export remained 14 pages; page text matches after excluding PDF extraction whitespace. Exact pixel comparison failed due to minor text rendering differences; this is not reported as pixel-identical. Conceptual framework rendered page inspected and arrow visibility confirmed. No application change/tests. Next: review the updated copy.

## User-reported manual acceptance — 2026-11-13

The user confirms that actual 400% zoom, physical-device and screen-reader checks all passed for the current interface. These are user-operated acceptance results, not independently observed automated results. No problems were reported. Device/browser/screen-reader versions and detailed procedures were not supplied. This supersedes earlier current-interface notes marking those three checks unverified; dated historical evidence retains its original scope. No code or pilot data changes. Hosting remains deferred. No commit or push performed.

### Paper formatting and Related Systems TOC — 2026-11-13
Created output/documents/KaAklat_Chapters_1_2_3_Formatting_Fixed.docx from the newly supplied November 12 source, preserving original. Changed four interior Chapter 1 section starts to continuous and compacted empty section terminators; added Related Systems bookmark, clickable TOC row/PAGEREF and keep-with-next formatting. Word refreshed page fields; 51 pages became 49 with no header-only blank pages. All chapter/body text, 26 table text sequences and 12 media binary hashes match source; internal TOC anchors resolve and school header is present on all49 pages. Word/Poppler renders inspected across page contact sheets and enlarged final TOC/Related Systems pages. Existing intentional space around figures and chapter starts retained. Initial save failed because output/documents directory was absent; created it and retried successfully. No application tests or source research performed. Next: user review of formatted copy.

### Follow-up spacing and Related Systems alignment — 2026-11-13
User screenshots showed large gaps left by the previous formatting pass. Created output/documents/KaAklat_Chapters_1_2_3_Layout_Refined.docx: consolidated interior Chapter1 sections, removed forced Heading2 page starts, matched Related Systems paragraph formatting to Synthesis and retained keep-with-next, changed conceptual framework and catalog ERD placement to inline without altering image bytes, and removed obsolete empty ERD spacer paragraphs. Preserved source security diagram overlapping-object arrangement after intermediate render exposed a duplicate; intermediate clipping/duplicate render was corrected before delivery. Word refreshed linked TOC fields; final47 pages (previous49). Verified all body text,26 table text sequences and12 media hashes unchanged; all internal TOC links resolve; school header appears on every page. Rendered page contact sheets and enlarged affected pages inspected. Remaining whitespace from full-size figures, tables and chapter boundaries is expected; no image resizing or content edits. Original and prior copy preserved. No application tests.

### Hierarchical TOC formatting — 2026-11-13
Created output/documents/KaAklat_Chapters_1_2_3_Styled_TOC.docx. TOC1 bold major chapter/reference/appendix rows with group spacing; TOC2 indented regular subheading rows; retained linked page-reference fields and refreshed in Word.47 pages. Exact document text/media unchanged; non-TOC page text unchanged; all internal links resolve. TOC rendered and visually verified. Initial whole-PDF-text comparison differed because dot leaders changed; corrected comparison excludes styled TOC and checks exact OOXML text instead. No application tests. Prior copy preserved.


## Controlled pilot restoration — 2026-11-14

Controlled current-pilot backup/restore rehearsal completed locally. Backed up kaaklat_pilot and restored only into new kaaklat_pilot_restore_a4dc97bdafc7 and a separate private application directory. Passed 251 restore checks plus 15 backup safety unit checks: exact rows/definitions across all 12 tables; 86 titles/274 copies/31 requests/16 loans/30 existing accounts preserved; all 143 cover files matched, referenced covers decoded, and derived catalog availability matched. Verified CHECK/FK/username/pending-title/active-copy uniqueness and rollback, scratch-only session/recovery quarantine, fictional authentication and request/approval/pickup/return, authorized original-cover retrieval and private-path denial. Final source comparisons confirmed pilot rows/schema/covers/config/source unchanged during the rehearsal. No accounts or fictional books were added to the pilot.

SQL import measured 0.617s; import-through-restore-verification measured 7.714s on this local run, within the 30-minute local target for the tested dataset. This is not a disaster-recovery guarantee. Original backup plus checksum-verified protected same-disk archive retained; off-device copy, daily RPO evidence and participant evaluation remain outstanding. Scratch server stopped; usual pilot restarted at http://127.0.0.1:8000 and health returned HTTP200/ready. First scratch attempt stopped on an invalid lowercase fictional copy code; corrected to uppercase and rerun in a new scratch. Both attempts retained. No application feature/schema change, commit, push or deployment. Prior unrelated documentation edits preserved.

Evidence: verification/PILOT_BACKUP_RESTORE_2026-11-14.md. Next: protected off-device copy and refreshed current-version performance evidence, then participant evaluation.


### Local performance refresh — 2026-11-14

Refreshed SRS NFR-06/T-32 using the existing isolated benchmark:100 fictional titles/300 copies/30 students/3 staff,183 requests/123 loans;14 current catalog scenarios. All2,520 measured responses plus252 warmups validated with exact content/order/totals and session identity.30 single-session samples and150 five-session concurrent samples per scenario. Maximum scenario p95 303.394ms versus2,000ms; largest response 352.383ms; zero failures/redirects/timeouts. Query plans reviewed; no confirmed application defect or query/index/schema change needed.36 regressions and416 HTTP assertions passed after timing, runner/router lint and whitespace passed. All pilot table rows/schema/covers/config fingerprints match; no pilot test records added. Metrics/raw CSV/EXPLAIN/source hashes retained in verification/performance-2026-11-14/ and verification/PERFORMANCE_2026-11-14.md.

Measured local loopback HTML/TTFB and Windows single-process queueing only; no new browser-paint, asset/GD, LAN, endurance, large-scale or hosted performance claim. Isolated benchmark server stopped, ordinary pilot8000 healthy. Existing research/backup/docs changes preserved. Hosting deferred; no commit/push. Next: participant evaluation and protected off-device backup/daily RPO evidence. Usage tool unavailable.


## Approved public entry design — 2026-11-14

Implemented the accepted landing/login/registration mockup, with a matching recovery form. Photo-led entry panels, concise field guidance, native expandable recovery help, password visibility buttons, light/dark layouts and finite reduced-motion-aware animation reuse existing images and palette. About remains in guest navigation and its complete content is unchanged. How borrowing works remains a real landing anchor. Prototype controls, simulated submissions and fictional preview messages were not copied into the application.

Verification: 36 regression scripts; 427 isolated workflow HTTP assertions; 132 PHP files linted; JavaScript syntax and strict static UI audit passed (zero findings). Chromium covered 64 entry route/theme/viewport combinations (320/360/390/768/820/1024/1280/1440px), images, wrapping, keyboard password/recovery controls, error summary/field focus, reduced motion, 320 CSS-pixel reflow, JS-disabled invalid registration, and active/pending student/librarian/admin sign-in. Zero browser runtime exceptions. Guest shell styles are scoped separately from authenticated navigation, including authenticated visits to entry routes. Pilot rows/schema/covers/configuration match the fresh read-only baseline; no test accounts added to pilot. Existing backup/performance/research work preserved. Actual 400% zoom, physical-device/Safari and screen-reader checks for this new presentation remain manual. Hosting deferred; no commit/push.

Evidence and replay commands: verification/ENTRY_DESIGN_2026-11-14.md.
