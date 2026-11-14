# Local backup and restore: operator instructions

Current procedure, 2026-11-04 (Asia/Manila). Hosting remains deferred. This is a manual local operation outside the website UI; it creates no scheduled jobs or new circulation permissions.

## Create a backup

1. Keep XAMPP MySQL running. Stop the PHP website server with Ctrl+C in its terminal. If it was started hidden in the background, inspect the process listening on port 8000 and its command line in Task Manager before stopping that specific PHP development-server process. Do not stop MySQL or other PHP processes blindly. Close phpMyAdmin editing tabs and pause all imports, password tools, CLI mutations and tests. Wait for in-flight writes to finish; do not edit code/config/covers until backup completes. A stopped browser is not a write pause.
2. Check that `private/` is accessible only to your trusted Windows account/operators: File Explorer → Properties → Security. The helper inherits Windows folder permissions; PHP chmod is not proof of a restrictive Windows ACL. Use protected/encrypted storage for copies containing account hashes, recovery digests, borrowing history and database credentials. Do not use a public/shared/cloud folder without approved protection.
3. From this project's PowerShell terminal, run:

```powershell
$backupName = 'local-' + (Get-Date -Format 'yyyyMMdd-HHmmss')
& 'C:\php-8.4.14\php.exe' bin/local_backup.php $backupName --writes-paused
if ($LASTEXITCODE -ne 0) { throw 'Backup failed. Keep writers paused, retain the partial folder and investigate before retrying with a new name.' }
```

`--writes-paused` is your acknowledgement, not an automated maintenance lock. The helper uses the configured **local** database and XAMPP mysqldump. No password goes in the command line or command history. The backup operation only reads the source database/files. It refuses an existing destination.

4. A successful bundle is at `private/backups/<backupName>/`. It contains:

- `database.sql`: all current InnoDB application tables, structure and data, including binary digests/generated-column definitions; no CREATE DATABASE/USE/DROP/TRUNCATE is requested.
- `covers/`: matching private cover files, unchanged bytes.
- `config.local.php`: a sensitive copy of local settings; **do not activate it blindly on a restore** because it still names the original database.
- `code/`: current app/public/views/bin/migrations, root Markdown/SQL files and .gitignore/.env.example, including uncommitted edits. It excludes Git history, tests, private runtime data and research output; preserve the repository/research separately.
- `manifest.json`: UTC backup times, source database/server version, table counts/definition hashes and SHA-256/byte count for each included file. Private client logs are included. SHA-256 checks detect accidental changes, not malicious replacement of both content and manifest.

No `INCOMPLETE.txt` may remain. No `client-*.cnf` may remain: these contain ephemeral client credentials and are removed in normal success/failure. A process/PC crash can leave one; secure that folder and have the trusted operator remove only the identified ephemeral credential file after confirming its client is no longer running. Do not use partial backups. Failed folders remain for diagnosis; retry to a different name. No secret-bearing error details are printed by the CLI wrapper.

5. Copy the entire completed bundle to a **new** folder outside this repository and every web root, e.g. a protected `C:\Users\63961\KaAklatBackups\<backupName>`. Keep a second protected copy on a separate device/approved encrypted storage. A same-disk private bundle is staging, not an off-device backup. Do not upload bundles to public issues, commits or hosting File Manager. For PowerShell copies, check that the final destination does not already exist and use `Copy-Item -LiteralPath ... -Recurse -ErrorAction Stop`.
6. Verify the copied bundle's manifest using the helper from this project (replace the example directory):

```powershell
& 'C:\php-8.4.14\php.exe' -r 'require "bin/backup_support.php"; verify_backup_files("C:/Users/63961/KaAklatBackups/REPLACE_WITH_BACKUP_NAME"); echo "Files verified\n";'
if ($LASTEXITCODE -ne 0) { throw 'Backup integrity verification failed.' }
```

7. Restart local development only after the backup succeeds:

```powershell
& .\start-local.ps1
```

Record the backup name/time and off-device copy completion privately. During active work back up daily, before schema/deployment changes and before the presentation. RPO ≤24h depends on actually doing this; no schedule or off-device copy was verified by this increment.

## Restore safely into a separate copy

A controlled current-pilot restore passed on 2026-11-14 into a new scratch database and separate private folder; see verification/PILOT_BACKUP_RESTORE_2026-11-14.md. This does not authorize overwriting the pilot or prove recovery from disk loss. The fictional rehearsal below remains useful for repeatable tool checks.

