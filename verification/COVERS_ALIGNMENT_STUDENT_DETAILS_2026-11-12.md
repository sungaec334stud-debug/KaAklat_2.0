# Circulation covers, alignment and student details

## Confirmed causes and correction

The review/pickup/return directory enhancement used title initials; the original
record markup did not contain a cover. These were omitted UI images, not evidence
that the stored catalog covers had been deleted.

Read-only circulation list projections now include existing cover/archive metadata.
`views/circulation_ui.php` renders only the existing authenticated `/cover.php?id=`
URL, never a private storage key. The original record header and enhanced summary
both show the cover. Missing/archived covers have an honest fallback; image errors
hide the failed image and show No cover. Selected-panel covers load eagerly while
list thumbnails remain lazy. Existing cover authorization/archive denial is unchanged.

Managed-book categories and availability values now center on the same grid tracks
as their headers; title/author text stays left-aligned. Thumbnail placeholders reset
the global minimum height, which otherwise made a no-cover row unnecessarily tall.
The catalog pagination now has a padded enclosing div. Its button design, renderer,
pagination classes and app.css are unchanged.

Active/suspended student detail cards have a read-only facts section. Librarians see
the existing school ID, registration and recorded status dates. The existing admin
ID restriction is preserved. Dates derive from users.created_at and the existing
student.activate/suspend/reactivate audit events with users entity scope. Times are
formatted in Asia/Manila; missing legacy history says Not recorded. No users columns,
backfilled dates, schema migration, mutation policy or lock-order changes.

## Executed checks

| Check | Result |
| --- | --- |
| Regression runner | 35 scripts passed; includes new cover and student-details helper tests. |
| Existing workflow HTTP | 390 assertions passed, including catalog covers, role/ownership denial, student activation, circulation and recovery. |
| Pagination HTTP | 373 checks passed. |
| Focused cover/alignment/history Chrome | 36 layouts: six states × two themes ×1440/768/360px. Real covers in summary/inspector, blocked-image fallback, column centers, compact placeholders, pagination bottom clearance, librarian IDs, recorded activation/suspension Manila timestamps and administrator ID exclusion passed. Zero runtime exceptions. |
| Theme browser sweep | 248 layouts; zero runtime exceptions and zero sampled text-contrast failures. Keyboard error focus and native appearance/no-script fallback checks passed. |
| Pagination browser sweep | 90 layouts and28 keyboard/no-JS navigations passed; zero runtime exceptions. |
| Lint/static | 128 PHP files linted; new/shared JavaScript syntax and workspace contract passed. Strict UI static audit: zero findings. |
| Pilot preservation | Exact table row/definition fingerprints, covers and configuration matched the fresh baseline. No pilot test accounts, data edits or imports. |

The cover and date helpers were first observed failing because they did not exist,
then passed. Initial focused browser attempts incorrectly checked a record that was
still closed after a narrow initial load; the test now explicitly opens the selected
record before checking its cover. Those attempts are retained and not counted as
passes. The final browser test checks loaded natural image dimensions, not just an
`img` tag. Screenshot review also caught the global no-cover minimum-height issue;
the final run asserts a maximum54px compact placeholder height.

Rendered light-theme pickup, suspended-student and managed-catalog screenshots were
inspected. Both-theme screenshots and browser geometry checks are retained below.
Actual400% zoom, real phones, screen readers and refreshed performance timings remain
unverified. Concurrency was not rerun: mutations/transactions/locks were not changed;
only read projections and UI were changed. Usage reporting unavailable; no percentage
invented. No commit/push or hosting work.

## Retained isolated resources

Source: `C:\Users\63961\Desktop\Emman\KaAklat2.0\private\circulation-covers-2556351f3d88\source`

- Empty source-schema database: `kaaklat_covers_test_2556351f3d88`.
- Final focused browser DB: `kaaklat_acceptance_9f598c65a7e1`.
- Theme browser DB: `kaaklat_acceptance_04008261e1f2`.
- Workflow HTTP DB: `kaaklat_acceptance_a0e51dfedbd2`.
- Pagination HTTP/browser DB: `kaaklat_acceptance_dd43a28e9591`.
- Earlier workspace browser DB: `kaaklat_acceptance_9f973cbf4b80`.
- Earlier workflow DB: `kaaklat_acceptance_1654de543b86`.
- Additional focused attempts and fixture locators remain under this source's `tmp/`;
  each `student-profile-browser-*/fixture.json` names its own separately created DB.
  Nothing was dropped or recursively cleaned up.

Final focused report/screenshots:
`tmp/student-profile-browser-9f598c65a7e1/cover-alignment-results.json` and sibling PNGs.
Raw output: `tmp/covers-final-regression.txt`, `tmp/covers-final-workflow.txt`,
`tmp/covers-pagination.txt`. Theme and pagination JSON reports remain in their named
fixture folders. Private pilot baseline: parent `baseline-private.json`, located via
the maintained root's ignored `private/circulation-covers-current.json`.

Run only from this explicitly isolated source/configuration:

```powershell
& 'C:\php-8.4.14\php.exe' tests/run_regression.php
& 'C:\php-8.4.14\php.exe' tests/pickup_http_acceptance_test.php
& 'C:\php-8.4.14\php.exe' tests/shared_pagination_http_test.php
node tests/cover_alignment_browser_check.cjs
node tests/theme_browser_check.cjs
node tests/shared_pagination_browser_check.cjs tmp/student-profile-browser-dd43a28e9591/fixture.json
```

Do not run fictional cover fixtures against the working pilot source/configuration.
Do not remove pilot records to make a comparison match if live tester activity occurs.

## Local review

Hard-refresh the current app with Ctrl+Shift+R. As a librarian, inspect review/pickup/
return covers and select a different record. Check Manage books column centers and
pagination bottom spacing. Open active/suspended Students and inspect the school ID
and available status dates. Legacy dates may correctly say Not recorded. Repeat in
both themes, at narrow widths and actual400% zoom; check Tab/Enter/Space and error links.
Keep real pilot mutations out of design-only acceptance.
