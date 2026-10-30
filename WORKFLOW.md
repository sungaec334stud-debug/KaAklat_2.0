# KaAklat beginner development workflow

Prepared 2026-10-29. Option A: plain PHP 8.4, PDO MySQL, a MySQL-compatible database, semantic HTML, custom responsive CSS and minimal JavaScript. This is a future work guide. Preparing these documents does not implement the application, start a database, create a hosting account or prove that deployment works.

Start with `DESIGN_BRIEF.md`, `SRS.md`, `SCHEMA.md`, `TEST_PLAN.md` and `AGENTS.md`. Follow the confirmed requirements; proposed defaults in the brief remain proposals until accepted by the school. Keep the original research paper unchanged. `schema.sql` and fictional `seed.sql` belong at the project root. `DEPLOYMENT.md` gives the detailed hosting and recovery procedure.

## How to use this guide

Complete one phase and its verification before proceeding. Ask an agent to implement only that phase, inspect its changes, then record the actual result in `PROGRESS.md` and a short change in `CHANGELOG.md`. “Verified” must say what command or browser check passed, when and in which environment. A future command is not test evidence.

Commands below are for Windows PowerShell in this workspace. The `&` runs the explicitly named executable. Keep passwords out of commands, screenshots and chat. Database `-p` asks for a password interactively. Do not paste command examples containing replacement values without editing them.

```powershell
Set-Location 'C:\Users\63961\Desktop\Emman\KaAklat2.0'
```

At the beginning, this was an empty project folder. The handoff adds design documents and SQL, but application folders, a test runner and admin-bootstrap scripts still need implementation. No `npm`, Composer, Laravel or other framework is needed. Git protects source history; it does not back up the live database or uploaded covers.

## Phase 1 — Understand the scope and prepare Git

**Goal:** keep a reviewable starting point before application code. Covers NFR-08.

**Prerequisites:** the handoff files and Git. Read the borrowing rules and role matrix first. Administrative account management does not grant librarian circulation powers.

**Actions:**

1. Open this project folder in your editor. Read `AGENTS.md`, `DESIGN_BRIEF.md` and `SRS.md`.
2. Run the checks below. If `.git` exists, inspect `git status` rather than initializing another repository.
3. Before staging, create `.gitignore` with the entries shown below. Private local configuration, runtime logs, uploads and backups must not enter Git. Tracked schema and fictional seed data are intentional exceptions to a general “do not commit database exports” rule.
4. If `.git` does not exist, run `git init -b main`. Set your own Git display name and email locally if Git asks; do not invent another person's identity.
5. Stage the named documentation files and review them, then commit. A remote repository is optional at this point. If you later create one, choose its visibility deliberately and upload source only after checking for secrets.

```powershell
git --version
Test-Path .git
git init -b main
git config user.name 'YOUR DISPLAY NAME'
git config user.email 'YOUR EMAIL'
git add -- .gitignore AGENTS.md DESIGN_BRIEF.md SRS.md SCHEMA.md schema.sql seed.sql WORKFLOW.md DEPLOYMENT.md TEST_PLAN.md PROGRESS.md CHANGELOG.md prompt.md STACK_AND_HOSTING_REVIEW.md RESEARCH_REVISION_PLAN.md
git diff --cached --stat
git diff --cached
git commit -m 'Document KaAklat scope, design and development workflow'
git status
```

Run `git init` only for the new repository; run the two configuration commands only after replacing their values. If a named handoff file is missing, resolve that omission before staging it.

Suggested `.gitignore` contents:

```gitignore
/private/
/storage/
/backups/
/tmp/
/.env
/.env.*
!/.env.example
**/config.local.php
/public/uploads/
*.log
```

**Expected result:** a first commit containing reviewed docs and fictional SQL, with no credentials or actual student records.

**Verify:** inspect `git status` and the staged diff before committing. Confirm that sensitive folders are ignored with `git check-ignore private/config.local.php backups/example.sql public/uploads/example.png`.

**Common problems and fixes:** “not a git repository” means you are in the wrong folder or have not initialized it. “author identity unknown” needs your local name/email settings. If a secret was committed, stop sharing that commit, rotate the secret and arrange history cleanup; ignoring it later does not remove it from history.