1. Select one complete bundle, verify checksums and inspect SQL. Refuse database selection, destructive DDL or unexpected content. Never import into `kaaklat_dev`, an existing scratch database or your only backup. Never combine a full dump with schema.sql/seed.sql/migrations; the dump already contains the schema.
2. Create a uniquely named empty database. If that name exists, stop and choose a new one. Use a restore-only database credential limited to that target where possible. The rehearsal's configured local operator needs CREATE DATABASE; it never grants broader website permissions. Read every import error; do not use `--force` or continue over a partial import. Choose a fresh target for another attempt.
3. Follow DEPLOYMENT.md's reviewed mysql `source` command, selecting only that new target. Keep the backup's matching code/covers. Place the restored application in a **different private directory**, serve only its `public/`, and restore covers to its `private/covers`. Do not extract a backup under any public/ directory.
4. Create a separate `private/config.local.php` for the restored copy, naming **only** the new database. Use local environment and a new unique `session_name`. Keep the original development config unchanged. Use a new empty session directory, outside public/, and start the restored PHP server on another port with `-d session.save_path=C:/PRIVATE_RESTORE_PATH/private/fresh-sessions`. A different port alone does not isolate browser cookies.
5. Before permitting use, quarantine authentication **on the new restored database only**. In one transaction increment every user's session_version and delete restored password_resets, then commit. Do not delete users, requests, loans or audit history. The new cookie name plus new session directory prevent older/later cookies from coinciding with versions restored from the past; incrementing a restored counter alone cannot rule out such a collision. Old recovery tokens may have become usable again if their consumption happened after the backup, so invalidate all restored tokens and reissue through the existing identity-checked workflow.
6. Password hashes also roll back. A password changed after the backup may no longer work, and an old/compromised password may work again. Preserve any newer safe data/security records before recovery. Reconcile post-backup account status/role/password changes, suspend affected accounts if needed and use existing assisted recovery/local operator tools to assign fresh passwords before reopening. Restoring data does not prove old credentials remain safe. Do not auto-activate accounts or provide shared passwords.
7. Compare all table counts/definitions and key relationships, original request/decision/pickup/return history, covers and derived availability **before** testing mutations. Verify CHECK/FK/unique enforcement, login/status/session revocation and signed-out/direct cover denial. Then run a fictional login → request → approval → pickup → return cycle on that separate copy. Record duration, source backup age and result. A tiny fixture restore's time is not a real-data RTO guarantee.
8. Keep the original and restored resources separate. Do not switch the current website configuration or discard newer circulation events automatically. Production rollback/reconciliation remains a separate DEPLOYMENT.md procedure (T-35).

## Repeat the fictional rehearsal

```powershell
& 'C:\php-8.4.14\php.exe' tests/local_backup_test.php
& 'C:\php-8.4.14\php.exe' tests/backup_restore_test.php
```

Each rehearsal creates new `kaaklat_backup_src_<12hex>` and `kaaklat_restore_<12hex>` databases and `private/restore-rehearsals/<12hex>/`, exports real SQL, restores matching code/covers, tests the application and terminates its own test server. Passwords are random and not printed. It neither resets nor deletes an existing database. Failure details/resources remain private. Unit tests leave fictional files under `tmp/backup-unit-<12hex>/`.

See verification/BACKUP_RESTORE_2026-11-03.md for actual executed results. For eventual cleanup, first compare the exact names printed in the report with the database/directory inventory, confirm all rehearsal processes are stopped and the owner no longer needs them, then request explicit deletion approval. No blanket cleanup command is supplied or executed.


## Controlled pilot restoration — 2026-11-14

Controlled current-pilot backup/restore rehearsal completed locally. Backed up kaaklat_pilot and restored only into new kaaklat_pilot_restore_a4dc97bdafc7 and a separate private application directory. Passed 251 restore checks plus 15 backup safety unit checks: exact rows/definitions across all 12 tables; 86 titles/274 copies/31 requests/16 loans/30 existing accounts preserved; all 143 cover files matched, referenced covers decoded, and derived catalog availability matched. Verified CHECK/FK/username/pending-title/active-copy uniqueness and rollback, scratch-only session/recovery quarantine, fictional authentication and request/approval/pickup/return, authorized original-cover retrieval and private-path denial. Final source comparisons confirmed pilot rows/schema/covers/config/source unchanged during the rehearsal. No accounts or fictional books were added to the pilot.

SQL import measured 0.617s; import-through-restore-verification measured 7.714s on this local run, within the 30-minute local target for the tested dataset. This is not a disaster-recovery guarantee. Original backup plus checksum-verified protected same-disk archive retained; off-device copy, daily RPO evidence and participant evaluation remain outstanding. Scratch server stopped; usual pilot restarted at http://127.0.0.1:8000 and health returned HTTP200/ready. First scratch attempt stopped on an invalid lowercase fictional copy code; corrected to uppercase and rerun in a new scratch. Both attempts retained. No application feature/schema change, commit, push or deployment. Prior unrelated documentation edits preserved.

Evidence: verification/PILOT_BACKUP_RESTORE_2026-11-14.md. Next: protected off-device copy and refreshed current-version performance evidence, then participant evaluation.

The current bundle helper omits start-local.ps1 and nested docs/. This rehearsal retained them separately in the protected archive with a supplemental SHA-256 manifest. Keep the maintained Git repository/research separately; do not assume the bundle is a complete workstation backup.
