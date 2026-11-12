# Real-title catalog expansion — 2026-11-12

User requested more real books in every category, with accurate author/title/category, covers, descriptions and varied copy quantities. Added two titles in each of the19 existing categories:38 books and127 simulated physical-copy records. The pilot now has86 books and273 copies. Categories were reused; no schema, application feature, borrowing policy or theme changes were made. Hosting remains deferred.

The public source manifest and bibliography are [JSON](../docs/catalog-additions-2026-11-12.json) and [readable sources/descriptions](../docs/catalog-additions-2026-11-12.md). Authors, descriptions, work-first-publication years, cover URLs, hashes and saved randomized quantities are recorded there. Counts are2–5 per title and are simulated pilot inventory, not verified university holdings. Textbooks are relevant subject resources, not verified university course adoptions. Foreign jurisdiction examples and edition/work-year distinctions are documented. Descriptions are short original paraphrases.

## Research and cover preparation

Checked Project Gutenberg title records and publisher/university/author sources, including OpenStax's public CMS metadata, BCcampus, Athabasca University Press, University of North Georgia Press, Green Tea Press, Open RN and the Open Textbook Library. One initially considered Gutenberg ID resolved to a Mark Twain essay collection instead of Phantastes; corrected to325 before preparing the manifest. Failed source requests/403s were replaced with accessible university/library metadata and image references. No invented publication or author was added.

All38 covers decoded with the existing catalog JPEG/PNG validator and were flattened/resized/re-encoded to metadata-free JPEG. Four publisher SVG cover cards were rasterized before validation. A contact sheet of all38 images was visually inspected; some Gutenberg edition assets are platform-generated covers. Sources/credits are preserved without asserting blanket rights to all publisher artwork or branding. No full books were downloaded or added to the website.

## Data safety and rehearsal

Exact import rehearsed in new scratch databases `kaaklat_catalog_test_8e391b2398ea` and, after filename correction, `kaaklat_catalog_test_e2576a6b5b6e`, with separate cover directories. No actual account records copied into these fixtures. A scratch-only database trigger forced audit insertion to fail; all inserted rows and new cover files rolled back. Successful import verified38 books/127 copies, exact author/category/availability results. A repeated batch was refused without duplicate rows, covers or audit events. Scratch resources were retained; no databases dropped.

Paused only verified maintained PHP listeners before the full pilot backup. A remaining listener was identified separately and stopped; import ran only after port8000 was no longer listening. Backup:

`private/backups/before-catalog-expansion-20261112-053400-61837c`

The manifest was verified before mutation and after HTTP acceptance. A transaction inserted new books, reused/added authors, linked author credits, created individually coded copies and recorded a non-sensitive operator audit. Original-row comparisons proved that existing accounts/password hashes, books, copies, author links/authors, categories, history and earlier security/audit records were preserved. Original visible catalog rows/availability and existing cover hashes were unchanged. Private config/session inventory and the unused kaaklat_dev database were compared unchanged during the import. No seed import, renumbering, reset or overwrite.

## Executed verification and correction

- Operator PHP syntax and application input/image validation PASS.
- Both scratch audit-failure rollback, successful import and duplicate-batch refusal rehearsals PASS.
- Initial live cover HTTP check found that the operator helper generated `catalog-` filenames, while the existing protected endpoint allowlists `upload-` or Gutenberg filenames. Corrected the helper to `upload-` and reran its scratch rehearsal. Corrected only the38 imported cover references/files in a separate audited transaction; previous names retained privately in the backup report folder. Image bytes unchanged. No cover-endpoint/security relaxation or unrelated book update was made.
- A pagination harness initially requested a page beyond the last page and mistook the application's existing last-page clamping for a duplicate. Corrected the harness to stop after traversing the expected catalog; no pagination code changed.
- Final306 read-only role/catalog HTTP checks PASS: existing student/librarian/admin pages and denials; all38 exact descriptions/authors/categories/copy quantities; all38 authenticated cover byte hashes; signed-out denial for each new cover; all38 combined category/author/title/year/availability searches; all19 category lists; all86 books reachable once across eight catalog pages. Database row hashes unchanged during HTTP acceptance.
- Headless Chrome:276 page/theme/width combinations PASS across eight catalog pages and38 new detail pages, Light/Dark,1440/768/360px. No horizontal page overflow or failed book images detected; zero JavaScript runtime exceptions. No participant screenshots or passwords captured. Temporary operator sessions removed afterward.
- No PHP fatal/warning/notice/parse diagnostics in the restarted server log during final acceptance. Maintained site left running at127.0.0.1:8000.

This is data-only work. Full circulation regression/concurrency suites were not rerun; no new claim about those suites. Automated geometry/image checks are not subjective visual, screen-reader, keyboard, actual-phone or400%-zoom acceptance. Participant password-login and actual lending were not exercised. The source cover sheet was observed separately. No zero-bug or production-readiness guarantee.

## Retained evidence and next action

Protected locator: `private/catalog-expansion-current.json`. Backup subdirectory `catalog-expansion/` contains preservation fingerprints, exact inserted IDs, corrected cover mapping and final HTTP/browser reports. Operator research/staging/rehearsal scripts and images remain in the separate Codex task workspace under `catalog-research/`; no credentials are in the public manifest. Scratch cleanup requires checking the exact retained names first. Do not reimport the batch or seed over the pilot. Full backup remains same-disk, not off-device or controlled real-pilot SQL-restore evidence.

Refresh the catalog, choose a category and open a newly listed book. Confirm the cover, credited names, description and2–5 copies. Reconcile simulated counts with physical university stock before actual lending. Accounts, history and existing catalog metadata remain intact. Existing uncommitted work preserved; no commit/push requested or performed. Usage tool unavailable.
