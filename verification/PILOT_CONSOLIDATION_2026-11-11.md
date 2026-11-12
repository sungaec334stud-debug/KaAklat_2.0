# Single-source pilot consolidation — 2026-11-11 (Asia/Manila)

> Current location update: subsequent authorized cleanup relocated the old pilot/rollback snapshots and earlier backups into the protected external archive. Source consolidation is saved in local commit412e40a. Historical paths and no-commit statements below describe the earlier checkpoint. Use the root launcher and see [cleanup evidence](PROJECT_CLEANUP_2026-11-11.md) for current paths.

## Authorization and resulting runtime

User authorized all steps to consolidate into the maintained main repository while retaining only the existing pilot data as active data. No demo import/merge, account reset, database deletion, commit/push or hosting requested/executed.

- Maintained source: C:/Users/63961/Desktop/Emman/KaAklat2.0.
- Active database: kaaklat_pilot, same database and credentials as before.
- URL: http://127.0.0.1:8000; loopback only.
- Startup: `& .\start-local.ps1` from the repository root. CLI bin/check_local_pilot.php rejects missing/invalid, nonlocal or nonpilot configuration before launching PHP. PHP executable remains C:/php-8.4.14/php.exe.
- Active covers: private/covers, exactly49 files matching the pilot inventory. Old main folder's126 cover files retained in rollback storage, not merged into active covers.
- Active session directory: private/pilot-sessions;20 existing pilot session files copied byte-for-byte while paused; pilot cookie name/configuration retained. Subsequent verification created private operator test sessions; session contents may update normally during requests.
- Active PHP PID19832 at handoff, started by the actual root PowerShell helper (parent4916), with root public/ and explicit private/pilot-sessions. PID numbers are checkpoint evidence, not permanent service identifiers.
- Old private/pilots/kaaklat_pilot remains a rollback snapshot. Its start-pilot.ps1 now delegates to the root helper; ARCHIVED-SNAPSHOT.txt explains it must not be maintained/served independently. No old database or code folder deleted.

## Preservation and backups

Application files under app/public/views/bin/migrations matched between the two copies before changes; only dated documentation differed. Main documentation edits present at entry were preserved. The only new runtime helpers are root startup script and CLI-only configuration guard; application services/permissions/schema were not changed.

Verified full pilot backup: private/pilots/kaaklat_pilot/private/backups/before-main-consolidation-20261111-000828-de0cec/. It contains matching database/config/covers/source plus checksums. This is same-disk storage, not an off-device backup or proof of a full SQL restore.

Rollback directory: private/consolidation-20261111-000829-6c49d1/. Contains previous main configuration, previous main covers, original pilot launcher, before-table fingerprints, original private ACL description and sanitized-operation/private verification reports. Do not blindly activate the old main config: it names the unused demo database. Rollback requires an explicit reviewed plan preserving any later pilot events/covers. Private locator is private/consolidation-current.json.

Every base table's exact row fingerprint and definition/count inventory matched before/after for BOTH kaaklat_pilot and kaaklat_dev. Rechecked after role HTTP checks and the scratch regression suite. Three pilot accounts and password hashes unchanged; no account/borrow/audit/login history imported or modified. Pilot catalog counts remain19 categories,111 authors,128 books,178 book-author links,386 copies. Seven account/security/history table contents were preserved as they existed, not emptied.

New main config is byte-identical to the pilot config, including the existing cookie name. Every referenced pilot cover exists. Main private folder/config/session ACLs restricted to existing Windows owner, administrators and SYSTEM, and private paths remain Git-ignored. No actual identities, passwords, cookie values or database dumps included in this tracked report.

## Executed verification

- Initial read-only inventory could not connect to XAMPP3306. A separate MySQL service on3307 was observed and left untouched. User restarted XAMPP;3306 inventory then succeeded. No database service/data-file repair or port change performed by the agent.
- Identified exact old pilot PHP executable/command/PID16356 before stopping only that process for the write pause. No other writer was started by the agent.
- Full backup manifest verified before switching and after final tests. No seed.sql, SQL imports, schema migration or SQL DML used in the consolidation itself.
- Startup guard first used an inline PHP expression that Windows PowerShell quoting rejected. Replaced it with CLI-only PHP file; do not count the parse-error run as a successful guard test.
- New tests/local_startup_test.php:5 cases PASS in wholly fictional temp files, without reading real private config or connecting to a database. Missing config, demo name, nonlocal environment and other database names rejected; local pilot accepted; no credential output. Added to the existing regression runner for future runs. This task did NOT claim a fresh27-script aggregate run.
- Actual PowerShell root launcher successfully started the maintained root and correct dedicated session path. Script parser and changed PHP syntax checks passed.
-52 read-only consolidation checks PASS: fresh database-backed existing student/librarian/admin sessions, correct allow/deny routes, dashboard/profile/history/settings reads, catalog/cover access, removed test book absent, signed-out redirects and cover403. Operator created temporary private session files without requesting/resetting passwords or modifying account rows. This validates live session/role routing, not a newly executed password-login flow. User should still sign in normally.
- Private/config/schema URL requests return200 public landing fallback with this PHP development server. Byte comparison proved only public landing content, not private files; do not report these as404 responses.
- Full existing HTTP suite:313 named checks PASS, exit0, in newly created fictional kaaklat_acceptance_fa8fad86a990. Its server stopped in harness finally. No fixture writes to pilot. Existing database fingerprints rechecked afterward and identical.
- Original demo database preserved byte-for-row/definition and unused by the website. Exact cover inventory comparison and pilot account preservation checked independently of the startup guard.

Operator scripts and verbose private reports remain in ignored tmp/private locations; no blanket cleanup performed. Existing untracked college-textbooks.json preserved. No code-design or keyboard-behavior change in this task; earlier keyboard follow-up remains unresolved. No screenshots of real participant data or credentials captured.

## Handoff

Open the MAIN folder in VS Code, start XAMPP MySQL and run `& .\start-local.ps1`. Existing server is left running at8000; identify it rather than launching duplicates if the address is in use. Keep root private files/database protected; Git updates source only. Future edits directly affect this local runtime, so pause tester use and verify changes with isolated fictional fixtures before resuming. Avoid any reset/seed/import tool against the pilot. New database migrations need a fresh backup and separate review.

Hosting, full controlled SQL restoration/off-device routine, school evaluation and broader accessibility are separate outstanding work. No account creation required and no demo records returned. Usage tool unavailable; no percentage inferred. No commit/push.

Final documentation/syntax checks:109 PHP files across app/public/views/bin/tests linted,33 README local links/anchors verified, root PowerShell parsed, old launcher target resolved to root start-local.ps1, whitespace check passed. These do not replace functional checks above. Private runtime folders remain ignored by Git.
