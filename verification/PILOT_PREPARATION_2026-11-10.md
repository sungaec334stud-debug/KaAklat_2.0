# Catalog-only local pilot preparation — 2026-11-10 (Asia/Manila)

User authorized pausing the demo website, backing up its database/covers/configuration, creating a new pilot database with schema+migrations and no seed, copying only catalog records/covers into a separate application, and isolating its configuration/sessions. Account creation explicitly remains with the user. All source account/transaction data was confirmed demonstration data. No source database deletion, account creation, commit, push or hosting authorized/executed.

## Resources

- Original source: kaaklat_dev, original project/config unchanged.
- Verified backup: private/backups/before-pilot-20261109-173552-e0fd90/. Name/time is UTC; corresponding local date is2026-11-10. Includes full database.sql, all private covers, private configuration, current working code and SHA-256 manifest. No INCOMPLETE marker or ephemeral client credential files remained. Backup is on the same disk; no off-device copy or complete SQL-restore rehearsal claimed.
- New database: kaaklat_pilot. It did not previously exist. Created using schema.sql, migrations001/002; seed.sql not executed. No force/import into an existing target.
- Separate application: private/pilots/kaaklat_pilot/. Outside public/, ignored by Git. Copied matching working source from the backup; referenced covers under its private/covers/. No old account sessions or source private configuration blindly activated.
- Pilot config names only kaaklat_pilot; unique cookie name and private/fresh-sessions directory. Existing local DB credentials reused only within private configuration; no new database-user privilege-isolation claim. Start helper: start-pilot.ps1 inside pilot folder. Do not use a plain PHP start command without its separate session directory.

## Executed results

Verified exact server PID11496 executable/command before stopping only that PHP listener on127.0.0.1:8000. MySQL left running. No other PHP process existed at that checkpoint. No source write tool run by the agent during preparation. Original source table row fingerprints, configuration checksum and complete cover inventory matched before/after.

Catalog records copied by explicit five-table allowlist in foreign-key order, retaining IDs, timestamps, archive flags and relationships. Exact sorted serialized row comparisons and normalized SHOW CREATE structure comparisons passed:

| Table | Rows |
| --- | ---: |
| categories |20|
| authors |111|
| books |129|
| book_authors |180|
| book_copies |389|

All seven other tables were verified empty: users, borrow_requests, loans, audit_events, password_resets, login_failures, login_throttle. No tester accounts, synthetic bootstrap identities or demo audit events created. All archived catalog records retained rather than silently removed. Availability of49 active titles checked against nonarchived copy totals; without old loans all eligible copies are available. These inventory quantities are carried-over test catalog values, not verified physical school holdings.

49 distinct referenced covers copied and checked by SHA-256 and actual image decoding. Original backup retains all source cover files, including unreferenced ones; pilot copies only referenced images. All backup manifest entries reverified after preparation. Matching code copied; no application logic/schema files edited.

New pilot and backup folder ACL inheritance restricted to the existing project owner USER-PC/VANCYYUU, BUILTIN/Administrators and NT AUTHORITY/SYSTEM. Config child ACL inspected and confirmed same access; broader source private-folder permissions were not changed. This is local file access restriction, not encryption or off-device protection.

Pilot PHP process4080 started hidden on loopback127.0.0.1:8010, working directory is the new application, public/ only, with explicit isolated session.save_path. It is intentionally left running for the user handoff. Port8000 demo server remains stopped. health/login/register200; signed-out catalog/profile/staff/settings303 to login; cover403 with empty body. Requests to nonexistent private/config.local.php, private/preparation-report.json and schema.sql returned200 because the PHP development server falls back to public/index.php: byte comparison confirmed only the public landing response, no private contents. Do not misreport those as404 tests.

Detailed private report: pilot private/preparation-report.json. Temporary operator script: tmp/prepare_catalog_pilot.php (not deployed, ignored). All preparation succeeded; no partial database retry or cleanup needed. Original demo data and uncommitted user-reported acceptance docs preserved.

## Handoff and limits

User creates the first administrator from the PILOT folder using bin/bootstrap_admin.php, then creates librarian via Staff accounts and assigns its password with existing bin/set_user_password.php. Students register with school ID and undergo librarian activation. No passwords, real student identities or school records supplied/created by the agent. Stop/review permissions and obtain school/tester approval before actual personal data entry. Keep real-data testing separate from synthetic automated regression fixtures.

Pilot is a separate working copy, not a separate Git checkout. Future source changes in the main repository do not automatically update it. Never run regular reset/seed/import tools against it. Keep both config files distinct. No complete-account login/circulation acceptance possible before the user creates accounts. No real-user/full-pilot readiness, production hosting, or full-demo SQL restoration claim. Backup is local same-disk only. Retain original database and backup; eventual deletion requires a separate explicit decision.

Usage tool unavailable; no percentage invented. No commit/push performed.

## Subsequent port change — 2026-11-10

User authorized moving the pilot to8000 while preserving accounts already created by the user. Verified prior pilot PID4080 and free8000, stopped only that listener, changed pilot start-pilot.ps1 from8010 to8000 and restarted same pilot folder with unchanged fresh-sessions path (PID6036 at handoff). Original project/database configuration unchanged. Private before/after fingerprints confirmed all3 pilot users including password hashes and configuration identical. No account writes. health/login200, signed-out catalog303 login; only8000 now listens. Current pilot URL http://127.0.0.1:8000. Historical8010 references above describe initial preparation only. User created these accounts after the agent's initial empty-account handoff. No credentials recorded here.

## User-authorized test catalog cleanup — 2026-11-10

User requested removal after reviewing exact Test category22 and Acceptance Test Book — Edited book102 with3 copies and no requests/loans. Paused verified pilot listener, created and verified full pilot backup at pilot private/backups/before-test-cleanup-20261109-180537-6200c1 (UTC name), then deleted only2 book-author links,3 copies,1 book and the now-empty category in one transaction with FK checks enabled. Locked/rechecked no borrowing history. Fingerprints of all unrelated rows, including accounts/history/audit, matched before/after; all cover files retained unchanged and author records retained. Postcommit targeted-row absence verified;128 books/19 categories remain. Pilot restarted on127.0.0.1:8000 (PID19816 at checkpoint), login HTTP200. Original kaaklat_dev unchanged. Private report in pilot private/test-catalog-cleanup-report.json. No application feature code changes, commit or push.
