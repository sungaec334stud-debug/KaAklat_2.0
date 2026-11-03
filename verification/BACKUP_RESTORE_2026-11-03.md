# Local backup/restore evidence — 2026-11-03–04 (Asia/Manila)

Base Git revision: 92f1e3241da1a19c6225671dc2cd21ae882adbeb, plus this uncommitted increment. No demo database import/reset/mutation; no commit/push. Hosting deferred.

## Executed evidence

- Test-first safety check initially failed because the helper was absent. Added code-bundle assertion later failed before implementing working-source capture. Final backup safety tests:15 PASS (private destination traversal/existing destination/preserved sentinel, missing tool, failed process/credential cleanup, no password in client error, valid/tampered checksums, relative bundle path, manifest traversal and incomplete marker). Final verification caught a genuine relative-parent path rejection introduced by containment hardening; reproduced with a failing test, fixed by resolving the existing parent first, then15 safety/22 regressions and52 restore checks rerun PASS.
- `tests/backup_restore_test.php`: 52 PASS using actual XAMPP mysqldump/mysql and MariaDB 10.4.32, full baseline + actual migration 001 (no simplified fixture constraints). Exact definitions/counts and every row compared for all 12 tables before security quarantine or workflow mutation. Binary reset digest, CHECK #4025, orphan FK #1452, username and generated active-copy uniqueness #1062, InnoDB rollback passed.
- Representative history: changed-duration approved active loan, returned loan, approved uncollected allocation, rejection and pending cancellation, with original terms/notes/audit. Before restoration: 4 users, 5 books, 5 copies, 5 requests, 3 loans, 14 audit events, 1 author, 5 book-author links, 1 category, 1 throttle bucket/failure/reset each. All restored exactly.
- Derived availability across 5 titles passed. Matching cover hashes/actual JPEG decode, restored password and suspended-login denial, version revocation, reset invalidation, new cookie namespace, fresh HTTP login and subsequent HTTP session revocation passed. Isolated copied source served only public/. Signed-out cover denied; direct private paths returned no private content. PHP development-server fallback may return the landing HTML with HTTP200 for nonexistent paths; checks examine content rather than treating status alone as evidence.
- Full request → approval → actual pickup → return executed against restored data, releasing derived availability; six own-history records visible afterward. The restored server used fresh private session storage, then was stopped in finally.
- Earlier successful rehearsal `c493ecbde3c5`:3.30 seconds; final `4167dc3a3d6a`: **3.90 seconds**, measured from before import/validation through cover/code restoration, authentication/HTTP checks and full workflow; source fixture creation/export excluded. This meets 30 minutes for this tiny fictional local fixture only. Backup and restore occurred in the same run; daily scheduling/RPO and real-data restoration time not verified.
- Regression suite: 22 scripts PASS; existing HTTP acceptance: 104 assertions PASS (`kaaklat_acceptance_dbcd8ae97f2a` retained); PHP lint: 84 files PASS. Git whitespace checks passed with ordinary LF/CRLF normalization warnings. No application routes, business rules or schema changed; existing concurrency behavior was not altered or rerun for this operator-only increment.

## Retained resources

All names below were newly created by this increment; no preexisting scratch database or backup deleted.

| Run suffix | Databases (prefixes kaaklat_backup_src_ / kaaklat_restore_) | Result |
| --- | --- | --- |
| 140c976aa340 | Both names retained | Harness lacked active PHP session; stopped before export. |
| 0f521ac36301 | Both names retained | SQL/constraints succeeded; HTTP test incorrectly demanded404 for PHP landing fallback. Test server stopped. |
| 22b7a376cac3 | Both names retained | 51 checks PASS, 3.23s; before adding source-code backup assertion. |
| 8a5d1efef4c0 | Both names retained | Expected failing source-code-in-bundle assertion (test-first). |
| c493ecbde3c5 | Both names retained | Final52 checks PASS, 3.30s, working-code restoration included. |
| 3d78327411c3 | Both names retained | Latest52 checks PASS,3.73s (2026-11-04 00:03 Manila), after resolved-path containment hardening and separate import logs; original bundle reverified after import. |
| 4167dc3a3d6a | Both names retained | Final52 checks PASS,3.90s (2026-11-04 00:06 Manila), after relative-parent correction. Fresh code restored from bundle; import leaves original bundle unchanged. |

Private directories: `private/restore-rehearsals/<suffix>/`. Latest bundle: `private/restore-rehearsals/4167dc3a3d6a/source/private/backups/snapshot/`; isolated restored code/covers/config/session files: `private/restore-rehearsals/4167dc3a3d6a/restored-app/`; private result.json records exact paths/timing. Unit fixture directories `tmp/backup-unit-009c9df7ad59`, `tmp/backup-unit-e35fab749486`, `tmp/backup-unit-f174f9c3450f`, `tmp/backup-unit-84837f1ca9db`, `tmp/backup-unit-3f2f01fe06ff`, `tmp/backup-unit-a077b670bb3e` retained. Failed relative-path unit run also retained its uniquely named tmp/backup-unit directory; its exact name is listed in the final PROGRESS handoff. Nothing here is a replacement for kaaklat_dev. Later verification runs may create additional names, recorded in TEST_PLAN/PROGRESS.

Eventual cleanup: inspect exact names and private reports, confirm no process uses them, obtain owner authorization for those specific scratch resources; never match/delete kaaklat_dev or use a blanket database reset/cleanup. Retain failed evidence until reviewed.

## Limits and next action

No real-demo backup or restoration performed; no off-device copy, encrypted-storage/Windows ACL verification, crash/power-loss injection, full-data volume RTO, 24-hour backup routine/RPO, production rollback/reconciliation (T-35), hosting or browser visual acceptance claim. CLI flags only acknowledge a manual write pause. A changed-row-count/file check helps detect inconsistency but cannot prove the absence of concurrent updates; stopping all writers is required. Config backup is sensitive and still names the source: a restore must use separate target-only settings. Restoring old hashes/statuses can revive old credentials; reconcile security changes and quarantine sessions/resets before use, as documented.

FR-11/T-33 now has actual **fictional local** restore evidence. Controlled real-demo recovery remains outstanding. Next small increment: performance verification against SRS targets; separate remaining manual account-access acceptance and deployment/evaluation work remain pending.