**Sample agent prompt:** “Read AGENTS.md and the design documents. Inspect Git state and propose the smallest first implementation phase. Verify ignored private paths. Do not implement features or deploy yet.”

**Checkpoint/recovery:** write the commit ID from `git rev-parse HEAD` in `PROGRESS.md`. If files change unexpectedly, inspect `git diff`; preserve work before attempting any restoration. Avoid reset/clean commands as beginner recovery tools.

## Phase 2 — Verify the local tools and create a small PHP shell

**Goal:** run one harmless page with the intended PHP version. Covers NFR-08.

**Prerequisites:** Phase 1; installed Git, PHP and XAMPP. The earlier inspection found PHP 8.4.14 at `C:\php-8.4.14\php.exe` and a separate PHP 8.2.12 bundled with XAMPP. XAMPP's MariaDB client version does not establish its server version.

**Actions:**

1. Verify executable paths and extensions. Use the explicit PHP path throughout development.
2. Ask the agent to create only the application shell: `public/` entry pages/assets, `app/` helpers, `views/` templates and ignored `private/` configuration/logging. Add `tests/` only when it contains actual tests. Keep a non-secret configuration example in Git.
3. Give `public/index.php` a minimal welcome response. Serve only `public/`, never the repository root. The PHP built-in server is for local development, not hosting.

```powershell
& 'C:\php-8.4.14\php.exe' -v
& 'C:\php-8.4.14\php.exe' --ini
& 'C:\php-8.4.14\php.exe' -m
& 'C:\xampp\mysql\bin\mysql.exe' --version
```

After `public/index.php` exists:

```powershell
& 'C:\php-8.4.14\php.exe' -l public/index.php
& 'C:\php-8.4.14\php.exe' -S 127.0.0.1:8000 -t public
```

Leave that terminal running and open `http://127.0.0.1:8000` in a browser. Stop the local server with Ctrl+C when finished.

**Expected result:** the page loads locally using PHP 8.4. PDO, `pdo_mysql`, sessions, `mbstring`, `fileinfo` and GD are checked; GD is required before safe cover uploads. Missing extensions are not proven present merely because a DLL exists.

**Verify:** confirm the page and private-path 404 responses. Check the active `php.ini` from `--ini`. Enable missing bundled extensions in that actual configuration and restart the PHP server. Preserve a copy before editing the configuration.

**Common problems and fixes:** “Directory public does not exist” means the shell has not been created. “Address already in use” means another program uses port 8000; stop the old local server or use an unused port such as 8001 consistently. A phone cannot reach your PC using its own `127.0.0.1`; use emulation now and hosted phone checks later.

**Sample agent prompt:** “Implement only the minimal PHP shell from Phase 2. Use public as the document root, private config outside it, and a tracked example without credentials. Run syntax checks with C:\php-8.4.14\php.exe. Do not add login or catalog yet.”

**Checkpoint/recovery:** commit the working shell. Record PHP version, extensions, active configuration path and local URL. If an extension breaks startup, restore the saved configuration and inspect the error before changing more settings.

## Phase 3 — Start the database and import fictional data

**Goal:** verify schema compatibility and a private PDO connection. Covers NFR-01, NFR-03 and NFR-08.

**Prerequisites:** Phases 1–2; reviewed `SCHEMA.md`, `schema.sql` and `seed.sql`. This is a fresh local development database, never a live database.

**Actions:**

1. Open XAMPP Control Panel and start its MySQL service. Do not start a second database service on the same port. The button may say MySQL even when the installed server is MariaDB.
2. Connect using the administrator account actually configured locally. These examples use local `root` only for setup; its password is not assumed. The application must use a separate database user.
3. Inspect the server and create a new database. If `kaaklat_dev` already exists, stop and inspect it; do not overwrite it or import blindly.

