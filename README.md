# KaAklat

**A local school-library catalog and borrowing system for Universidad de Dagupan pilot testing.**

KaAklat helps students find physical books and follow their borrowing requests. Librarians verify student accounts, review requests and record physical pickup and return. Administrators manage staff access and review audit records.

The current delivery is a **local pilot**. Hosting is deferred. Implemented features and recorded tests do not constitute a production-readiness, school-policy or accessibility certification. Use real tester information only with the school’s and participants’ permission.

## Contents

- [Features and roles](#features-and-roles)
- [Student guide](#student-guide)
- [Librarian guide](#librarian-guide)
- [Administrator guide](#administrator-guide)
- [Borrowing rules](#borrowing-rules)
- [Appearance and keyboard navigation](#appearance-and-keyboard-navigation)
- [Run the existing local pilot](#run-the-existing-local-pilot)
- [Set up a new development installation](#set-up-a-new-development-installation)
- [Create accounts](#create-accounts)
- [Code, pilot data and backups](#code-pilot-data-and-backups)
- [Testing and current limits](#testing-and-current-limits)
- [Troubleshooting](#troubleshooting)
- [Project structure and documentation](#project-structure-and-documentation)

## Features and roles

| Capability | Student | Librarian | Administrator |
| --- | --- | --- | --- |
| Authenticated catalog, book details, covers and filters | Yes; pending students can browse | Yes | Yes |
| Submit requests and view own history/profile | Active students; own records only | No student borrowing permissions | No student borrowing permissions |
| Cancel eligible own requests | Yes | May cancel uncollected approvals with explanation | No circulation permissions |
| Verify school IDs and activate pending students | No | Yes, after an in-person check | Cannot perform the librarian’s initial identity approval |
| Suspend/reactivate students | No | Yes | Yes, subject to existing account rules |
| Manage books, authors, categories, covers and copies | No | Yes | No catalog-mutation permissions |
| Approve/reject requests; record pickup and return | No | Yes | No circulation permissions |
| Create/manage staff accounts | No | No | Yes, with protection for the last usable administrator |
| Assisted password recovery | Receives assistance | May assist students | May assist permitted accounts |
| Browse administrator audit records | No | No administrator audit browser | Yes |
| Settings: System default / Light / Dark | Yes | Yes | Yes |

The interface includes role-specific dashboards/navigation, search and bounded pagination, borrowing-status badges, active/overdue/returned history and clear validation/error states. Navigation visibility is not authorization: the server checks roles, account status, sessions and ownership.

## Student guide

1. Select **Create an account** and enter your school student ID, display name, username and password.
2. Use the printed student ID format **`YY-####-###`**, for example the fictional `26-0123-456`. Leading zeros matter. An ID can be claimed by only one account, including pending accounts.
3. Sign in using your **username and password**, not your student ID. You can browse while pending.
4. Visit the librarian with your physical school ID. Borrowing becomes available after the librarian verifies and activates your account.
5. Open **Book catalog**, use the filters or numbered pages, then open a book’s details. For an available book, choose **Request this book**, request 1–30 days and explain why you need it.
6. Follow **My requests & loans**. Approval means a copy is ready for pickup; it does not mean the loan has started. Collect it before the displayed deadline.
7. Return the physical copy to the librarian. Only the librarian can record pickup and return.

**My Profile** is view-only and shows your own basic information, recorded school ID and linked borrowing counts. Do not share accounts or passwords. If your ID is already claimed, contact the library; registration does not reveal the other account.

## Librarian guide

- **Students:** check the physical school ID in person, confirm the check and activate the pending account. Resolve a mistaken pending ID claim only after checking identity. Suspension and other sensitive account changes require the affected user to sign in again.
- **Manage books:** maintain titles, authors, categories, publication years, descriptions, covers and individually tracked copies. Internal copy codes identify copies; they are not barcodes. Archive eligible records to preserve history.
- **Review requests:** review the student’s eligibility, original reason/duration and current availability. Approve with a final duration or reject with an explanation. Explain a changed duration. Approval allocates one copy for pickup.
- **Pickups:** identify the student and allocated physical copy before confirming handover. This starts the loan and its due date.
- **Returns:** identify and receive the physical copy before confirming return. Returns remain possible for overdue or suspended borrowers. Archived titles/copies remain unavailable afterward.
- **Account recovery:** verify identity in person before issuing a recovery secret. It is private, expires and can be used only once. Never request or reveal the user’s current password.

Always read the displayed result. A stale form, expired pickup or record decided by another librarian may require refreshing; do not assume an action succeeded merely because its button was clicked.

## Administrator guide

Use **Staff accounts** for librarian/administrator creation and permitted access changes, **Students** for permitted student status management, **Account recovery** for assisted recovery and **Audit records** for recorded administrative activity.

Administrators do **not** automatically receive librarian approval, pickup, return or catalog-editing privileges. Staff accounts are created through administration, not public registration. Initial staff passwords are assigned using the local operator tool described below.

Audit records are operational evidence; they intentionally exclude passwords, recovery secrets and sensitive borrowing/decision text. Do not post private records or configuration in GitHub issues.

## Borrowing rules

These are the **approved demonstration/pilot defaults**, not independently verified university policy. [SRS.md](SRS.md) and [SCHEMA.md](SCHEMA.md) define the exact rules and lifecycle.

| Rule | Behavior |
| --- | --- |
| Requested/final duration | Whole days, 1–30 |
| Borrowing reason | Required, 10–500 characters |
| Changed-duration/rejection explanation | Required, 1–500 characters |
| Pending requests | Maximum five per student; do not reserve copies |
| Duplicate title | One outstanding pending request, live approved pickup or active loan per student/title |
| Borrowing allowance | Maximum five combined live approved pickups and active loans |
| Availability | A copy must currently be available to request; this does not guarantee later approval |
| Pickup deadline | 48 hours after approval; expired uncollected allocations cannot be collected |
| Loan clock | Starts at actual librarian-recorded physical pickup |
| Due date | Calculated using Asia/Manila; the entire displayed due date is allowed |
| Overdue | Starts the following Manila calendar day; blocks new requests, approvals and pickup, but never return |
| Cancellation | Students can cancel eligible own pending/uncollected requests; librarians need an explanation to cancel an uncollected approval |

Availability is derived from copy/loan records. Nonarchived copies become available after a return or eligible cancellation. Expired allocations are handled through ordinary application activity and derived queries, not an email or background timer.

Renewals, fines, ISBNs, barcode scanning, email verification/reminders, waitlists and automated notifications are not part of the current implemented scope. There is no email self-service password-reset workflow.

## Appearance and keyboard navigation

Open **Settings → Appearance** and choose **System default**, **Light** or **Dark**. The preference belongs to the browser, survives logout and also applies to signed-out pages; it is not synchronized to an account or another device. On a shared browser, the next user sees the last saved appearance.

- **Tab / Shift + Tab:** move between controls and links.
- **Enter:** activate a focused link or button; **Space:** select a focused radio/checkbox or activate a button.
- For the appearance **radio group**, Tab enters the group at the selected choice. Use **arrow keys** to switch choices, then Tab to leave. Tab does not visit each radio separately.
- **Skip to content** bypasses navigation. At narrow widths, the navigation row scrolls horizontally; keyboard focus brings its links into view.

If browser storage is blocked, the page explains that a choice could not be saved. With JavaScript disabled, the device theme still applies; manual appearance controls explain that JavaScript is needed. A recent user-reported keyboard concern awaits confirmation after the native radio instructions; earlier testing is not proof that every accessibility state works.

## Run the existing local pilot

**For the operator who already has the configured pilot on this computer.** Since2026-11-11, the main Git repository is the single maintained application source and its private configuration connects to `kaaklat_pilot`. The database, accounts, covers and configuration are **not included in a GitHub clone**.

1. Start **MySQL in XAMPP**.
2. Open the main project folder in VS Code, then its PowerShell terminal:

   ```powershell
   cd 'C:\Users\63961\Desktop\Emman\KaAklat2.0'
   & .\start-local.ps1
   ```

3. Open **http://127.0.0.1:8000**. Keep the terminal open; Ctrl+C stops the server.

The launcher verifies the local database name before starting. If script execution is blocked, first confirm the same guard from the main folder, then start PHP:

```powershell
& 'C:\php-8.4.14\php.exe' bin/check_local_pilot.php
if ($LASTEXITCODE -ne 0) { throw 'Pilot configuration was not verified.' }
& 'C:\php-8.4.14\php.exe' `
  -d "session.save_path=$PWD/private/pilot-sessions" `
  -S 127.0.0.1:8000 -t public
```

The active website uses **`kaaklat_pilot`**, its preserved pilot cookie name and `private/pilot-sessions`. The old demo database remains unused and separate. No demo records were imported or merged during consolidation. Existing pilot accounts and passwords persist when PHP/MySQL restart. The old pilot code snapshot has been archived outside this project. Use only the maintained root startup helper; do not run an archived launcher.

Always confirm the working folder and private database configuration before running an account or data-management tool. Do not seed, reset or import over the pilot. Catalog quantities are carried-over test inventory until physically verified by the school.

## Set up a new development installation

**For a fresh clone only. Do not run these database-import steps on an existing installation or pilot.**

### Requirements

- PHP **8.4** CLI; selected Windows installation is `C:\php-8.4.14\php.exe`.
- MySQL-compatible **InnoDB** database: MariaDB 10.4+ or MySQL 8.0.16+ compatibility target. Verify the actual server/constraints rather than assuming compatibility from a client version.
- PHP extensions: PDO/pdo_mysql, session, mbstring, fileinfo, GD with JPEG/PNG support and OpenSSL. cURL is also used by HTTP verification tools. Check with `php -m` using your selected executable.
- Git, a modern browser and a trusted local machine. Windows PowerShell is used by the private password prompt tools.

Application stack: plain PHP/PDO, relational SQL, server-rendered semantic HTML, custom CSS and minimal JavaScript. No Laravel, Bootstrap, Composer or npm build step. Python is optional tooling for a scoped static UI audit, not a website runtime requirement. Node is needed only for the supplied JavaScript/browser checks.

### Installation

1. Clone the repository and open its root in VS Code:

   ```powershell
   git clone https://github.com/sungaec334stud-debug/KaAklat_2.0.git
   cd KaAklat_2.0
   ```

2. Start the local database service. In phpMyAdmin, create a **new, empty** database, for example `kaaklat_local`, using utf8mb4/utf8mb4_unicode_ci.
3. Select that new database. Import these files **once, in order**, stopping on any error:
   1. `schema.sql`
   2. `migrations/001_book_categories_year.sql`
   3. `migrations/002_student_school_id.sql`

   **Do not import `seed.sql` for a clean installation.** It is fictional development data. Do not rerun schema/migrations over populated tables, force through errors or disable foreign keys to bypass failures. A fresh clone contains no private pilot database, accounts or uploaded covers.

4. Create `private/covers` and `private/fresh-sessions`. Keep these outside `public/`. Restrict private-folder access to trusted operators; do not assume PHP `chmod` alone secures Windows permissions.
5. Create **`private/config.local.php`** with your own local credentials:

   ```php
   <?php
   return [
       'app_env' => 'local',
       'app_name' => 'KaAklat',
       'timezone' => 'Asia/Manila',
       'session_name' => 'kaaklat_local_unique',
       'database' => [
           'host' => '127.0.0.1',
           'port' => 3306,
           'database' => 'kaaklat_local',
           'username' => 'YOUR_LOCAL_DATABASE_USER',
           'password' => 'YOUR_LOCAL_DATABASE_PASSWORD',
       ],
   ];
   ```

   These are placeholders, not working credentials. Use a dedicated local application database user where possible; schema setup and scratch tests may require a separate operator account. Never commit this file. `.env.example` is explanatory; the application loads `private/config.local.php`.

6. Create the first administrator using [Create accounts](#create-accounts), then start PHP from this new installation root:

   ```powershell
   & 'C:\php-8.4.14\php.exe' `
     -d "session.save_path=$PWD/private/fresh-sessions" `
     -S 127.0.0.1:8000 -t public
   ```

7. Sign in and create catalog records through an authorized librarian account. Do not expect the private pilot catalog/covers to appear in a fresh clone.

The PHP development server is for controlled local use. Serve **only `public/`**, never the repository/pilot root. For supervised LAN testing, see the network notes below; this is not deployment.

## Create accounts

Run operator commands from the installation you intend to change. Passwords are entered privately in an interactive Windows terminal; do not put passwords directly in command arguments or screenshots.

### First administrator

```powershell
& 'C:\php-8.4.14\php.exe' bin/bootstrap_admin.php 'your_admin_username' 'Your Display Name'
```

Replace both values. Bootstrap refuses to run if a usable administrator already exists. That is a safety check, not a reason to delete the existing administrator. Use assisted recovery or the authorized local password tool for a known existing account.

### Librarian and additional administrator

1. Sign in as administrator → **Staff accounts** → create the account with the correct role.
2. Newly created staff have no usable password until the operator assigns one:

   ```powershell
   & 'C:\php-8.4.14\php.exe' bin/set_user_password.php 'created_staff_username'
   ```

3. Enter a private password when prompted, then test sign-in. Passwords must be 12–72 UTF-8 bytes; non-ASCII characters can occupy more than one byte. A plain passphrase is easier to size.

### Student

Register through the website, then have a librarian verify the physical school ID and activate the account. Public registration cannot create staff or automatically grant borrowing access. Existing legacy accounts without IDs are not a model for new registration.

### Forgotten password

Contact the librarian/administrator for the permitted identity-checked recovery process. A recovery secret expires after 30 minutes and works once; successful recovery invalidates old sessions and outstanding secrets. Do not share it. There is no emailed reset link.

## Code, pilot data and backups

| Tracked in Git | Stored privately outside Git |
| --- | --- |
| PHP/CSS/JS/templates, public branding assets | Database accounts, password hashes, requests/loans and audit records |
| Schema/migrations and clearly fictional seed | Configuration credentials, database exports and backups |
| Tests and sanitized verification evidence | Uploaded covers, runtime sessions, private logs and archived snapshots |

**A Git commit/push does not back up accounts or borrowing records.** Do not upload a database dump, private configuration or real participant records to GitHub. Public logos/illustrations are tracked; protected catalog cover files are separate.

The main repository now serves the pilot directly; source edits there affect the local website without copying code between folders. Keep the active private configuration, `private/covers`, `private/pilot-sessions` and `kaaklat_pilot` database protected. The old pilot code snapshot is now in the protected external cleanup archive. Test feature changes using isolated fictional fixtures before allowing tester traffic; do not run scripts that reset/import/write fixture records into the pilot. Database migrations require a separate reviewed backup procedure. A Git checkout/pull alone must never replace private operational data.

### Manual backup

Follow the full [backup and restore operator guide](docs/local-backup-restore.md). In the intended installation:

1. Keep MySQL running; stop PHP and all other writers, including imports, editing tools and tests.
2. Run:

   ```powershell
   $backupName = 'local-' + (Get-Date -Format 'yyyyMMdd-HHmmss')
   & 'C:\php-8.4.14\php.exe' bin/local_backup.php $backupName --writes-paused
   if ($LASTEXITCODE -ne 0) { throw 'Backup failed. Stop and investigate.' }
   ```

3. Confirm verification succeeds, then protect the completed bundle under `private/backups`. Make a protected copy outside the project and on a separate device. The flag acknowledges a manual write pause; it does not enforce maintenance mode.

Back up before data/schema changes and during active use. Never restore over the active database to “test” a backup. Use a separately named empty database, separate application/configuration, new cookie and session directory; revoke restored sessions/recovery secrets as documented. Restoring an older backup also restores older password/account state. A checksum-verified backup is not proof of a successful full restoration or an off-device backup.

### Archived local material

Old temporary artifacts, research outputs, import material, code snapshots and earlier backups were relocated to `C:\Users\63961\Documents\KaAklat-Archives\cleanup-20261111`. They remain protected and outside Git. Current runtime files and the latest pilot backup remain in this project. See [cleanup inventory and verification](verification/PROJECT_CLEANUP_2026-11-11.md). Archived launchers/configurations are historical; continue using `& .\start-local.ps1` from the main folder. This archive is on the same disk; keep an off-device protected backup too.

## Testing and current limits

Read [TEST_PLAN.md](TEST_PLAN.md) for the exact scope. The main repository's default connection is now the pilot: inspect test isolation before running a script. Use only explicitly isolated temporary-table/new-scratch-database fixtures, never seed/reset the active pilot. Some scripts create retained scratch databases and require local operator privileges. Do not paste private fixture manifests or logs into public issues. The consolidation used the HTTP harness's new scratch database and read-only role checks; it did not rerun arbitrary mutation tools on tester accounts.

```powershell
& 'C:\php-8.4.14\php.exe' tests/run_regression.php
& 'C:\php-8.4.14\php.exe' tests/pickup_http_acceptance_test.php
node tests/theme_test.cjs
```

Optional operator checks include the fictional backup/restore rehearsal, independent-process concurrency suites, `tests/theme_browser_check.cjs` (installed Chrome/Node required) and `tests/catalog_performance.php`. Review each script before execution; scratch resources are deliberately retained, not indiscriminately deleted.

Recorded 2026-11-10 evidence includes 26 regression scripts, 313 named HTTP checks, 12 theme-controller checks and 248 authenticated page/theme/viewport combinations. Local catalog HTTP performance passed its two-second p95 target; the highest final scenario p95 was 284.106 ms across 900 valid measured responses. These are dated results, not guarantees about another machine, later edits or production capacity. Windows’ PHP development server serializes requests.

User-reported local acceptance and the later appearance-keyboard follow-up are recorded separately. Full screen-reader/interactive-state accessibility, controlled full regular-data restore, off-device backup routine, school evaluation and hosted checks remain outstanding or deferred. Hosting is outside the current local phase. See [progress](PROGRESS.md), [theme evidence](verification/THEME_NAVIGATION_2026-11-10.md), [performance evidence](verification/PERFORMANCE_2026-11-10.md) and [pilot preparation](verification/PILOT_PREPARATION_2026-11-10.md).

## Troubleshooting

| Problem | What to check |
| --- | --- |
| Database unavailable | Start MySQL; check this installation’s private host/port/database/credentials and required migrations. Never reset/reimport an existing database as a routine fix. |
| Address already in use | A server may already run on 8000. Identify its process/folder before stopping it; do not kill all PHP processes. |
| Browser rejects `0.0.0.0` | That is a listening address. On the server computer open `http://127.0.0.1:8000`. |
| Phone cannot connect | Use the same trusted Wi-Fi and `http://COMPUTER-IP:8000`, including the port. Loopback-only PHP cannot accept phone connections. Supervised LAN mode requires an intentional server bind change and narrowly scoped firewall permission. Do not disable the firewall, forward router ports or expose the development server publicly. |
| Wrong/missing accounts | The maintained main folder must use `kaaklat_pilot`. Run the startup guard; do not switch to `kaaklat_dev` or import demo records. The old private code copy is archived. |
| “A usable administrator already exists” | Use the existing administrator or authorized recovery; do not bypass bootstrap protection. |
| Student cannot borrow | Check pending/suspended state, librarian activation, overdue loans, pending/allowance limits, duplicate title and available copies. |
| Appearance cannot be saved | Browser storage may be blocked. The page reports the limitation; System follows device appearance. |
| Tab skips Light/Dark options | Native radio groups use arrow keys within the group. Tab enters/leaves it. |
| Old styles after an update | Hard-refresh with Ctrl+F5; confirm the updated files are in the installation actually being served. |
| Session expires or old form is refused | Sign in again or refresh the page. Current status/session checks and CSRF/stale-record protections remain authoritative. |

For a bug report, record the page, role, browser, theme, screen size, steps, expected/actual behavior and safe error reference. Remove personal identities, passwords, secrets and configuration before sharing.

## Project structure and documentation

```text
app/            Authentication, validation, PDO and circulation services
public/         Only HTTP document root; entry pages and public assets
views/          Escaped server-rendered UI templates and shared layout
bin/            Operator-only account and backup tools
migrations/     Numbered additive schema changes
tests/          Fictional unit/integration/HTTP/concurrency/browser checks
verification/   Sanitized, dated verification evidence
docs/           Operator guides and design/implementation notes
private/        Ignored current configuration, covers, backups and pilot sessions
tmp/            Ignored temporary fixture resources and diagnostics
schema.sql      Baseline schema; apply migrations afterward
seed.sql        Optional fictional development seed, not clean-pilot data
```

| Document | Purpose |
| --- | --- |
| [PROGRESS.md](PROGRESS.md) | Current handoff, completed work and remaining checks; dated notes may be historical |
| [SRS.md](SRS.md) | Requirements and approved defaults versus proposals |
| [SCHEMA.md](SCHEMA.md) | Relationships, lifecycle, constraints and lock order |
| [TEST_PLAN.md](TEST_PLAN.md) | Test procedures and actual evidence/limits |
| [WORKFLOW.md](WORKFLOW.md) | Development/operator workflow; check current handoffs before older phase examples |
| [Backup/restore guide](docs/local-backup-restore.md) | Protected backup and isolated recovery precautions |
| [DEPLOYMENT.md](DEPLOYMENT.md) | Future deployment/recovery considerations; hosting deferred |
| [DESIGN.md](DESIGN.md) / [UX-CONTRACT.md](UX-CONTRACT.md) | Shared interface tokens, navigation and behavior owners |
| [CHANGELOG.md](CHANGELOG.md) / [prompt.md](prompt.md) | Change history and implementation requests/outcomes |

University branding and catalog metadata/cover assets retain their respective owners’ rights. This README does not grant redistribution rights to third-party material. Confirm school authorization and applicable asset permissions before wider distribution.
