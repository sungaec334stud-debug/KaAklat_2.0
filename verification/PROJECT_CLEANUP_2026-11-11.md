# Protected project cleanup — 2026-11-11

User authorized the reviewed cleanup and requested functional verification. The maintained application remains the repository root, using kaaklat_pilot. Hosting remains deferred. This is operational housekeeping, not a circulation or design change.

## Preserved and relocated

Consolidation was saved first in local commit `412e40a`; it has not been pushed. A fresh, checksum-verified full pilot backup was created with website writes paused: `private/backups/before-project-cleanup-20261111-002603-ecb1f2`. MySQL remained running. The separate service on port3307 was not changed.

Protected external archive: `C:\Users\63961\Documents\KaAklat-Archives\cleanup-20261111`. It is outside the project and Git, on the same disk. Moving material there reduces project clutter, not total disk usage or off-device risk. It contains private data/configuration and must not be published.

| Original project location | Archive location |
| --- | --- |
| tmp/ | temporary-artifacts/; a new empty project tmp/ is retained |
| output/ | research-output/; all research documents and PDFs preserved |
| college-textbooks.json | catalog-import-sources/college-textbooks.json |
| premium-audit.json | generated-reports/premium-audit.json |
| private/pilots/ | private-snapshots/old-pilots/ |
| private/restore-rehearsals/ | private-snapshots/restore-rehearsals/ |
| private/consolidation-20261111-000829-6c49d1/ | private-snapshots/consolidation-20261111-000829-6c49d1/ |
| Earlier private/backups bundles and legacy catalog JSON | older-backups/ |

The archive manifest records exact paths, file counts and SHA256 inventories. The latest pilot backup is retained in the project and copied to `latest-pilot-backup/` in the archive. The archived startup helper is historical: use the maintained root `start-local.ps1`, not the relocated old launcher. Private locator JSON files are updated to the new archive paths.

Completed inventory:17,033 files (674.2MB) relocated in11 verified moves. Main project afterward:1,517 files/14.6MB including Git and active private files. Archive ACL inheritance is blocked at its protected root; descendants were reset to inherit its owner/Administrators/SYSTEM restrictions (24,040 entries processed, zero failures). No archived data was discarded.

Retained: .git, app, public, views, bin, migrations, tests, verification, docs, schema.sql, fictional seed.sql, documentation, the startup helper, current private configuration, active covers and pilot sessions. Operator tools still depend on sample-source material under docs. No database was dropped, reset, reimported or cleaned. No account, loan, catalog row or cover was removed. Existing unrelated test-plan edits were preserved.

## Verification and reproducibility

Tests run in a protected disposable copy of tracked source at `verification-workspace/source/` under the archive. Its default database is newly created `kaaklat_cleanup_test_159286fe3d6b`, not the pilot. It has separate configuration, covers and sessions. Do not maintain application code in this archived verification copy.

From that isolated source:

```powershell
& 'C:\php-8.4.14\php.exe' tests/run_regression.php
& 'C:\php-8.4.14\php.exe' tests/pickup_http_acceptance_test.php
& 'C:\php-8.4.14\php.exe' tests/backup_restore_test.php
node tests/theme_test.cjs
node tests/theme_browser_check.cjs
```

Seven concurrency entry points were executed separately: borrow_requests, request_decisions, pickups, returns, approved_cancellation, staff_accounts and recovery (`tests/*_concurrency_test.php`). Results:27 regression scripts PASS;313 named HTTP checks PASS;all seven independent-process concurrency suites PASS;52 fictional restore checks PASS. Browser matrix:248 role/page/theme/width combinations PASS, zero JavaScript runtime errors and zero sampled opaque-text contrast failures. Keyboard appearance controls, narrow navigation visibility, student/librarian invalid-input summary focus, storage failure and JavaScript-free theme behavior passed. Theme-controller checks:12 PASS. PHP syntax:109 files PASS;root startup PowerShell parser PASS.

Retained database names:

- kaaklat_cleanup_test_159286fe3d6b (isolated base)
- kaaklat_acceptance_b7b0b6d75927 (HTTP)
- kaaklat_request_test_e644dc13a606
- kaaklat_decision_test_b2f42d35439f
- kaaklat_pickup_test_ca439d984dbb
- kaaklat_return_test_9766aae5b789
- kaaklat_cancel_test_17211416754d
- kaaklat_staff_test_85cbfd88ddde
- kaaklat_recovery_test_270c5dde5cb0
- kaaklat_acceptance_8086f7cd354d (browser)
- kaaklat_backup_src_81b6b9412e18 and kaaklat_restore_81b6b9412e18 (fictional restore;36.75 seconds)

Restore resource names and reports are recorded in protected `verification-workspace/restore.log` and its fixture directory. Raw regression/HTTP/concurrency logs remain there. Original pilot/demo row fingerprints, configuration/cover/session inventories, archive manifest and final pilot verification report remain private in the archive.

A command mistakenly named theme_test.php did not run; it was corrected to the existing Node theme_test.cjs and executed. Initial slow PowerShell temporary-file hashing was stopped before any moves; the replacement relocated artifacts intact and verified their inventories. No temporary originals were discarded. A whitespace check detected an existing extra blank line at TEST_PLAN.md EOF; only trailing whitespace was normalized alongside the appended evidence.

Final post-cleanup verification:52 read-only pilot/config/session/role/access checks PASS. Every pilot row/table definition and the unused demo database matched the pre-cleanup fingerprints. Actual student/librarian/admin protected routes and authenticated cover loads worked; guest/wrong-role access was denied. The guarded root launcher restarted the maintained site on127.0.0.1:8000. Its first restart attempt used an absent conventional PowerShell path and did not start; the installed shell path was then used successfully. The final private report is final-verification.json. Temporary operator sessions were destroyed after checks; no participant password was used or changed.

Final source comparison:124 tracked runtime/operator/test/schema/startup files byte-identical to the isolated tested copy. README34 local file/anchor links and Git whitespace checks passed. Active private config/covers/sessions/backups and tmp remain ignored. Restarted server log contained no PHP fatal/warning/notice/parse diagnostics. Cleanup documentation remains uncommitted for review; only the earlier consolidation commit was created, and nothing was pushed.

## Limits and recovery precautions

Automated browser checks do not certify every visual state, real phone, screen reader or actual400% zoom. Pilot HTTP role checks use operator-created temporary sessions; they do not reset or exercise participants' actual passwords. The fictional restore does not establish that the real pilot SQL backup has been restored successfully. A controlled pilot restore to a separate empty scratch database/folder and an off-device protected backup remain outstanding. No blanket guarantee of zero bugs is made.

Do not copy an archived demo configuration back into the active project. Do not import seed.sql. Recover individual archived files only after checking the manifest and their purpose; replacing current pilot state requires a separate backup and reviewed recovery plan. Retained scratch databases and archived verification fixtures may be removed later only after explicitly checking their exact names against the pilot and preserving needed evidence/backups. No scratch databases were deleted in this task.