```powershell
& 'C:\xampp\mysql\bin\mysql.exe' -h 127.0.0.1 -u root -p --execute="SELECT VERSION(); SHOW ENGINES;"
& 'C:\xampp\mysql\bin\mysql.exe' -h 127.0.0.1 -u root -p --execute="CREATE DATABASE kaaklat_dev CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
& 'C:\xampp\mysql\bin\mysql.exe' -h 127.0.0.1 -u root -p kaaklat_dev --execute='source C:/Users/63961/Desktop/Emman/KaAklat2.0/schema.sql'
& 'C:\xampp\mysql\bin\mysql.exe' -h 127.0.0.1 -u root -p kaaklat_dev --execute='source C:/Users/63961/Desktop/Emman/KaAklat2.0/seed.sql'
& 'C:\xampp\mysql\bin\mysql.exe' -h 127.0.0.1 -u root -p kaaklat_dev --execute="SHOW TABLES; SELECT TABLE_NAME, ENGINE, TABLE_COLLATION FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE();"
```

4. For a beginner-friendly database-user setup, start XAMPP Apache temporarily, open `http://localhost/phpmyadmin`, and use User accounts → Add user account. Create a local-only user with a unique password and grant only SELECT, INSERT, UPDATE and DELETE on `kaaklat_dev`. Use the account host that matches the tested connection; do not allow connections from every host. Stop Apache afterward if no longer needed. Keep schema changes under the separate setup account.
5. Write those credentials into ignored private configuration. Set PDO to exception mode, `utf8mb4`, and native prepared statements. Set application time handling explicitly: store instants consistently, calculate due dates using `Asia/Manila`, and document the database-session timezone.
6. Confirm from PHP that the application account can read and update a disposable fictional record within a transaction that rolls back. Never print the DSN password or stack trace in the browser.

PowerShell does not support the common Bash-style `mysql ... < schema.sql` example. Use the `--execute='source C:/.../schema.sql'` form above. Do not rerun the schema or seed on an existing populated database as an “update.”

**Expected result:** schema and seed import without errors; all transactional tables use InnoDB and the supported collation. Fixture accounts have no usable shared login password. Initial admin creation comes in Phase 5.

**Verify:** follow `SCHEMA.md` constraint checks; confirm foreign-key rejection, uniqueness and rollback on disposable data. Record the actual `SELECT VERSION()` result. Compare seed counts with its documented contents.

**Common problems and fixes:** connection refused usually means the service/port is wrong; inspect XAMPP logs. Access denied requires checking username, host and password, not disabling authentication. “Could not find driver” requires enabling `pdo_mysql` in PHP 8.4's configuration. On a partial import, preserve the error and use a differently named empty test database for the corrected attempt; do not delete the existing database reflexively.

**Sample agent prompt:** “Validate schema.sql and seed.sql on the actual local server. Implement the private PDO configuration and non-public connection check. Report server version, table engines and constraint/rollback results. Do not disclose credentials or create public admin installers.”

**Checkpoint/recovery:** record the schema commit, server version and import result. Save a private local export before later schema changes. Retain failed-import evidence and isolate the next trial in a new database.

## Phase 4 — Prove the host before building full features

**Goal:** catch hosting incompatibility early. Covers NFR-01, NFR-03 and NFR-08.

**Prerequisites:** Phase 3 and the owner's decision to proceed with the recommended InfinityFree free plan. No account or deployment was created during the documentation handoff.

**Actions:** follow `DEPLOYMENT.md` sections “Early hosting gate” and “Private configuration and document root.” The owner creates the free account and direct subdomain, then uses its actual database settings. Upload only a small temporary diagnostic and non-secret test fixtures, using private configuration only after public-access denial is verified.

**Expected result:** HTTPS, PHP/PDO, sessions, GD and InnoDB behavior are demonstrated on the assigned account. SQL compatibility is based on the real server, not the advertised version. Browser checks pass with JavaScript and cookies enabled.

**Verify:** exercise foreign keys, rollback and independent database connections for locking; prove private files cannot be fetched; inspect secure cookies. Remove diagnostics and confirm their URLs no longer work. Record pass/fail evidence for every gate in `PROGRESS.md` without passwords or internal configuration dumps.

**Common problems and fixes:** the hosted database hostname is usually not `localhost`; copy it from the account panel. A successful automated HTTP status can be the provider's browser-check page, so inspect the real browser result. If privacy, transactions or HTTPS cannot be established, pause host-dependent implementation and revise the hosting choice; do not weaken the application requirements.

