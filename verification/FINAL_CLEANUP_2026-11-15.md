# Final project cleanup — 2026-11-15

The maintained application remains at `C:/Users/63961/Desktop/Emman/KaAklat2.0`. Startup stays `& .\start-local.ps1` from that root, with the existing pilot configuration, covers and sessions. Hosting remains deferred.

## Scope and preservation

- Seven root Markdown documents remain: README, AGENTS, PROGRESS, CHANGELOG, SRS, SCHEMA and TEST_PLAN. Other documentation was organized under docs/design, docs/operations, docs/research, docs/catalog and docs/history; docs/README.md is the index. Historical content and research guidance remain available.
- Generated tmp/output, research document exports, old private rehearsal folders, old backups and operator locators were moved outside the application into a protected archive. Moves preserve content with SHA-256 inventories; nothing was deleted from the pilot database. Current config, covers and pilot sessions remain in private/.
- Retired the unreferenced public/assets/landing.css, three optional sample/metadata installer scripts, docs/sample_books.php and seed.sql. Exact copies and hashes are retained outside the repository; source history also retains them. Current schema.sql and numbered migrations remain.
- Fixed a backup coverage gap: working-source bundles now include nested docs and start-local.ps1. Database export, restore safeguards and application behavior are unchanged. Three focused checks were added; the missing-document check failed before the fix and all 18 backup checks passed afterward.

Private archive: `C:/Users/63961/Documents/KaAklat-Archives/final-cleanup-20261115-110918-46556e`. It contains sensitive configuration/database backups, pre-cleanup Git bundle/source ZIP, research output, move/retirement manifests and isolated verification work. Keep it protected. It is on the same device and does not replace an off-device copy. Older phone-verified backups were not overwritten.

## Fresh verification

- Full 36-script regression suite passed on an isolated source copy using a newly created fictional default database. All 448 isolated workflow HTTP assertions passed.
- Chromium navigation: 56 role/theme/viewport layouts and 30 native route checks passed. Entry pages: 64 route/theme/viewport combinations passed. Both suites reported zero runtime exceptions. Sanitized reports are alongside this report.
- All 130 remaining PHP files linted; public JavaScript syntax and local startup configuration guard passed. No app/public/views runtime edits except retiring the unused stylesheet; schema, migrations and launcher content are unchanged. All tested application/operator/test source bytes match the isolated verification copy (hash list retained).
- Pilot read-only before/after fingerprints match exactly for all table rows and definitions, every cover file and private configuration. No pilot fixture accounts, catalog records, schema changes or resets occurred.

Raw test logs and fixture credentials remain in the protected archive, outside Git. Existing tests and dated verification evidence remain in the repository because they support maintenance and research reproducibility.

## Archive verification note

Archiving paused when a pre-move directory enumeration reported a stale 258-byte length for an old rehearsal log. Its original SHA-256 matched the complete archived 93,131-byte stream. All 543 file hashes in that batch were independently checked; original metadata and a corrected manifest were retained. This was an archive inventory metadata issue; no pilot data or application defect was found.

## Limits

These checks establish the tested local behavior; they cannot guarantee the absence of every bug. Physical-device/Safari/screen-reader and actual 400% zoom acceptance for the recent navigation design remain manual. No circulation or locking implementation changed, so concurrency suites were not rerun. The fresh backup is checksum-verified; this cleanup does not claim a new restoration rehearsal or an off-device transfer. Hosting and new features remain deferred.

## Final storage and startup checks

Archived 43,321.0 files across 29 batches (1415.6 MiB), plus the six retired source files and focused cleanup-unit artifacts. The seven root Markdown documents remain; tmp is empty. Current private covers/configuration/pilot sessions remain in place.

The unchanged start-local.ps1 launched the actual pilot on loopback8000. Health returned ready; nine guest/access/static checks passed. PHP's development server returns the public landing fallback (HTTP200) for unknown private/config and schema paths. The check first assumed404, then compared complete response bodies with the landing page and confirmed identical public content, with no private/source disclosure. No application change was needed. Pilot fingerprints still matched after these requests. The launched server was stopped for the final backup and is restarted after verification.

Final cleaned-state backup name: `after-final-cleanup-20261115-46556e-final2` under private/backups, with a protected copy under the external cleanup archive. Before relying on it, verify its manifest and retain an off-device copy; no new restore rehearsal is claimed.

The user authorized the cleanup and local commit. Publishing the cleanup commit to GitHub is deferred to a separate request.
