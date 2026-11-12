# Lantern Valley demo catalog removal — 2026-11-12

The user explicitly requested removal of the remaining Lantern Valley demo books from kaaklat_pilot, preserving the rest of the system and data. This is a narrowly scoped operator data cleanup, not an application feature or schema change. Hosting remains deferred.

Inspection identified exactly80 archived fictional titles, `The Lantern Valley Notebooks 021` through `100` (book IDs21–100). Each description explicitly identifies an invented practice publication. They had240 DEMO-coded copies,80 book-author links, no cover paths and no requests or loans. Their archived status explains why they remained in the database but were absent from the student catalog. The original Mapmakers seed title was not present under that name and was not targeted by ID alone.

No PHP server was running when the operation began. A full database/configuration/covers/source backup was created and its manifest verified before deletion:

`private/backups/before-lantern-removal-20261112-051953-a61347`

The operator script revalidated the exact title/ID/provenance set, history counts and DEMO copy codes. In one transaction it locked the80 book rows and240 copy rows, deleted only the240 copies,80 author links and80 books, and appended one non-sensitive `catalog.demo_removed` audit event with a NULL operator actor. Foreign-key checks remained enabled. No DROP/TRUNCATE, schema migration, seed import, ID renumbering or account reset was used. Any pre-commit verification/write failure would roll back the deletion and audit together.

Before committing, row comparisons checked that all other table rows were unchanged, allowing only the intended deletions and new audit record. Accounts/password hashes, authors, categories, requests, loans and earlier audit records were preserved. Visible catalog query output and derived copy availability matched exactly. Active covers, configuration and original session-file inventories were unchanged. The unused kaaklat_dev database was independently compared and unchanged.

Afterward: zero Lantern Valley title matches;48 books and146 copies remain. Authors111 and categories19 were retained, including any unreferenced author records; unrelated cleanup was outside scope. All49 cover files were retained. Earlier128-book/386-copy counts describe the pre-removal checkpoint.

## Executed verification

- Operator cleanup PHP syntax PASS.
- Fresh full backup manifest PASS before removal and after HTTP checks.
- Exact target/provenance/history/copy checks and transaction row comparisons PASS.
-38 post-removal read-only HTTP/preservation checks PASS: existing student/librarian/administrator permitted pages, wrong-role denial, catalog search without demo titles, remaining authenticated cover, guest cover denial and guest catalog redirect. All database table-row hashes remained unchanged during these HTTP checks.
- Root guarded start-local.ps1 restarted the maintained pilot at127.0.0.1:8000. Temporary operator-only sessions were removed after verification. No participant password was requested, reset or exercised.
- No application source or business rules changed; the full regression/concurrency/browser suites were not rerun for this catalog-only removal. HTTP checks do not establish fresh visual, keyboard or password-login acceptance.

Protected operation reports and pre-removal fingerprints are in the backup's `operator-removal/` directory; private/lantern-removal-current.json records the exact report/backup paths. They are outside Git and contain private preservation metadata. The backup is on the same disk, not an off-device copy or proof of a controlled pilot SQL restore. Do not restore it over the active pilot to recover individual demo rows: use a separately reviewed scratch restore if ever necessary.

Existing uncommitted documentation edits were preserved. No commit or push was requested or performed for this operation.