**Sample agent prompt:** “Prepare the smallest temporary hosted diagnostic described in DEPLOYMENT.md, with no phpinfo dump or publicly displayed credentials. Make the files and checks reviewable first. Record untested items honestly and remove diagnostics after verification.”

**Checkpoint/recovery:** keep a sanitized deployment note: canonical URL, PHP/database versions, host layout and check results. Preserve a local working copy. Hosting failure does not require rebuilding the application from scratch.

## Phase 5 — Build accounts, sessions and permissions

**Goal:** establish secure identity before adding private records. Covers FR-01, FR-02, FR-12; NFR-01 and NFR-02.

**Prerequisites:** working local database and the hosting gate. Read the exact status/role rules and security requirements in `SRS.md`.

**Actions:** implement registration, login/logout, pending-student activation by librarians, administrator staff management, suspension and staff-assisted recovery. Add shared authorization/ownership checks that every protected action calls. Build a local CLI or other controlled initial-admin bootstrap; its command must be documented only after the script exists. Never publish a web installer or commit usable seed passwords. Use password hashing/verification, CSRF, parameterized SQL, escaping, generic sign-in errors, persistent sign-in throttling and secure sessions. Apply 12–72 byte passwords and a 30-minute idle timeout as specified. Check current role/status and session revocation on protected requests.

**Expected result:** pending students can read the catalog after login, but borrowing waits for activation. Suspended users cannot log in. An administrator cannot acquire librarian powers by changing a URL. Recovery uses an in-person one-time secret, expires after 30 minutes, changes the password and revokes prior sessions.

**Verify:** use separate browser profiles for student, librarian and admin. Attempt protected URLs while logged out, role spoofing, another student's identifiers, CSRF failure, repeated bad sign-ins, idle expiry, suspension of an already signed-in user and replay of a used recovery token. Check that passwords and tokens do not appear in logs.

**Common problems and fixes:** repeated login redirects often mean cookies/session paths are wrong. `Secure` cookies need HTTPS on hosting; local HTTP must have an explicit development-only configuration. Never switch production cookie security off to fix a local problem. UI-hidden buttons are not authorization; test direct POST requests locally.

**Sample agent prompt:** “Implement Phase 5 against FR-01, FR-02 and FR-12. Keep admin and librarian permissions separate. Add meaningful security tests and document the real commands you create. Use individual test credentials generated locally, never shared seeded passwords.”

**Checkpoint/recovery:** commit after passing role/session tests. Keep bootstrap secrets private and remove temporary bootstrap artifacts from any hosted upload. Revoke test sessions after security changes.

## Phase 6 — Build books, authors, physical copies and covers

**Goal:** create a catalog whose displayed availability matches physical copies. Covers FR-03, FR-04; NFR-01 and NFR-03.

**Prerequisites:** working authorization and verified GD. Read schema relationships and cover constraints.

**Actions:** implement librarian catalog forms for title, multiple authors, description, optional cover and copy quantity. Adding N copies creates N unique internal copy records; there is no ISBN or barcode. Derive counts from copy/assignment state. Archive specific available copies instead of erasing history. Block archive of books/copies with live assignments. Implement the JPEG/PNG validation, 2 MB/4000×4000 limits, decoder verification, re-encoding, random filenames and non-executable storage from `DEPLOYMENT.md`.

**Expected result:** students see login-protected catalog details, correct counts and a placeholder where a cover is absent. Unauthorized users cannot mutate catalog data or upload files.

**Verify:** add a fictional title with two authors and three copies; verify three distinct copy IDs. Test editing, archive restrictions, Unicode, HTML-like input, duplicate copy codes, false image extensions, malformed/oversized images and forbidden uploads. Ensure failed uploads do not leave an orphan file or incorrect database reference.

**Common problems and fixes:** repeated author text should not create unintended duplicate author relationships. A missing cover should show a placeholder, not break details. File permissions should permit only the necessary upload location; do not make the entire site writable. Rejected large images should produce a clear validation message rather than a memory error.

**Sample agent prompt:** “Implement FR-03 detail views and FR-04 catalog management. Generate individual copy records and derived counts. Enforce safe GD cover uploads server-side. Preserve history by archiving and test forbidden roles and invalid image inputs.”

