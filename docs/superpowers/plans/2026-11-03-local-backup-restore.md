# Safe local backup and restore implementation plan

**Goal:** fulfil the requested local backup workflow and execute a fictional, isolated restore rehearsal; no deployment or real-demo recoverability claim.

**Architecture:** reuse XAMPP mysqldump/mysql and current PHP/PDO services. A CLI helper creates a new bundle under ignored private/backups, with SQL, matching covers, configuration and checksum manifest. It requires the operator to confirm writes are paused; it does not implement maintenance mode. A separate test imports the generated dump only into a newly created, uniquely named scratch database and tests the restored services. No application routes/schema changes.

**Constraints:** preserve kaaklat_dev and all existing databases/files; no overwrite, reset, reimport, DROP/TRUNCATE, dependency or hosting work. Credentials travel via a short-lived private option file, never arguments/output. Keep fixture resources for review. The manual workflow must retain matching source including uncommitted changes, protect backups with OS access controls, and make an off-device copy.

- [x] Write/run failing-before/passing-after safety tests: new-only destinations, traversal, failed tools/credential cleanup, checksum/manifest/incomplete handling. Resolved-path/link guards reviewed; OS-link fixture execution remains unverified.
- [x] Implement bin/backup_support.php and CLI-only bin/local_backup.php. New bundle completion marker only after all exports/checks; clean up only the helper's ephemeral credentials file. Includes working source snapshot.
- [x] Build tests/backup_restore_test.php with complete baseline + migration in a new fictional source database, a real generated JPEG and representative lifecycle history. Actual export/import to a different new database;52 checks PASS.
- [x] Run22 regressions,104 HTTP assertions,84 PHP lints and whitespace checks. Exact resources/timing/failures/limits recorded. No performance or hosting claim.
- [x] Update WORKFLOW, DEPLOYMENT, TEST_PLAN, PROGRESS/current summary, CHANGELOG, SRS FR-11 and append actual prompt/outcome. No commit/push.

**Review focus:** existing destination never overwritten; partial bundles never labelled complete; binary reset digests/generated columns survive SQL; restored cookies/secrets cannot revive after quarantine; files remain outside public even when nested paths are requested. Manual write pause and Windows inherited ACLs must be explained as operator responsibilities, not automated guarantees.
