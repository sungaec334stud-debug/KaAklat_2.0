# KaAklat deployment and recovery guide

Prepared 2026-10-29 (Asia/Manila). This is a deployment plan for the selected plain-PHP option. Official hosting documentation was reviewed on this date; **no account, live PHP/database connection, upload, HTTPS behavior or full application deployment was tested during this handoff**. Record real results in `PROGRESS.md` as they are obtained.

Requirements covered: FR-01–FR-12 as hosted smoke checks; particularly FR-04 covers and FR-11 backup/restore, plus NFR-01 security, NFR-02 privacy, NFR-03 integrity, NFR-06 performance, NFR-07 recovery and NFR-08 maintainability.

Use this with `WORKFLOW.md`, `TEST_PLAN.md`, `SCHEMA.md`, root `schema.sql` and root `seed.sql`. Proposed policy defaults are documented in `DESIGN_BRIEF.md`; deployment must not silently change them.

## Hosting decision and its limits

**Recommendation: InfinityFree's free plan for a small school demonstration, subject to the early hosting gate below.** It advertises no credit card, no trial expiry, free subdomains, PHP 8.4, MySQL 8.0/MariaDB 11.4, 5 GB website space and 400 databases. Actual assigned versions and limits must be checked. The project needs neither a paid domain nor a paid API. [Official free-plan page](https://www.infinityfree.com/).