**Checkpoint/recovery:** commit the catalog increment and back up both database and covers before changing file layouts. Keep replaced images until the associated database update succeeds; clean orphaned files only after reviewing references.

## Phase 7 — Build the request, approval, pickup and return flow

**Goal:** make the core library transaction correct under repeated and concurrent actions. Covers FR-05 through FR-10; NFR-02 and NFR-03.

**Prerequisites:** accounts, catalog and transaction tests. Read `SRS.md`, `SCHEMA.md` and the circulation cases in `TEST_PLAN.md`.

**Actions:** implement small reusable services in this order: student request/cancel; librarian approval/rejection with copy allocation; expiry release; physical pickup; physical return; student history/librarian outstanding lists; essential audit. Apply all current design rules: one copy per request; 1–30 requested/approved days and 10–500-character reason; decision note for rejection or changed duration; at most five pending requests; no duplicate live request for a title; combined approved pickups plus active loans at most five. Pending requests do not use the borrowing allowance.

Use a transaction and a consistent lock order for every allocation-changing action. Lock the student's eligibility/cap check and the affected request/copy before writing. Recheck stale status inside the transaction. Expire uncollected approvals at approval time plus 48 hours, including at the exact deadline. Availability excludes expired holds even before cleanup; allocation releases a conflicting expired assignment inside its transaction. No cron is required. Pickup starts the loan clock; due date is the Manila pickup date plus approved days. Returning on the due date is on time. Overdue loans block new requests, approvals and pickups, but never returns. No cancellation after pickup.

**Expected result:** only librarians record pickup/return; cancellation/expiry releases the assigned copy; double submits do not create duplicate loans or returns; suspension still permits staff to record a return. Audit records avoid raw reasons and secrets.

**Verify:** use independent connections/browser sessions for two librarians approving the last copy and two approvals near the five-book cap. Verify only one conflicting operation succeeds and every failure rolls back. Test exact expiry, due-date boundaries, duplicate POST, cancel-versus-pickup, return twice, and returning a suspended student's loan. Check that each student sees only their own history.

**Common problems and fixes:** stale browser counts are not authoritative; recheck in the transaction and show a friendly conflict message. Single-threaded development-server behavior can hide races: concurrency tests must use genuinely independent database connections/processes, not merely quick sequential clicks. Never fix a race by removing a uniqueness constraint.

**Sample agent prompt:** “Implement Phase 7 using the current schema and policy proposals. Cover last-copy and five-book races, expiry at the exact boundary, duplicate returns and suspension-safe returns. Report the real locking strategy and run independent-connection tests before claiming success.”

**Checkpoint/recovery:** commit each completed transition separately with its tests. Before schema changes, export a private backup and rehearse the change on a clone. Preserve unexpected transaction records for diagnosis instead of editing them manually into a passing state.

## Phase 8 — Complete search, filters and daily-use lists

**Goal:** help users find books and librarians find outstanding work. Covers FR-03, FR-09 and NFR-06.

**Prerequisites:** catalog and circulation are working.

**Actions:** add title/author search, useful availability filtering, stable pagination and clearly labelled sorting. Parameterize values and allowlist sort names/directions; handle literal `%` and `_` intentionally. Keep the same canonical availability/expiry rules as approval. Show the student's request decisions and due dates; show librarian pickup/outstanding/overdue lists. A separate reports/export module is outside this MVP.

**Expected result:** searches preserve filters across pages and do not duplicate books with multiple authors. Empty results offer a useful message. Status labels reflect the same rules used by transactions.

**Verify:** test spaces, Unicode, apostrophes, multiple authors, empty/long queries, page bounds and invalid sort input. Use `EXPLAIN` in the database client/phpMyAdmin for important queries. Measure the proposed 100-title/300-copy fixture rather than claiming performance from a nearly empty database.

**Common problems and fixes:** duplicate rows usually need a corrected join/query design. A filter that treats an expired hold as unavailable must use the shared live-assignment rule. Add indexes based on actual query plans, not guesses.

**Sample agent prompt:** “Implement FR-03 search/filter/pagination and FR-09 lists. Keep SQL parameterized and sorting allowlisted. Verify multi-author results and expired-hold availability; record query-plan findings without adding an advanced reports module.”

