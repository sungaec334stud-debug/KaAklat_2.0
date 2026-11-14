# Controlled pilot backup/restore — 14 November 2026

Controlled current-pilot backup/restore rehearsal completed locally. Backed up kaaklat_pilot and restored only into new kaaklat_pilot_restore_a4dc97bdafc7 and a separate private application directory. Passed 251 restore checks plus 15 backup safety unit checks: exact rows/definitions across all 12 tables; 86 titles/274 copies/31 requests/16 loans/30 existing accounts preserved; all 143 cover files matched, referenced covers decoded, and derived catalog availability matched. Verified CHECK/FK/username/pending-title/active-copy uniqueness and rollback, scratch-only session/recovery quarantine, fictional authentication and request/approval/pickup/return, authorized original-cover retrieval and private-path denial. Final source comparisons confirmed pilot rows/schema/covers/config/source unchanged during the rehearsal. No accounts or fictional books were added to the pilot.

SQL import measured 0.617s; import-through-restore-verification measured 7.714s on this local run, within the 30-minute local target for the tested dataset. This is not a disaster-recovery guarantee. Original backup plus checksum-verified protected same-disk archive retained; off-device copy, daily RPO evidence and participant evaluation remain outstanding. Scratch server stopped; usual pilot restarted at http://127.0.0.1:8000 and health returned HTTP200/ready. First scratch attempt stopped on an invalid lowercase fictional copy code; corrected to uppercase and rerun in a new scratch. Both attempts retained. No application feature/schema change, commit, push or deployment. Prior unrelated documentation edits preserved.

## Resources (private; never commit their contents)

- Source: kaaklat_pilot, MariaDB10.4.32 / selected PHP8.4.14.
- Successful backup: private/backups/pilot-rehearsal-20261114-a4dc97bdafc7/.
- Protected same-disk archive: C:/Users/63961/Documents/KaAklat-Archives/pilot-rehearsal-20261114-a4dc97bdafc7/.
- Successful scratch: kaaklat_pilot_restore_a4dc97bdafc7.
- Private restored app/report: private/restore-rehearsals/pilot-a4dc97bdafc7/restored-app and result.json.
- First attempt: kaaklat_pilot_restore_d4f7288a3357; private/restore-rehearsals/pilot-d4f7288a3357; backup private/backups/pilot-rehearsal-20261114-d4f7288a3357. It failed during a scratch-only fictional copy insert due to lowercase generated code. No application defect or pilot mutation resulted. Kept for diagnosis.
- Unit fixtures: tmp/backup-unit-01f4c8bf6d5b.
- One-off script retained privately: private/operator-tools/pilot_restore_rehearsal_20261114.php. It explicitly guards source kaaklat_pilot, creates unique targets, never imports into source, and preserves failed resources. It is an operator rehearsal, not an application endpoint.

## Method / observed results

No PHP server/writer process was running at preflight; MySQL3306 remained running. Writes were paused throughout snapshot/restore/source comparison. A read-only source connection compared sorted full-row hashes and SHOW CREATE/count inventory to the newly imported target before quarantine/tests. Comparison includes all original users/password hashes/IDs, audit, request/decision/reason/history, loans and security tables; no raw identities, hashes or history were printed in public evidence. Active catalog fields and derived availability compared title-by-title. Backup file manifests, original/restored cover SHA-256 inventories and actual image decoding checked.

The new copy alone received incremented session versions and cleared recovery records (source had zero recovery records). All original session versions were refused there. Unique cookie namespace and empty private session directory prevent pilot session reuse. New random-password fictional accounts existed only in scratch. No existing tester password was used or reset. CHECK/FK/unique constraints and transaction rollback executed on scratch. A fictional borrow/approve/pickup/return cycle passed, as did own-history isolation, signed-out original-cover denial, authenticated exact-byte original-cover retrieval, direct private paths and stale-session rejection. Real pilot account passwords were verified for preservation by exact row comparison, not by attempting real logins.

All12 source table rows/definitions and original covers/config/application source matched the pre-rehearsal state at the end. The backup remained immutable. Source server restarted only after these comparisons; normal user activity after restart may legitimately alter pilot data. Pilot guard passed and health HTTP200 ready observed. Restored server was terminated. Only ordinary pilot loopback server PID5868 was intentionally started; PID is historical, inspect before any later process control.

251 restore assertions and15 backup safety unit checks passed. Import0.617s; import-through-verification7.714s; this excludes manual planning, archive copying and disaster reconciliation. Measured local target passes for this snapshot only. No full application regression rerun because no application source or behavior changed. The one-off PHP script lint passed. No off-device device/copy, backup schedule, crash test, hosted recovery or participant evaluation claimed.

## Protection and dependencies

Both project private directory and new archive ACLs allow only owner, Administrators and SYSTEM. Archive bundle manifest verified after copy; supplemental source includes docs/, start-local.ps1 and one-off script, each hashed in supplemental-manifest.json. The existing helper excludes these from code/: they were deliberately retained separately. Original start-local.ps1 belongs to the maintained pilot and must not be used to launch a restored scratch app. Do not activate copied config.local.php unchanged because it names the source.

A restore brings older password hashes, roles/status and history back. A newer password can stop working and an older compromised password can become valid. Reconcile later security changes before reopening any real restore; use a new cookie/session directory, increment restored session versions and invalidate restored recovery secrets. The rehearsal never switched the actual pilot to the restored database.

## Reproduction / cleanup

From the maintained project, with all local writers paused, the existing backup command remains `& 'C:\php-8.4.14\php.exe' bin/local_backup.php UNIQUE_NAME --writes-paused`. The private operator script is retained for reviewed reproduction with a new scratch name; no secrets are command arguments. `tests/local_backup_test.php` ran independently for15 safety checks. Inspect the private result.json for251 named assertions. Verify copied bundle with bin/backup_support.php verify_backup_files before restoring.

Do not delete backup bundles as scratch cleanup. For eventual cleanup, independently confirm the exact two scratch database names above and private directories, verify their servers are stopped and confirm the owner no longer needs them; get explicit deletion approval. Never drop/truncate/reset kaaklat_pilot, blanket-delete private/, or reactivate an old demo configuration. Scratch copies contain private pilot data and require the same protection as the backup.

Next: copy the protected archive to a separate approved protected device, reverify its manifest, and record that event. Then refresh local performance evidence and run participant evaluation. Hosting stays deferred.