Use the direct free-subdomain address assigned to the account, without `www` or another level of subdomain. System-managed HTTPS certificates cover direct free subdomains; the application still needs HTTPS URLs and an HTTP-to-HTTPS redirect. [Official free-subdomain SSL guidance](https://forum.infinityfree.com/t/why-ssl-doesnt-work-on-www-subdomains-and-other-free-subdomain-ssl-issues/116209).

| Constraint checked in official sources | Consequence for KaAklat |
| --- | --- |
| FTP or online File Manager is offered. [Plan](https://www.infinityfree.com/) | Manual upload/import is the baseline. Do not assume SSH, shell access, Git pull or a deployment pipeline. |
| PHP/HTML/JavaScript files are limited to 1 MB each, `.htaccess` to 10 kB, other files to 10 MB. Oversized files can disappear after upload. [File limits](https://forum.infinityfree.com/t/file-size-and-upload-limits/49308) | Keep source files small. The application's stricter 2 MB cover limit still applies. Verify uploaded files. |
| No fixed database storage/query quota is published in the cited Admin reply; fair use and query behavior still limit load. Excess load can cause suspension. [Capacity](https://forum.infinityfree.com/t/the-capacity-of-the-free-account-mysql/93016), [database load](https://forum.infinityfree.com/t/what-does-mysql-overloading-mean/49333) | Use indexed, paginated queries and a small demo fixture. “Unlimited” marketing is not permission for heavy tests. |
| Cron is disabled and periodic automated-request replacements are prohibited. [Cron policy](https://forum.infinityfree.com/t/cron-job-feature-is-now-disabled/80495) | Release expired pickups during normal actions and calculate overdue labels on read. Use manual backups/checks; no cron workaround. |
| PHP mail is heavily restricted; external SMTP is documented. [Email guidance](https://forum.infinityfree.com/t/how-to-send-email-with-gmail-smtp/49239/) | The MVP has no email dependency. Decisions appear in the dashboard and recovery is staff-assisted in person. |
| Mandatory browser checks need JavaScript and cookies; automated HTTP/API clients and external monitors can receive a challenge instead of the app. [Browser security](https://forum.infinityfree.com/t/browser-security-system-features-and-limitations/49353) | Verify with real browsers. An HTTP 200 from a script does not prove an application page worked. Do not claim JavaScript-disabled hosted operation. |
| No provider backup recovery is supplied on the free plan. [Admin backup explanation](https://forum.infinityfree.com/t/mysql-database-disappeared-wordpress-database-recovery/120319) | Keep off-host SQL and file backups and perform local restore tests. |

Additional documented hit/CPU, inode and inactivity limits are linked in `STACK_AND_HOSTING_REVIEW.md`. Check the actual account panel and current policy before the presentation. A free demo host is not evidence of suitability for daily school operations or real student data. AwardSpace was screened out because its free tier does not support SSL; details are in the hosting review.

## Owner's preparation checklist

1. Select a presentation owner and a backup owner; these may be the same person for the demo. Decide who retains account credentials and off-host backups.
2. Create the free account and direct subdomain through the official site. Verify account email and review the current terms. This guide does not create an account for you.
3. Record the actual canonical HTTPS URL and deployment date. Store hosting, database and file-transfer passwords in a private password store, never in this document, Git, screenshots or chat.
4. In the hosting database panel, create a database and record the assigned database name, username and hostname privately. Use the exact values given; do not invent a server hostname or reuse the local `root` account.
5. Open the provider's phpMyAdmin/database manager. Hosting may restrict external database connections; plan to import/export through its panel and connect from hosted PHP. Do not assume the Windows MySQL client can connect directly to hosting.
6. Inspect File Manager for the actual site document root, account base path and allowed writable directories. `htdocs` is a common panel label, not a verified path for this account.

## Early hosting gate — before full application development

Use fictional data only. Prepare the smallest temporary diagnostic and test schema needed to answer these checks. A diagnostic must not show `phpinfo()`, full environment/configuration dumps, passwords, connection strings, internal paths, student records or detailed exceptions publicly. Prefer a temporary access-controlled page showing only allowlisted pass/fail results, with technical detail in a private log. It is removed when the check is complete.

| Check | Evidence required before proceeding |
| --- | --- |
| Canonical HTTPS | Direct subdomain opens without a certificate warning. HTTP reaches the HTTPS address before any login form accepts credentials. No mixed-content requests. |
| PHP and extensions | Actual PHP version and availability of PDO MySQL, sessions, `mbstring`, `fileinfo` and GD recorded privately/sanitized. JPEG and PNG decoding/re-encoding succeeds. |
| Private storage | A non-secret canary in the intended private location cannot be fetched by an unauthenticated browser. PHP can read the same file internally. See layout checks below. |
| Database | Hosted PHP connects with the actual assigned account; record `SELECT VERSION()`, InnoDB support and the accepted `utf8mb4` collation. |
| Constraints | A disposable foreign-key violation and duplicate unique key are rejected. Existing valid rows remain unchanged. |
| Transactions | Insert/update in a transaction followed by rollback leaves no change. Do not use DDL as the rollback test; schema statements may implicitly commit. |
| Row locking | Two genuinely independent database connections contend for the same disposable row; record which blocks/fails and verify only the intended committed outcome. Sequential clicks alone are insufficient evidence. |
| Sessions | Login-shaped session data persists between requests; regeneration changes the session identifier; cookie flags match the HTTPS requirements. |
| Error handling | A controlled failure returns a generic message/reference; private logging records useful diagnostics without secrets. |
| Cleanup | Temporary diagnostic URLs no longer return diagnostic content after removal; temporary public test files and credentials are absent. |

Do not proceed with real records or depend on the host if HTTPS, private storage, InnoDB transactions/constraints or secure sessions fail. Preserve local progress, document the failed gate and reassess the hosting choice. Hosting database grants may be broader than desired; record the actual privileges and keep the credentials out of every public file response.

## Private configuration and document root

The intended local source layout is:

```text
KaAklat2.0/
  public/          browser entry pages, CSS, JavaScript, safe cover outputs
  app/             database, authorization, validation and circulation functions
  views/           templates
  private/         local configuration and logs; ignored by Git
  tests/           future test code; not uploaded publicly
  schema.sql       initial empty-database schema
  seed.sql         fictional demonstration data
```

**Preferred hosted layout:** put the contents of local `public/` in the account's actual document root. Keep `app/`, `views/` and private configuration outside that root if the provider permits PHP access there. Resolve include paths on the server explicitly. Confirm both filesystem permissions and PHP restrictions such as `open_basedir`; do not assume a writable parent folder is also readable by PHP. Never upload the whole repository into the public directory.

Before putting any credential in the private location, place a harmless text canary there and attempt its corresponding URL in a clean browser session. It must be inaccessible. Then confirm the application can read it internally. A `.php` file that returns a blank page is not proof the folder is protected: test a plain text file too.

**Fallback only if the host cannot support the preferred layout:** create a dedicated denied directory beneath the document root for `app/`, `views/`, config and logs. Establish and test server-side denial before uploading secrets. On Apache 2.4, a candidate `.htaccess` in that directory is:

```apache
Require all denied
```

This is a candidate configuration, not a claim that it works on the assigned host. Test a direct URL to a harmless `.txt` canary, a harmless PHP canary and nested files; all must deny access (403/404) and never disclose content. Repeat after deployment/configuration changes. Disable directory listings using the supported server configuration, and confirm the site still runs through its public entry pages. Do not rely on obscure folder names, robots.txt, a blank index page or PHP execution alone. If denial cannot be demonstrated, do not deploy secrets there; choose another layout/host.

Database exports, release archives and backup copies **always stay off-host and outside the public tree and Git**, including when this fallback is used. Never leave `.bak`, `.old`, ZIP files, editor backups or configuration copies under the web root. The upload directory has a different purpose and must never contain config, logs or SQL.

## Runtime, HTTPS and upload settings

Use a private production configuration holding the actual database values, canonical HTTPS base URL, explicit timezone settings and log location. Keep only a non-secret example in source. Disable public error display, enable private error logging and use a generic error reference. Use PDO prepared data values, exception mode, `utf8mb4` and native prepared statements. Do not grant schema-alteration privileges to a local application user; use whatever scoped account separation the host actually permits.

Set application date handling to `Asia/Manila` for pickup/due-date policy, with a documented consistent timestamp storage convention. Do not interpret the host's default timezone as the library's timezone. Test just before/at expiry and the end of the due date.

Verify HTTPS first, then enable a canonical HTTP-to-HTTPS redirect using the provider-supported mechanism or reviewed application/server configuration. Avoid copying an untested rewrite rule that can loop behind a proxy. On HTTPS set session cookies `Secure`, `HttpOnly` and `SameSite=Lax`, use session regeneration, a 30-minute idle timeout and server-side revocation/role/status checks. Inspect cookie properties in browser developer tools. Confirm logout and recovery invalidate old sessions. Never test by submitting a real password over HTTP.

For covers, implement and verify all of these before enabling the upload form:

1. Only authorized catalog staff may upload, using POST and a validated CSRF token.
2. Check the PHP upload error and actual file size; input maximum 2 MB. Accept JPEG/PNG only. Client extensions and browser MIME labels are untrusted.
3. Use `fileinfo` plus an image decoder; reject invalid/truncated images, SVG and other formats. Check dimensions before expensive processing: maximum 4000×4000 pixels, plus a memory/pixel budget appropriate to the verified hosted memory limit. A permitted dimension does not guarantee enough RAM to decode.
4. Decode and re-encode through GD to a clean JPEG/PNG; generate a random filename and controlled extension. Never preserve a submitted path or executable extension. Apply an output size/dimension cap too.
5. Store only re-encoded output in the dedicated image location, with script execution disabled using verified host configuration. Confirm the configured non-execution/denial behavior with harmless test fixtures before opening uploads. Reject arbitrary files at the application layer even when server rules exist.
6. Stage the output and coordinate file/database changes so a failure leaves no broken reference. Show a placeholder for an absent cover. The optional field must not force use of an external image/ISBN API.
7. Test a valid JPEG, valid PNG, oversized bytes/dimensions, deceptive extension, malformed file and unauthorized request. If GD is unavailable, the upload capability is a blocked requirement; do not silently accept raw files as a workaround.

The provider's PHP upload cap does not replace the application's stricter validation. Hosted file limits also apply after upload. Track cover storage usage and include cover files in backups.

## First deployment — files and a new database

Do this only after the local release checks pass. All paths below are logical mappings to inspect in the actual account, not fixed remote filesystem paths.

1. Run local checks in `TEST_PLAN.md` and record the source commit with `git rev-parse HEAD`. Prepare a manifest of files to upload. Exclude `.git`, tests, docs, `schema.sql`, `seed.sql`, backups, diagnostics, local configuration and runtime logs from public files.
2. Keep the new site in maintenance mode while configuring it. The maintenance gate must block normal mutation endpoints as well as pages; it should use HTTP 503 and allow only a controlled owner/staff verification route. This capability must be implemented and tested before releases, not assumed from a filename mentioned here.
3. In the provider panel, verify the target database name and that it is empty. Import root `schema.sql` using phpMyAdmin → the intended database → Import. Read every error. Inspect table names, InnoDB engines, collation and constraints.
4. Import root `seed.sql` only into this new demonstration database if fictional fixtures are desired. It must not create usable shared-password staff accounts. Import is not an update procedure; never reimport it over actual requests/loans.
5. Create the initial administrator through the documented secure bootstrap procedure implemented in Phase 5. If shell access is unavailable, generate the password hash locally with the implemented private bootstrap tool and insert only the required account fields through the protected database manager. Do not invent an untested SQL account insert, expose the plaintext password or upload a public setup page. Record that bootstrap is complete, without its secret.
6. Establish the verified private directory/layout and credentials. Upload the reviewed `app/`/`views/` files to their protected destinations, then local `public/` contents to the actual document root. Use a provider-supported secure file-transfer method where available; otherwise use the HTTPS authenticated File Manager. Do not assume SFTP or FTPS is offered without checking.
7. Configure HTTPS, sessions, private logs and the cover directory. Check include paths and file permissions without broad “make everything writable” changes. Ensure there is no old default page masking the app's entry page.
8. Run the hosted smoke checklist below while maintenance prevents ordinary writes. Remove diagnostic/bootstrap artifacts and verify removal. Only then open normal access and record the release.

If import or upload fails, keep maintenance active. Record the exact error privately and verify whether any partial change was applied before retrying. Use a new empty database for a fresh schema trial; preserve the failed attempt for diagnosis rather than deleting data blindly.

## Hosted smoke checklist

Record date, canonical URL, source commit, actual runtime/server versions, tester, browsers and pass/fail results. Use fictional participants and distinct browser profiles; ordinary tabs may share the same session.

- FR-01/FR-02/FR-12: registration creates a pending student; login-required catalog; librarian activation; suspended account denied; admin staff management; forbidden direct URLs/actions rejected; recovery token expires/is single-use and old sessions stop working.
- FR-03/FR-04: title/author search, pagination, book detail, correct derived counts, placeholder/valid cover and forbidden upload rejection. No private files, SQL, source backups or logs reachable.
- FR-05/FR-06: create/cancel request, reject with note, approve with copy assignment, changed duration note and 48-hour pickup expiry including the exact boundary.
- FR-07/FR-08: approved pickups plus active loans cannot exceed five; pickup starts the due-date clock; overdue blocks new borrowing but permits return; return repeated safely. Use actual independent connections to verify last-copy and cap races; do not treat local race evidence as hosted evidence.
- FR-09/FR-10: student sees only their own history; librarian sees outstanding/overdue work; audit records needed events without passwords, tokens or raw reasons.
- FR-11: export and restore sample data/covers locally. Match row counts and key relationships, then perform one full workflow on the restored system.
- NFR-04/NFR-05: 360/768/1280 CSS pixels, keyboard use, 200% zoom and an actual phone; labels/focus/errors remain usable.
- NFR-06: record ordinary hosted page timings and network conditions against the proposed five-second observation target. Do not run aggressive load tests or scheduled monitoring against free hosting.

Do not use a passing HTTP status alone as evidence because the host's browser challenge can also return success status. Keep all unverified checks explicit.

## Manual off-host backup

**Proposed target:** daily during active use, immediately before changes and before the presentation; RPO at most 24 hours and same-day local restoration within 30 minutes. RPO is the maximum tolerable loss since the latest successful backup; RTO is the restoration-time target. These are goals to measure, not guarantees.

During a backup, prevent writes briefly so SQL and cover files describe the same point in time. In the hosting panel's phpMyAdmin, select the intended database, choose Export → Custom, include all tables with structure and data, and download the SQL. Do not save it into File Manager. Record whether the export contains `CREATE DATABASE`, `USE`, `DROP` or foreign-key-check changes; inspect these before any restore. Download the matching cover directory and preserve the exact source release. Keep config secrets separately in protected storage.

Store backups in a private folder outside this repository and every served directory, for example `C:\Users\63961\KaAklatBackups\`. This is an example private location the owner creates, not a folder created during this handoff. Protect access; retain a second protected copy on another device or approved storage. Never attach actual student exports to a public issue or commit them to Git.

For a **local** database backup, the installed XAMPP client includes `mysqldump.exe`. Once the owner has created a private backup folder, use a unique timestamped filename. `--result-file` avoids PowerShell output-encoding problems, and `-p` asks for the password:

```powershell
$backupStamp = Get-Date -Format 'yyyyMMdd-HHmmss'
$backupFile = "C:\Users\63961\KaAklatBackups\kaaklat-dev-$backupStamp.sql"
& 'C:\xampp\mysql\bin\mysqldump.exe' -h 127.0.0.1 -u root -p --single-transaction --default-character-set=utf8mb4 --skip-add-drop-table --result-file="$backupFile" kaaklat_dev
if ($LASTEXITCODE -ne 0) { throw 'Backup failed; preserve the error and do not rely on this file.' }
Get-Item -LiteralPath $backupFile
Get-FileHash -LiteralPath $backupFile -Algorithm SHA256
```

This local command does not bypass hosting's remote-database restrictions. Avoid DDL during a dump; for database/file consistency, retain the short write pause. Store the export time, source release, schema changes, checksum and cover backup reference in a private backup manifest. A non-empty file/checksum proves that a file exists, not that restoration succeeds.

## Restore rehearsal into a new local database

1. Choose a specific backup with matching covers/source release. Keep the live database untouched. Inspect the SQL for embedded database selection or destructive statements. A copied export must not redirect import into `kaaklat_dev` or a live database.
2. Start the local XAMPP database service. Create a new, uniquely named empty restore database. The example name is for one rehearsal; if it exists, choose a new name rather than dropping it.

```powershell
& 'C:\xampp\mysql\bin\mysql.exe' -h 127.0.0.1 -u root -p --execute="CREATE DATABASE kaaklat_restore_20261029 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

3. Replace the example backup filename below with the actual reviewed export. The `source` command imports through the database client; PowerShell `<` redirection is not used.

```powershell
& 'C:\xampp\mysql\bin\mysql.exe' -h 127.0.0.1 -u root -p kaaklat_restore_20261029 --execute='source C:/Users/63961/KaAklatBackups/REPLACE_WITH_ACTUAL_BACKUP.sql'
```

4. Do not import `schema.sql` or `seed.sql` as well when the backup already contains structure/data. Read all import errors. Give a temporary local application user access to only this restore database, and point a separate ignored local configuration at it.
5. Restore matching covers into a separate local test copy, then run the matching application revision with its `public/` directory as the web root. Keep the current development copy/configuration intact.
6. Compare table counts and meaningful relationships: copies per book, assigned copies, active loans, due dates, users/statuses and audit history. Check representative covers. Perform login, request, approval, pickup and return using fictional accounts on the restored copy.
7. Record start/end time, elapsed minutes, source backup, checks and any loss. Keep the restored copy until the rehearsal is accepted; no deletion is required to declare the rehearsal successful.

If the restore fails, retain the source backup and logs. Fix the cause in another new restore database. Never overwrite the only backup or promote an unverified restore into live use.

## Updating an existing site

The initial schema is not a migration system. After first deployment, each structural change needs a separately reviewed SQL migration with its prerequisites, expected result and tested recovery strategy. Do not rerun `schema.sql`, seed again or drop tables as a shortcut.

1. Rehearse the exact code/database change and rollback on a local restore of the latest data. Prefer additive/backward-compatible changes; record whether the previous code works with the new schema.
2. Schedule a short maintenance window with the demo owner. Enable a tested write block for all normal entry points, allow in-flight transactions to finish, and confirm new requests/pickups/returns cannot write. Use an agreed offline note for any physical return during maintenance and enter it once after service resumes.
3. Export the now-quiescent database and matching covers off-host. Record the previous source revision and schema state. Confirm that this backup is usable from the rehearsal.
4. Upload the reviewed files and apply the migration in the tested order. Database DDL may auto-commit; a failed import can leave partial changes. Check each statement/result and do not assume a surrounding transaction undoes DDL.
5. Run the smoke checks and verify the file manifest while maintenance remains active. If acceptable, reopen access, record release time and take a post-release backup after the verification transactions.

Do not copy a local development database over the hosted database during an update. The hosted records may include newer approvals, pickups and returns.

## Rollback without losing new transactions

Keep maintenance active when a release fails before opening access. Preserve the failed files/error evidence. If the schema remains compatible, restoring the previous code is usually the smallest recovery. If code and database must both revert and **no live transactions occurred after the pre-release backup**, restore the verified pre-release database into a new target, restore matching covers/code, check it, then change private configuration to the verified target. Keep the failed target for diagnosis.

If any requests, approvals, pickups or returns happened after release, **do not replace current data with the old backup**. That would erase real circulation events and can make physical copy status incorrect. Block new writes, export the current database/covers first and keep both pre-release and current snapshots. Prefer a forward fix or a backward-compatible code rollback that retains current data. If a data rollback is unavoidable, reconcile the post-release events from current rows/audit and staff records into a separately restored database, check copy/loan/student limits, and have the responsible librarian validate the result before switching traffic. Audit alone is not a guaranteed full replay log.

After any rollback, repeat login, permissions, request, approval, pickup, return, availability and private-file checks. Reopen only after data and physical circulation agree. Record the incident, chosen recovery, any confirmed loss and the next preventive change.

## Presentation and maintenance routine

The day before and again before the presentation: manually open the canonical URL, verify a full fictional workflow and phone layout, inspect account notices, take a fresh off-host backup, and test the local fallback. Keep student/librarian/admin accounts distinct and their passwords private. Do not invent evaluation results; the planned 20 students and a couple of librarians are prospective participants until an evaluation actually occurs.

During active work, review private error references, disk/cover usage and backup completion daily. Recheck provider policy/runtime changes and keep a clear `PROGRESS.md` entry for the next task. Do not promise guaranteed uptime, email delivery, scheduled jobs or provider restore support. Before any school pilot with real records, separately agree on consent/data handling, access ownership, support and recovery responsibilities.