**Checkpoint/recovery:** commit query/UI changes and record test fixture sizes. Retain the previous query and measured results in Git if an optimization regresses behavior.

## Phase 9 — Make every workflow responsive and accessible

**Goal:** make the same tasks usable on phones, tablets and desktops. Covers NFR-04 and NFR-05.

**Prerequisites:** representative forms, tables, errors, empty states and real workflow content exist.

**Actions:** apply semantic headings, explicit labels, visible focus, readable contrast, keyboard operation, helpful validation messages and touch-friendly controls. Test at 360, 768 and 1280 CSS pixels, at 200% zoom, and on an actual phone through HTTPS. Provide usable narrow-screen table/card behavior without hiding essential due dates or action labels. Ensure validation works without application JavaScript; the provider's browser check still requires JavaScript on hosting.

**Expected result:** no clipped critical content or accidental page-wide horizontal scroll; users can complete tasks by keyboard; errors identify both the problem and the field.

**Verify:** walk through login, request, approval, pickup and return at each size. Tab through controls, test focus after errors/dialogs and check text/background contrast against relevant WCAG 2.2 AA targets. Record actual checks; do not claim formal accessibility certification.

**Common problems and fixes:** desktop fixed widths cause overflow; use flexible layout and deliberate breakpoints. Placeholder text is not a label. Color alone is not enough for overdue/decision status. A desktop browser's mobile emulation is useful but does not replace an actual-phone check.

**Sample agent prompt:** “Improve the existing pages for 360/768/1280 widths and keyboard use without changing business rules. Check labels, focus, contrast, zoom and error messages. Provide screenshots and a concise list of remaining accessibility limitations.”

**Checkpoint/recovery:** commit the verified layout increment. Keep screenshots in a non-public evidence location without personal records or credentials. If a visual change breaks a task, use the diff to isolate the CSS change.

## Phase 10 — Run the release checks and restore rehearsal

**Goal:** establish evidence that the demo is ready. Covers FR-01–FR-12 and NFR-01–NFR-08.

**Prerequisites:** implemented features and the current `TEST_PLAN.md`. No test runner exists merely because this document mentions tests.

**Actions:** run every relevant manual and automated case; verify each requirement has evidence. The implementing agent must add exact test commands to `TEST_PLAN.md` when it creates the runner. Do not copy a speculative `tests/run.php`, PHPUnit or npm command. Run syntax checks with the installed PHP:

```powershell
$phpFiles = Get-ChildItem -Path app, public, views, tests -Filter *.php -File -Recurse -ErrorAction SilentlyContinue
if ($phpFiles.Count -eq 0) { throw 'No PHP files found; syntax validation did not run.' }
foreach ($phpFile in $phpFiles) {
    & 'C:\php-8.4.14\php.exe' -l $phpFile.FullName
    if ($LASTEXITCODE -ne 0) { throw "Syntax check failed: $($phpFile.FullName)" }
}
```

Use fictional performance fixtures of 100 titles, 300 copies, 30 student accounts and three staff accounts, generated by an explicitly reviewed future fixture tool. These counts are the test target, not a claim about `seed.sql`. Measure five simultaneous browser users locally. Record warmed catalog server response p95 against the proposed two-second target, with machine, sample size and procedure; the PHP development server alone is not a production-capacity test. Observe hosted pages against a five-second target under recorded network conditions; do not load-test shared free hosting aggressively.

Export a backup and restore it into a new, empty local database using `DEPLOYMENT.md`. Confirm accounts, copies, requests, loans, audit history and covers agree. Target a successful same-day restore within 30 minutes.

**Expected result:** no unresolved critical permission, privacy, data-integrity or recovery defects. Failed checks remain visible with owners and next actions.

**Verify:** record command output/exit status, browser evidence, independent-connection race results, timing and restore duration in `PROGRESS.md`. Syntax success alone does not prove functionality.

**Common problems and fixes:** a passing empty suite proves nothing; check tests executed and fixtures were loaded. Keep test data isolated from live data. If a performance target fails, inspect the slow query/path and remeasure after the specific fix.

**Sample agent prompt:** “Execute TEST_PLAN.md against this exact revision. Use real available commands, report tests executed and failures, run concurrency checks with independent connections, and rehearse backup restoration. Update PROGRESS.md with evidence and unverified items.”

**Checkpoint/recovery:** commit the tested revision and retain its local backup/evidence. Fix critical failures before preparing the presentation release; preserve failing output for diagnosis.

## Phase 11 — Deploy and rehearse the presentation

**Goal:** produce a verified HTTPS demonstration and a usable local fallback. Covers NFR-07 and NFR-08.

**Prerequisites:** passing Phase 10, the early hosting gate and a planned short maintenance window for any update. Read all of `DEPLOYMENT.md` before uploading.

**Actions:** record the release commit; export current data/covers off-host; enable maintenance before a paired code/schema update; upload the reviewed file set; import into the intended empty database or apply an individually tested migration; configure private credentials; verify HTTPS/cookies and run hosted smoke checks. Do not reimport `seed.sql` into a used demo database. Remove diagnostics and bootstrap artifacts. Keep maintenance active until checks pass. Rehearse the demonstration with separate student/librarian/admin browser profiles, then verify the actual phone and presentation network.

**Expected result:** a canonical direct-subdomain HTTPS URL works with fictional data and the checked release. The local fallback is on the same tested revision with its own private database.

**Verify:** hosted login → request → approve → physical pickup → return; rejected/expired/cancelled requests; last-copy/cap conflicts; unauthorized direct access; covers; logout; no private-file exposure. Record observed hosted behavior separately from local test results.

**Common problems and fixes:** incomplete uploads can mix old/new code, so deploy during maintenance and verify the file manifest. A database import failure is not fixed by importing again blindly. If a release fails, follow the rollback decision tree in `DEPLOYMENT.md` and preserve any transactions already accepted.

**Sample agent prompt:** “Prepare the release package and exact deployment checklist for the tested commit. Exclude secrets, SQL exports, tests and diagnostics from public files. After an authorized deployment, perform hosted smoke checks and record failures honestly. Preserve current data before any rollback.”

**Checkpoint/recovery:** record release commit, schema version/change list, upload time and backup locations without secrets. Keep a verified local presentation fallback. Do not record fabricated evaluation results in the paper.

## Phase 12 — Maintain, resume and improve safely

**Goal:** keep progress recoverable and the demonstration current. Covers FR-10, FR-11; NFR-07 and NFR-08.

**Prerequisites:** an assigned backup/release owner and a working restore procedure.

**Actions:** during active work, manually back up database and changed covers daily, and before every release/presentation. Target no more than 24 hours of data loss; this depends on actually completing the backups. Check the hosted site manually, its account notices, storage, error references and certificate. Follow provider inactivity rules; do not add scheduled pings, cron substitutes or email dependencies. Rehearse restore after schema/storage changes. Review requirements and school permission before putting real student records on a free demo host.

For each new work session:

```powershell
Set-Location 'C:\Users\63961\Desktop\Emman\KaAklat2.0'
git status
git log -5 --oneline
Get-Content PROGRESS.md
```

Read `AGENTS.md`, the relevant requirements and the most recent checkpoint. Choose the next small incomplete item. At the end, update progress with what changed, checks actually run, known issues and one concrete next action.

**Expected result:** another session can resume without repeating completed work or confusing planned features with working features.

**Verify:** confirm backups exist outside Git/public folders and open a recent export; run periodic local restore checks. Compare the deployed release with the release record before changing it.

**Common problems and fixes:** “it worked last time” is not current evidence; check account/runtime changes and logs. Git cannot recover a hosted loan record absent from your backups. Free-host suspension should trigger the local fallback and investigation, not a promise of guaranteed uptime.

**Sample agent prompt:** “Read AGENTS.md, PROGRESS.md and the relevant requirements. Inspect current Git/files without discarding changes. Continue the next unfinished checkpoint only, verify it, and leave precise resume notes. Keep credentials and backups private.”

**Checkpoint/recovery:** keep source history, release notes and off-host database/cover backups together by release/date, with secrets stored separately. After data loss or an uncertain release, stop new writes, preserve current evidence and follow `DEPLOYMENT.md` recovery steps before restoring anything.
